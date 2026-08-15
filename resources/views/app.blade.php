<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#020617">
    @viteReactRefresh
    @vite('resources/js/app.tsx')
    @inertiaHead
</head>
<body class="font-sans antialiased">
    <div id="boot-fallback" style="position:fixed;inset:0;z-index:9999;display:grid;place-items:center;background:#020617;color:#cbd5e1;font-family:ui-sans-serif,system-ui,sans-serif">
        <div style="text-align:center;padding:24px">
            <div style="margin:auto;width:42px;height:42px;border-radius:14px;background:#fbbf24;color:#020617;display:grid;place-items:center;font-weight:900">B</div>
            <div id="boot-fallback-text" style="margin-top:16px;font-weight:700">Chargement de BATIX Growth…</div>
        </div>
    </div>
    @inertia
    <script>
        window.setTimeout(function () {
            var fallback = document.getElementById('boot-fallback');
            var text = document.getElementById('boot-fallback-text');
            if (fallback && text) {
                text.textContent = "L'interface n'a pas démarré. Vérifiez le bundle Vite puis rechargez la page.";
            }
        }, 8000);
    </script>
</body>
</html>
