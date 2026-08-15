<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'BATIX Growth')</title>
    <link rel="stylesheet" href="/app.css">
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="logo-block">
            <div class="eyebrow">Agent marketing BatixPro</div>
            <div class="logo">BATIX <span>Growth</span></div>
        </div>
        <nav class="nav">
            <a href="{{ route('growth.index') }}" class="{{ request()->routeIs('growth.index') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('campaigns.index') }}" class="{{ request()->routeIs('campaigns.*') ? 'active' : '' }}">Campagnes</a>
            <a href="{{ route('contents.index') }}" class="{{ request()->routeIs('contents.*') ? 'active' : '' }}">Contenus</a>
            <a href="{{ route('leads.index') }}" class="{{ request()->routeIs('leads.*') ? 'active' : '' }}">Prospects</a>
        </nav>
        <div class="sidebar-foot">MVP autonome · validation humaine</div>
    </aside>

    <main class="main">
        <header class="page-head">
            <div>
                <div class="eyebrow">@yield('eyebrow', 'BATIX Growth')</div>
                <h1>@yield('heading', 'Dashboard')</h1>
                @hasSection('subheading')<p>@yield('subheading')</p>@endif
            </div>
            @hasSection('head_action')<div>@yield('head_action')</div>@endif
        </header>

        @if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert err">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif

        @yield('content')
    </main>
</div>
<script>
document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-copy]');
    if (!button) return;
    const target = document.getElementById(button.dataset.copy);
    if (!target) return;
    navigator.clipboard.writeText(target.innerText).then(() => {
        const old = button.innerText;
        button.innerText = 'Copié';
        setTimeout(() => button.innerText = old, 1200);
    });
});
</script>
</body>
</html>
