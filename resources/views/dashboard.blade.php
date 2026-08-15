@extends('layouts.app')
@section('title','Dashboard · BATIX Growth')
@section('heading','Dashboard')
@section('subheading','Vue d’ensemble de l’acquisition, des contenus et des prospects BatixPro.')
@section('head_action')<span class="pill {{ $aiConfigured ? 'good' : 'warn' }}">IA {{ $aiConfigured ? 'configurée' : 'non configurée' }}</span>@endsection
@section('content')
<div class="kpi-grid">
    <div class="kpi"><span>Campagnes</span><strong>{{ $stats['campaigns'] }}</strong><small>toutes campagnes</small></div>
    <div class="kpi"><span>À valider</span><strong>{{ $stats['draft_contents'] }}</strong><small>contenus brouillons</small></div>
    <div class="kpi"><span>Prospects</span><strong>{{ $stats['leads'] }}</strong><small>leads enregistrés</small></div>
    <div class="kpi"><span>Qualifiés</span><strong>{{ $stats['qualified'] }}</strong><small>qualified / demo / won</small></div>
</div>

<div class="dashboard-grid">
    <section class="panel span-2">
        <div class="panel-head"><div><h2>Campagnes récentes</h2><p>Dernières actions d’acquisition.</p></div><a class="text-link" href="{{ route('campaigns.index') }}">Voir tout →</a></div>
        <div class="table-wrap"><table><thead><tr><th>Campagne</th><th>Canal</th><th>Statut</th><th>Budget/j</th></tr></thead><tbody>
        @forelse($campaigns as $campaign)
            <tr><td><strong>{{ $campaign->name }}</strong><small>{{ Str::limit($campaign->objective, 70) }}</small></td><td><span class="tag">{{ $campaign->channel }}</span></td><td>{{ $campaign->status }}</td><td>{{ $campaign->daily_budget ? number_format((float)$campaign->daily_budget,0,',',' ') . ' FCFA' : '—' }}</td></tr>
        @empty<tr><td colspan="4" class="empty">Aucune campagne.</td></tr>@endforelse
        </tbody></table></div>
    </section>

    <section class="panel">
        <div class="panel-head"><div><h2>Prospects récents</h2><p>Priorité commerciale.</p></div><a class="text-link" href="{{ route('leads.index') }}">Voir tout →</a></div>
        <div class="stack">
        @forelse($leads as $lead)
            <div class="mini-row"><div><strong>{{ $lead->name }}</strong><small>{{ $lead->company ?: $lead->business_type ?: $lead->source }}</small></div><div class="score-badge">{{ $lead->score }}/100</div></div>
        @empty<div class="empty">Aucun prospect.</div>@endforelse
        </div>
    </section>

    <section class="panel span-3">
        <div class="panel-head"><div><h2>Derniers contenus</h2><p>Production éditoriale à contrôler avant diffusion.</p></div><a class="text-link" href="{{ route('contents.index') }}">Voir tout →</a></div>
        <div class="content-grid compact">
        @forelse($contents as $content)
            <article class="content-card"><div class="content-meta"><span class="tag">{{ $content->format }}</span><span class="status {{ $content->status }}">{{ $content->status }}</span></div><h3>{{ $content->title ?: ucfirst($content->format) }}</h3><p>{{ Str::limit($content->hook ?: $content->body, 145) }}</p><small>{{ $content->campaign?->name ?: 'Sans campagne' }}</small></article>
        @empty<div class="empty">Aucun contenu généré.</div>@endforelse
        </div>
    </section>
</div>
@endsection
