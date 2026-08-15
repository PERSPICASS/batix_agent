export type Campaign = {
    id: number;
    name: string;
    channel: string;
    status: string;
    objective: string;
    audience?: string | null;
    offer?: string | null;
    daily_budget?: string | number | null;
    start_date?: string | null;
    end_date?: string | null;
    metrics?: { reach?: number; clicks?: number; conversations?: number; demos?: number; spend?: number } | null;
    contents_count?: number;
    leads_count?: number;
    content_generation_status?: 'idle' | 'queued' | 'processing' | 'completed' | 'failed';
    content_generation_attempts?: number;
    content_generation_error?: string | null;
    created_at?: string;
};

export type ContentItem = {
    id: number;
    channel: string;
    format: string;
    status: string;
    title?: string | null;
    hook?: string | null;
    body: string;
    cta?: string | null;
    campaign?: Campaign | null;
    created_at?: string;
};

export type Lead = {
    id: number;
    name: string;
    phone: string;
    company?: string | null;
    business_type?: string | null;
    source: string;
    status: string;
    score: number;
    notes?: string | null;
    ai_summary?: string | null;
    ai_next_action?: string | null;
    whatsapp_script?: string | null;
    scoring_status?: 'idle' | 'queued' | 'processing' | 'completed' | 'failed';
    scoring_attempts?: number;
    scoring_error?: string | null;
    interactions?: LeadInteraction[];
    campaign?: Campaign | null;
    created_at?: string;
};

export type LeadInteraction = {
    id: number;
    type: 'note' | 'call' | 'whatsapp' | 'email';
    body: string;
    occurred_at: string;
    author?: { id: number; name: string } | null;
};

export type AdminUser = {
    id: number;
    name: string;
    username: string;
    is_active: boolean;
    last_login_at?: string | null;
    created_at?: string;
};

export type SharedProps = {
    flash?: { success?: string | null; error?: string | null };
    aiConfigured?: boolean;
    [key: string]: unknown;
};
