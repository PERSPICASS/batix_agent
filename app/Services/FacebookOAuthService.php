<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FacebookOAuthService
{
    private const SCOPES = [
        'pages_show_list',
        'pages_read_engagement',
        'pages_manage_posts',
    ];

    public function configured(): bool
    {
        return filled(config('services.meta.app_id')) && filled(config('services.meta.app_secret'));
    }

    public function authorizationUrl(string $state): string
    {
        $this->ensureConfigured();

        return 'https://www.facebook.com/'.config('services.meta.graph_version').'/dialog/oauth?'.http_build_query([
            'client_id' => config('services.meta.app_id'),
            'redirect_uri' => $this->redirectUri(),
            'state' => $state,
            'scope' => implode(',', self::SCOPES),
            'response_type' => 'code',
        ]);
    }

    public function pagesForCode(string $code): array
    {
        $this->ensureConfigured();

        $shortLivedToken = $this->tokenRequest([
            'client_id' => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'redirect_uri' => $this->redirectUri(),
            'code' => $code,
        ]);

        $longLivedToken = $this->tokenRequest([
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'fb_exchange_token' => $shortLivedToken,
        ]);

        $response = $this->request()
            ->withToken($longLivedToken)
            ->get($this->graphUrl('me/accounts'), [
                'fields' => 'id,name,access_token,tasks',
                'limit' => 100,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Impossible de récupérer les pages Facebook autorisées.');
        }

        return collect($response->json('data', []))
            ->filter(fn ($page) => filled(data_get($page, 'id')) && filled(data_get($page, 'access_token')))
            ->map(fn ($page) => [
                'id' => (string) $page['id'],
                'name' => (string) ($page['name'] ?? 'Page Facebook'),
                'access_token' => (string) $page['access_token'],
            ])
            ->values()
            ->all();
    }

    public function redirectUri(): string
    {
        return config('services.meta.redirect_uri') ?: route('facebook.callback');
    }

    private function tokenRequest(array $query): string
    {
        $response = $this->request()->get($this->graphUrl('oauth/access_token'), $query);

        if ($response->failed() || blank($response->json('access_token'))) {
            throw new RuntimeException('Meta n’a pas pu fournir un jeton d’accès valide.');
        }

        return (string) $response->json('access_token');
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()->timeout(30);
    }

    private function graphUrl(string $endpoint): string
    {
        return sprintf('https://graph.facebook.com/%s/%s', config('services.meta.graph_version'), $endpoint);
    }

    private function ensureConfigured(): void
    {
        if (! $this->configured()) {
            throw new RuntimeException('L’application Meta n’est pas configurée.');
        }
    }
}
