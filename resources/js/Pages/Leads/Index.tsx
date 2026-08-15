import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Bot, Copy, History, Loader2, MessageCircleMore, Plus, UserPlus } from 'lucide-react';
import { FormEvent, useEffect, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import type { Campaign, Lead, SharedProps } from '../../types';

type Props = { leads: Lead[]; campaigns: Campaign[]; status: string };

const leadFilters = [['', 'Tous'], ['new', 'Nouveaux'], ['contacted', 'Contactés'], ['qualified', 'Qualifiés'], ['demo', 'Démos'], ['won', 'Gagnés'], ['lost', 'Perdus']] as const;
const leadStatuses = ['new', 'contacted', 'qualified', 'demo', 'won', 'lost'];

const isProcessing = (status?: Lead['scoring_status']) => status === 'queued' || status === 'processing';

const scoreLabel = (status?: Lead['scoring_status']) => {
    if (status === 'queued') return 'En attente…';
    if (status === 'processing') return 'Qualification…';
    if (status === 'failed') return 'Réessayer';

    return 'Qualifier avec l’IA';
};

export default function Leads({ leads, campaigns, status }: Props) {
    const { props } = usePage<SharedProps>();
    const aiConfigured = Boolean(props.aiConfigured);
    const [scoringId, setScoringId] = useState<number | null>(null);
    const [copied, setCopied] = useState<number | null>(null);
    const [interactionBodies, setInteractionBodies] = useState<Record<number, string>>({});
    const [interactionTypes, setInteractionTypes] = useState<Record<number, string>>({});
    const form = useForm({ name: '', phone: '', company: '', business_type: '', source: 'whatsapp', marketing_campaign_id: '', notes: '' });
    const hasRunningScore = leads.some((lead) => isProcessing(lead.scoring_status));

    useEffect(() => {
        if (!hasRunningScore) return;

        const refresh = window.setInterval(() => router.reload({ only: ['leads'] }), 5000);

        return () => window.clearInterval(refresh);
    }, [hasRunningScore]);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/leads', { preserveScroll: true, onSuccess: () => form.reset('name', 'phone', 'company', 'business_type', 'marketing_campaign_id', 'notes') });
    };
    const score = (id: number) => {
        setScoringId(id);
        router.post(`/leads/${id}/score`, {}, { preserveScroll: true, onFinish: () => setScoringId(null) });
    };
    const updateStatus = (id: number, nextStatus: string) => router.patch(`/leads/${id}/status`, { status: nextStatus }, { preserveScroll: true });
    const addInteraction = (lead: Lead) => {
        const body = interactionBodies[lead.id]?.trim();
        if (!body) return;

        router.post(`/leads/${lead.id}/interactions`, { type: interactionTypes[lead.id] || 'note', body }, {
            preserveScroll: true,
            onSuccess: () => setInteractionBodies((bodies) => ({ ...bodies, [lead.id]: '' })),
        });
    };
    const copy = async (lead: Lead) => {
        if (!lead.whatsapp_script) return;

        await navigator.clipboard.writeText(lead.whatsapp_script);
        setCopied(lead.id);
        setTimeout(() => setCopied(null), 1200);
    };

    return (
        <AppLayout title="Prospects" subtitle="Centralise les leads, laisse l’IA prioriser les opportunités, puis utilise les recommandations pour préparer la prise de contact." actions={<div className={`rounded-full border px-3 py-2 text-xs font-bold ${aiConfigured ? 'border-emerald-800 text-emerald-300' : 'border-amber-800 text-amber-300'}`}>{aiConfigured ? 'Scoring IA actif' : 'IA non configurée'}</div>}>
            <Head title="Prospects" />
            <div className="mb-5 flex gap-2 overflow-x-auto pb-1">{leadFilters.map(([value, label]) => <Link key={value} href={value ? `/leads?status=${value}` : '/leads'} className={`whitespace-nowrap rounded-xl px-3.5 py-2 text-xs font-bold ${status === value ? 'bg-amber-400 text-slate-950' : 'border border-slate-800 bg-slate-900 text-slate-400 hover:text-white'}`}>{label}</Link>)}</div>
            <div className="grid gap-6 xl:grid-cols-[380px_1fr]">
                <section className="panel h-fit p-5">
                    <div className="mb-5 flex items-center gap-3"><div className="rounded-xl bg-amber-400 p-2 text-slate-950"><UserPlus size={18}/></div><div><h2 className="font-bold">Ajouter un prospect</h2><p className="text-xs text-slate-500">Saisie manuelle / WhatsApp.</p></div></div>
                    <form className="space-y-4" onSubmit={submit}>
                        <div><label className="label">Nom</label><input className="field" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} required/></div>
                        <div><label className="label">WhatsApp / téléphone</label><input className="field" value={form.data.phone} onChange={(event) => form.setData('phone', event.target.value)} required/></div>
                        <div className="grid grid-cols-2 gap-3"><div><label className="label">Entreprise</label><input className="field" value={form.data.company} onChange={(event) => form.setData('company', event.target.value)}/></div><div><label className="label">Activité</label><input className="field" value={form.data.business_type} onChange={(event) => form.setData('business_type', event.target.value)}/></div></div>
                        <div><label className="label">Source</label><select className="field" value={form.data.source} onChange={(event) => form.setData('source', event.target.value)}>{['whatsapp', 'facebook', 'instagram', 'tiktok', 'linkedin', 'manual'].map((source) => <option key={source}>{source}</option>)}</select></div>
                        <div><label className="label">Campagne</label><select className="field" value={form.data.marketing_campaign_id} onChange={(event) => form.setData('marketing_campaign_id', event.target.value)}><option value="">Aucune</option>{campaigns.map((campaign) => <option key={campaign.id} value={campaign.id}>{campaign.name}</option>)}</select></div>
                        <div><label className="label">Notes / besoin détecté</label><textarea className="field min-h-24" value={form.data.notes} onChange={(event) => form.setData('notes', event.target.value)}/></div>
                        <button className="btn-primary w-full" disabled={form.processing}>{form.processing && <Loader2 size={16} className="animate-spin"/>}Enregistrer</button>
                    </form>
                </section>

                <section className="space-y-4">
                    {leads.map((lead) => {
                        const processing = isProcessing(lead.scoring_status);
                        const scoring = scoringId === lead.id || processing;

                        return <article key={lead.id} className="panel p-5">
                            <div className="flex flex-col justify-between gap-4 md:flex-row">
                                <div><div className="flex flex-wrap items-center gap-2"><h2 className="text-lg font-black">{lead.name}</h2><span className="rounded-full bg-slate-800 px-2.5 py-1 text-[10px] font-black uppercase text-slate-300">{lead.status}</span>{lead.scoring_status && lead.scoring_status !== 'idle' && <span className={`rounded-full px-2.5 py-1 text-[10px] font-black uppercase ${lead.scoring_status === 'failed' ? 'bg-red-500/10 text-red-300' : processing ? 'bg-sky-500/10 text-sky-300' : 'bg-emerald-500/10 text-emerald-300'}`}>IA · {lead.scoring_status}</span>}</div><div className="mt-1 text-xs text-slate-500">{lead.company || lead.business_type || 'Entreprise non précisée'} · {lead.source} · {lead.phone}</div></div>
                                <div className="text-right"><div className="text-3xl font-black text-amber-300">{lead.score}<span className="text-xs text-slate-600">/100</span></div><button className="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-amber-300 disabled:text-slate-600" disabled={!aiConfigured || scoring} onClick={() => score(lead.id)}>{scoring ? <Loader2 size={14} className="animate-spin"/> : <Bot size={14}/>} {scoreLabel(lead.scoring_status)}</button></div>
                            </div>
                            <div className="mt-4 flex flex-wrap items-center gap-3"><label className="label">Étape commerciale</label><select className="field mt-0 w-auto" value={lead.status} onChange={(event) => updateStatus(lead.id, event.target.value)}>{leadStatuses.map((leadStatus) => <option key={leadStatus} value={leadStatus}>{leadStatus}</option>)}</select></div>
                            {lead.scoring_attempts ? <p className="mt-2 text-xs text-slate-500">{lead.scoring_attempts} tentative{lead.scoring_attempts > 1 ? 's' : ''}</p> : null}
                            {lead.scoring_error && <p className="mt-2 text-xs text-red-300">{lead.scoring_error}</p>}
                            {lead.notes && <p className="mt-4 text-sm leading-6 text-slate-400">{lead.notes}</p>}
                            <section className="mt-5 border-t border-slate-800 pt-4"><div className="mb-3 flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-400"><History size={15}/>Journal commercial</div><div className="space-y-2">{lead.interactions?.slice(0, 5).map((interaction) => <div key={interaction.id} className="rounded-xl border border-slate-800 bg-slate-950/60 p-3"><div className="flex justify-between gap-3 text-[10px] font-bold uppercase tracking-wide text-slate-500"><span>{interaction.type} · {interaction.author?.name || 'Système'}</span><span>{new Date(interaction.occurred_at).toLocaleString('fr-FR')}</span></div><p className="mt-2 whitespace-pre-wrap text-sm leading-5 text-slate-300">{interaction.body}</p></div>)}{!lead.interactions?.length && <p className="text-sm text-slate-500">Aucune interaction enregistrée.</p>}</div><div className="mt-3 flex flex-col gap-2 sm:flex-row"><select className="field mt-0 w-auto" value={interactionTypes[lead.id] || 'note'} onChange={(event) => setInteractionTypes((types) => ({ ...types, [lead.id]: event.target.value }))}>{['note', 'call', 'whatsapp', 'email'].map((type) => <option key={type}>{type}</option>)}</select><input className="field mt-0 flex-1" value={interactionBodies[lead.id] || ''} onChange={(event) => setInteractionBodies((bodies) => ({ ...bodies, [lead.id]: event.target.value }))} placeholder="Ajouter une note ou le résumé d’un échange…" onKeyDown={(event) => { if (event.key === 'Enter') { event.preventDefault(); addInteraction(lead); } }}/><button className="btn-secondary shrink-0" onClick={() => addInteraction(lead)} disabled={!interactionBodies[lead.id]?.trim()}><Plus size={16}/>Ajouter</button></div></section>
                            {lead.ai_summary && <div className="mt-4 grid gap-3 md:grid-cols-2"><div className="rounded-xl border border-slate-800 bg-slate-950/60 p-4"><div className="mb-2 text-[10px] font-black uppercase tracking-wider text-slate-500">Analyse IA</div><p className="text-sm leading-6 text-slate-300">{lead.ai_summary}</p></div><div className="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4"><div className="mb-2 text-[10px] font-black uppercase tracking-wider text-amber-300">Prochaine action</div><p className="text-sm leading-6 text-amber-50">{lead.ai_next_action}</p></div></div>}
                            {lead.whatsapp_script && <div className="mt-3 rounded-xl border border-emerald-800/40 bg-emerald-950/30 p-4"><div className="mb-2 flex items-center justify-between"><div className="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-emerald-300"><MessageCircleMore size={15}/>Message WhatsApp proposé</div><button onClick={() => copy(lead)} className="inline-flex items-center gap-1 text-xs font-bold text-emerald-300"><Copy size={13}/>{copied === lead.id ? 'Copié' : 'Copier'}</button></div><p className="whitespace-pre-wrap text-sm leading-6 text-slate-300">{lead.whatsapp_script}</p></div>}
                        </article>;
                    })}
                    {leads.length === 0 && <div className="panel p-12 text-center text-sm text-slate-500">Aucun prospect enregistré.</div>}
                </section>
            </div>
        </AppLayout>
    );
}
