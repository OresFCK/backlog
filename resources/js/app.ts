import '../css/app.css';

import { createApp, h } from 'vue';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ValidationErrors from '@/components/ui/ValidationErrors.vue';

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob('./pages/**/*.vue'),
        ),

    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h('div', [h(App, props), h(ValidationErrors)]),
        })
            .use(plugin)
            .mount(el);
    },

    progress: {
        color: '#6366f1',
    },
});
