<?php

namespace App\Support;

final class GrowthOptions
{
    public const CAMPAIGN_CHANNELS = ['facebook', 'instagram', 'whatsapp', 'tiktok', 'linkedin'];

    public const CAMPAIGN_STATUSES = ['draft', 'active', 'paused', 'completed'];

    public const LEAD_SOURCES = [...self::CAMPAIGN_CHANNELS, 'manual'];

    public const CONTENT_STATUSES = ['draft', 'approved', 'rejected', 'published'];

    public const LEAD_STATUSES = ['new', 'contacted', 'qualified', 'demo', 'won', 'lost'];

    public const PROTECTED_LEAD_STATUSES = ['qualified', 'demo', 'won', 'lost'];

    public const QUALIFIED_LEAD_STATUSES = ['qualified', 'demo', 'won'];

    public const INTERACTION_TYPES = ['note', 'call', 'whatsapp', 'email'];
}
