<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Safe default provider used in local/dev and in tests. Logs the SMS to the
 * application log and returns success without ever contacting an external
 * network endpoint. This is what config('sms.default') points at by default.
 */
class LogSmsProvider extends AbstractSmsProvider
{
    public function key(): string
    {
        return 'log';
    }

    public function enabled(): bool
    {
        return true;
    }

    public function send(SmsMessage $message): SmsResult
    {
        Log::info('SMS (log provider) outbound', [
            'to' => $message->to,
            'sender' => $this->senderId($message->senderId),
            'body' => $message->body,
        ]);

        return SmsResult::ok('log_'.Str::ulid());
    }
}
