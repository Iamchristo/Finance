<?php

namespace App\Services;

use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebhookDispatcher
{
    public function dispatch(string $event, User $owner, array $payload): void
    {
        $endpoints = $owner->webhookEndpoints()->active()->get()->filter(
            fn (WebhookEndpoint $endpoint) => $endpoint->subscribesTo($event)
        );

        foreach ($endpoints as $endpoint) {
            $this->deliver($endpoint, $event, $payload);
        }
    }

    private function deliver(WebhookEndpoint $endpoint, string $event, array $payload): void
    {
        $body = [
            'event' => $event,
            'data' => $payload,
            'sent_at' => now()->toIso8601String(),
        ];

        $signature = hash_hmac('sha256', json_encode($body), $endpoint->secret);

        $delivery = [
            'event' => $event,
            'payload' => $body,
        ];

        try {
            $response = Http::withHeaders(['X-Webhook-Signature' => $signature])
                ->timeout(10)
                ->post($endpoint->url, $body);

            $delivery['response_status'] = $response->status();
        } catch (Throwable $e) {
            $delivery['error'] = $e->getMessage();
            Log::warning('Webhook delivery failed', ['endpoint_id' => $endpoint->id, 'message' => $e->getMessage()]);
        }

        $endpoint->deliveries()->create($delivery);
    }
}
