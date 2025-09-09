---
layout: default
title: Icon Standards
parent: Components
nav_order: 4
---

# Icon Standards

## CRITICAL REQUIREMENTS

**ONLY Heroicons are allowed** - No exceptions.

### FORBIDDEN ❌
- Inline SVG code in components
- Creating separate components just for SVG icons  
- Custom SVG files or other icon libraries
- Any custom icon components

### REQUIRED ✅
- **ONLY use Heroicons**: Import from `@heroicons/vue/24/solid` or `@heroicons/vue/24/outline`
- Import icons directly in the component where they're used
- Use semantic aliases for clarity: `import { CogIcon as ContextIcon }`

## Entity Icon Standards

Each entity has a standardized icon that must be used consistently:

| Entity | Icon | Import Path | Usage Context |
|--------|------|-------------|---------------|
| Items | `ArchiveBoxIcon` | `@heroicons/vue/24/solid` | All Items-related components |
| Partners | `UserGroupIcon` | `@heroicons/vue/24/solid` | All Partners-related components |
| Languages | `LanguageIcon` | `@heroicons/vue/24/outline` | All Languages-related components |
| Countries | `GlobeAltIcon` | `@heroicons/vue/24/outline` | All Countries-related components |
| Contexts | `CogIcon` | `@heroicons/vue/24/outline` | All Contexts-related components |
| Projects | `FolderIcon` | `@heroicons/vue/24/outline` | All Projects-related components |

## Icon Size Standards

| Context | Size Class | Example Usage |
|---------|------------|---------------|
| Navigation Menu | `w-4 h-4` | AppHeader.vue menu items |
| List Pages | `h-5 w-5` | Table row icons, InternalName icons |
| Detail Pages | `h-6 w-6` | Resource icons in DetailView |
| Dashboard Cards | Auto-sized | NavigationCard component handles sizing |

## Implementation Examples

### ✅ CORRECT Usage

```vue
<script setup lang="ts">
// Import Heroicons directly
import { CogIcon as ContextIcon, CheckCircleIcon } from '@heroicons/vue/24/solid'
import { ArrowLeftIcon, PencilIcon } from '@heroicons/vue/24/outline'
</script>

<template>
  <!-- Use icons directly in template with proper classes -->
  <ContextIcon class="h-6 w-6 text-green-600" />
  <CheckCircleIcon class="h-4 w-4 text-green-600" />
  
  <!-- Navigation menu usage -->
  <ContextIcon class="w-4 h-4 text-green-600" />
  
  <!-- List page usage -->  
  <ContextIcon class="h-5 w-5 text-green-600" />
</template>
```

### ❌ FORBIDDEN Usage

```vue
<!-- NEVER do these -->
<template>
  <!-- Inline SVG - FORBIDDEN -->
  <svg viewBox="0 0 24 24">
    <path d="..."/>
  </svg>
  
  <!-- Custom icon components - FORBIDDEN -->
  <CustomContextIcon />
  <ProjectIcon />  <!-- Custom component -->
</template>
```

## Color Consistency

Icons must use entity-specific colors:

```vue
<!-- Contexts always use green -->
<CogIcon class="text-green-600" />

<!-- Items always use teal -->  
<ArchiveBoxIcon class="text-teal-600" />

<!-- Countries always use blue -->
<GlobeAltIcon class="text-blue-600" />
```

## Status Icons

### CheckCircleIcon

Icon indicating success, completion, or enabled status.

**Usage:**

```vue
<CheckCircleIcon class="text-green-600" />
```

### XCircleIcon

Icon indicating failure, cancellation, or disabled status.

**Usage:**

```vue
<XCircleIcon class="text-red-600" />
```

## Action Icons

### RocketIcon

Icon representing launch, deployment, or active status.

### PackageIcon

Icon for packages, modules, or inactive status.

## System Icons

### SystemIcon

Generic system or settings icon.

### GenericIcon

Fallback icon for generic use cases.

## Usage Patterns

### In Status Cards

```vue
<StatusCard
  :active-icon-component="CheckCircleIcon"
  :inactive-icon-component="XCircleIcon"
  active-icon-class="text-green-600"
  inactive-icon-class="text-red-600"
/>
```

### In Navigation

```vue
<router-link to="/projects">
  <ProjectIcon class="w-5 h-5 mr-2" />
  Projects
</router-link>
```

### In Back Links

```vue
const backLink = computed(() => ({ title: 'Back to Projects', route:
'/projects', icon: ProjectIcon, color: 'orange' }))
```

## Styling

All icons support standard CSS classes for:

- **Size**: `w-4 h-4`, `w-5 h-5`, `w-6 h-6`, etc.
- **Color**: `text-gray-600`, `text-blue-600`, etc.
- **Margin/Padding**: `mr-2`, `ml-1`, etc.

## Icon Guidelines

1. **Consistent Sizing**: Use standardized sizes (4, 5, 6 units) for consistency
2. **Color Context**: Apply appropriate colors based on context (status, theme, etc.)
3. **Accessibility**: Icons should be accompanied by appropriate text or aria-labels
4. **Performance**: Icons are optimized SVG components for fast rendering

## Example Usage in Components

### List Header

```vue
<div class="flex items-center">
  <ProjectIcon class="w-8 h-8 mr-3 text-orange-600" />
  <Title variant="page">Projects</Title>
</div>
```

### Button with Icon

```vue
<button class="flex items-center">
  <CheckCircleIcon class="w-4 h-4 mr-2" />
  Enable
</button>
```

### Status Indicator

```vue
<div class="flex items-center">
  <CheckCircleIcon v-if="isActive" class="w-5 h-5 text-green-600" />
  <XCircleIcon v-else class="w-5 h-5 text-red-600" />
  <span>{{ status }}</span>
</div>
```
