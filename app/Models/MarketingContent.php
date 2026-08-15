<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingContent extends Model
{
    use HasFactory;

    protected $fillable = ['marketing_campaign_id', 'channel', 'format', 'status', 'title', 'hook', 'body', 'cta', 'scheduled_at', 'published_at', 'meta', 'image_path', 'image_prompt', 'image_generation_status', 'image_generation_attempts', 'image_generation_error', 'image_generated_at', 'facebook_post_id', 'facebook_publish_error'];

    protected $casts = ['scheduled_at' => 'datetime', 'published_at' => 'datetime', 'image_generated_at' => 'datetime', 'meta' => 'array'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }
}
