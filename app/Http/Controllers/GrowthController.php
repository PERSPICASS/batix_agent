<?php

namespace App\Http\Controllers;

use App\Models\MarketingCampaign;
use App\Models\MarketingContent;
use App\Models\MarketingLead;
use App\Services\BatixGrowthAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class GrowthController
{
    public function index(): View
    {
        return view('dashboard', [
            'stats' => [
                'campaigns' => MarketingCampaign::count(),
                'draft_contents' => MarketingContent::where('status', 'draft')->count(),
                'leads' => MarketingLead::count(),
                'qualified' => MarketingLead::whereIn('status', ['qualified', 'demo', 'won'])->count(),
            ],
            'campaigns' => MarketingCampaign::latest()->limit(5)->get(),
            'contents' => MarketingContent::with('campaign')->latest()->limit(5)->get(),
            'leads' => MarketingLead::with('campaign')->latest()->limit(6)->get(),
            'aiConfigured' => (bool) config('services.openai.api_key'),
        ]);
    }

    public function campaigns(): View
    {
        return view('campaigns.index', [
            'campaigns' => MarketingCampaign::withCount(['contents', 'leads'])->latest()->get(),
            'aiConfigured' => (bool) config('services.openai.api_key'),
        ]);
    }

    public function contents(Request $request): View
    {
        $status = $request->string('status')->toString();
        $query = MarketingContent::with('campaign')->latest();
        if (in_array($status, ['draft', 'approved', 'rejected', 'published'], true)) {
            $query->where('status', $status);
        }

        return view('contents.index', [
            'contents' => $query->get(),
            'status' => $status,
        ]);
    }

    public function leads(): View
    {
        return view('leads.index', [
            'leads' => MarketingLead::with('campaign')->latest()->get(),
            'campaigns' => MarketingCampaign::latest()->get(),
            'aiConfigured' => (bool) config('services.openai.api_key'),
        ]);
    }

    public function storeCampaign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:facebook,instagram,whatsapp,tiktok,linkedin',
            'objective' => 'required|string|max:2000',
            'audience' => 'required|string|max:2000',
            'offer' => 'nullable|string|max:2000',
            'daily_budget' => 'nullable|numeric|min:0',
        ]);
        $data['status'] = 'draft';
        MarketingCampaign::create($data);
        return back()->with('success', 'Campagne créée.');
    }

    public function generate(MarketingCampaign $campaign, BatixGrowthAiService $ai): RedirectResponse
    {
        try {
            foreach ($ai->generateCampaignContents($campaign) as $content) {
                MarketingContent::create([
                    'marketing_campaign_id' => $campaign->id,
                    'channel' => $campaign->channel,
                    'format' => $content['format'],
                    'status' => 'draft',
                    'title' => $content['title'],
                    'hook' => $content['hook'],
                    'body' => $content['body'],
                    'cta' => $content['cta'],
                    'meta' => ['generated_by' => 'batix-growth-ai', 'generated_at' => now()->toIso8601String()],
                ]);
            }
            return back()->with('success', '3 contenus générés en brouillon.');
        } catch (Throwable $e) {
            Log::error('BATIX Growth generation failed', ['campaign_id' => $campaign->id, 'exception' => $e]);
            return back()->with('error', 'La génération IA a échoué. Consultez les logs serveur.');
        }
    }

    public function storeLead(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'marketing_campaign_id' => 'nullable|exists:marketing_campaigns,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:40',
            'company' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'source' => 'required|in:facebook,instagram,whatsapp,tiktok,linkedin,manual',
            'notes' => 'nullable|string|max:4000',
        ]);
        $data['status'] = 'new';
        $data['score'] = 0;
        MarketingLead::create($data);
        return back()->with('success', 'Prospect enregistré.');
    }

    public function score(MarketingLead $lead, BatixGrowthAiService $ai): RedirectResponse
    {
        try {
            $result = $ai->scoreLead($lead);
            $nextStatus = match ($result['qualification']) {
                'qualified' => 'qualified',
                'warm' => 'contacted',
                default => 'new',
            };
            if (in_array($lead->status, ['demo', 'won'], true)) $nextStatus = $lead->status;
            $lead->update([
                'score' => $result['score'],
                'status' => $nextStatus,
                'ai_summary' => $result['summary'],
                'ai_next_action' => $result['next_action'],
                'whatsapp_script' => $result['whatsapp_message'],
                'scored_at' => now(),
            ]);
            return back()->with('success', 'Prospect qualifié.');
        } catch (Throwable $e) {
            Log::error('BATIX Growth scoring failed', ['lead_id' => $lead->id, 'exception' => $e]);
            return back()->with('error', 'Le scoring IA a échoué. Consultez les logs serveur.');
        }
    }

    public function updateContentStatus(Request $request, MarketingContent $content): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:draft,approved,rejected,published']);
        $content->update($data);
        return back()->with('success', 'Statut du contenu mis à jour.');
    }
}
