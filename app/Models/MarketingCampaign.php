<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'channel', 'status', 'objective', 'audience', 'offer', 'daily_budget', 'start_date', 'end_date', 'metrics',
        'content_generation_status', 'content_generation_attempts', 'content_generation_error',
        'content_generation_started_at', 'content_generation_completed_at',
    ];

    protected $casts = [
        'daily_budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'metrics' => 'array',
        'content_generation_started_at' => 'datetime',
        'content_generation_completed_at' => 'datetime',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(MarketingContent::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(MarketingLead::class);
    }
}
