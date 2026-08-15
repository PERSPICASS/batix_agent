import { Head, router, useForm } from '@inertiajs/react';
import { KeyRound, ShieldCheck, UserPlus } from 'lucide-react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import type { AdminUser } from '../../types';

type Props = { admins: AdminUser[] };

export default function Admins({ admins }: Props) {
    const form = useForm({ name: '', username: '', password: '' });
    const [passwords, setPasswords] = useState<Record<number, string>>({});

    const create = (event: FormEvent) => {
        event.preventDefault();
        form.post('/admins', { preserveScroll: true, onSuccess: () => form.reset() });
    };
    const toggle = (admin: AdminUser) => router.patch(`/admins/${admin.id}`, { is_active: !admin.is_active }, { preserveScroll: true });
    const updatePassword = (admin: AdminUser) => {
        const password = passwords[admin.id] || '';
        if (!password) return;

        router.patch(`/admins/${admin.id}`, { password }, {
            preserveScroll: true,
            onSuccess: () => setPasswords((values) => ({ ...values, [admin.id]: '' })),
        });
    };

    return (
        <AppLayout title="Administrateurs" subtitle="Gère les personnes autorisées à accéder à BATIX Growth et réinitialise leurs accès sans passer par le terminal.">
            <Head title="Administrateurs" />
            <div className="grid gap-6 xl:grid-cols-[390px_1fr]">
                <section className="panel h-fit p-5">
                    <div className="mb-5 flex items-center gap-3"><div className="rounded-xl bg-amber-400 p-2 text-slate-950"><UserPlus size={18}/></div><div><h2 className="font-bold">Nouvel administrateur</h2><p className="text-xs text-slate-500">Un accès individuel et traçable.</p></div></div>
                    <form className="space-y-4" onSubmit={create}>
                        <div><label className="label">Nom</label><input className="field" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} required/>{form.errors.name && <p className="mt-1 text-xs text-red-300">{form.errors.name}</p>}</div>
                        <div><label className="label">Identifiant</label><input className="field" value={form.data.username} onChange={(event) => form.setData('username', event.target.value)} autoComplete="username" required/>{form.errors.username && <p className="mt-1 text-xs text-red-300">{form.errors.username}</p>}</div>
                        <div><label className="label">Mot de passe</label><input type="password" className="field" value={form.data.password} onChange={(event) => form.setData('password', event.target.value)} autoComplete="new-password" minLength={12} required/>{form.errors.password && <p className="mt-1 text-xs text-red-300">{form.errors.password}</p>}</div>
                        <button className="btn-primary w-full" disabled={form.processing}><UserPlus size={16}/>Créer l’accès</button>
                    </form>
                </section>

                <section className="space-y-4">
                    {admins.map((admin) => <article key={admin.id} className="panel p-5">
                        <div className="flex flex-col justify-between gap-4 md:flex-row md:items-start"><div><div className="flex flex-wrap items-center gap-2"><h2 className="text-lg font-black">{admin.name}</h2><span className={`rounded-full px-2.5 py-1 text-[10px] font-black uppercase ${admin.is_active ? 'bg-emerald-500/10 text-emerald-300' : 'bg-slate-800 text-slate-400'}`}>{admin.is_active ? 'actif' : 'désactivé'}</span></div><p className="mt-1 text-sm text-slate-400">@{admin.username}</p>{admin.last_login_at && <p className="mt-2 text-xs text-slate-500">Dernière connexion : {new Date(admin.last_login_at).toLocaleString('fr-FR')}</p>}</div><button className="btn-secondary" onClick={() => toggle(admin)}>{admin.is_active ? 'Désactiver' : 'Réactiver'}</button></div>
                        <div className="mt-5 border-t border-slate-800 pt-4"><label className="label">Nouveau mot de passe</label><div className="mt-1 flex gap-2"><input type="password" className="field mt-0" value={passwords[admin.id] || ''} onChange={(event) => setPasswords((values) => ({ ...values, [admin.id]: event.target.value }))} placeholder="12 caractères minimum" minLength={12}/><button className="btn-secondary shrink-0" onClick={() => updatePassword(admin)} disabled={!passwords[admin.id]}><KeyRound size={16}/>Modifier</button></div></div>
                    </article>)}
                    {admins.length === 0 && <div className="panel p-12 text-center text-sm text-slate-500"><ShieldCheck className="mx-auto mb-3 text-slate-600"/>Aucun administrateur.</div>}
                </section>
            </div>
        </AppLayout>
    );
}
