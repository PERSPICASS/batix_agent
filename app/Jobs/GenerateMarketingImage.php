<?php

namespace App\Jobs;

use App\Models\MarketingContent;
use App\Services\MarketingImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class GenerateMarketingImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public readonly int $contentId) {}

    public function handle(MarketingImageService $images): void
    {
        $content = MarketingContent::query()->findOrFail($this->contentId);
        $content->increment('image_generation_attempts');
        $content->update([
            'image_generation_status' => 'processing',
            'image_generation_error' => null,
        ]);

        $image = $images->generate($content);
        $content->update([
            'image_path' => $image['path'],
            'image_prompt' => $image['prompt'],
            'image_generation_status' => 'completed',
            'image_generation_error' => null,
            'image_generated_at' => now(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        MarketingContent::query()->whereKey($this->contentId)->update([
            'image_generation_status' => 'failed',
            'image_generation_error' => Str::limit($exception->getMessage(), 1000),
        ]);
    }
}
