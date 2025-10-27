---
layout: default
title: Styling
nav_order: 4
parent: Blade/Livewire Frontend
has_children: false
---

# Styling Guide

This guide covers the Tailwind CSS conventions and entity color system used in the application.

## Tailwind CSS

The application uses Tailwind CSS for styling with utility-first classes.

### Configuration

Tailwind is configured in `tailwind.config.js`:

```javascript
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                // Custom entity colors
                item: colors.blue,
                partner: colors.purple,
                collection: colors.green,
                // ...
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
```

### Common Patterns

#### Layout Container

```blade
<div class="container mx-auto px-4">
    <!-- Content -->
</div>
```

#### Card

```blade
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold mb-4">Card Title</h2>
    <p class="text-gray-700">Card content</p>
</div>
```

#### Grid Layout

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
</div>
```

#### Flexbox

```blade
<div class="flex items-center justify-between">
    <div>Left content</div>
    <div>Right content</div>
</div>
```

#### Responsive Design

```blade
<div class="w-full md:w-1/2 lg:w-1/3">
    Responsive width
</div>

<div class="text-sm md:text-base lg:text-lg">
    Responsive text
</div>
```

## Entity Color System

The application uses a consistent color system for different entity types.

### Available Entity Colors

| Entity | Color | Usage |
|--------|-------|-------|
| Item | Blue | Items, objects, monuments |
| Partner | Purple | Partners, institutions |
| Collection | Green | Collections, exhibitions |
| Tag | Yellow | Tags, categories |
| Project | Indigo | Projects |
| Language | Pink | Languages |
| Country | Red | Countries |

### Using Entity Colors

#### Via PHP Helper

{% raw %}
```blade
<div class="bg-{{ $entityColor('item') }}-100 border-{{ $entityColor('item') }}-500">
    Item content
</div>
```
{% endraw %}

#### In Components

{% raw %}
```blade
<x-badge :color="$entityColor('partner')">
    Partner Badge
</x-badge>
```
{% endraw %}

#### Dynamic Colors

{% raw %}
```blade
@php
    $color = match($type) {
        'item' => 'blue',
        'partner' => 'purple',
        'collection' => 'green',
        default => 'gray',
    };
@endphp

<div class="bg-{{ $color }}-100 text-{{ $color }}-800">
    Colored content
</div>
```
{% endraw %}

### Color Variants

Tailwind provides different shades (50-900):

```blade
<!-- Light backgrounds -->
<div class="bg-blue-50">Very light blue</div>
<div class="bg-blue-100">Light blue</div>

<!-- Medium backgrounds -->
<div class="bg-blue-500">Medium blue</div>

<!-- Dark backgrounds -->
<div class="bg-blue-800">Dark blue</div>
<div class="bg-blue-900">Very dark blue</div>

<!-- Text colors -->
<span class="text-blue-600">Blue text</span>

<!-- Border colors -->
<div class="border-blue-500">Blue border</div>
```

### Common Entity Color Patterns

#### Badge

{% raw %}
```blade
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $entityColor('item') }}-100 text-{{ $entityColor('item') }}-800">
    Item
</span>
```
{% endraw %}

#### Button

{% raw %}
```blade
<button class="bg-{{ $entityColor('partner') }}-600 hover:bg-{{ $entityColor('partner') }}-700 text-white px-4 py-2 rounded">
    Partner Action
</button>
```
{% endraw %}

#### Alert

{% raw %}
```blade
<div class="bg-{{ $entityColor('collection') }}-50 border-l-4 border-{{ $entityColor('collection') }}-500 p-4">
    <p class="text-{{ $entityColor('collection') }}-700">Collection notice</p>
</div>
```
{% endraw %}

## Typography

### Headings

```blade
<h1 class="text-3xl font-bold text-gray-900">Heading 1</h1>
<h2 class="text-2xl font-semibold text-gray-900">Heading 2</h2>
<h3 class="text-xl font-medium text-gray-900">Heading 3</h3>
<h4 class="text-lg font-medium text-gray-900">Heading 4</h4>
```

### Body Text

```blade
<p class="text-base text-gray-700">Regular paragraph</p>
<p class="text-sm text-gray-600">Small text</p>
<p class="text-xs text-gray-500">Extra small text</p>
```

### Links

```blade
<a href="#" class="text-blue-600 hover:text-blue-800 underline">
    Standard link
</a>
```

## Forms

### Input Fields

```blade
<input 
    type="text" 
    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    placeholder="Enter text"
/>
```

### Select Dropdown

```blade
<select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
    <option>Option 1</option>
    <option>Option 2</option>
</select>
```

### Textarea

```blade
<textarea 
    rows="4" 
    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
></textarea>
```

### Checkbox

```blade
<input 
    type="checkbox" 
    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
/>
```

## Buttons

### Primary Button

```blade
<button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
    Primary Action
</button>
```

### Secondary Button

```blade
<button class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded">
    Secondary Action
</button>
```

### Danger Button

```blade
<button class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded">
    Delete
</button>
```

### Button Sizes

```blade
<button class="px-2 py-1 text-sm">Small</button>
<button class="px-4 py-2 text-base">Medium</button>
<button class="px-6 py-3 text-lg">Large</button>
```

## Icons

The application uses Heroicons for SVG icons.

### Using Icons

```blade
<!-- Outline icons (24x24) -->
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
    <path stroke-linecap="round" stroke-linejoin="round" d="..." />
</svg>

<!-- Solid icons (20x20) -->
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
    <path d="..." />
</svg>
```

### Icon Sizes

```blade
<svg class="w-4 h-4"><!-- 16px --></svg>
<svg class="w-5 h-5"><!-- 20px --></svg>
<svg class="w-6 h-6"><!-- 24px --></svg>
<svg class="w-8 h-8"><!-- 32px --></svg>
```

### Icon with Text

```blade
<button class="flex items-center gap-2">
    <svg class="w-5 h-5"><!-- icon --></svg>
    <span>Button Text</span>
</button>
```

## Best Practices

1. **Use utility classes** - Prefer Tailwind utilities over custom CSS
2. **Be consistent** - Follow entity color conventions
3. **Responsive design** - Use responsive modifiers (sm:, md:, lg:)
4. **Dark mode** - Prepare for dark mode with dark: prefix
5. **Accessibility** - Include focus states and ARIA attributes
6. **Component extraction** - Extract repeated patterns into Blade components

## Custom CSS

When Tailwind utilities aren't enough, add custom CSS to `resources/css/app.css`:

```css
@layer components {
    .btn-primary {
        @apply bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded;
    }
}
```

## Related Documentation

- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Heroicons](https://heroicons.com/)
- [Blade Components]({{ '/frontend-blade/components/' | relative_url }})
