import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
      '@metanull/inventory-app-api-client': fileURLToPath(new URL('./api-client', import.meta.url)),
    },
  },
  define: {
    global: 'globalThis',
  },
  test: {
    globals: true,
    environment: 'jsdom',
    watch: false,
    // Explicitly exclude integration tests
    exclude: [
      '**/node_modules/**',
      '**/dist/**',
      '**/vendor/**',
      '**/storage/**',
      '**/bootstrap/cache/**',
      '**/*.integration.test.ts',
      '**/integration.test.ts',
    ],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      // Exclude integration tests from coverage as well
      exclude: [
        'node_modules/**',
        'dist/**',
        'vendor/**',
        'storage/**',
        'bootstrap/**',
        'scripts/**',
        'docs/**',
        '.github/**',
        '**/*.config.{js,ts}',
        '**/*.d.ts',
        '**/*.integration.test.ts',
        '**/integration.test.ts',
        'resources/js/__tests__/**',
        '**/__tests__/**',
        '**/test-utils.ts',
      ],
      // Only include src files for coverage
      include: ['resources/js/**/*.{ts,vue}'],
      // Coverage thresholds disabled
      thresholds: {
        global: {
          lines: 0,
          functions: 0,
          branches: 0,
          statements: 0,
        },
      },
    },
    setupFiles: ['./vitest.setup.ts', './resources/js/__tests__/test-utils.ts'],
  },
})
