<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingContent extends Model
{
    use HasFactory;

    protected $fillable = ['marketing_campaign_id', 'channel', 'format', 'status', 'title', 'hook', 'body', 'cta', 'scheduled_at', 'published_at', 'meta'];

    protected $casts = ['scheduled_at' => 'datetime', 'published_at' => 'datetime', 'meta' => 'array'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }
}
