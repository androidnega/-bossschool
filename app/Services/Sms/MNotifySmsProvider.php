<?php

namespace App\Services\Sms;

class MNotifySmsProvider extends AbstractSmsProvider
{
    public function key(): string
    {
        return 'mnotify';
    }

    public function send(SmsMessage $message): SmsResult
    {
        return $this->safeRequest(function () use ($message): SmsResult {
            $apiKey = (string) ($this->config['api_key'] ?? '');
            $endpoint = (string) ($this->config['endpoint'] ?? 'https://api.mnotify.com/api/sms/quick');

            if ($apiKey === '') {
                return SmsResult::failure('mNotify API key is not configured.');
            }

            $response = $this->http()
                ->asJson()
                ->post($endpoint.'?key='.urlencode($apiKey), [
                    'recipient' => [$message->to],
                    'sender' => $this->senderId($message->senderId),
                    'message' => $message->body,
                ]);

            if (! $response->successful()) {
                return SmsResult::failure('mNotify HTTP '.$response->status().': '.$response->body());
            }

            $reference = (string) ($response->json('summary.message_id')
                ?? $response->json('message_id')
                ?? '');

            return SmsResult::ok($reference !== '' ? $reference : null);
        });
    }
}
