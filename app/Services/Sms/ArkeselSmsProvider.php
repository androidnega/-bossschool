<?php

namespace App\Services\Sms;

class ArkeselSmsProvider extends AbstractSmsProvider
{
    public function key(): string
    {
        return 'arkesel';
    }

    public function send(SmsMessage $message): SmsResult
    {
        return $this->safeRequest(function () use ($message): SmsResult {
            $apiKey = (string) ($this->config['api_key'] ?? '');
            $endpoint = (string) ($this->config['endpoint'] ?? 'https://sms.arkesel.com/api/v2/sms/send');

            if ($apiKey === '') {
                return SmsResult::failure('Arkesel API key is not configured.');
            }

            $response = $this->http()
                ->withHeaders(['api-key' => $apiKey])
                ->asJson()
                ->post($endpoint, [
                    'sender' => $this->senderId($message->senderId),
                    'message' => $message->body,
                    'recipients' => [$message->to],
                ]);

            if (! $response->successful()) {
                return SmsResult::failure('Arkesel HTTP '.$response->status().': '.$response->body());
            }

            $status = (string) ($response->json('status') ?? '');
            if ($status && ! in_array(strtolower($status), ['ok', 'success', 'sent'], true)) {
                return SmsResult::failure('Arkesel responded with status: '.$status);
            }

            $reference = (string) ($response->json('data.0.id')
                ?? $response->json('data.id')
                ?? '');

            return SmsResult::ok($reference !== '' ? $reference : null);
        });
    }
}
