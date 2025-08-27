import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from 'tailwindcss';
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5174,
        hmr: {
            host: 'localhost',
            port: 5174,
            protocol: 'ws'
        },
        watch: {
            usePolling: true,
        },
    },
    css: {
        postcss: {
            plugins: [
                tailwindcss,
            ],
        },
    },
});
