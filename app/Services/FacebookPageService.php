<?php

namespace App\Services;

use App\Models\FacebookConnection;
use App\Models\MarketingContent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class FacebookPageService
{
    public function configured(): bool
    {
        return FacebookConnection::query()->exists()
            || (filled(config('services.meta.page_id')) && filled(config('services.meta.page_access_token')));
    }

    public function connectionDetails(): ?array
    {
        if ($connection = FacebookConnection::query()->first()) {
            return [
                'pageId' => $connection->page_id,
                'pageName' => $connection->page_name,
                'managed' => true,
            ];
        }

        if ($this->configured()) {
            return [
                'pageId' => (string) config('services.meta.page_id'),
                'pageName' => 'Page Facebook',
                'managed' => false,
            ];
        }

        return null;
    }

    public function publish(MarketingContent $content): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('La page Facebook BatixPro n’est pas configurée.');
        }
        $credentials = $this->credentials();
        $request = Http::withToken($credentials['access_token'])
            ->acceptJson()
            ->timeout(60);

        if ($content->image_path && Storage::disk('public')->exists($content->image_path)) {
            $response = $request
                ->attach('source', Storage::disk('public')->get($content->image_path), basename($content->image_path))
                ->post($this->graphUrl($credentials['page_id'], 'photos'), [
                    'caption' => $this->captionFor($content),
                    'published' => 'true',
                ]);
        } else {
            $response = $request->post($this->graphUrl($credentials['page_id'], 'feed'), [
                'message' => $this->captionFor($content),
                'published' => 'true',
            ]);
        }

        if ($response->failed()) {
            throw new RuntimeException('Facebook Graph API error '.$response->status());
        }

        return $response->json();
    }

    private function graphUrl(string $pageId, string $endpoint): string
    {
        return sprintf(
            'https://graph.facebook.com/%s/%s/%s',
            config('services.meta.graph_version', 'v25.0'),
            $pageId,
            $endpoint,
        );
    }

    private function credentials(): array
    {
        if ($connection = FacebookConnection::query()->first()) {
            return [
                'page_id' => $connection->page_id,
                'access_token' => $connection->access_token,
            ];
        }

        return [
            'page_id' => (string) config('services.meta.page_id'),
            'access_token' => (string) config('services.meta.page_access_token'),
        ];
    }

    private function captionFor(MarketingContent $content): string
    {
        return collect([$content->hook, $content->body, $content->cta])
            ->filter()
            ->implode("\n\n");
    }
}
