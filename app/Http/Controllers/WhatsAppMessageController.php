<?php

namespace App\Http\Controllers;

use App\Models\MarketingLead;
use App\Services\WhatsAppCloudService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppMessageController
{
    public function store(Request $request, MarketingLead $lead, WhatsAppCloudService $whatsApp): RedirectResponse
    {
        $data = $request->validate([
            'body' => 'required|string|max:4096',
        ]);

        try {
            $response = $whatsApp->sendText($lead, $data['body']);
            $lead->interactions()->create([
                'admin_user_id' => $request->session()->get('growth_admin_id'),
                'type' => 'whatsapp',
                'direction' => 'outbound',
                'external_id' => data_get($response, 'messages.0.id'),
                'body' => $data['body'],
                'meta' => ['recipient_id' => data_get($response, 'contacts.0.wa_id')],
                'occurred_at' => now(),
            ]);

            return back()->with('success', 'Message WhatsApp envoyé et ajouté au journal.');
        } catch (Throwable $exception) {
            Log::warning('WhatsApp message could not be sent.', [
                'lead_id' => $lead->id,
                'exception' => $exception,
            ]);

            return back()->with('error', 'L’envoi WhatsApp a échoué. Vérifiez la configuration Meta et le numéro du prospect.');
        }
    }
}
