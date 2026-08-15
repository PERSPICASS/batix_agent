<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingLeadInteraction extends Model
{
    protected $fillable = ['marketing_lead_id', 'admin_user_id', 'type', 'direction', 'external_id', 'body', 'meta', 'occurred_at'];

    protected $casts = ['occurred_at' => 'datetime', 'meta' => 'array'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class, 'marketing_lead_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }
}
