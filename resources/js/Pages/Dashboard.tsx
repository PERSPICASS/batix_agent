import { Head, Link } from '@inertiajs/react';
import { ArrowRight, BarChart3, FileCheck2, Megaphone, Sparkles, Target, Users } from 'lucide-react';
import AppLayout from '../Layouts/AppLayout';
import type { Campaign, ContentItem, Lead } from '../types';

type Props = {
    stats: { campaigns: number; active_campaigns: number; daily_budget: number; draft_contents: number; leads: number; qualified: number };
    pipeline: Record<string, number>;
    sources: { source: string; total: number }[];
    campaign_metrics: { reach: number; clicks: number; conversations: number; demos: number; spend: number };
    campaigns: Campaign[];
    contents: ContentItem[];
    leads: Lead[];
};

const pipelineLabels: Record<string, string> = { new: 'Nouveaux', contacted: 'Contactés', qualified: 'Qualifiés', demo: 'Démos', won: 'Gagnés', lost: 'Perdus' };
const statusClass = (status: string) => status === 'approved' || status === 'qualified' || status === 'won' || status === 'active' ? 'bg-emerald-500/10 text-emerald-300' : status === 'rejected' || status === 'lost' ? 'bg-red-500/10 text-red-300' : 'bg-amber-500/10 text-amber-200';
const formatNumber = (value: number) => value.toLocaleString('fr-FR');

export default function Dashboard({ stats, pipeline, sources, campaign_metrics: campaignMetrics, campaigns, contents, leads }: Props) {
    const cards = [
        ['Campagnes actives', stats.active_campaigns, Megaphone, `${stats.campaigns} au total`],
        ['Budget journalier', `${formatNumber(stats.daily_budget)} FCFA`, BarChart3, 'Campagnes actives'],
        ['À valider', stats.draft_contents, FileCheck2, 'Contenus IA en brouillon'],
        ['Prospects', stats.leads, Users, 'Tous canaux confondus'],
        ['Qualifiés', stats.qualified, Target, 'Qualifiés, démos et gagnés'],
    ] as const;

    return <AppLayout title="Dashboard" subtitle="Pilote l’acquisition BatixPro, la production de contenus et la qualification commerciale depuis un seul poste de contrôle." actions={<><Link className="btn-secondary" href="/leads">Voir les prospects</Link><Link className="btn-primary" href="/campaigns"><Sparkles size={16}/>Nouvelle campagne</Link></>}>
        <Head title="Dashboard" />
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">{cards.map(([label, value, Icon, helper]) => <div className="panel p-5" key={label}><div className="mb-5 flex items-center justify-between"><span className="text-sm font-semibold text-slate-400">{label}</span><div className="rounded-xl bg-slate-800 p-2 text-amber-300"><Icon size={18}/></div></div><div className="text-3xl font-black tracking-tight">{value}</div><div className="mt-2 text-xs text-slate-500">{helper}</div></div>)}</div>

        <div className="mt-6 grid gap-6 xl:grid-cols-3">
            <section className="panel p-5 xl:col-span-2"><div className="mb-5 flex items-center justify-between"><div><h2 className="font-bold">Pipeline commercial</h2><p className="mt-1 text-xs text-slate-500">Répartition des prospects selon leur étape actuelle.</p></div><Link href="/leads" className="text-xs font-bold text-amber-300">Gérer les prospects</Link></div><div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">{Object.entries(pipeline).map(([status, total]) => <Link key={status} href={`/leads?status=${status}`} className="rounded-xl border border-slate-800 bg-slate-950/60 p-4 transition hover:border-slate-600"><div className="text-xs font-bold uppercase tracking-wide text-slate-500">{pipelineLabels[status] || status}</div><div className="mt-2 text-3xl font-black text-amber-300">{total}</div></Link>)}</div></section>
            <section className="panel p-5"><h2 className="font-bold">Sources des prospects</h2><p className="mt-1 text-xs text-slate-500">Canaux qui alimentent le pipeline.</p><div className="mt-5 space-y-3">{sources.map(({ source, total }) => <div key={source} className="flex items-center justify-between rounded-xl border border-slate-800 bg-slate-950/60 px-3 py-3"><span className="text-sm font-semibold capitalize">{source}</span><span className="text-lg font-black text-amber-300">{total}</span></div>)}{sources.length === 0 && <p className="py-8 text-center text-sm text-slate-500">Aucune source enregistrée.</p>}</div></section>

            <section className="panel p-5 xl:col-span-3"><div className="mb-5 flex items-center justify-between"><div><h2 className="font-bold">Résultats déclarés des campagnes</h2><p className="mt-1 text-xs text-slate-500">Synthèse des résultats saisis manuellement dans les campagnes.</p></div><Link href="/campaigns" className="text-xs font-bold text-amber-300">Mettre à jour</Link></div><div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">{[['Portée', campaignMetrics.reach], ['Clics', campaignMetrics.clicks], ['Conversations', campaignMetrics.conversations], ['Démos', campaignMetrics.demos], ['Dépenses', `${formatNumber(campaignMetrics.spend)} FCFA`]].map(([label, value]) => <div key={String(label)} className="rounded-xl border border-slate-800 bg-slate-950/60 p-4"><div className="text-xs font-bold uppercase tracking-wide text-slate-500">{label}</div><div className="mt-2 text-2xl font-black text-amber-300">{value}</div></div>)}</div></section>

            <section className="panel p-5 xl:col-span-2"><div className="mb-4 flex items-center justify-between"><div><h2 className="font-bold">Campagnes récentes</h2><p className="mt-1 text-xs text-slate-500">Dernières initiatives d’acquisition.</p></div><Link href="/campaigns" className="text-xs font-bold text-amber-300">Tout voir</Link></div><div className="overflow-x-auto"><table className="w-full text-left text-sm"><thead className="text-xs uppercase text-slate-500"><tr><th className="py-3">Campagne</th><th>Canal</th><th>Statut</th></tr></thead><tbody className="divide-y divide-slate-800">{campaigns.map((campaign) => <tr key={campaign.id}><td className="py-4 pr-4"><div className="font-semibold text-white">{campaign.name}</div><div className="mt-1 max-w-xl truncate text-xs text-slate-500">{campaign.objective}</div></td><td className="pr-4 text-slate-300">{campaign.channel}</td><td><span className={`rounded-full px-2.5 py-1 text-[10px] font-black uppercase ${statusClass(campaign.status)}`}>{campaign.status}</span></td></tr>)}</tbody></table>{campaigns.length === 0 && <div className="py-12 text-center text-sm text-slate-500">Aucune campagne pour le moment.</div>}</div></section>
            <section className="panel p-5"><div className="mb-4 flex items-center justify-between"><div><h2 className="font-bold">Prospects récents</h2><p className="mt-1 text-xs text-slate-500">Derniers leads captés.</p></div><Link href="/leads"><ArrowRight size={17} className="text-amber-300"/></Link></div><div className="space-y-3">{leads.slice(0, 5).map((lead) => <div key={lead.id} className="rounded-xl border border-slate-800 bg-slate-950/60 p-3"><div className="flex items-start justify-between gap-3"><div><div className="text-sm font-bold">{lead.name}</div><div className="mt-1 text-xs text-slate-500">{lead.company || lead.business_type || lead.source}</div></div><div className="text-lg font-black text-amber-300">{lead.score}<span className="text-[10px] text-slate-600">/100</span></div></div></div>)}{leads.length === 0 && <div className="py-10 text-center text-sm text-slate-500">Aucun prospect.</div>}</div></section>

            <section className="panel p-5 xl:col-span-3"><div className="mb-4 flex items-center justify-between"><div><h2 className="font-bold">Contenus récents</h2><p className="mt-1 text-xs text-slate-500">Production IA en attente de validation humaine.</p></div><Link href="/contents" className="text-xs font-bold text-amber-300">Ouvrir la bibliothèque</Link></div><div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">{contents.map((item) => <article key={item.id} className="rounded-xl border border-slate-800 bg-slate-950/60 p-4"><div className="mb-3 flex items-center justify-between"><span className="text-[10px] font-black uppercase tracking-wider text-slate-500">{item.format} · {item.channel}</span><span className={`rounded-full px-2 py-1 text-[10px] font-bold ${statusClass(item.status)}`}>{item.status}</span></div><h3 className="font-bold">{item.title || item.format}</h3><p className="mt-2 line-clamp-3 text-sm leading-6 text-slate-400">{item.hook || item.body}</p></article>)}{contents.length === 0 && <div className="text-sm text-slate-500">Aucun contenu généré.</div>}</div></section>
        </div>
    </AppLayout>;
}
