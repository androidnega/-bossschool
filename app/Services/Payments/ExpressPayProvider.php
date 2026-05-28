<?php

namespace App\Services\Payments;

class ExpressPayProvider extends AbstractPaymentProvider
{
    public function key(): string
    {
        return \App\Models\Payment::PROVIDER_EXPRESSPAY;
    }

    protected function signatureHeaderName(): string
    {
        return 'X-ExpressPay-Signature';
    }
}
