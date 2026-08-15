<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use App\Models\MarketingContent;
use App\Services\BatixGrowthAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class GenerateCampaignContents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public readonly int $campaignId) {}

    public function handle(BatixGrowthAiService $ai): void
    {
        $campaign = MarketingCampaign::query()->findOrFail($this->campaignId);
        $campaign->increment('content_generation_attempts');
        $campaign->update([
            'content_generation_status' => 'processing',
            'content_generation_error' => null,
            'content_generation_started_at' => now(),
        ]);

        $facebookPostIds = DB::transaction(function () use ($ai, $campaign): array {
            $facebookPostIds = [];
            foreach ($ai->generateCampaignContents($campaign) as $content) {
                $created = MarketingContent::create([
                    'marketing_campaign_id' => $campaign->id,
                    'channel' => $campaign->channel,
                    'format' => $content['format'],
                    'status' => 'draft',
                    'title' => $content['title'],
                    'hook' => $content['hook'],
                    'body' => $content['body'],
                    'cta' => $content['cta'],
                    'meta' => ['generated_by' => 'batix-growth-ai', 'generated_at' => now()->toIso8601String()],
                ]);
                if ($created->channel === 'facebook' && $created->format === 'post') {
                    $facebookPostIds[] = $created->id;
                }
            }

            $campaign->update([
                'content_generation_status' => 'completed',
                'content_generation_error' => null,
                'content_generation_completed_at' => now(),
            ]);

            return $facebookPostIds;
        });

        foreach ($facebookPostIds as $contentId) {
            GenerateMarketingImage::dispatch($contentId);
        }
    }

    public function failed(Throwable $exception): void
    {
        MarketingCampaign::query()->whereKey($this->campaignId)->update([
            'content_generation_status' => 'failed',
            'content_generation_error' => Str::limit($exception->getMessage(), 1000),
            'content_generation_completed_at' => now(),
        ]);
    }
}
