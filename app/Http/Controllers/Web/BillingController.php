<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Platform\PaystackSettingsController;
use App\Http\Requests\Billing\BillingSubscribeRequest;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\Subscription;
use App\Services\ActivityLogger;
use App\Services\Payments\PaymentGatewayService;
use App\Services\Payments\PaystackProvider;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(): View
    {
        $this->authorize('billing.view');

        $tenant = app('currentTenant');
        $tenant->load('plan');

        $activeSubscription = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->orderByDesc('start_date')
            ->with('plan')
            ->first();

        return view('billing.index', [
            'tenant' => $tenant,
            'activeSubscription' => $activeSubscription,
        ]);
    }

    public function plans(): View
    {
        $this->authorize('billing.view');

        $plans = Plan::query()->orderBy('price')->get();
        $tenant = app('currentTenant');
        $tenant->load('plan');

        $paystackEnabled = (bool) PlatformSetting::getValue(PaystackSettingsController::KEY_ENABLED_SUB);

        return view('billing.plans', compact('plans', 'tenant', 'paystackEnabled'));
    }

    public function subscribe(BillingSubscribeRequest $request, Plan $plan): RedirectResponse
    {
        $tenant = app('currentTenant');

        DB::transaction(function () use ($tenant, $plan): void {
            $today = Carbon::today();

            Subscription::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', Subscription::STATUS_ACTIVE)
                ->update([
                    'status' => Subscription::STATUS_CANCELLED,
                    'end_date' => $today,
                ]);

            Subscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'start_date' => $today,
                'end_date' => $today->copy()->addYear(),
                'status' => Subscription::STATUS_ACTIVE,
                'payment_id' => null,
            ]);

            $tenant->update(['plan_id' => $plan->id]);
        });

        return redirect()->route('billing.index')->with('status', __('Subscription updated.'));
    }

    /**
     * Pay for a plan via Paystack. Initiates a generic-purpose transaction
     * (purpose=subscription, metadata.plan_id=…) and redirects the user to
     * the Paystack-hosted checkout. The webhook + synchronous callback both
     * land in PaymentGatewayService::applySubscriptionPurchase which is the
     * single source of truth for "the subscription is paid".
     */
    public function subscribeWithPaystack(
        Request $request,
        Plan $plan,
        PaymentGatewayService $gateway,
        PaystackProvider $paystack,
        ActivityLogger $logger,
    ): RedirectResponse {
        $this->authorize('billing.view');

        if (! (bool) PlatformSetting::getValue(PaystackSettingsController::KEY_ENABLED_SUB)) {
            return back()->withErrors(['plan' => __('Paystack subscription payments are not enabled.')]);
        }
        if (! $paystack->isConfigured()) {
            return back()->withErrors(['plan' => __('Paystack is not configured. Ask your SuperAdmin to set the keys.')]);
        }

        $tenant = app('currentTenant');
        $user = $request->user();

        $amount = round((float) $plan->price, 2);
        if ($amount <= 0) {
            return back()->withErrors(['plan' => __('This plan has no price; activate the free subscribe button instead.')]);
        }

        $reference = 'sub_'.Str::ulid();

        $transaction = $gateway->initiateGenericPurchase(
            initiator: $user,
            tenantId: (int) $tenant->id,
            purpose: PaymentGatewayService::PURPOSE_SUBSCRIPTION,
            providerKey: Payment::PROVIDER_PAYSTACK,
            amount: $amount,
            providerReference: $reference,
            checkoutUrl: null,
            metadata: [
                'plan_id' => (int) $plan->id,
                'plan_name' => (string) $plan->name,
                'billing_cycle' => (string) ($plan->billing_cycle ?: 'monthly'),
                'tenant_subdomain' => (string) $tenant->subdomain,
            ],
            rawRequest: ['ip' => $request->ip(), 'user_id' => $user?->id],
        );

        try {
            $init = $paystack->initializeTransaction(
                email: (string) ($user?->email ?: 'billing@'.($tenant->subdomain ?: 'bossschool').'.local'),
                amountInPesewas: (int) round($amount * 100),
                callbackUrl: route('billing.paystack.callback'),
                reference: $reference,
                metadata: [
                    'purpose' => PaymentGatewayService::PURPOSE_SUBSCRIPTION,
                    'tenant_id' => (int) $tenant->id,
                    'plan_id' => (int) $plan->id,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('paystack.subscription.initialize.exception', [
                'reference' => $reference,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
            $transaction->update(['status' => PaymentTransaction::STATUS_FAILED]);

            return back()->withErrors(['plan' => __('Could not start the Paystack checkout: :msg', ['msg' => $e->getMessage()])]);
        }

        $transaction->update(['checkout_url' => $init['authorization_url']]);

        $logger->log(
            'subscription_purchase_initiated',
            sprintf('Initiated Paystack purchase of plan "%s" (GHS %.2f)', $plan->name, $amount),
            ['transaction_id' => $transaction->id, 'reference' => $reference, 'plan_id' => $plan->id],
            (int) $tenant->id,
            PaymentTransaction::class,
            (int) $transaction->id,
        );

        return redirect()->away($init['authorization_url']);
    }

    public function history(): View
    {
        $this->authorize('billing.view');

        $tenant = app('currentTenant');

        $subscriptions = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->with('plan')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('billing.history', compact('subscriptions'));
    }
}
