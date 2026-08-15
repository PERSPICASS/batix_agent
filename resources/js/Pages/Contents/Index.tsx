import { Head, Link, router } from '@inertiajs/react';
import { Check, Copy, FileText, X } from 'lucide-react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import type { ContentItem } from '../../types';

type Props = { contents: ContentItem[]; status: string };
const filters = [['','Tous'],['draft','Brouillons'],['approved','Approuvés'],['rejected','Rejetés'],['published','Publiés']];

export default function Contents({ contents, status }: Props) {
    const [copied, setCopied] = useState<number|null>(null);
    const update = (id:number,next:string) => router.patch(`/contents/${id}/status`, { status: next }, { preserveScroll: true });
    const copy = async (item:ContentItem) => { await navigator.clipboard.writeText([item.hook,item.body,item.cta].filter(Boolean).join('\n\n')); setCopied(item.id); setTimeout(()=>setCopied(null),1200); };

    return <AppLayout title="Contenus" subtitle="Bibliothèque des créations produites par l’agent. Rien n’est publié automatiquement : chaque contenu passe par ta validation." actions={<Link href="/campaigns" className="btn-primary">Créer depuis une campagne</Link>}>
        <Head title="Contenus" />
        <div className="mb-5 flex gap-2 overflow-x-auto pb-1">{filters.map(([value,label]) => <Link key={value} href={value ? `/contents?status=${value}` : '/contents'} className={`whitespace-nowrap rounded-xl px-3.5 py-2 text-xs font-bold ${status===value ? 'bg-amber-400 text-slate-950' : 'border border-slate-800 bg-slate-900 text-slate-400 hover:text-white'}`}>{label}</Link>)}</div>
        <div className="grid gap-4 lg:grid-cols-2">{contents.map(item => <article key={item.id} className="panel flex flex-col p-5"><div className="mb-4 flex flex-wrap items-center justify-between gap-2"><div className="flex items-center gap-2"><FileText size={17} className="text-amber-300"/><span className="text-xs font-black uppercase tracking-wider text-slate-400">{item.format} · {item.channel}</span></div><span className="rounded-full bg-slate-800 px-2.5 py-1 text-[10px] font-black uppercase text-slate-300">{item.status}</span></div><h2 className="text-lg font-black">{item.title || item.format}</h2>{item.campaign && <div className="mt-1 text-xs text-slate-500">Campagne : {item.campaign.name}</div>}{item.hook && <div className="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/5 p-3 text-sm font-semibold text-amber-100">{item.hook}</div>}<p className="mt-4 whitespace-pre-wrap text-sm leading-6 text-slate-300">{item.body}</p>{item.cta && <div className="mt-4 text-sm"><span className="font-bold text-slate-500">CTA · </span>{item.cta}</div>}<div className="mt-auto flex flex-wrap gap-2 pt-5"><button className="btn-secondary" onClick={()=>copy(item)}><Copy size={15}/>{copied===item.id?'Copié':'Copier'}</button>{item.status!=='approved' && <button className="btn-primary" onClick={()=>update(item.id,'approved')}><Check size={15}/>Approuver</button>}{item.status!=='rejected' && <button className="btn-secondary" onClick={()=>update(item.id,'rejected')}><X size={15}/>Rejeter</button>}</div></article>)}{contents.length===0 && <div className="panel p-12 text-center text-sm text-slate-500 lg:col-span-2">Aucun contenu pour ce filtre.</div>}</div>
    </AppLayout>;
}
