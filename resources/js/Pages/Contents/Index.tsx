import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Check, Copy, FileText, Image, Loader2, Send, Sparkles, X } from 'lucide-react';
import { FormEvent, useEffect, useState } from 'react';
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
    const hasPendingImage = contents.some((item) => item.image_generation_status === 'queued' || item.image_generation_status === 'processing');

    useEffect(() => {
        if (!hasPendingImage) return;

        const refresh = window.setInterval(() => router.reload({ only: ['contents'] }), 5000);

        return () => window.clearInterval(refresh);
    }, [hasPendingImage]);

    const generateImage = (id: number) => router.post(`/contents/${id}/facebook-image`, {}, { preserveScroll: true });
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
        <section className="panel mb-6 grid overflow-hidden lg:grid-cols-[minmax(0,1fr)_minmax(360px,0.9fr)]">
            <div className="p-5 sm:p-6 lg:border-r lg:border-slate-800">
                <div className="mb-6 flex items-start gap-3"><div className="rounded-xl bg-amber-400 p-2.5 text-slate-950"><Sparkles size={19}/></div><div><h2 className="font-bold">Créer un post Facebook</h2><p className="mt-1 text-sm text-slate-500">Donne le brief. BATIX Growth rédige le post et lance son visuel.</p></div></div>
                <form className="space-y-5" onSubmit={createPost}>
                    <div><label className="label">Sujet du post</label><textarea className="field min-h-28" value={postForm.data.subject} onChange={(event) => postForm.setData('subject', event.target.value)} placeholder="Ex. Comment éviter les ruptures de stock en période de forte demande" required/>{postForm.errors.subject && <p className="mt-1 text-xs text-red-400">{postForm.errors.subject}</p>}</div>
                    <div className="space-y-5"><div><label className="label">Audience</label><textarea className="field min-h-24" value={postForm.data.audience} onChange={(event) => postForm.setData('audience', event.target.value)} required/></div><div><label className="label">Appel à l’action</label><textarea className="field min-h-24" value={postForm.data.offer} onChange={(event) => postForm.setData('offer', event.target.value)}/></div></div>
                    <div className="flex flex-col justify-between gap-3 border-t border-slate-800 pt-5 sm:flex-row sm:items-center"><p className="text-xs leading-5 text-slate-500">Création en brouillon : tu gardes la main avant la publication.</p><button className="btn-primary shrink-0" disabled={!aiConfigured || postForm.processing}>{postForm.processing ? <Loader2 size={16} className="animate-spin"/> : <Sparkles size={16}/>}{aiConfigured ? 'Créer le post et le visuel' : 'IA non configurée'}</button></div>
                </form>
            </div>
            <div className="bg-slate-950/50 p-5 sm:p-6">
                <div className="mb-4 flex items-center justify-between"><div><div className="text-xs font-black uppercase tracking-[.16em] text-amber-400">Aperçu en direct</div><h3 className="mt-1 font-bold">Rendu Facebook</h3></div><span className="rounded-full border border-slate-700 px-2.5 py-1 text-[10px] font-black uppercase text-slate-400">Brouillon</span></div>
                <div className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-2xl shadow-black/20"><div className="flex items-center gap-3 p-4"><div className="grid h-10 w-10 place-items-center rounded-full bg-amber-400 text-xs font-black text-slate-950">B</div><div><div className="text-sm font-bold text-slate-100">BatixPro</div><div className="text-xs text-slate-500">À l’instant · 🌐</div></div></div><div className="px-4 pb-4"><p className="text-sm font-semibold leading-6 text-slate-100">{postForm.data.subject || 'Votre prochain post BatixPro'}</p><p className="mt-2 text-sm leading-6 text-slate-400">Un post clair et pertinent sera généré pour {postForm.data.audience || 'votre audience'}.</p></div><div className="flex aspect-[1.91/1] flex-col items-center justify-center gap-2 border-y border-slate-800 bg-gradient-to-br from-amber-400/20 via-slate-800 to-emerald-500/10 text-slate-400"><Image size={30} className="text-amber-300"/><span className="text-xs font-bold">Visuel IA BatixPro</span></div><div className="p-4"><div className="text-sm font-semibold text-amber-300">{postForm.data.offer || 'Demander une démonstration gratuite de BatixPro.'}</div><div className="mt-4 flex justify-around border-t border-slate-800 pt-3 text-xs font-bold text-slate-500"><span>J’aime</span><span>Commenter</span><span>Partager</span></div></div></div>
                <p className="mt-4 text-xs leading-5 text-slate-500">L’aperçu illustre la structure. Le texte final et le visuel seront produits par l’IA après création.</p>
            </div>
        </section>
        <div className="mb-5 flex gap-2 overflow-x-auto pb-1">{filters.map(([value,label]) => <Link key={value} href={value ? `/contents?status=${value}` : '/contents'} className={`whitespace-nowrap rounded-xl px-3.5 py-2 text-xs font-bold ${status===value ? 'bg-amber-400 text-slate-950' : 'border border-slate-800 bg-slate-900 text-slate-400 hover:text-white'}`}>{label}</Link>)}</div>
        <div className="grid gap-4 lg:grid-cols-2">{contents.map(item => {
            const isFacebookPost = item.channel === 'facebook' && item.format === 'post';
            const imageRunning = item.image_generation_status === 'queued' || item.image_generation_status === 'processing';

            return <article key={item.id} className="panel flex flex-col p-5"><div className="mb-4 flex flex-wrap items-center justify-between gap-2"><div className="flex items-center gap-2"><FileText size={17} className="text-amber-300"/><span className="text-xs font-black uppercase tracking-wider text-slate-400">{item.format} · {item.channel}</span></div><span className="rounded-full bg-slate-800 px-2.5 py-1 text-[10px] font-black uppercase text-slate-300">{item.status}</span></div><h2 className="text-lg font-black">{item.title || item.format}</h2>{item.campaign && <div className="mt-1 text-xs text-slate-500">Campagne : {item.campaign.name}</div>}{isFacebookPost && <section className="mt-4 overflow-hidden rounded-xl border border-slate-800 bg-slate-950/60">{item.image_path ? <img src={`/storage/${item.image_path}`} alt={`Visuel de ${item.title || 'post Facebook'}`} className="aspect-square w-full object-cover"/> : <div className="flex aspect-square flex-col items-center justify-center gap-2 text-sm text-slate-500"><Image size={28}/>{imageRunning ? 'Création du visuel…' : 'Visuel Facebook à générer'}</div>}<div className="flex items-center justify-between gap-3 p-3 text-xs"><span className={item.image_generation_status === 'failed' ? 'text-red-300' : item.image_path ? 'text-emerald-300' : 'text-slate-500'}>{item.image_generation_status === 'failed' ? item.image_generation_error || 'Génération échouée' : item.image_path ? 'Visuel généré' : imageRunning ? 'Génération en cours' : 'Aucun visuel'}</span><button className="btn-secondary" disabled={!aiConfigured || imageRunning} onClick={() => generateImage(item.id)}>{imageRunning ? <Loader2 size={15} className="animate-spin"/> : <Image size={15}/>}{item.image_path ? 'Regénérer' : 'Générer'}</button></div></section>}{item.hook && <div className="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/5 p-3 text-sm font-semibold text-amber-100">{item.hook}</div>}<p className="mt-4 whitespace-pre-wrap text-sm leading-6 text-slate-300">{item.body}</p>{item.cta && <div className="mt-4 text-sm"><span className="font-bold text-slate-500">CTA · </span>{item.cta}</div>}{item.facebook_publish_error && <p className="mt-3 text-xs text-red-300">{item.facebook_publish_error}</p>}<div className="mt-auto flex flex-wrap gap-2 pt-5"><button className="btn-secondary" onClick={()=>copy(item)}><Copy size={15}/>{copied===item.id?'Copié':'Copier'}</button>{item.status !== 'published' && <>{item.status!=='approved' && <button className="btn-primary" onClick={()=>update(item.id,'approved')}><Check size={15}/>Approuver</button>}{item.status!=='rejected' && <button className="btn-secondary" onClick={()=>update(item.id,'rejected')}><X size={15}/>Rejeter</button>}</>}{isFacebookPost && item.status === 'approved' && <button className="btn-primary" disabled={!facebookConfigured || !item.image_path || Boolean(item.facebook_post_id)} onClick={() => publish(item.id)}><Send size={15}/>{item.facebook_post_id ? 'Déjà publié' : 'Publier sur Facebook'}</button>}</div></article>;
        })}{contents.length===0 && <div className="panel p-12 text-center text-sm text-slate-500 lg:col-span-2">Aucun contenu pour ce filtre.</div>}</div>
    </AppLayout>;
}
