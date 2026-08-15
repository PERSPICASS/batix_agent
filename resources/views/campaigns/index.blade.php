@extends('layouts.app')
@section('title','Campagnes · BATIX Growth')
@section('heading','Campagnes')
@section('subheading','Créez les campagnes et générez les contenus marketing associés.')
@section('head_action')<span class="pill {{ $aiConfigured ? 'good' : 'warn' }}">IA {{ $aiConfigured ? 'prête' : 'à configurer' }}</span>@endsection
@section('content')
<div class="two-col">
    <section class="panel sticky-panel">
        <div class="panel-head"><div><h2>Nouvelle campagne</h2><p>Définissez l’angle avant génération IA.</p></div></div>
        <form method="post" action="{{ route('campaigns.store') }}" class="form-grid">@csrf
            <label>Nom<input name="name" value="{{ old('name') }}" required placeholder="Acquisition quincailleries Abidjan"></label>
            <label>Canal<select name="channel"><option>facebook</option><option>instagram</option><option>whatsapp</option><option>tiktok</option><option>linkedin</option></select></label>
            <label class="full">Objectif<textarea name="objective" rows="3" required>{{ old('objective','Générer des conversations WhatsApp qualifiées pour une démonstration BatixPro.') }}</textarea></label>
            <label class="full">Audience<textarea name="audience" rows="3" required>{{ old('audience','Gérants de quincailleries, commerces, grossistes et distributeurs.') }}</textarea></label>
            <label class="full">Offre<textarea name="offer" rows="2">{{ old('offer','Démonstration gratuite de BatixPro.') }}</textarea></label>
            <label>Budget journalier FCFA<input type="number" min="0" name="daily_budget" value="{{ old('daily_budget') }}"></label>
            <div class="form-action"><button>Créer la campagne</button></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-head"><div><h2>Campagnes existantes</h2><p>{{ $campaigns->count() }} campagne(s).</p></div></div>
        <div class="stack campaign-stack">
        @forelse($campaigns as $campaign)
            <article class="campaign-card">
                <div class="campaign-top"><div><div class="content-meta"><span class="tag">{{ $campaign->channel }}</span><span class="status {{ $campaign->status }}">{{ $campaign->status }}</span></div><h3>{{ $campaign->name }}</h3></div><div class="metric-inline"><strong>{{ $campaign->contents_count }}</strong><span>contenus</span><strong>{{ $campaign->leads_count }}</strong><span>leads</span></div></div>
                <p><strong>Objectif :</strong> {{ $campaign->objective }}</p>
                <p class="muted"><strong>Audience :</strong> {{ $campaign->audience }}</p>
                @if($campaign->offer)<p class="muted"><strong>Offre :</strong> {{ $campaign->offer }}</p>@endif
                <div class="campaign-actions"><span>{{ $campaign->daily_budget ? number_format((float)$campaign->daily_budget,0,',',' ') . ' FCFA/j' : 'Budget non défini' }}</span><form method="post" action="{{ route('campaigns.generate',$campaign) }}">@csrf<button {{ !$aiConfigured ? 'disabled' : '' }}>Générer 3 contenus</button></form></div>
            </article>
        @empty<div class="empty">Créez votre première campagne.</div>@endforelse
        </div>
    </section>
</div>
@endsection
