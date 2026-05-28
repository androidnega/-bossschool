<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Platform\PaystackSettingsController;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\PlatformSetting;
use App\Models\SmsCreditTransaction;
use App\Services\ActivityLogger;
use App\Services\Payments\PaymentGatewayService;
use App\Services\Payments\PaystackProvider;
use App\Services\Sms\SmsCreditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Tenant-facing SMS-credit top-up flow.
 *
 *   1. School admin lands on /billing/sms-credits (preset bundles).
 *   2. Picks a bundle → POST /billing/sms-credits/buy → we create a
 *      payment_transaction (purpose=sms_credits, metadata.credits=N) and
 *      ask Paystack to initialize a hosted checkout.
 *   3. Paystack redirects the user back to /payments/callback/paystack
 *      where we verify the reference and (synchronously) apply the
 *      verified event — the webhook is a redundant safety net.
 */
class SmsCreditController extends Controller
{
    /** Bundles we present to the school admin. Always integer SMS counts. */
    public const BUNDLES = [100, 500, 1000, 5000, 10000, 25000];

    public function __construct(
        private readonly SmsCreditService $credits,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function index(): View
    {
        $this->authorize('billing.view');
        $tenant = app('currentTenant');

        $bundles = collect(self::BUNDLES)->map(fn (int $n) => [
            'credits' => $n,
            'cost_ghs' => $this->credits->quoteCostGhs($n),
        ])->all();

        $recent = SmsCreditTransaction::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('billing.sms_credits', [
            'tenant' => $tenant,
            'balance' => $this->credits->balance((int) $tenant->id),
            'bundles' => $bundles,
            'pricePesewas' => $this->credits->pricePerSmsPesewas(),
            'paystackEnabled' => (bool) PlatformSetting::getValue(PaystackSettingsController::KEY_ENABLED_SMS),
            'recent' => $recent,
        ]);
    }

    public function purchase(
        Request $request,
        PaymentGatewayService $gateway,
        PaystackProvider $paystack,
    ): RedirectResponse {
        $this->authorize('billing.view');
        $tenant = app('currentTenant');
        $user = $request->user();

        if (! (bool) PlatformSetting::getValue(PaystackSettingsController::KEY_ENABLED_SMS)) {
            return back()->withErrors(['credits' => __('Paystack SMS-credit purchases are not enabled.')]);
        }
        if (! $paystack->isConfigured()) {
            return back()->withErrors(['credits' => __('Paystack is not configured. Ask your SuperAdmin to set the keys.')]);
        }

        $data = $request->validate([
            'credits' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $creditsRequested = (int) $data['credits'];
        $costGhs = $this->credits->quoteCostGhs($creditsRequested);
        if ($costGhs < 1) {
            return back()->withErrors(['credits' => __('Minimum top-up is GHS 1.00. Increase the bundle size.')]);
        }

        $reference = 'sms_'.Str::ulid();

        $transaction = $gateway->initiateGenericPurchase(
            initiator: $user,
            tenantId: (int) $tenant->id,
            purpose: PaymentGatewayService::PURPOSE_SMS_CREDITS,
            providerKey: Payment::PROVIDER_PAYSTACK,
            amount: $costGhs,
            providerReference: $reference,
            checkoutUrl: null,
            metadata: [
                'credits' => $creditsRequested,
                'price_pesewas' => $this->credits->pricePerSmsPesewas(),
                'tenant_subdomain' => (string) $tenant->subdomain,
            ],
            rawRequest: ['ip' => $request->ip(), 'user_id' => $user?->id],
        );

        try {
            $init = $paystack->initializeTransaction(
                email: (string) ($user?->email ?: 'billing@'.($tenant->subdomain ?: 'bossschool').'.local'),
                amountInPesewas: (int) round($costGhs * 100),
                callbackUrl: route('billing.paystack.callback'),
                reference: $reference,
                metadata: [
                    'purpose' => PaymentGatewayService::PURPOSE_SMS_CREDITS,
                    'tenant_id' => (int) $tenant->id,
                    'credits' => $creditsRequested,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('paystack.initialize.exception', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            $transaction->update(['status' => PaymentTransaction::STATUS_FAILED]);

            return back()->withErrors(['credits' => __('Could not start the Paystack checkout: :msg', ['msg' => $e->getMessage()])]);
        }

        $transaction->update(['checkout_url' => $init['authorization_url']]);

        $this->activityLogger->log(
            'sms_credits_purchase_initiated',
            sprintf('Initiated purchase of %d SMS credits (GHS %.2f)', $creditsRequested, $costGhs),
            ['transaction_id' => $transaction->id, 'reference' => $reference],
            (int) $tenant->id,
            PaymentTransaction::class,
            (int) $transaction->id,
        );

        return redirect()->away($init['authorization_url']);
    }
}
