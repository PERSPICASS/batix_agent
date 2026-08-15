import { Head, Link } from '@inertiajs/react';
import { ArrowRight, FileCheck2, Megaphone, Sparkles, Target, Users } from 'lucide-react';
import AppLayout from '../Layouts/AppLayout';
import type { Campaign, ContentItem, Lead } from '../types';

type Props = {
    stats: { campaigns: number; draft_contents: number; leads: number; qualified: number };
    campaigns: Campaign[];
    contents: ContentItem[];
    leads: Lead[];
};

const statusClass = (status: string) => status === 'approved' || status === 'qualified' || status === 'won' ? 'bg-emerald-500/10 text-emerald-300' : status === 'rejected' ? 'bg-red-500/10 text-red-300' : 'bg-amber-500/10 text-amber-200';

export default function Dashboard({ stats, campaigns, contents, leads }: Props) {
    const cards = [
        ['Campagnes', stats.campaigns, Megaphone, 'Plans d’acquisition'],
        ['À valider', stats.draft_contents, FileCheck2, 'Contenus IA en brouillon'],
        ['Prospects', stats.leads, Users, 'Leads enregistrés'],
        ['Qualifiés', stats.qualified, Target, 'Leads prêts pour une démo'],
    ] as const;

    return <AppLayout title="Dashboard" subtitle="Pilote l’acquisition BatixPro, la production de contenus et la qualification commerciale depuis un seul poste de contrôle." actions={<><Link className="btn-secondary" href="/leads">Voir les prospects</Link><Link className="btn-primary" href="/campaigns"><Sparkles size={16}/>Nouvelle campagne</Link></>}>
        <Head title="Dashboard" />
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {cards.map(([label, value, Icon, helper]) => <div className="panel p-5" key={label}><div className="mb-5 flex items-center justify-between"><span className="text-sm font-semibold text-slate-400">{label}</span><div className="rounded-xl bg-slate-800 p-2 text-amber-300"><Icon size={18}/></div></div><div className="text-4xl font-black tracking-tight">{value}</div><div className="mt-2 text-xs text-slate-500">{helper}</div></div>)}
        </div>

        <div className="mt-6 grid gap-6 xl:grid-cols-3">
            <section className="panel p-5 xl:col-span-2"><div className="mb-4 flex items-center justify-between"><div><h2 className="font-bold">Campagnes récentes</h2><p className="mt-1 text-xs text-slate-500">Dernières initiatives d’acquisition.</p></div><Link href="/campaigns" className="text-xs font-bold text-amber-300">Tout voir</Link></div><div className="overflow-x-auto"><table className="w-full text-left text-sm"><thead className="text-xs uppercase text-slate-500"><tr><th className="py-3">Campagne</th><th>Canal</th><th>Statut</th></tr></thead><tbody className="divide-y divide-slate-800">{campaigns.map(c => <tr key={c.id}><td className="py-4 pr-4"><div className="font-semibold text-white">{c.name}</div><div className="mt-1 max-w-xl truncate text-xs text-slate-500">{c.objective}</div></td><td className="pr-4 text-slate-300">{c.channel}</td><td><span className={`rounded-full px-2.5 py-1 text-[10px] font-black uppercase ${statusClass(c.status)}`}>{c.status}</span></td></tr>)}</tbody></table>{campaigns.length === 0 && <div className="py-12 text-center text-sm text-slate-500">Aucune campagne pour le moment.</div>}</div></section>

            <section className="panel p-5"><div className="mb-4 flex items-center justify-between"><div><h2 className="font-bold">Prospects prioritaires</h2><p className="mt-1 text-xs text-slate-500">Les derniers leads captés.</p></div><Link href="/leads"><ArrowRight size={17} className="text-amber-300"/></Link></div><div className="space-y-3">{leads.slice(0,5).map(lead => <div key={lead.id} className="rounded-xl border border-slate-800 bg-slate-950/60 p-3"><div className="flex items-start justify-between gap-3"><div><div className="text-sm font-bold">{lead.name}</div><div className="mt-1 text-xs text-slate-500">{lead.company || lead.business_type || lead.source}</div></div><div className="text-lg font-black text-amber-300">{lead.score}<span className="text-[10px] text-slate-600">/100</span></div></div></div>)}{leads.length === 0 && <div className="py-10 text-center text-sm text-slate-500">Aucun prospect.</div>}</div></section>

            <section className="panel p-5 xl:col-span-3"><div className="mb-4 flex items-center justify-between"><div><h2 className="font-bold">Contenus récents</h2><p className="mt-1 text-xs text-slate-500">Production IA en attente de validation humaine.</p></div><Link href="/contents" className="text-xs font-bold text-amber-300">Ouvrir la bibliothèque</Link></div><div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">{contents.map(item => <article key={item.id} className="rounded-xl border border-slate-800 bg-slate-950/60 p-4"><div className="mb-3 flex items-center justify-between"><span className="text-[10px] font-black uppercase tracking-wider text-slate-500">{item.format} · {item.channel}</span><span className={`rounded-full px-2 py-1 text-[10px] font-bold ${statusClass(item.status)}`}>{item.status}</span></div><h3 className="font-bold">{item.title || item.format}</h3><p className="mt-2 line-clamp-3 text-sm leading-6 text-slate-400">{item.hook || item.body}</p></article>)}{contents.length === 0 && <div className="text-sm text-slate-500">Aucun contenu généré.</div>}</div></section>
        </div>
    </AppLayout>;
}
