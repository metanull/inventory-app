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
                        // Handle both Windows and Unix paths, and extract everything from 'resources/' onwards
                        let normalizedKey = key;
                        const resourcesIndex = key.lastIndexOf('resources/');
                        if (resourcesIndex !== -1) {
                            normalizedKey = key.substring(resourcesIndex);
                        } else {
                            // Fallback for Windows paths with backslashes
                            const resourcesIndexWin = key.lastIndexOf('resources\\');
                            if (resourcesIndexWin !== -1) {
                                normalizedKey = key.substring(resourcesIndexWin).replace(/\\/g, '/');
                            }
                        }
                        
                        // Normalize the src path as well
                        const normalizedValue = { ...value };
                        if (normalizedValue.src) {
                            const srcResourcesIndex = normalizedValue.src.lastIndexOf('resources/');
                            if (srcResourcesIndex !== -1) {
                                normalizedValue.src = normalizedValue.src.substring(srcResourcesIndex);
                            } else {
                                // Fallback for Windows paths with backslashes
                                const srcResourcesIndexWin = normalizedValue.src.lastIndexOf('resources\\');
                                if (srcResourcesIndexWin !== -1) {
                                    normalizedValue.src = normalizedValue.src.substring(srcResourcesIndexWin).replace(/\\/g, '/');
                                }
                            }
                        }
                        
                        normalizedManifest[normalizedKey] = normalizedValue;
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
