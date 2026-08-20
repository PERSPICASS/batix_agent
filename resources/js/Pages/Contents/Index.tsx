import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Check, Copy, FileText, Loader2, Send, Sparkles, X } from 'lucide-react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import type { ContentItem, SharedProps } from '../../types';

type Props = { contents: ContentItem[]; status: string };
const filters = [['','Tous'],['draft','Brouillons'],['approved','Approuvés'],['rejected','Rejetés'],['published','Publiés']];

export default function Contents({ contents, status }: Props) {
    const { props } = usePage<SharedProps>();
    const aiConfigured = Boolean(props.aiConfigured);
    const facebookConfigured = Boolean(props.facebookConfigured);
    const [copied, setCopied] = useState<number|null>(null);
    const postForm = useForm({
        subject: '',
        audience: 'Gérants de quincailleries, commerces, grossistes et distributeurs.',
        offer: 'Demander une démonstration gratuite de BatixPro.',
    });
    const update = (id:number,next:string) => router.patch(`/contents/${id}/status`, { status: next }, { preserveScroll: true });
    const copy = async (item:ContentItem) => { await navigator.clipboard.writeText([item.hook,item.body,item.cta].filter(Boolean).join('\n\n')); setCopied(item.id); setTimeout(()=>setCopied(null),1200); };
    const publish = (id: number) => router.post(`/contents/${id}/facebook-publish`, {}, { preserveScroll: true });
    const createPost = (event: FormEvent) => {
        event.preventDefault();
        postForm.post('/facebook-posts', {
            preserveScroll: true,
            onSuccess: () => postForm.reset('subject'),
        });
    };

    return <AppLayout title="Contenus" subtitle="Bibliothèque des créations produites par l’agent. Chaque post Facebook est relu avant sa publication sur la page BatixPro." actions={<div className="flex items-center gap-2"><span className={`rounded-full border px-3 py-2 text-xs font-bold ${facebookConfigured ? 'border-emerald-800 text-emerald-300' : 'border-slate-700 text-slate-400'}`}>{facebookConfigured ? 'Facebook connecté' : 'Facebook à configurer'}</span><Link href="/campaigns" className="btn-primary">Créer depuis une campagne</Link></div>}>
        <Head title="Contenus" />
        <div className="grid items-start gap-6 lg:grid-cols-[minmax(300px,0.72fr)_minmax(360px,1.28fr)]">
            <section className="panel p-5 sm:p-6 lg:sticky lg:top-24">
                <div className="mb-6 flex items-start gap-3"><div className="rounded-xl bg-amber-400 p-2.5 text-slate-950"><Sparkles size={19}/></div><div><h2 className="font-bold">Créer un post Facebook</h2><p className="mt-1 text-sm text-slate-500">Donne le brief. BATIX Growth rédige un texte prêt à être relu et publié.</p></div></div>
                <form className="space-y-5" onSubmit={createPost}>
                    <div><label className="label">Sujet du post</label><textarea className="field min-h-28" value={postForm.data.subject} onChange={(event) => postForm.setData('subject', event.target.value)} placeholder="Ex. Comment éviter les ruptures de stock en période de forte demande" required/>{postForm.errors.subject && <p className="mt-1 text-xs text-red-400">{postForm.errors.subject}</p>}</div>
                    <div className="space-y-5"><div><label className="label">Audience</label><textarea className="field min-h-24" value={postForm.data.audience} onChange={(event) => postForm.setData('audience', event.target.value)} required/></div><div><label className="label">Appel à l’action</label><textarea className="field min-h-24" value={postForm.data.offer} onChange={(event) => postForm.setData('offer', event.target.value)}/></div></div>
                    <div className="flex flex-col justify-between gap-3 border-t border-slate-800 pt-5 sm:flex-row sm:items-center"><p className="text-xs leading-5 text-slate-500">Création en brouillon : tu gardes la main avant la publication.</p><button className="btn-primary shrink-0" disabled={!aiConfigured || postForm.processing}>{postForm.processing ? <Loader2 size={16} className="animate-spin"/> : <Sparkles size={16}/>}{aiConfigured ? 'Créer le post' : 'IA non configurée'}</button></div>
                </form>
            </section>
            <section className="min-w-0">
                <div className="mb-4"><div className="text-xs font-black uppercase tracking-[.16em] text-amber-400">Bibliothèque</div><h2 className="mt-1 text-xl font-black">Posts générés</h2><p className="mt-1 text-sm text-slate-500">Le post le plus récent apparaît en premier.</p></div>
                <div className="mb-5 flex gap-2 overflow-x-auto pb-1">{filters.map(([value,label]) => <Link key={value} href={value ? `/contents?status=${value}` : '/contents'} className={`whitespace-nowrap rounded-xl px-3.5 py-2 text-xs font-bold ${status===value ? 'bg-amber-400 text-slate-950' : 'border border-slate-800 bg-slate-900 text-slate-400 hover:text-white'}`}>{label}</Link>)}</div>
                <div className="grid gap-4">{contents.map(item => {
                    const isFacebookPost = item.channel === 'facebook' && item.format === 'post';
                    return <article key={item.id} className="panel p-5 sm:p-6">
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-2"><div className="flex items-center gap-2"><FileText size={17} className="text-amber-300"/><span className="text-xs font-black uppercase tracking-wider text-slate-400">{item.format} · {item.channel}</span></div><span className="rounded-full bg-slate-800 px-2.5 py-1 text-[10px] font-black uppercase text-slate-300">{item.status}</span></div>
                        <h2 className="text-xl font-black">{item.title || item.format}</h2>
                        {item.campaign && <div className="mt-1 text-xs text-slate-500">Campagne : {item.campaign.name}</div>}

                        <section className="mt-5 rounded-xl border border-slate-800 bg-slate-950/40 p-4 sm:p-5">
                            <div className="mb-3 text-[10px] font-black uppercase tracking-[.16em] text-amber-400">Texte généré</div>
                            {item.hook && <div className="rounded-xl border border-amber-500/20 bg-amber-500/5 p-3 text-base font-semibold leading-6 text-amber-100">{item.hook}</div>}
                            <p className="mt-4 whitespace-pre-wrap text-base leading-7 text-slate-200">{item.body}</p>
                            {item.cta && <div className="mt-4 border-t border-slate-800 pt-4 text-sm"><span className="font-bold text-slate-500">CTA · </span><span className="font-semibold text-amber-200">{item.cta}</span></div>}
                        </section>

                        {item.facebook_publish_error && <p className="mt-3 text-xs text-red-300">{item.facebook_publish_error}</p>}
                        <div className="flex flex-wrap gap-2 pt-5"><button className="btn-secondary" onClick={()=>copy(item)}><Copy size={15}/>{copied===item.id?'Copié':'Copier le texte'}</button>{item.status !== 'published' && <>{item.status!=='approved' && <button className="btn-primary" onClick={()=>update(item.id,'approved')}><Check size={15}/>Approuver</button>}{item.status!=='rejected' && <button className="btn-secondary" onClick={()=>update(item.id,'rejected')}><X size={15}/>Rejeter</button>}</>}{isFacebookPost && item.status === 'approved' && <button className="btn-primary" disabled={!facebookConfigured || Boolean(item.facebook_post_id)} onClick={() => publish(item.id)}><Send size={15}/>{item.facebook_post_id ? 'Déjà publié' : 'Publier sur Facebook'}</button>}</div>
                    </article>;
                })}{contents.length===0 && <div className="panel p-12 text-center text-sm text-slate-500">Aucun contenu pour ce filtre.</div>}</div>
            </section>
        </div>
    </AppLayout>;
}
