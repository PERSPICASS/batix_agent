import { Link, router, usePage } from '@inertiajs/react';
import { Bot, FileText, LayoutDashboard, LogOut, Megaphone, ShieldCheck, Users } from 'lucide-react';
import type { PropsWithChildren, ReactNode } from 'react';
import type { SharedProps } from '../types';

type Props = PropsWithChildren<{ title: string; subtitle: string; actions?: ReactNode }>;

const navigation = [
    { label: 'Dashboard', href: '/', icon: LayoutDashboard },
    { label: 'Campagnes', href: '/campaigns', icon: Megaphone },
    { label: 'Contenus', href: '/contents', icon: FileText },
    { label: 'Prospects', href: '/leads', icon: Users },
    { label: 'Administrateurs', href: '/admins', icon: ShieldCheck },
];

export default function AppLayout({ title, subtitle, actions, children }: Props) {
    const { url, props } = usePage<SharedProps>();
    const flash = props.flash;
    const aiConfigured = Boolean(props.aiConfigured);

    const active = (href: string) => href === '/' ? url === '/' : url.startsWith(href);

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <aside className="fixed inset-y-0 left-0 z-30 hidden w-64 border-r border-slate-800 bg-slate-950/95 p-5 lg:flex lg:flex-col">
                <div className="mb-8 flex items-center gap-3 px-2">
                    <div className="grid h-11 w-11 place-items-center rounded-2xl bg-amber-400 text-slate-950"><Bot size={23} strokeWidth={2.6} /></div>
                    <div><div className="text-[10px] font-black uppercase tracking-[.2em] text-amber-400">BATIX</div><div className="text-xl font-black tracking-tight">Growth</div></div>
                </div>
                <nav className="space-y-1.5">
                    {navigation.map(({ label, href, icon: Icon }) => (
                        <Link key={href} href={href} className={`flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition ${active(href) ? 'bg-amber-400 text-slate-950' : 'text-slate-400 hover:bg-slate-900 hover:text-white'}`}>
                            <Icon size={18} />{label}
                        </Link>
                    ))}
                </nav>
                <button onClick={() => router.post('/logout')} className="mt-5 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-900 hover:text-white"><LogOut size={18}/>Se déconnecter</button>
                <div className="mt-auto rounded-2xl border border-slate-800 bg-slate-900/60 p-4">
                    <div className="mb-1 text-xs font-bold text-slate-300">Moteur IA</div>
                    <div className={`text-xs font-semibold ${aiConfigured ? 'text-emerald-400' : 'text-amber-300'}`}>{aiConfigured ? '● OpenAI configuré' : '● Clé OpenAI manquante'}</div>
                    <p className="mt-2 text-xs leading-5 text-slate-500">Validation humaine avant toute publication ou prise de contact.</p>
                </div>
            </aside>

            <div className="lg:pl-64">
                <div className="sticky top-0 z-20 border-b border-slate-800 bg-slate-950/90 px-4 py-3 backdrop-blur lg:hidden">
                    <div className="mb-3 flex items-center justify-between gap-2 font-black"><div className="flex items-center gap-2"><Bot className="text-amber-400" size={20}/> BATIX Growth</div><button onClick={() => router.post('/logout')} className="text-slate-400 hover:text-white" title="Se déconnecter"><LogOut size={18}/></button></div>
                    <nav className="flex gap-2 overflow-x-auto pb-1">
                        {navigation.map(({ label, href }) => <Link key={href} href={href} className={`whitespace-nowrap rounded-lg px-3 py-2 text-xs font-bold ${active(href) ? 'bg-amber-400 text-slate-950' : 'bg-slate-900 text-slate-300'}`}>{label}</Link>)}
                    </nav>
                </div>

                <main className="mx-auto max-w-[1500px] p-4 sm:p-6 lg:p-8">
                    <header className="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div><div className="mb-2 text-xs font-black uppercase tracking-[.18em] text-amber-400">Agent marketing autonome</div><h1 className="text-3xl font-black tracking-tight sm:text-4xl">{title}</h1><p className="mt-2 max-w-2xl text-sm leading-6 text-slate-400">{subtitle}</p></div>
                        {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
                    </header>

                    {flash?.success && <div className="mb-5 rounded-xl border border-emerald-700/50 bg-emerald-950/60 px-4 py-3 text-sm text-emerald-200">{flash.success}</div>}
                    {flash?.error && <div className="mb-5 rounded-xl border border-red-800/60 bg-red-950/60 px-4 py-3 text-sm text-red-200">{flash.error}</div>}
                    {children}
                </main>
            </div>
        </div>
    );
}
