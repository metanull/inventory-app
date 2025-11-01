import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        vue(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
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
        outDir: '../public/spa-build',
        manifest: 'manifest.json',
        rollupOptions: {
            input: 'index.html',
        },
        emptyOutDir: true,
        assetsInlineLimit: 0,
    },
});
