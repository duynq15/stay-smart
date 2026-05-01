import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp, Link, Head } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'STAY-SMART';

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(name, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('Link', Link)
            .component('Head', Head)
            .mount(el);
    },
    progress: {
        color: '#14724f',
        showSpinner: true,
    },
});

function resolvePageComponent(name, pages) {
    const importPage = pages[`./Pages/${name}.vue`];
    if (!importPage) {
        throw new Error(`Page not found: ${name}`);
    }
    return importPage().then((module) => module.default);
}
