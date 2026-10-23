import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/fonts.css', // font-only entry for the standalone login page
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
