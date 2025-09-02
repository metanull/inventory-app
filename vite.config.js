import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.ts'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            '@metanull/inventory-app-api-client': fileURLToPath(new URL('./api-client', import.meta.url)),
        },
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
            port: 5173,
        },
    },
    build: {
            target: 'esnext',
            sourcemap: true,
            outDir: 'public/build', // Ensures assets and manifest are placed correctly
            manifest: true,
            rollupOptions: {
                input: [
                    'resources/js/app.ts',
                    'resources/css/app.css',
                ],
            },
            assetsInlineLimit: 0,
        },
});
