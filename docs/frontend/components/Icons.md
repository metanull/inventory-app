# Icon Components

SVG icon components used throughout the application. All icons are implemented as Vue components with consistent sizing and styling.

## Application Icons

### ProjectIcon

Icon representing projects and project-related functionality.

**Usage:**

```vue
<ProjectIcon />
```

### ContextIcon

Icon for context-related features.

### CountryIcon

Icon representing countries and geographical features.

### LanguageIcon

Icon for language and localization features.

### FeaturesIcon

Icon for feature toggles and feature-related functionality.

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
