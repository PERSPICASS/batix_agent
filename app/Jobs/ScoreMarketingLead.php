<?php

namespace App\Jobs;

use App\Models\MarketingLead;
use App\Services\BatixGrowthAiService;
use App\Support\GrowthOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class ScoreMarketingLead implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public readonly int $leadId) {}

    public function handle(BatixGrowthAiService $ai): void
    {
        $lead = MarketingLead::query()->findOrFail($this->leadId);
        $lead->increment('scoring_attempts');
        $lead->update([
            'scoring_status' => 'processing',
            'scoring_error' => null,
            'scoring_started_at' => now(),
        ]);

        $result = $ai->scoreLead($lead);
        $nextStatus = match ($result['qualification']) {
            'qualified' => 'qualified',
            'warm' => 'contacted',
            default => 'new',
        };

        if (in_array($lead->status, GrowthOptions::PROTECTED_LEAD_STATUSES, true)) {
            $nextStatus = $lead->status;
        }

        $lead->update([
            'score' => $result['score'],
            'status' => $nextStatus,
            'ai_summary' => $result['summary'],
            'ai_next_action' => $result['next_action'],
            'whatsapp_script' => $result['whatsapp_message'],
            'scored_at' => now(),
            'scoring_status' => 'completed',
            'scoring_error' => null,
            'scoring_completed_at' => now(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        MarketingLead::query()->whereKey($this->leadId)->update([
            'scoring_status' => 'failed',
            'scoring_error' => Str::limit($exception->getMessage(), 1000),
            'scoring_completed_at' => now(),
        ]);
    }
}
