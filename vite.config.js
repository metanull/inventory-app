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
        // Custom plugin to normalize manifest paths
        {
            name: 'normalize-manifest-paths',
            generateBundle(options, bundle) {
                const manifestKey = 'manifest.json';
                if (bundle[manifestKey]) {
                    const manifest = JSON.parse(bundle[manifestKey].source);
                    const normalizedManifest = {};
                    
                    for (const [key, value] of Object.entries(manifest)) {
                        // Normalize the key to be relative to project root
                        const normalizedKey = key.replace(/^.*[\\\/]resources[\\\/]/, 'resources/');
                        // Normalize the src path as well
                        if (value.src) {
                            value.src = value.src.replace(/^.*[\\\/]resources[\\\/]/, 'resources/');
                        }
                        normalizedManifest[normalizedKey] = value;
                    }
                    
                    bundle[manifestKey].source = JSON.stringify(normalizedManifest, null, 2);
                }
            }
        }
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
        manifest: 'manifest.json', // Place manifest directly in build dir, not .vite subdir
        rollupOptions: {
            input: [
                'resources/js/app.ts',
                'resources/css/app.css',
            ],
        },
        assetsInlineLimit: 0,
    },
});
