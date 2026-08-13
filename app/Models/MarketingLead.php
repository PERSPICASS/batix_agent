<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingLead extends Model
{
    protected $fillable = [
        'marketing_campaign_id', 'name', 'phone', 'company', 'business_type', 'source', 'status', 'score', 'notes',
        'ai_summary', 'ai_next_action', 'whatsapp_script', 'last_contact_at', 'scored_at'
    ];

    protected $casts = ['last_contact_at' => 'datetime', 'scored_at' => 'datetime'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }
}
