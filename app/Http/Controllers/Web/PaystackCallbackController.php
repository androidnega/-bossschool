<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Services\Payments\PaymentGatewayService;
use App\Services\Payments\PaystackProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Synchronous Paystack callback (the human-facing redirect).
 *
 * Paystack appends `?reference=…` to the URL we registered as `callback_url`
 * when initializing the transaction. We do NOT trust this redirect blindly —
 * the user could replay it — instead we call `verify` server-to-server and
 * route the verified event through the existing gateway pipeline. The async
 * webhook (POST /api/webhooks/payments/paystack) is the safety net: this
 * synchronous path just makes the UX snappier so the user sees the result
 * immediately rather than waiting for the webhook to fire.
 */
class PaystackCallbackController extends Controller
{
    public function handle(
        Request $request,
        PaystackProvider $paystack,
        PaymentGatewayService $gateway,
    ): RedirectResponse {
        $reference = trim((string) $request->query('reference', ''));
        if ($reference === '') {
            return redirect()->route('billing.sms-credits.index')
                ->withErrors(['paystack' => __('Missing reference in Paystack callback.')]);
        }

        // Look up the transaction we recorded at /purchase time. If we have
        // never heard of this reference, refuse (probably a forged callback).
        $transaction = PaymentTransaction::query()
            ->where('provider', Payment::PROVIDER_PAYSTACK)
            ->where('provider_reference', $reference)
            ->first();

        if (! $transaction) {
            Log::warning('paystack.callback.unknown_reference', ['reference' => $reference]);

            return redirect()->route('billing.sms-credits.index')
                ->withErrors(['paystack' => __('We could not find that transaction. If you were charged, please contact support.')]);
        }

        // Tenant scoping: the user that bounced back from Paystack must
        // belong to the same tenant the transaction was started against.
        // Without this an attacker who guesses a reference could apply
        // someone else's payment to their own session.
        $tenant = app('currentTenant');
        if ((int) $transaction->tenant_id !== (int) ($tenant?->id ?? -1)) {
            Log::warning('paystack.callback.cross_tenant', [
                'reference' => $reference,
                'tenant_callback' => $tenant?->id,
                'tenant_transaction' => $transaction->tenant_id,
            ]);

            return redirect()->route('home')
                ->withErrors(['paystack' => __('Transaction does not belong to this school.')]);
        }

        try {
            $verified = $paystack->verifyTransaction($reference);
        } catch (\Throwable $e) {
            Log::warning('paystack.callback.verify_failed', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('billing.sms-credits.index')
                ->withErrors(['paystack' => __('Could not verify payment yet. Please check back shortly.')]);
        }

        // Feed the verified event through the same pipeline the webhook uses.
        // The pipeline is idempotent on (provider, provider_reference) so a
        // subsequent webhook callback for the same reference is a no-op.
        $gateway->applyVerifiedEvent(Payment::PROVIDER_PAYSTACK, [
            'provider_reference' => $verified['reference'],
            'amount' => $verified['amount'],
            'currency' => $verified['currency'],
            'status' => $verified['status'],
            'payment_channel' => $verified['channel'] ?: 'card',
            'raw' => $verified['raw'],
        ]);

        $purpose = (string) $transaction->purpose;

        if ($verified['status'] !== PaymentTransaction::STATUS_SUCCESSFUL) {
            $route = $purpose === PaymentGatewayService::PURPOSE_SUBSCRIPTION
                ? 'billing.plans'
                : 'billing.sms-credits.index';

            return redirect()->route($route)
                ->withErrors(['paystack' => __('Paystack reported the payment as :status. Try again or use another payment method.', ['status' => $verified['status']])]);
        }

        if ($purpose === PaymentGatewayService::PURPOSE_SUBSCRIPTION) {
            return redirect()->route('billing.index')
                ->with('status', __('Subscription activated. Thank you for your payment.'));
        }

        $credits = (int) ($transaction->metadata['credits'] ?? 0);

        return redirect()->route('billing.sms-credits.index')
            ->with('status', __(':n SMS credits added to your balance.', ['n' => number_format($credits)]));
    }
}
