export type Campaign = {
    id: number;
    name: string;
    channel: string;
    status: string;
    objective: string;
    audience?: string | null;
    offer?: string | null;
    daily_budget?: string | number | null;
    contents_count?: number;
    leads_count?: number;
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
    campaign?: Campaign | null;
    created_at?: string;
};

export type SharedProps = {
    flash?: { success?: string | null; error?: string | null };
    aiConfigured?: boolean;
    [key: string]: unknown;
};
