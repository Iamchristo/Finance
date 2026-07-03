<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenAiClient
{
    private ?string $apiKey;

    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return string|null  The assistant's reply, or null if the AI is unconfigured/unreachable.
     */
    public function chat(array $messages, array $options = []): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $payload = [
                'model' => $options['model'] ?? $this->model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.4,
            ];

            if (isset($options['response_format'])) {
                $payload['response_format'] = $options['response_format'];
            }

            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if (! $response->successful()) {
                Log::warning('OpenAI request failed', ['status' => $response->status()]);

                return null;
            }

            return $response->json('choices.0.message.content');
        } catch (Throwable $e) {
            Log::warning('OpenAI request threw an exception', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
