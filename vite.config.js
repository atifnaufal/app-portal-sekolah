import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Tailwind sengaja dihentikan: resources/css/app.css tidak pernah di-link lewat
// @vite() di layout mana pun dan tidak di-import oleh resources/js/app.js,
// jadi selama ini ia ikut dibangun tiap deploy tanpa pernah dikirim ke browser.
// Seluruh UI memakai Bootstrap 5 (CDN) + blok <style> inline pada layout.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
    ],
});
