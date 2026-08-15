<?php

namespace App\Http\Middleware;

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
            'aiConfigured' => fn () => (bool) config('services.openai.api_key'),
            'whatsappConfigured' => fn () => app(WhatsAppCloudService::class)->configured(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
