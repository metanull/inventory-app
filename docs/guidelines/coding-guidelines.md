---
layout: default
title: Coding Guidelines
nav_order: 1
parent: Guidelines
---

# Coding Guidelines

This document outlines the coding standards and best practices for the Inventory Management UI project.

## 🎯 General Principles

### Code Quality
- Write clean, readable, and maintainable code
- Follow the principle of least surprise
- Use meaningful names for variables, functions, and components
- Keep functions and components small and focused
- Comment complex logic and business rules

### Performance
- Optimize for user experience
- Use lazy loading for routes and heavy components
- Implement proper error boundaries
- Minimize bundle size through tree shaking

## 🔧 Vue.js & TypeScript Standards

### Component Structure
- **Use `<script setup>` syntax** for all new components
- **TypeScript strict mode** - all code must be properly typed
- **Composition API** over Options API for new components
- **Single File Components** with proper separation of concerns

```vue
<!-- ✅ Good: Proper SFC structure -->
<template>
  <div class="component-container">
    <!-- Template content -->
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { ApiResponse } from '@/api/client'

// Proper TypeScript typing
const loading = ref<boolean>(false)
const data = ref<ApiResponse | null>(null)

onMounted(() => {
  // Component logic
})
</script>

<style scoped>
/* Component-specific styles */
</style>
```

### Code Organization
- **Keep components focused** - one responsibility per component
- **Use composables** for reusable logic
- **Proper naming** - PascalCase for components, camelCase for functions
- **Type safety** - define interfaces for all API responses and props

```typescript
// ✅ Good - Organized imports
// 1. Vue imports
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

// 2. Third-party imports
import { z } from 'zod';

// 3. Internal imports - stores
import { useUserStore } from '@/stores/user';

// 4. Internal imports - composables
import { useApi } from '@/composables/useApi';

// 5. Internal imports - components
import UserForm from '@/components/forms/UserForm.vue';

// 6. Internal imports - types
import type { User, ApiResponse } from '@/types';
```

## 🎨 Vue.js Guidelines

### Component Naming
- Use PascalCase for component names
- Use descriptive, multi-word names
- Avoid generic names like `Base`, `App`, or `V`

```typescript
// ✅ Good
UserProfile.vue
ItemListView.vue
NavigationMenu.vue

// ❌ Avoid
User.vue
Item.vue
Nav.vue
```

### Props and Events
```vue
<script setup lang="ts">
// ✅ Good - Explicit prop types
interface Props {
  items: Item[];
  loading?: boolean;
  maxItems?: number;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  maxItems: 100
});

// ✅ Good - Typed events
interface Emits {
  'item-selected': [item: Item];
  'load-more': [];
}

const emit = defineEmits<Emits>();
</script>
```

## 🎨 Styling Guidelines

### Tailwind CSS
- Use Tailwind utility classes for consistent styling
- Create custom components for repeated patterns
- Use CSS variables for theme customization

```vue
<template>
  <!-- ✅ Good - Utility classes -->
  <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ title }}</h2>
    <p class="text-gray-600 leading-relaxed">{{ description }}</p>
  </div>
</template>
```

### Component Styling
```vue
<style scoped>
/* ✅ Good - Scoped styles for component-specific needs */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  @apply bg-gray-300 rounded-full;
}

/* Use CSS variables for dynamic values */
.progress-bar {
  width: var(--progress-width, 0%);
}
</style>
```

## ✅ Quality Controls

Before submitting code, ensure all quality controls pass:

### 1. Code Quality

```bash
# Run linting
npm run lint

# Run type checking
npm run type-check

# Format code
npm run format
```

### 2. Build Verification

```bash
# Ensure the application builds successfully
npm run build

# Test the production build
npm run preview
```

### 3. Security & Dependencies

```bash
# Check for vulnerabilities
npm audit

# Fix vulnerabilities if found
npm audit fix

# Update dependencies (if needed)
npm update
```

### 4. Git Hygiene

```bash
# Ensure your branch is up to date
git fetch upstream
git rebase upstream/main

# Squash commits if needed
git rebase -i HEAD~n
```

## 🏗️ Application Architecture

### Entity Management Pattern

Each entity follows a consistent pattern:

```
src/views/
├── EntityName.vue          # List view with CRUD operations
├── EntityNameDetail.vue    # Detail view for single entity
└── __tests__/
    ├── EntityName.test.ts  # Tests for list view
    └── EntityNameDetail.test.ts # Tests for detail view
```

### Adding New Entities

When adding new entities to the application:

1. **API Client** - Add interfaces and CRUD methods to `src/api/client.ts`
2. **Views** - Create list and detail views in `src/views/`
3. **Routes** - Add routes to `src/router/index.ts`
4. **Navigation** - Update `src/components/layout/AppHeader.vue`
5. **Tests** - Add comprehensive tests for new functionality
6. **Documentation** - Update this guide and API documentation

### Core Entities

#### Primary Entities
- **Items** - Inventory objects and monuments
- **Partners** - Museums, institutions, and individuals
- **Projects** - Collections with launch dates and status
- **Tags** - Categorization system
- **Pictures** - Image management

#### Reference Data
- **Countries** - Geographic reference data with names and codes
- **Languages** - Language reference data with names and codes
- **Contexts** - Content organization and categorization
- **ImageUploads** - File upload management for images

#### Content Management
- **Contextualizations** - Links between contexts and content
- **Details** - Detailed information records
- **AvailableImages** - Available image resources

## 🚫 Common Pitfalls

### Avoid These Mistakes

1. **Not following TypeScript strict mode**
2. **Using `any` type instead of proper typing**
3. **Not handling loading and error states**
4. **Ignoring accessibility requirements**
5. **Not updating documentation**
6. **Committing without linting**

---

Following these guidelines ensures consistent, maintainable, and high-quality code across the entire application.
