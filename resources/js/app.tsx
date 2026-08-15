import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import AppErrorBoundary from './Components/AppErrorBoundary';

const appName = import.meta.env.VITE_APP_NAME || 'BATIX Growth';

createInertiaApp({
    title: (title) => `${title} · ${appName}`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.tsx`,
        import.meta.glob('./Pages/**/*.tsx'),
    ),
    setup({ el, App, props }) {
        document.getElementById('boot-fallback')?.remove();
        createRoot(el).render(
            <AppErrorBoundary>
                <App {...props} />
            </AppErrorBoundary>,
        );
    },
    progress: { color: '#FBBF24' },
});
