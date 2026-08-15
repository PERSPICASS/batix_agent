<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketing_campaign_id', 'name', 'phone', 'company', 'business_type', 'source', 'status', 'score', 'notes',
        'ai_summary', 'ai_next_action', 'whatsapp_script', 'last_contact_at', 'scored_at', 'scoring_status',
        'scoring_attempts', 'scoring_error', 'scoring_started_at', 'scoring_completed_at',
    ];

    protected $casts = [
        'last_contact_at' => 'datetime',
        'scored_at' => 'datetime',
        'scoring_started_at' => 'datetime',
        'scoring_completed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(MarketingLeadInteraction::class)->latest('occurred_at');
    }
}
