<?php

namespace App\Http\Controllers;

use App\Models\MarketingLead;
use App\Models\MarketingLeadInteraction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController
{
    public function verify(Request $request): Response
    {
        $valid = $request->query('hub_mode') === 'subscribe'
            && hash_equals((string) config('services.meta.verify_token'), (string) $request->query('hub_verify_token'));

        abort_unless($valid, 403);

        return response((string) $request->query('hub_challenge'), 200);
    }

    public function receive(Request $request): Response
    {
        if (! $this->hasValidSignature($request)) {
            Log::warning('WhatsApp webhook rejected: invalid signature.');

            return response('', 403);
        }

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];
                $contacts = collect($value['contacts'] ?? [])->keyBy('wa_id');
                foreach ($value['messages'] ?? [] as $message) {
                    $this->storeMessage($message, $contacts->get($message['from'] ?? ''));
                }
            }
        }

        return response('', 200);
    }

    private function hasValidSignature(Request $request): bool
    {
        $secret = (string) config('services.meta.app_secret');
        $signature = (string) $request->header('X-Hub-Signature-256');
        if ($secret === '' || ! str_starts_with($signature, 'sha256=')) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    private function storeMessage(array $message, mixed $contact): void
    {
        $externalId = $message['id'] ?? null;
        $phone = preg_replace('/\D+/', '', (string) ($message['from'] ?? '')) ?: '';
        if ($externalId === null || $phone === '' || MarketingLeadInteraction::where('external_id', $externalId)->exists()) {
            return;
        }

        $lead = MarketingLead::query()->get()->first(fn (MarketingLead $lead) => preg_replace('/\D+/', '', $lead->phone) === $phone);
        $lead ??= MarketingLead::create([
            'name' => data_get($contact, 'profile.name') ?: 'Prospect WhatsApp',
            'phone' => '+'.$phone,
            'source' => 'whatsapp',
            'status' => 'new',
            'score' => 0,
        ]);
        $body = data_get($message, 'text.body') ?: sprintf('[Message %s reçu]', $message['type'] ?? 'WhatsApp');

        $lead->interactions()->create([
            'type' => 'whatsapp',
            'direction' => 'inbound',
            'external_id' => $externalId,
            'body' => $body,
            'meta' => ['message_type' => $message['type'] ?? null],
            'occurred_at' => isset($message['timestamp']) ? now()->setTimestamp((int) $message['timestamp']) : now(),
        ]);
    }
}
