@extends('layouts.app')
@section('title','Prospects · BATIX Growth')
@section('heading','Prospects')
@section('subheading','Centralisez les leads, qualifiez-les et préparez le prochain message commercial.')
@section('head_action')<span class="pill {{ $aiConfigured ? 'good' : 'warn' }}">Scoring IA {{ $aiConfigured ? 'actif' : 'indisponible' }}</span>@endsection
@section('content')
<div class="two-col">
    <section class="panel sticky-panel">
        <div class="panel-head"><div><h2>Nouveau prospect</h2><p>Ajout manuel avant connexion Meta / WhatsApp.</p></div></div>
        <form method="post" action="{{ route('leads.store') }}" class="form-grid">@csrf
            <label>Nom<input name="name" required value="{{ old('name') }}"></label>
            <label>Téléphone / WhatsApp<input name="phone" required value="{{ old('phone') }}"></label>
            <label>Entreprise<input name="company" value="{{ old('company') }}"></label>
            <label>Activité<input name="business_type" value="{{ old('business_type') }}"></label>
            <label>Source<select name="source"><option>whatsapp</option><option>facebook</option><option>instagram</option><option>tiktok</option><option>linkedin</option><option>manual</option></select></label>
            <label>Campagne<select name="marketing_campaign_id"><option value="">Aucune</option>@foreach($campaigns as $campaign)<option value="{{ $campaign->id }}">{{ $campaign->name }}</option>@endforeach</select></label>
            <label class="full">Notes / besoin détecté<textarea name="notes" rows="4">{{ old('notes') }}</textarea></label>
            <div class="form-action"><button>Enregistrer le prospect</button></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-head"><div><h2>Pipeline</h2><p>{{ $leads->count() }} prospect(s) enregistrés.</p></div></div>
        <div class="lead-list">
        @forelse($leads as $lead)
            <article class="lead-card">
                <div class="lead-top"><div><div class="content-meta"><span class="tag">{{ $lead->source }}</span><span class="status {{ $lead->status }}">{{ $lead->status }}</span></div><h3>{{ $lead->name }} @if($lead->company)<span>· {{ $lead->company }}</span>@endif</h3><small>{{ $lead->phone }} @if($lead->business_type) · {{ $lead->business_type }} @endif</small></div><div class="score-ring"><strong>{{ $lead->score }}</strong><span>/100</span></div></div>
                @if($lead->campaign)<p class="muted">Campagne : {{ $lead->campaign->name }}</p>@endif
                @if($lead->notes)<p>{{ $lead->notes }}</p>@endif
                @if($lead->ai_summary)<div class="ai-box"><strong>Analyse IA</strong><p>{{ $lead->ai_summary }}</p><small>Prochaine action : {{ $lead->ai_next_action }}</small></div>@endif
                @if($lead->whatsapp_script)<div class="script" id="wa-{{ $lead->id }}">{{ $lead->whatsapp_script }}</div>@endif
                <div class="actions"><form method="post" action="{{ route('leads.score',$lead) }}">@csrf<button {{ !$aiConfigured ? 'disabled' : '' }}>Qualifier avec l’IA</button></form>@if($lead->whatsapp_script)<button type="button" class="secondary" data-copy="wa-{{ $lead->id }}">Copier WhatsApp</button>@endif</div>
            </article>
        @empty<div class="empty">Aucun prospect. Ajoutez le premier à gauche.</div>@endforelse
        </div>
    </section>
</div>
@endsection
