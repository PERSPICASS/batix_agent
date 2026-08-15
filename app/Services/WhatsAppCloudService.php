<?php

namespace App\Services;

use App\Models\MarketingLead;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppCloudService
{
    public function configured(): bool
    {
        return filled(config('services.whatsapp.graph_version'))
            && filled(config('services.whatsapp.phone_number_id'))
            && filled(config('services.whatsapp.access_token'));
    }

    public function sendText(MarketingLead $lead, string $body): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('WhatsApp Cloud API n’est pas configurée.');
        }

        $response = Http::withToken(config('services.whatsapp.access_token'))
            ->acceptJson()
            ->timeout(30)
            ->post($this->messagesUrl(), [
                'messaging_product' => 'whatsapp',
                'to' => $this->normalizePhone($lead->phone),
                'type' => 'text',
                'text' => ['preview_url' => false, 'body' => $body],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('WhatsApp API error '.$response->status());
        }

        return $response->json();
    }

    public function messagesUrl(): string
    {
        return sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            config('services.whatsapp.graph_version'),
            config('services.whatsapp.phone_number_id'),
        );
    }

    public function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?: '';
    }
}
