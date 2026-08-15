@extends('layouts.app')
@section('title','Contenus · BATIX Growth')
@section('heading','Contenus')
@section('subheading','Relisez, approuvez ou rejetez les contenus avant toute diffusion externe.')
@section('head_action')<a class="button-link" href="{{ route('campaigns.index') }}">Générer depuis une campagne</a>@endsection
@section('content')
<div class="filter-bar">
    <a href="{{ route('contents.index') }}" class="{{ !$status ? 'active' : '' }}">Tous</a>
    @foreach(['draft'=>'Brouillons','approved'=>'Approuvés','rejected'=>'Rejetés','published'=>'Publiés'] as $key=>$label)
        <a href="{{ route('contents.index',['status'=>$key]) }}" class="{{ $status === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="content-grid">
@forelse($contents as $content)
    <article class="content-card large">
        <div class="content-meta"><span class="tag">{{ $content->channel }}</span><span class="tag subtle">{{ $content->format }}</span><span class="status {{ $content->status }}">{{ $content->status }}</span></div>
        <h2>{{ $content->title ?: ucfirst($content->format) }}</h2>
        @if($content->campaign)<small>Campagne : {{ $content->campaign->name }}</small>@endif
        @if($content->hook)<div class="hook">{{ $content->hook }}</div>@endif
        <div class="body-copy">{{ $content->body }}</div>
        @if($content->cta)<div class="cta-box"><strong>CTA</strong><span>{{ $content->cta }}</span></div>@endif
        <div class="actions">
            @if($content->status !== 'approved')<form method="post" action="{{ route('contents.status',$content) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button>Approuver</button></form>@endif
            @if($content->status !== 'rejected')<form method="post" action="{{ route('contents.status',$content) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="secondary">Rejeter</button></form>@endif
            @if($content->status !== 'draft')<form method="post" action="{{ route('contents.status',$content) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="draft"><button class="ghost">Remettre en brouillon</button></form>@endif
        </div>
    </article>
@empty<div class="panel empty">Aucun contenu pour ce filtre.</div>@endforelse
</div>
@endsection
