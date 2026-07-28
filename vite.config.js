import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/firebase.js', 'resources/js/login-phone.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
