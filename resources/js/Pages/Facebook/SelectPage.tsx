import { Head, useForm } from '@inertiajs/react';
import { Facebook, Loader2 } from 'lucide-react';
import { FormEvent } from 'react';
import AppLayout from '../../Layouts/AppLayout';

type Props = { pages: { id: string; name: string }[] };

export default function SelectPage({ pages }: Props) {
    const form = useForm({ page_id: pages[0]?.id ?? '' });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/facebook/select-page');
    };

    return <AppLayout title="Connecter Facebook" subtitle="Choisis la page que BATIX Growth pourra publier.">
        <Head title="Connecter Facebook" />
        <section className="panel mx-auto max-w-xl p-6 sm:p-8">
            <div className="mb-6 flex items-start gap-3">
                <div className="rounded-xl bg-blue-500 p-2.5 text-white"><Facebook size={20}/></div>
                <div><h2 className="font-bold">Pages autorisées</h2><p className="mt-1 text-sm text-slate-500">Le jeton sera stocké chiffré et utilisé uniquement pour publier les contenus approuvés.</p></div>
            </div>
            <form className="space-y-5" onSubmit={submit}>
                <div><label className="label">Page Facebook</label><select className="field" value={form.data.page_id} onChange={(event) => form.setData('page_id', event.target.value)}>{pages.map(page => <option key={page.id} value={page.id}>{page.name}</option>)}</select></div>
                <button className="btn-primary" disabled={form.processing || !form.data.page_id}>{form.processing ? <Loader2 size={16} className="animate-spin"/> : <Facebook size={16}/>}Connecter cette page</button>
            </form>
        </section>
    </AppLayout>;
}
