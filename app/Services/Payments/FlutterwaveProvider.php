<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;

class FlutterwaveProvider extends AbstractPaymentProvider
{
    public function key(): string
    {
        return \App\Models\Payment::PROVIDER_FLUTTERWAVE;
    }

    protected function signatureHeaderName(): string
    {
        return 'verif-hash';
    }

    /**
     * Flutterwave uses a static "secret hash" comparison instead of an HMAC
     * of the payload — but the security goal is identical: a request lacking
     * the agreed-upon secret must be rejected.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = $this->signingSecret();
        if (! is_string($secret) || $secret === '') {
            return false;
        }

        $provided = (string) $request->header($this->signatureHeaderName(), '');

        return $provided !== '' && hash_equals($secret, $provided);
    }
}
