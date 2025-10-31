import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';
import path from 'node:path';

export default defineConfig({
    plugins: [
        vue(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
            '@metanull/inventory-app-api-client': fileURLToPath(new URL('./node_modules/@metanull/inventory-app-api-client', import.meta.url)),
        },
    },
    server: {
        host: '127.0.0.1',
        port: 5174,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
            port: 5174,
        },
    },
    build: {
        target: 'esnext',
        sourcemap: true,
        outDir: path.resolve(__dirname, '../public/spa-build'),
        manifest: 'manifest.json',
        rollupOptions: {
            input: 'index.html',
        },
        assetsInlineLimit: 0,
    },
});
