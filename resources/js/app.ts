import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import axios from 'axios';

declare global {
    interface Window {
        axios: typeof axios;
    }
}

// --- CRITICAL AXIOS/CSRF CONFIGURATION ---
// Set global window reference for common use
window.axios = axios; 
window.axios.defaults.withCredentials = true;

// Set the default request header for all Axios calls
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Fetch the CSRF token from the <meta> tag in app.blade.php
const token = document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;

if (token && token.content) {
    // CRITICAL FIX: Send the CSRF token with the header to satisfy Laravel's middleware
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    // This warning is crucial for debugging if the meta tag is missing!
    console.error('CSRF token not found: Please ensure <meta name="csrf-token" content="{{ csrf_token() }}"> is in app.blade.php');
}
// --- END AXIOS/CSRF CONFIGURATION ---

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});