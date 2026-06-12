import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import axios from 'axios';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        app.use(ZiggyVue);
        // ...existing code...
        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

router.on('invalid', (event) => {
    // Si la respuesta es un error 419 (CSRF expirado), evitar que Inertia muestre el modal negro
    if (event.detail.response.status === 419) {
        event.preventDefault();
        window.location.reload();
    }
});
