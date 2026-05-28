<?php

namespace App\Services\Sms;

class TwilioSmsProvider extends AbstractSmsProvider
{
    public function key(): string
    {
        return 'twilio';
    }

    public function send(SmsMessage $message): SmsResult
    {
        return $this->safeRequest(function () use ($message): SmsResult {
            $sid = (string) ($this->config['account_sid'] ?? '');
            $token = (string) ($this->config['auth_token'] ?? '');
            $from = (string) ($this->config['from'] ?? '');

            if ($sid === '' || $token === '' || $from === '') {
                return SmsResult::failure('Twilio credentials are not configured.');
            }

            $endpoint = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

            $response = $this->http()
                ->withBasicAuth($sid, $token)
                ->asForm()
                ->post($endpoint, [
                    'From' => $from,
                    'To' => $message->to,
                    'Body' => $message->body,
                ]);

            if (! $response->successful()) {
                return SmsResult::failure('Twilio HTTP '.$response->status().': '.$response->body());
            }

            return SmsResult::ok((string) ($response->json('sid') ?? '') ?: null);
        });
    }
}
