import { Component, type ErrorInfo, type ReactNode } from 'react';
import { AlertTriangle, RefreshCw } from 'lucide-react';

type Props = { children: ReactNode };
type State = { hasError: boolean };

export default class AppErrorBoundary extends Component<Props, State> {
    state: State = { hasError: false };

    static getDerivedStateFromError(): State {
        return { hasError: true };
    }

    componentDidCatch(error: Error, info: ErrorInfo) {
        console.error('BATIX Growth frontend error', error, info);
    }

    render() {
        if (!this.state.hasError) {
            return this.props.children;
        }

        return (
            <div className="grid min-h-screen place-items-center bg-slate-950 px-6 text-slate-100">
                <div className="w-full max-w-lg rounded-3xl border border-red-900/60 bg-slate-900 p-8 text-center shadow-2xl">
                    <div className="mx-auto mb-5 grid h-14 w-14 place-items-center rounded-2xl bg-red-500/10 text-red-300"><AlertTriangle /></div>
                    <h1 className="text-2xl font-black">L’interface n’a pas pu s’afficher</h1>
                    <p className="mt-3 text-sm leading-6 text-slate-400">Une erreur React s’est produite. Recharge la page ; si elle persiste, consulte la console du navigateur et les logs du conteneur.</p>
                    <button onClick={() => window.location.reload()} className="mt-6 inline-flex items-center gap-2 rounded-xl bg-amber-400 px-4 py-3 text-sm font-black text-slate-950"><RefreshCw size={16}/>Recharger</button>
                </div>
            </div>
        );
    }
}
