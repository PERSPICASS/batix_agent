<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateMarketingImage;
use App\Models\MarketingContent;
use App\Services\BatixGrowthAiService;
use App\Services\FacebookPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class FacebookPostController
{
    public function store(Request $request, BatixGrowthAiService $ai): RedirectResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|max:1000',
            'audience' => 'required|string|max:1000',
            'offer' => 'nullable|string|max:1000',
        ]);

        try {
            $post = $ai->generateFacebookPost($data['subject'], $data['audience'], $data['offer'] ?? null);
            $content = MarketingContent::create([
                'channel' => 'facebook',
                'format' => 'post',
                'status' => 'draft',
                'title' => $post['title'],
                'hook' => $post['hook'],
                'body' => $post['body'],
                'cta' => $post['cta'],
                'meta' => ['generated_by' => 'batix-growth-ai', 'generated_at' => now()->toIso8601String()],
            ]);
            GenerateMarketingImage::dispatch($content->id);

            return back()->with('success', 'Post Facebook créé. Son visuel est en cours de génération.');
        } catch (Throwable $exception) {
            Log::warning('Facebook post could not be generated.', ['exception' => $exception]);

            return back()->with('error', 'La génération du post a échoué. Vérifiez la clé OpenAI et réessayez.');
        }
    }

    public function generateImage(MarketingContent $content): RedirectResponse
    {
        if (! $this->isFacebookPost($content)) {
            return back()->with('error', 'Un visuel ne peut être généré que pour un post Facebook.');
        }
        if (in_array($content->image_generation_status, ['queued', 'processing'], true)) {
            return back()->with('error', 'La génération du visuel est déjà en cours.');
        }

        $content->update([
            'image_generation_status' => 'queued',
            'image_generation_error' => null,
        ]);
        GenerateMarketingImage::dispatch($content->id);

        return back()->with('success', 'Génération du visuel Facebook programmée.');
    }

    public function publish(MarketingContent $content, FacebookPageService $facebook): RedirectResponse
    {
        if (! $this->isFacebookPost($content) || $content->status !== 'approved') {
            return back()->with('error', 'Seul un post Facebook approuvé peut être publié.');
        }
        if ($content->facebook_post_id) {
            return back()->with('error', 'Ce post a déjà été publié sur Facebook.');
        }

        try {
            $result = $facebook->publish($content);
            $content->update([
                'status' => 'published',
                'published_at' => now(),
                'facebook_post_id' => data_get($result, 'post_id') ?: data_get($result, 'id'),
                'facebook_publish_error' => null,
            ]);

            return back()->with('success', 'Post publié sur la page Facebook BatixPro.');
        } catch (Throwable $exception) {
            Log::warning('Facebook post could not be published.', [
                'content_id' => $content->id,
                'exception' => $exception,
            ]);
            $content->update(['facebook_publish_error' => 'La publication Facebook a échoué.']);

            return back()->with('error', 'La publication Facebook a échoué. Vérifiez les accès Meta et réessayez.');
        }
    }

    private function isFacebookPost(MarketingContent $content): bool
    {
        return $content->channel === 'facebook' && $content->format === 'post';
    }
}
