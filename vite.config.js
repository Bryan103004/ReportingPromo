import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // Ini membantu saat 'npm run build'
    build: {
        manifest: true,
        outDir: 'public/build',
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // Penting: pastikan ini diarahkan ke subfolder
            publicDirectory: 'public',
        }),
    ],
});