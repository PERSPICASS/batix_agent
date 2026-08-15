import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Loader2, Megaphone, Sparkles } from 'lucide-react';
import { FormEvent, useEffect, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import type { Campaign, SharedProps } from '../../types';

type Props = { campaigns: Campaign[] };
const campaignStatuses = ['draft', 'active', 'paused', 'completed'];
const metricFields = [['reach', 'Portée'], ['clicks', 'Clics'], ['conversations', 'Conversations'], ['demos', 'Démos'], ['spend', 'Dépenses FCFA']] as const;

const isProcessing = (status?: Campaign['content_generation_status']) => status === 'queued' || status === 'processing';

const generateLabel = (status?: Campaign['content_generation_status']) => {
    if (status === 'queued') return 'En attente…';
    if (status === 'processing') return 'Génération…';
    if (status === 'failed') return 'Réessayer';
    if (status === 'completed') return 'Générer à nouveau';

    return 'Générer 3 contenus';
};

export default function Campaigns({ campaigns }: Props) {
    const { props } = usePage<SharedProps>();
    const aiConfigured = Boolean(props.aiConfigured);
    const [generatingId, setGeneratingId] = useState<number | null>(null);
    const form = useForm({
        name: '',
        channel: 'facebook',
        objective: 'Générer des conversations WhatsApp qualifiées pour une démonstration BatixPro.',
        audience: 'Gérants de quincailleries, commerces, grossistes et distributeurs.',
        offer: 'Démonstration gratuite de BatixPro.',
        daily_budget: '',
        start_date: '',
        end_date: '',
    });
    const hasRunningGeneration = campaigns.some((campaign) => isProcessing(campaign.content_generation_status));

    useEffect(() => {
        if (!hasRunningGeneration) return;

        const refresh = window.setInterval(() => router.reload({ only: ['campaigns'] }), 5000);

        return () => window.clearInterval(refresh);
    }, [hasRunningGeneration]);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/campaigns', { preserveScroll: true, onSuccess: () => form.reset('name', 'daily_budget', 'start_date', 'end_date') });
    };
    const generate = (id: number) => {
        setGeneratingId(id);
        router.post(`/campaigns/${id}/generate`, {}, { preserveScroll: true, onFinish: () => setGeneratingId(null) });
    };
    const updateCampaign = (id: number, data: { status?: string; metrics?: NonNullable<Campaign['metrics']> }) => router.patch(`/campaigns/${id}`, data, { preserveScroll: true });
    const updateMetric = (campaign: Campaign, metric: string, value: string) => updateCampaign(campaign.id, {
        metrics: { ...campaign.metrics, [metric]: Number(value) || 0 },
    });

    return (
        <AppLayout title="Campagnes" subtitle="Crée des campagnes structurées et demande à BATIX Growth de produire les déclinaisons de contenu nécessaires." actions={<div className={`rounded-full border px-3 py-2 text-xs font-bold ${aiConfigured ? 'border-emerald-800 bg-emerald-950/50 text-emerald-300' : 'border-amber-800 bg-amber-950/50 text-amber-300'}`}>{aiConfigured ? 'IA disponible' : 'OPENAI_API_KEY manquante'}</div>}>
            <Head title="Campagnes" />
            <div className="grid gap-6 xl:grid-cols-[390px_1fr]">
                <section className="panel h-fit p-5">
                    <div className="mb-5 flex items-center gap-3"><div className="rounded-xl bg-amber-400 p-2 text-slate-950"><Megaphone size={18}/></div><div><h2 className="font-bold">Nouvelle campagne</h2><p className="text-xs text-slate-500">Définis le brief marketing.</p></div></div>
                    <form className="space-y-4" onSubmit={submit}>
                        <div><label className="label">Nom</label><input className="field" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} placeholder="Acquisition quincailleries Abidjan" required/>{form.errors.name && <p className="mt-1 text-xs text-red-400">{form.errors.name}</p>}</div>
                        <div><label className="label">Canal</label><select className="field" value={form.data.channel} onChange={(event) => form.setData('channel', event.target.value)}>{['facebook', 'instagram', 'whatsapp', 'tiktok', 'linkedin'].map((channel) => <option key={channel} value={channel}>{channel}</option>)}</select></div>
                        <div><label className="label">Objectif</label><textarea className="field min-h-24" value={form.data.objective} onChange={(event) => form.setData('objective', event.target.value)} required/></div>
                        <div><label className="label">Audience</label><textarea className="field min-h-24" value={form.data.audience} onChange={(event) => form.setData('audience', event.target.value)} required/></div>
                        <div><label className="label">Offre</label><textarea className="field" value={form.data.offer} onChange={(event) => form.setData('offer', event.target.value)}/></div>
                        <div className="grid grid-cols-2 gap-3"><div><label className="label">Début</label><input type="date" className="field" value={form.data.start_date} onChange={(event) => form.setData('start_date', event.target.value)}/></div><div><label className="label">Fin</label><input type="date" className="field" value={form.data.end_date} onChange={(event) => form.setData('end_date', event.target.value)}/></div></div>
                        <div><label className="label">Budget journalier (FCFA)</label><input type="number" min="0" className="field" value={form.data.daily_budget} onChange={(event) => form.setData('daily_budget', event.target.value)}/></div>
                        <button className="btn-primary w-full" disabled={form.processing}>{form.processing && <Loader2 size={16} className="animate-spin"/>}Créer la campagne</button>
                    </form>
                </section>

                <section className="space-y-4">
                    {campaigns.map((campaign) => {
                        const processing = isProcessing(campaign.content_generation_status);
                        const generating = generatingId === campaign.id || processing;

                        return <article key={campaign.id} className="panel p-5">
                            <div className="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
                                <div className="min-w-0 flex-1">
                                    <div className="mb-3 flex flex-wrap items-center gap-2"><span className="rounded-full bg-slate-800 px-2.5 py-1 text-[10px] font-black uppercase text-slate-300">{campaign.channel}</span><span className="rounded-full bg-amber-500/10 px-2.5 py-1 text-[10px] font-black uppercase text-amber-200">{campaign.status}</span>{campaign.content_generation_status && campaign.content_generation_status !== 'idle' && <span className={`rounded-full px-2.5 py-1 text-[10px] font-black uppercase ${campaign.content_generation_status === 'failed' ? 'bg-red-500/10 text-red-300' : processing ? 'bg-sky-500/10 text-sky-300' : 'bg-emerald-500/10 text-emerald-300'}`}>IA · {campaign.content_generation_status}</span>}</div>
                                    <h3 className="text-lg font-black">{campaign.name}</h3>
                                    <p className="mt-2 text-sm leading-6 text-slate-400">{campaign.objective}</p>
                                    <div className="mt-4 flex flex-wrap gap-4 text-xs text-slate-500"><span>{campaign.contents_count ?? 0} contenus</span><span>{campaign.leads_count ?? 0} prospects</span>{campaign.daily_budget && <span>{Number(campaign.daily_budget).toLocaleString('fr-FR')} FCFA/j</span>}{campaign.content_generation_attempts ? <span>{campaign.content_generation_attempts} tentative{campaign.content_generation_attempts > 1 ? 's' : ''}</span> : null}</div>
                                    <div className="mt-4 flex flex-wrap items-center gap-3"><label className="label">État</label><select className="field mt-0 w-auto" value={campaign.status} onChange={(event) => updateCampaign(campaign.id, { status: event.target.value })}>{campaignStatuses.map((status) => <option key={status}>{status}</option>)}</select>{campaign.start_date && <span className="text-xs text-slate-500">Du {campaign.start_date}{campaign.end_date ? ` au ${campaign.end_date}` : ''}</span>}</div>
                                    <div className="mt-4 grid gap-2 sm:grid-cols-5">{metricFields.map(([metric, label]) => <label key={metric} className="text-[10px] font-bold uppercase tracking-wide text-slate-500">{label}<input type="number" min="0" className="field mt-1 py-2" defaultValue={campaign.metrics?.[metric] ?? 0} onBlur={(event) => updateMetric(campaign, metric, event.target.value)}/></label>)}</div>
                                    {campaign.content_generation_error && <p className="mt-3 text-xs text-red-300">{campaign.content_generation_error}</p>}
                                </div>
                                <button className="btn-primary shrink-0" onClick={() => generate(campaign.id)} disabled={!aiConfigured || generating}>{generating ? <Loader2 size={16} className="animate-spin"/> : <Sparkles size={16}/>} {generateLabel(campaign.content_generation_status)}</button>
                            </div>
                        </article>;
                    })}
                    {campaigns.length === 0 && <div className="panel p-12 text-center text-sm text-slate-500">Crée ta première campagne pour commencer.</div>}
                </section>
            </div>
        </AppLayout>
    );
}
