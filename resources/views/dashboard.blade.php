<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>BATIX Growth</title>
    <link rel="stylesheet" href="/app.css">
</head>
<body>
<div class="wrap">
    <div class="top">
        <div class="brand"><small>Agent marketing BatixPro</small><h1>BATIX Growth</h1></div>
        <div class="badge">IA : {{ $aiConfigured ? 'configurée' : 'non configurée' }}</div>
    </div>
    @if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif

    <div class="grid kpis">
        <div class="kpi"><span>Campagnes</span><strong>{{ $stats['campaigns'] }}</strong></div>
        <div class="kpi"><span>Contenus à valider</span><strong>{{ $stats['draft_contents'] }}</strong></div>
        <div class="kpi"><span>Prospects</span><strong>{{ $stats['leads'] }}</strong></div>
        <div class="kpi"><span>Qualifiés</span><strong>{{ $stats['qualified'] }}</strong></div>
    </div>

    <div class="grid cols">
        <section class="card">
            <h2>Nouvelle campagne</h2>
            <form method="post" action="{{ route('campaigns.store') }}">@csrf
                <div class="field"><label>Nom</label><input name="name" required placeholder="Acquisition quincailleries Abidjan"></div>
                <div class="field"><label>Canal</label><select name="channel"><option>facebook</option><option>instagram</option><option>whatsapp</option><option>tiktok</option><option>linkedin</option></select></div>
                <div class="field"><label>Objectif</label><textarea name="objective" rows="2" required>Générer des conversations WhatsApp qualifiées pour une démonstration BatixPro.</textarea></div>
                <div class="field"><label>Audience</label><textarea name="audience" rows="2" required>Gérants de quincailleries, commerces, grossistes et distributeurs.</textarea></div>
                <div class="field"><label>Offre</label><textarea name="offer" rows="2">Démonstration gratuite de BatixPro.</textarea></div>
                <div class="field"><label>Budget journalier FCFA</label><input type="number" min="0" name="daily_budget"></div>
                <button>Créer la campagne</button>
            </form>
        </section>
        <section class="card">
            <h2>Nouveau prospect</h2>
            <form method="post" action="{{ route('leads.store') }}">@csrf
                <div class="field"><label>Nom</label><input name="name" required></div>
                <div class="field"><label>WhatsApp / téléphone</label><input name="phone" required></div>
                <div class="field"><label>Entreprise</label><input name="company"></div>
                <div class="field"><label>Activité</label><input name="business_type"></div>
                <div class="field"><label>Source</label><select name="source"><option>whatsapp</option><option>facebook</option><option>instagram</option><option>tiktok</option><option>linkedin</option><option>manual</option></select></div>
                <div class="field"><label>Campagne</label><select name="marketing_campaign_id"><option value="">Aucune</option>@foreach($campaigns as $campaign)<option value="{{ $campaign->id }}">{{ $campaign->name }}</option>@endforeach</select></div>
                <div class="field"><label>Notes / besoin détecté</label><textarea name="notes" rows="3"></textarea></div>
                <button>Enregistrer</button>
            </form>
        </section>
    </div>

    <div class="grid three" style="margin-top:16px">
        <section class="card"><h2>Campagnes</h2>
            @forelse($campaigns as $campaign)<div class="item"><h3>{{ $campaign->name }}</h3><div class="muted">{{ $campaign->channel }} · {{ $campaign->status }}</div><p class="muted">{{ $campaign->objective }}</p><form method="post" action="{{ route('campaigns.generate',$campaign) }}">@csrf<button {{ !$aiConfigured ? 'disabled' : '' }}>Générer 3 contenus</button></form></div>@empty<div class="empty">Aucune campagne.</div>@endforelse
        </section>
        <section class="card"><h2>Prospects</h2>
            @forelse($leads as $lead)<div class="item"><h3>{{ $lead->name }} @if($lead->company) — {{ $lead->company }} @endif</h3><div class="muted">{{ $lead->source }} · {{ $lead->status }}</div><div class="score">{{ $lead->score }}/100</div>@if($lead->ai_summary)<p class="body">{{ $lead->ai_summary }}</p><div class="muted">Action : {{ $lead->ai_next_action }}</div>@endif @if($lead->whatsapp_script)<div class="script">{{ $lead->whatsapp_script }}</div>@endif<form class="actions" method="post" action="{{ route('leads.score',$lead) }}">@csrf<button {{ !$aiConfigured ? 'disabled' : '' }}>Qualifier avec l’IA</button></form></div>@empty<div class="empty">Aucun prospect.</div>@endforelse
        </section>
        <section class="card"><h2>Contenus</h2>
            @forelse($contents as $content)<div class="item"><h3>{{ $content->title ?: $content->format }}</h3><div class="muted">{{ $content->channel }} · {{ $content->format }} · {{ $content->status }}</div>@if($content->hook)<p><strong>{{ $content->hook }}</strong></p>@endif<div class="body">{{ $content->body }}</div>@if($content->cta)<p class="muted">CTA : {{ $content->cta }}</p>@endif<div class="actions"><form method="post" action="{{ route('contents.status',$content) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button>Approuver</button></form><form method="post" action="{{ route('contents.status',$content) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="secondary">Rejeter</button></form></div></div>@empty<div class="empty">Aucun contenu.</div>@endforelse
        </section>
    </div>
</div>
</body>
</html>
