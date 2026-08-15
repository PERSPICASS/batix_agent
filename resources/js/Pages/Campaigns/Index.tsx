import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Loader2, Megaphone, Sparkles } from 'lucide-react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import type { Campaign, SharedProps } from '../../types';

type Props = { campaigns: Campaign[] };

export default function Campaigns({ campaigns }: Props) {
    const { props } = usePage<SharedProps>();
    const aiConfigured = Boolean(props.aiConfigured);
    const [generatingId, setGeneratingId] = useState<number | null>(null);
    const form = useForm({ name: '', channel: 'facebook', objective: 'Générer des conversations WhatsApp qualifiées pour une démonstration BatixPro.', audience: 'Gérants de quincailleries, commerces, grossistes et distributeurs.', offer: 'Démonstration gratuite de BatixPro.', daily_budget: '' });

    const submit = (e: FormEvent) => { e.preventDefault(); form.post('/campaigns', { preserveScroll: true, onSuccess: () => form.reset('name', 'daily_budget') }); };
    const generate = (id: number) => { setGeneratingId(id); router.post(`/campaigns/${id}/generate`, {}, { preserveScroll: true, onFinish: () => setGeneratingId(null) }); };

    return <AppLayout title="Campagnes" subtitle="Crée des campagnes structurées et demande à BATIX Growth de produire les déclinaisons de contenu nécessaires." actions={<div className={`rounded-full border px-3 py-2 text-xs font-bold ${aiConfigured ? 'border-emerald-800 bg-emerald-950/50 text-emerald-300' : 'border-amber-800 bg-amber-950/50 text-amber-300'}`}>{aiConfigured ? 'IA disponible' : 'OPENAI_API_KEY manquante'}</div>}>
        <Head title="Campagnes" />
        <div className="grid gap-6 xl:grid-cols-[390px_1fr]">
            <section className="panel h-fit p-5"><div className="mb-5 flex items-center gap-3"><div className="rounded-xl bg-amber-400 p-2 text-slate-950"><Megaphone size={18}/></div><div><h2 className="font-bold">Nouvelle campagne</h2><p className="text-xs text-slate-500">Définis le brief marketing.</p></div></div><form className="space-y-4" onSubmit={submit}><div><label className="label">Nom</label><input className="field" value={form.data.name} onChange={e=>form.setData('name',e.target.value)} placeholder="Acquisition quincailleries Abidjan" required/>{form.errors.name && <p className="mt-1 text-xs text-red-400">{form.errors.name}</p>}</div><div><label className="label">Canal</label><select className="field" value={form.data.channel} onChange={e=>form.setData('channel',e.target.value)}>{['facebook','instagram','whatsapp','tiktok','linkedin'].map(v=><option key={v} value={v}>{v}</option>)}</select></div><div><label className="label">Objectif</label><textarea className="field min-h-24" value={form.data.objective} onChange={e=>form.setData('objective',e.target.value)} required/></div><div><label className="label">Audience</label><textarea className="field min-h-24" value={form.data.audience} onChange={e=>form.setData('audience',e.target.value)} required/></div><div><label className="label">Offre</label><textarea className="field" value={form.data.offer} onChange={e=>form.setData('offer',e.target.value)}/></div><div><label className="label">Budget journalier (FCFA)</label><input type="number" min="0" className="field" value={form.data.daily_budget} onChange={e=>form.setData('daily_budget',e.target.value)}/></div><button className="btn-primary w-full" disabled={form.processing}>{form.processing && <Loader2 size={16} className="animate-spin"/>}Créer la campagne</button></form></section>

            <section className="space-y-4">{campaigns.map(c => <article key={c.id} className="panel p-5"><div className="flex flex-col justify-between gap-5 lg:flex-row lg:items-start"><div className="min-w-0 flex-1"><div className="mb-3 flex flex-wrap items-center gap-2"><span className="rounded-full bg-slate-800 px-2.5 py-1 text-[10px] font-black uppercase text-slate-300">{c.channel}</span><span className="rounded-full bg-amber-500/10 px-2.5 py-1 text-[10px] font-black uppercase text-amber-200">{c.status}</span></div><h3 className="text-lg font-black">{c.name}</h3><p className="mt-2 text-sm leading-6 text-slate-400">{c.objective}</p><div className="mt-4 flex flex-wrap gap-4 text-xs text-slate-500"><span>{c.contents_count ?? 0} contenus</span><span>{c.leads_count ?? 0} prospects</span>{c.daily_budget && <span>{Number(c.daily_budget).toLocaleString('fr-FR')} FCFA/j</span>}</div></div><button className="btn-primary shrink-0" onClick={()=>generate(c.id)} disabled={!aiConfigured || generatingId===c.id}>{generatingId===c.id ? <Loader2 size={16} className="animate-spin"/> : <Sparkles size={16}/>}Générer 3 contenus</button></div></article>)}{campaigns.length===0 && <div className="panel p-12 text-center text-sm text-slate-500">Crée ta première campagne pour commencer.</div>}</section>
        </div>
    </AppLayout>;
}
