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
        $request = Http::withToken(config('services.meta.page_access_token'))
            ->acceptJson()
            ->timeout(60);

        if ($content->image_path && Storage::disk('public')->exists($content->image_path)) {
            $response = $request
                ->attach('source', Storage::disk('public')->get($content->image_path), basename($content->image_path))
                ->post($this->graphUrl('photos'), [
                    'caption' => $this->captionFor($content),
                    'published' => 'true',
                ]);
        } else {
            $response = $request->post($this->graphUrl('feed'), [
                'message' => $this->captionFor($content),
                'published' => 'true',
            ]);
        }

        if ($response->failed()) {
            throw new RuntimeException('Facebook Graph API error '.$response->status());
        }

        return $response->json();
    }

    private function graphUrl(string $endpoint): string
    {
        return sprintf(
            'https://graph.facebook.com/%s/%s/%s',
            config('services.meta.graph_version', 'v21.0'),
            config('services.meta.page_id'),
            $endpoint,
        );
    }

    private function captionFor(MarketingContent $content): string
    {
        return collect([$content->hook, $content->body, $content->cta])
            ->filter()
            ->implode("\n\n");
    }
}
