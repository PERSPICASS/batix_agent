<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateCampaignContents;
use App\Jobs\ScoreMarketingLead;
use App\Models\MarketingCampaign;
use App\Models\MarketingContent;
use App\Models\MarketingLead;
use App\Support\GrowthOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class GrowthController
{
    public function index(): Response
    {
        $pipeline = MarketingLead::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $sources = MarketingLead::query()->selectRaw('source, count(*) as total')->groupBy('source')->orderByDesc('total')->get();
        $campaigns = MarketingCampaign::latest()->limit(5)->get();
        $campaignMetrics = MarketingCampaign::query()->get(['metrics'])->reduce(function (array $totals, MarketingCampaign $campaign): array {
            foreach (['reach', 'clicks', 'conversations', 'demos'] as $metric) {
                $totals[$metric] += (int) ($campaign->metrics[$metric] ?? 0);
            }
            $totals['spend'] += (float) ($campaign->metrics['spend'] ?? 0);

            return $totals;
        }, ['reach' => 0, 'clicks' => 0, 'conversations' => 0, 'demos' => 0, 'spend' => 0]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'campaigns' => MarketingCampaign::count(),
                'active_campaigns' => MarketingCampaign::where('status', 'active')->count(),
                'daily_budget' => (float) MarketingCampaign::where('status', 'active')->sum('daily_budget'),
                'draft_contents' => MarketingContent::where('status', 'draft')->count(),
                'leads' => MarketingLead::count(),
                'qualified' => MarketingLead::whereIn('status', GrowthOptions::QUALIFIED_LEAD_STATUSES)->count(),
            ],
            'pipeline' => collect(GrowthOptions::LEAD_STATUSES)->mapWithKeys(fn (string $status) => [$status => (int) ($pipeline[$status] ?? 0)]),
            'sources' => $sources,
            'campaign_metrics' => $campaignMetrics,
            'campaigns' => $campaigns,
            'contents' => MarketingContent::with('campaign')->latest()->limit(5)->get(),
            'leads' => MarketingLead::with('campaign')->latest()->limit(6)->get(),
        ]);
    }

    public function campaigns(): Response
    {
        return Inertia::render('Campaigns/Index', [
            'campaigns' => MarketingCampaign::withCount(['contents', 'leads'])->latest()->get(),
        ]);
    }

    public function contents(Request $request): Response
    {
        $status = $request->string('status')->toString();
        $query = MarketingContent::with('campaign')->latest();
        if (in_array($status, GrowthOptions::CONTENT_STATUSES, true)) {
            $query->where('status', $status);
        }

        return Inertia::render('Contents/Index', [
            'contents' => $query->get(),
            'status' => $status,
        ]);
    }

    public function leads(): Response
    {
        $status = request()->string('status')->toString();
        $query = MarketingLead::with(['campaign', 'interactions.author'])->latest();
        if (in_array($status, GrowthOptions::LEAD_STATUSES, true)) {
            $query->where('status', $status);
        }

        return Inertia::render('Leads/Index', [
            'leads' => $query->get(),
            'campaigns' => MarketingCampaign::latest()->get(),
            'status' => $status,
        ]);
    }

    public function storeCampaign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => ['required', Rule::in(GrowthOptions::CAMPAIGN_CHANNELS)],
            'objective' => 'required|string|max:2000',
            'audience' => 'required|string|max:2000',
            'offer' => 'nullable|string|max:2000',
            'daily_budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        $data['status'] = 'draft';
        MarketingCampaign::create($data);

        return back()->with('success', 'Campagne créée.');
    }

    public function generate(MarketingCampaign $campaign): RedirectResponse
    {
        if (in_array($campaign->content_generation_status, ['queued', 'processing'], true)) {
            return back()->with('error', 'Une génération est déjà en cours pour cette campagne.');
        }

        try {
            $campaign->update([
                'content_generation_status' => 'queued',
                'content_generation_attempts' => 0,
                'content_generation_error' => null,
                'content_generation_started_at' => null,
                'content_generation_completed_at' => null,
            ]);
            GenerateCampaignContents::dispatch($campaign->id);

            return back()->with('success', 'Génération IA programmée. Les brouillons apparaîtront dans quelques instants.');
        } catch (Throwable $e) {
            Log::error('BATIX Growth generation dispatch failed', ['campaign_id' => $campaign->id, 'exception' => $e]);
            $campaign->update([
                'content_generation_status' => 'failed',
                'content_generation_error' => 'Impossible de programmer la génération IA.',
                'content_generation_completed_at' => now(),
            ]);

            return back()->with('error', 'La génération IA a échoué. Vérifiez la configuration Claude puis réessayez.');
        }
    }

    public function updateCampaign(Request $request, MarketingCampaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', 'required', Rule::in(GrowthOptions::CAMPAIGN_STATUSES)],
            'daily_budget' => 'sometimes|nullable|numeric|min:0',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
            'metrics' => 'sometimes|array',
            'metrics.reach' => 'nullable|integer|min:0',
            'metrics.clicks' => 'nullable|integer|min:0',
            'metrics.conversations' => 'nullable|integer|min:0',
            'metrics.demos' => 'nullable|integer|min:0',
            'metrics.spend' => 'nullable|numeric|min:0',
        ]);
        if (array_key_exists('metrics', $data)) {
            $data['metrics'] = array_filter($data['metrics'], fn ($value) => $value !== null);
        }
        $campaign->update($data);

        return back()->with('success', 'Suivi de campagne mis à jour.');
    }

    public function storeLead(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'marketing_campaign_id' => 'nullable|exists:marketing_campaigns,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:40',
            'company' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'source' => ['required', Rule::in(GrowthOptions::LEAD_SOURCES)],
            'notes' => 'nullable|string|max:4000',
        ]);
        $data['status'] = 'new';
        $data['score'] = 0;
        MarketingLead::create($data);

        return back()->with('success', 'Prospect enregistré.');
    }

    public function score(MarketingLead $lead): RedirectResponse
    {
        if (in_array($lead->scoring_status, ['queued', 'processing'], true)) {
            return back()->with('error', 'Un scoring est déjà en cours pour ce prospect.');
        }

        try {
            $lead->update([
                'scoring_status' => 'queued',
                'scoring_attempts' => 0,
                'scoring_error' => null,
                'scoring_started_at' => null,
                'scoring_completed_at' => null,
            ]);
            ScoreMarketingLead::dispatch($lead->id);

            return back()->with('success', 'Scoring IA programmé. Le résultat apparaîtra dans quelques instants.');
        } catch (Throwable $e) {
            Log::error('BATIX Growth scoring dispatch failed', ['lead_id' => $lead->id, 'exception' => $e]);
            $lead->update([
                'scoring_status' => 'failed',
                'scoring_error' => 'Impossible de programmer le scoring IA.',
                'scoring_completed_at' => now(),
            ]);

            return back()->with('error', 'Le scoring IA n’a pas pu être programmé. Consultez les logs serveur.');
        }
    }

    public function updateContentStatus(Request $request, MarketingContent $content): RedirectResponse
    {
        if ($content->status === 'published') {
            return back()->with('error', 'Un contenu déjà publié ne peut plus être modifié.');
        }

        $data = $request->validate(['status' => ['required', Rule::in(GrowthOptions::CONTENT_STATUSES)]]);
        $content->update($data);

        return back()->with('success', 'Statut du contenu mis à jour.');
    }

    public function updateLeadStatus(Request $request, MarketingLead $lead): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(GrowthOptions::LEAD_STATUSES)]]);
        $lead->update($data);

        return back()->with('success', 'Étape commerciale mise à jour.');
    }

    public function storeLeadInteraction(Request $request, MarketingLead $lead): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(GrowthOptions::INTERACTION_TYPES)],
            'body' => 'required|string|max:4000',
            'occurred_at' => 'nullable|date',
        ]);
        $lead->interactions()->create([
            ...$data,
            'admin_user_id' => $request->session()->get('growth_admin_id'),
            'occurred_at' => $data['occurred_at'] ?? now(),
        ]);

        return back()->with('success', 'Interaction ajoutée au journal commercial.');
    }
}
