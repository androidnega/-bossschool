<?php

namespace App\Services\Payments;

class HubtelProvider extends AbstractPaymentProvider
{
    public function key(): string
    {
        return \App\Models\Payment::PROVIDER_HUBTEL;
    }

    protected function signatureHeaderName(): string
    {
        return 'X-Hubtel-Signature';
    }
}
