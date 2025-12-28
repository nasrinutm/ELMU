import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy';
import axios from 'axios';

declare global {
    interface Window {
        axios: typeof axios;
    }
}

// --- CRITICAL AXIOS/CSRF CONFIGURATION ---
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;

if (token && token.content) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: Please ensure <meta name="csrf-token" content="{{ csrf_token() }}"> is in app.blade.php');
}
// --- END AXIOS/CSRF CONFIGURATION ---

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            // FIX: Added 'as any' to silence the strict TypeScript mismatch
            .use(ZiggyVue, Ziggy as any)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});