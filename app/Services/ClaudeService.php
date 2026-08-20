<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeService
{
    public function structured(array $schema, string $input, int $maxTokens = 4096): array
    {
        $text = $this->request([
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $input],
            ],
            'output_config' => [
                'format' => [
                    'type' => 'json_schema',
                    'schema' => $schema,
                ],
            ],
        ]);

        $decoded = json_decode($text, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Claude a renvoyé une réponse JSON inexploitable.');
        }

        return $decoded;
    }

    public function text(string $input, int $maxTokens = 8192, ?string $system = null): string
    {
        $payload = [
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $input],
            ],
        ];

        if ($system) {
            $payload['system'] = $system;
        }

        return $this->request($payload);
    }

    private function request(array $payload): string
    {
        $apiKey = config('services.anthropic.api_key');
        if (! $apiKey) {
            throw new RuntimeException('ANTHROPIC_API_KEY n’est pas configurée.');
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])
            ->acceptJson()
            ->timeout(120)
            ->retry(2, 500)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => config('services.anthropic.model', 'claude-sonnet-5'),
                'thinking' => ['type' => 'disabled'],
                ...$payload,
            ]);

        if ($response->failed()) {
            $this->throwApiError($response);
        }

        $stopReason = $response->json('stop_reason');
        if ($stopReason === 'max_tokens') {
            throw new RuntimeException('La réponse de Claude a dépassé la limite de sortie.');
        }
        if ($stopReason === 'refusal') {
            throw new RuntimeException('Claude a refusé cette demande.');
        }

        foreach ($response->json('content', []) as $content) {
            if (($content['type'] ?? null) === 'text' && isset($content['text'])) {
                return $content['text'];
            }
        }

        throw new RuntimeException('Réponse de Claude inexploitable.');
    }

    private function throwApiError(Response $response): never
    {
        $type = $response->json('error.type');
        $suffix = is_string($type) && $type !== '' ? ' ('.$type.')' : '';

        throw new RuntimeException('Anthropic API error '.$response->status().$suffix);
    }
}
