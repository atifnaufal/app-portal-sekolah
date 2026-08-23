import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            // Files in public/ (including service-worker.js and manifest.json)
            // are served as static assets and must never be processed/bundled
            // by Vite - they need to stay byte-for-byte as authored.
            ignored: ['**/storage/framework/views/**', 'public/service-worker.js', 'public/manifest.json'],
        },
    },
});
