import { Head, useForm } from '@inertiajs/react';
import { Bot, LockKeyhole, LogIn } from 'lucide-react';
import { FormEvent } from 'react';

export default function Login() {
    const form = useForm({ username: '', password: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/login');
    };

    return (
        <main className="grid min-h-screen place-items-center bg-slate-950 p-5 text-slate-100">
            <Head title="Connexion" />
            <section className="w-full max-w-md">
                <div className="mb-7 flex items-center justify-center gap-3">
                    <div className="grid h-12 w-12 place-items-center rounded-2xl bg-amber-400 text-slate-950"><Bot size={25} strokeWidth={2.6}/></div>
                    <div><div className="text-[10px] font-black uppercase tracking-[.2em] text-amber-400">BATIX</div><div className="text-2xl font-black tracking-tight">Growth</div></div>
                </div>
                <div className="panel p-6 sm:p-8">
                    <div className="mb-6"><div className="mb-3 inline-flex rounded-xl bg-amber-400/10 p-2 text-amber-300"><LockKeyhole size={19}/></div><h1 className="text-2xl font-black">Connexion</h1><p className="mt-2 text-sm leading-6 text-slate-400">Accède à l’espace de pilotage marketing BatixPro.</p></div>
                    <form className="space-y-5" onSubmit={submit}>
                        <div><label className="label" htmlFor="username">Identifiant</label><input id="username" className="field" value={form.data.username} onChange={(event) => form.setData('username', event.target.value)} autoComplete="username" autoFocus required/>{form.errors.username && <p className="mt-2 text-xs text-red-300">{form.errors.username}</p>}</div>
                        <div><label className="label" htmlFor="password">Mot de passe</label><input id="password" type="password" className="field" value={form.data.password} onChange={(event) => form.setData('password', event.target.value)} autoComplete="current-password" required/>{form.errors.password && <p className="mt-2 text-xs text-red-300">{form.errors.password}</p>}</div>
                        <button className="btn-primary w-full" disabled={form.processing}>{form.processing ? 'Connexion…' : <><LogIn size={17}/>Se connecter</>}</button>
                    </form>
                </div>
                <p className="mt-5 text-center text-xs text-slate-600">Accès réservé à l’équipe BatixPro.</p>
            </section>
        </main>
    );
}
