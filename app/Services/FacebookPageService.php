<?php

namespace App\Services;

use App\Models\MarketingContent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class FacebookPageService
{
    public function configured(): bool
    {
        return filled(config('services.meta.page_id')) && filled(config('services.meta.page_access_token'));
    }

    public function publish(MarketingContent $content): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('La page Facebook BatixPro n’est pas configurée.');
        }
        if (! $content->image_path || ! Storage::disk('public')->exists($content->image_path)) {
            throw new RuntimeException('Le visuel du post est introuvable.');
        }

        $response = Http::withToken(config('services.meta.page_access_token'))
            ->acceptJson()
            ->timeout(60)
            ->attach('source', Storage::disk('public')->get($content->image_path), basename($content->image_path))
            ->post($this->photosUrl(), [
                'caption' => $this->captionFor($content),
                'published' => 'true',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Facebook Graph API error '.$response->status());
        }

        return $response->json();
    }

    private function photosUrl(): string
    {
        return sprintf(
            'https://graph.facebook.com/%s/%s/photos',
            config('services.meta.graph_version', 'v21.0'),
            config('services.meta.page_id'),
        );
    }

    private function captionFor(MarketingContent $content): string
    {
        return collect([$content->hook, $content->body, $content->cta])
            ->filter()
            ->implode("\n\n");
    }
}
