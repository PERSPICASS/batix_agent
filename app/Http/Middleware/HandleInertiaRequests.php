<?php

namespace App\Http\Middleware;

use App\Services\FacebookOAuthService;
use App\Services\FacebookPageService;
use App\Services\WhatsAppCloudService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'aiConfigured' => fn () => (bool) config('services.anthropic.api_key'),
            'whatsappConfigured' => fn () => app(WhatsAppCloudService::class)->configured(),
            'facebookConfigured' => fn () => app(FacebookPageService::class)->configured(),
            'facebookConnection' => fn () => app(FacebookPageService::class)->connectionDetails(),
            'facebookOAuthConfigured' => fn () => app(FacebookOAuthService::class)->configured(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
