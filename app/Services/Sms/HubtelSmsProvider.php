<?php

namespace App\Services\Sms;

class HubtelSmsProvider extends AbstractSmsProvider
{
    public function key(): string
    {
        return 'hubtel';
    }

    public function send(SmsMessage $message): SmsResult
    {
        return $this->safeRequest(function () use ($message): SmsResult {
            $clientId = (string) ($this->config['client_id'] ?? '');
            $clientSecret = (string) ($this->config['client_secret'] ?? '');
            $endpoint = (string) ($this->config['endpoint'] ?? 'https://smsc.hubtel.com/v1/messages/send');

            if ($clientId === '' || $clientSecret === '') {
                return SmsResult::failure('Hubtel credentials are not configured.');
            }

            $response = $this->http()
                ->withBasicAuth($clientId, $clientSecret)
                ->asJson()
                ->post($endpoint, [
                    'From' => $this->senderId($message->senderId),
                    'To' => $message->to,
                    'Content' => $message->body,
                ]);

            if (! $response->successful()) {
                return SmsResult::failure('Hubtel HTTP '.$response->status().': '.$response->body());
            }

            $reference = (string) ($response->json('MessageId') ?? $response->json('messageId') ?? '');

            return SmsResult::ok($reference !== '' ? $reference : null);
        });
    }
}
