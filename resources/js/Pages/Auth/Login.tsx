import { Head, useForm, usePage } from '@inertiajs/react';
import { ArrowRight, Bot, LockKeyhole, ShieldCheck, Sparkles } from 'lucide-react';
import type { FormEvent } from 'react';
import type { SharedProps } from '../../types';

export default function Login() {
    const { props } = usePage<SharedProps>();
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post('/login');
    };

    return (
        <div className="relative min-h-screen overflow-hidden bg-slate-950 text-slate-100">
            <Head title="Connexion" />
            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(251,191,36,.14),transparent_32%),radial-gradient(circle_at_80%_80%,rgba(59,130,246,.09),transparent_28%)]" />

            <main className="relative mx-auto grid min-h-screen max-w-7xl items-center gap-12 px-6 py-12 lg:grid-cols-2 lg:px-10">
                <section className="hidden lg:block">
                    <div className="mb-8 flex items-center gap-3">
                        <div className="grid h-12 w-12 place-items-center rounded-2xl bg-amber-400 text-slate-950 shadow-lg shadow-amber-400/10">
                            <Bot size={26} strokeWidth={2.5} />
                        </div>
                        <div>
                            <div className="text-[11px] font-black uppercase tracking-[.24em] text-amber-400">BATIX</div>
                            <div className="text-2xl font-black tracking-tight">Growth</div>
                        </div>
                    </div>

                    <h1 className="max-w-xl text-5xl font-black leading-[1.08] tracking-tight xl:text-6xl">
                        Le cockpit marketing de <span className="text-amber-400">BatixPro.</span>
                    </h1>
                    <p className="mt-6 max-w-xl text-lg leading-8 text-slate-400">
                        Campagnes, contenus IA, prospects et suivi commercial centralisés dans une seule interface.
                    </p>

                    <div className="mt-10 grid max-w-xl gap-3 sm:grid-cols-2">
                        <div className="rounded-2xl border border-slate-800 bg-slate-900/50 p-4">
                            <Sparkles className="mb-3 text-amber-300" size={20} />
                            <div className="font-bold">Production assistée par IA</div>
                            <p className="mt-1 text-sm leading-6 text-slate-500">Création et qualification avec validation humaine.</p>
                        </div>
                        <div className="rounded-2xl border border-slate-800 bg-slate-900/50 p-4">
                            <ShieldCheck className="mb-3 text-emerald-400" size={20} />
                            <div className="font-bold">Accès privé</div>
                            <p className="mt-1 text-sm leading-6 text-slate-500">Session Laravel sécurisée et protégée contre le brute force.</p>
                        </div>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-md">
                    <div className="mb-8 flex items-center gap-3 lg:hidden">
                        <div className="grid h-11 w-11 place-items-center rounded-2xl bg-amber-400 text-slate-950"><Bot size={23} /></div>
                        <div><div className="text-[10px] font-black uppercase tracking-[.2em] text-amber-400">BATIX</div><div className="text-xl font-black">Growth</div></div>
                    </div>

                    <div className="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-2xl shadow-black/30 backdrop-blur sm:p-8">
                        <div className="mb-7 grid h-12 w-12 place-items-center rounded-2xl border border-slate-700 bg-slate-950 text-amber-300">
                            <LockKeyhole size={22} />
                        </div>
                        <h2 className="text-2xl font-black tracking-tight">Connexion administrateur</h2>
                        <p className="mt-2 text-sm leading-6 text-slate-400">Utilise les identifiants configurés dans <code className="text-slate-300">ADMIN_USER</code> et <code className="text-slate-300">ADMIN_PASSWORD</code>.</p>

                        {props.flash?.success && <div className="mt-5 rounded-xl border border-emerald-800/50 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-200">{props.flash.success}</div>}

                        <form onSubmit={submit} className="mt-7 space-y-5">
                            <div>
                                <label htmlFor="username" className="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Identifiant</label>
                                <input
                                    id="username"
                                    name="username"
                                    autoComplete="username"
                                    autoFocus
                                    value={data.username}
                                    onChange={(event) => setData('username', event.target.value)}
                                    className="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/10"
                                    placeholder="admin"
                                />
                                {errors.username && <p className="mt-2 text-sm text-red-300">{errors.username}</p>}
                            </div>

                            <div>
                                <label htmlFor="password" className="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Mot de passe</label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autoComplete="current-password"
                                    value={data.password}
                                    onChange={(event) => setData('password', event.target.value)}
                                    className="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/10"
                                    placeholder="••••••••••••"
                                />
                                {errors.password && <p className="mt-2 text-sm text-red-300">{errors.password}</p>}
                            </div>

                            <button type="submit" disabled={processing} className="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-400 px-4 py-3.5 text-sm font-black text-slate-950 transition hover:bg-amber-300 disabled:cursor-not-allowed disabled:opacity-60">
                                {processing ? 'Connexion…' : 'Accéder à BATIX Growth'} <ArrowRight size={17} />
                            </button>
                        </form>
                    </div>
                    <p className="mt-5 text-center text-xs text-slate-600">BATIX Growth · Agent marketing interne BatixPro</p>
                </section>
            </main>
        </div>
    );
}
