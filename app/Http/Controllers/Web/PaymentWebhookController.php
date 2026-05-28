<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentGatewayService;
use App\Services\Payments\PaymentProviderRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Public webhook endpoint for Ghana Mobile Money / card gateways.
 *
 * Flow:
 *  1. Resolve provider — unknown slug returns 404 (no leakage).
 *  2. Verify signature — invalid returns 401. Nothing is mutated otherwise.
 *  3. Parse to normalised event.
 *  4. Delegate to PaymentGatewayService::applyVerifiedEvent so the database
 *     mutation logic is shared with the staff-side initiation flow.
 *
 * Idempotency lives in the service — (provider, provider_reference) is unique
 * so duplicate webhooks update the existing transaction instead of creating
 * a second Payment.
 */
class PaymentWebhookController extends Controller
{
    public function handle(
        Request $request,
        string $provider,
        PaymentProviderRegistry $registry,
        PaymentGatewayService $gateway,
    ): JsonResponse {
        $providerImpl = $registry->get($provider);
        if (! $providerImpl) {
            return response()->json(['error' => 'Provider not found.'], 404);
        }

        if (! $providerImpl->verifyWebhookSignature($request)) {
            Log::warning('payment.webhook.unsigned', [
                'provider' => $provider,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return response()->json(['error' => 'Invalid signature.'], 401);
        }

        $event = $providerImpl->parseWebhookEvent($request);
        $transaction = $gateway->applyVerifiedEvent($provider, $event);

        Log::info('payment.webhook.applied', [
            'provider' => $provider,
            'transaction_id' => $transaction?->id,
            'status' => $transaction?->status,
        ]);

        return response()->json(['ok' => true, 'transaction_id' => $transaction?->id]);
    }
}
