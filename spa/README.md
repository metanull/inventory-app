# Inventory Management API - Vue 3 SPA Demo

This is a Vue 3 + TypeScript Single Page Application (SPA) demonstrating the usage of the **published** Inventory Management API client.

## Overview

This SPA is a **reference implementation** for external API consumers. It showcases how to use the `@metanull/inventory-app-api-client` npm package to interact with the Inventory Management API.

**Important**: This SPA uses the **published npm package** from GitHub Packages, not the local `/api-client/` directory. This is intentional - external applications should follow the same pattern.

## Project Structure

```
spa/
├── src/                          # Vue application source code
│   ├── App.vue                   # Root component
│   ├── app.ts                    # Application entry point
│   ├── router/                   # Vue Router configuration
│   ├── stores/                   # Pinia state management
│   ├── components/               # Reusable Vue components
│   ├── views/                    # Page-level components
│   ├── utils/                    # Utility functions
│   ├── composables/              # Vue 3 composables
│   ├── api/                      # API client integration
│   ├── css/                      # Component styles
│   └── __tests__/               # Test suites
├── index.html                    # SPA entry point
├── vite.config.js               # Vite configuration
├── vitest.config.ts             # Unit test configuration
├── vitest.integration.config.ts # Integration test configuration
├── tsconfig.json                # TypeScript configuration
├── tailwind.config.js           # Tailwind CSS configuration
├── eslint.config.js             # ESLint configuration
├── postcss.config.js            # PostCSS configuration
└── package.json                 # SPA dependencies
```

## Technologies

- **Vue 3** - Progressive JavaScript framework
- **TypeScript** - Static typing for JavaScript
- **Vue Router** - Client-side routing
- **Pinia** - State management
- **Tailwind CSS** - Utility-first CSS framework
- **Vite** - Next generation frontend build tool
- **Vitest** - Unit testing framework
- **ESLint** - Code linting
- **Prettier** - Code formatting

## Installation

### 1. Install Dependencies

```bash
cd spa
npm install
```

### 2. Build

Build for production:
```bash
npm run build
```

Output: `/public/spa-build/`

### 3. Development

Start development server:
```bash
npm run dev
```

Server will run on `http://localhost:5174`

### 4. Testing

Run unit tests:
```bash
npm run test
npm run test:watch    # Watch mode
npm run test:ui       # UI dashboard
```

Run integration tests:
```bash
npm run test:integration
```

### 5. Linting & Formatting

Check code:
```bash
npm run lint:check    # ESLint
npm run format:check  # Prettier
npm run type-check    # TypeScript
```

Fix code:
```bash
npm run lint          # Fix with ESLint
npm run format        # Fix with Prettier
```

## API Client Usage

The SPA imports from the **published npm package**:

```typescript
import { Configuration, DefaultApi } from '@metanull/inventory-app-api-client';

// Create API instance
const api = new DefaultApi(new Configuration({
  basePath: 'http://localhost:8000',
  apiKey: 'your-sanctum-token',
}));

// Use the API
const items = await api.itemIndex();
```

For external applications, install the package from GitHub Packages:

```bash
npm install @metanull/inventory-app-api-client
```

See `src/api/` directory for practical examples.

## Architecture

### State Management (Pinia)

Stores are organized by entity (Items, Partners, Languages, etc.):

```typescript
// src/stores/items.ts
export const useItemsStore = defineStore('items', () => {
  const items = ref<Item[]>([]);
  
  const fetchItems = async () => {
    // API call
  };
  
  return { items, fetchItems };
});
```

### Composables

Reusable logic extracted into composables:

```typescript
// src/composables/useApi.ts
export const useApi = () => {
  // API client setup and error handling
};
```

### Components

Components follow Vue 3 Composition API best practices:

```vue
<script setup lang="ts">
import { ref, computed } from 'vue';
import type { Item } from '@metanull/inventory-app-api-client';

const items = ref<Item[]>([]);
const selectedId = ref<string | null>(null);
</script>

<template>
  <div class="container">
    <!-- Component JSX/template -->
  </div>
</template>

<style scoped>
/* Component styles */
</style>
```

## Deployment

### Build Output

Running `npm run build` generates:

```
public/spa-build/
├── index.js              # Main JavaScript bundle
├── style.css             # Tailwind CSS bundle
├── manifest.json         # Asset manifest
└── assets/               # Images, fonts, etc.
```

### Serving the SPA

The SPA is served from `/cli/{any?}` route in Laravel. The route fallback ensures Vue Router handles all client-side navigation.

### Environment Configuration

Create `.env.local` for development:

```
VITE_API_BASE_URL=http://localhost:8000
VITE_API_TIMEOUT=10000
```

Access in code:

```typescript
const apiUrl = import.meta.env.VITE_API_BASE_URL;
```

## Testing Strategy

### Unit Tests (`__tests__/feature/`)

Test individual components and composables:

```typescript
// src/components/ItemList.test.ts
describe('ItemList', () => {
  it('renders items', () => {
    // Test logic
  });
});
```

### Integration Tests (`__tests__/integration/`)

Test feature workflows with mocked API:

```typescript
// src/__tests__/integration/items.test.ts
describe('Items workflow', () => {
  it('loads and displays items', () => {
    // Test workflow
  });
});
```

### Resource Integration Tests (`__tests__/resource_integration/`)

Test API client usage with real backend:

```typescript
// src/__tests__/resource_integration/items.tests.ts
describe('Items API', () => {
  it('fetches items from API', () => {
    // Real API test
  });
});
```

## Code Quality

All code must pass:

- **TypeScript**: `npm run type-check` - No `any` types
- **Linting**: `npm run lint:check` - ESLint rules
- **Formatting**: `npm run format:check` - Prettier standards
- **Tests**: `npm run test` - All tests passing

Run all checks:

```bash
npm run quality-check
```

## Common Issues

### Port Already in Use

If port 5174 is in use:

```bash
# Use different port
npm run dev -- --port 5175
```

### Module Resolution Issues

Check tsconfig.json paths are correct:

```json
{
  "paths": {
    "@/*": ["./src/*"],
    "@metanull/inventory-app-api-client": ["../node_modules/@metanull/inventory-app-api-client"]
  }
}
```

### API Connection Issues

Ensure:
1. Backend is running: `php artisan serve`
2. API base URL is correct in `.env.local`
3. CORS is properly configured
4. Sanctum token is valid

## Documentation

- [Vue 3 Documentation](https://vuejs.org/)
- [Vue Router Documentation](https://router.vuejs.org/)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)
- [Vite Documentation](https://vitejs.dev/)

## Contributing

When contributing to the SPA:

1. Follow the existing code structure
2. Use TypeScript for all code (no `any` types)
3. Write tests for new features
4. Run quality checks before committing
5. Keep components small and focused
6. Extract reusable logic into composables

## License

Same as parent project - see LICENSE file in repository root.
