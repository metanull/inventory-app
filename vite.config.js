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
            async writeBundle() {
                const manifestPath = 'public/build/manifest.json';
                const { readFileSync, writeFileSync, existsSync } = await import('fs');
                
                if (!existsSync(manifestPath)) return;
                
                const normalizePath = (path) => {
                    const resourcesIndex = Math.max(
                        path.lastIndexOf('resources/'),
                        path.lastIndexOf('resources\\')
                    );
                    return resourcesIndex !== -1 
                        ? path.substring(resourcesIndex).replace(/\\/g, '/')
                        : path;
                };
                
                // eslint-disable-next-line no-undef
                console.log('🔧 Normalizing manifest paths...');
                const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
                let hasChanges = false;
                
                const normalizedManifest = Object.fromEntries(
                    Object.entries(manifest).map(([key, value]) => {
                        const normalizedKey = normalizePath(key);
                        const normalizedValue = {
                            ...value,
                            ...(value.src && { src: normalizePath(value.src) })
                        };
                        
                        if (normalizedKey !== key || (value.src && normalizedValue.src !== value.src)) {
                            hasChanges = true;
                        }
                        
                        return [normalizedKey, normalizedValue];
                    })
                );
                
                if (hasChanges) {
                    writeFileSync(manifestPath, JSON.stringify(normalizedManifest, null, 2));
                    // eslint-disable-next-line no-undef
                    console.log('✅ Manifest paths normalized successfully');
                } else {
                    // eslint-disable-next-line no-undef
                    console.log('ℹ️  Manifest paths were already normalized');
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
