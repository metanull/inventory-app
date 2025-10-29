---
layout: default
title: Icons
nav_order: 5
parent: Components
grand_parent: Blade/Livewire Frontend
---

# Icon Components

The application uses [Heroicons](https://heroicons.com/) for consistent, beautiful SVG icons.

## Icon Styles

Heroicons provides two styles:

- **Outline** (`heroicon-o-*`): 24x24px stroke-based icons
- **Solid** (`heroicon-s-*`): 20x20px filled icons
- **Mini** (`heroicon-m-*`): 20x20px solid icons

## Usage

### Outline Icons

{% raw %}

```blade
<x-heroicon-o-home class="w-6 h-6" />
<x-heroicon-o-user class="w-5 h-5 text-gray-500" />
<x-heroicon-o-cog class="w-4 h-4 mr-2" />
```

{% endraw %}

### Solid Icons

{% raw %}

```blade
<x-heroicon-s-check-circle class="w-5 h-5 text-green-500" />
<x-heroicon-s-x-circle class="w-5 h-5 text-red-500" />
```

{% endraw %}

### Mini Icons

{% raw %}

```blade
<x-heroicon-m-star class="w-5 h-5 text-yellow-500" />
```

{% endraw %}

## Common Icons

### Navigation

{% raw %}

```blade
<x-heroicon-o-home class="w-5 h-5" />              <!-- Home -->
<x-heroicon-o-squares-2x2 class="w-5 h-5" />       <!-- Grid/Dashboard -->
<x-heroicon-o-archive-box class="w-5 h-5" />       <!-- Items -->
<x-heroicon-o-user-group class="w-5 h-5" />        <!-- Partners/Users -->
<x-heroicon-o-photo class="w-5 h-5" />             <!-- Images -->
<x-heroicon-o-cog-6-tooth class="w-5 h-5" />       <!-- Settings -->
```

{% endraw %}

### Actions

{% raw %}

```blade
<x-heroicon-o-plus class="w-5 h-5" />              <!-- Add -->
<x-heroicon-o-pencil-square class="w-4 h-4" />     <!-- Edit -->
<x-heroicon-o-trash class="w-4 h-4" />             <!-- Delete -->
<x-heroicon-o-eye class="w-4 h-4" />               <!-- View -->
<x-heroicon-o-arrow-down-tray class="w-5 h-5" />   <!-- Download -->
<x-heroicon-o-arrow-up-tray class="w-5 h-5" />     <!-- Upload -->
```

{% endraw %}

### Status/Feedback

{% raw %}

```blade
<x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />     <!-- Success -->
<x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />           <!-- Error -->
<x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-500" />  <!-- Warning -->
<x-heroicon-o-information-circle class="w-5 h-5 text-blue-500" />  <!-- Info -->
```

{% endraw %}

### Sorting

{% raw %}

```blade
<x-heroicon-o-chevron-up class="w-4 h-4" />        <!-- Sort ascending -->
<x-heroicon-o-chevron-down class="w-4 h-4" />      <!-- Sort descending -->
<x-heroicon-o-arrows-up-down class="w-4 h-4" />    <!-- Sortable -->
```

{% endraw %}

## Icon Sizing

Standard sizes:

```blade
class="w-3 h-3"   <!-- 12px - Tiny -->
class="w-4 h-4"   <!-- 16px - Small -->
class="w-5 h-5"   <!-- 20px - Medium (default) -->
class="w-6 h-6"   <!-- 24px - Large -->
class="w-8 h-8"   <!-- 32px - Extra Large -->
```

## Entity Icons

Each entity has a specific icon:

{% raw %}

```blade
<!-- Items -->
<x-heroicon-o-archive-box class="w-5 h-5" />

<!-- Partners -->
<x-heroicon-o-user-group class="w-5 h-5" />

<!-- Collections -->
<x-heroicon-o-rectangle-stack class="w-5 h-5" />

<!-- Countries -->
<x-heroicon-o-globe-europe-africa class="w-5 h-5" />

<!-- Languages -->
<x-heroicon-o-language class="w-5 h-5" />

<!-- Tags -->
<x-heroicon-o-tag class="w-5 h-5" />

<!-- Glossary -->
<x-heroicon-o-book-open class="w-5 h-5" />
```

{% endraw %}

## Icon with Text

### Leading Icon

{% raw %}

```blade
<a href="#" class="flex items-center gap-2">
    <x-heroicon-o-plus class="w-5 h-5" />
    <span>Add Item</span>
</a>
```

{% endraw %}

### Trailing Icon

{% raw %}

```blade
<button class="flex items-center gap-2">
    <span>Continue</span>
    <x-heroicon-o-arrow-right class="w-5 h-5" />
</button>
```

{% endraw %}

## Entity Header Icons

The `<x-entity.header>` component automatically includes icons:

{% raw %}

```blade
<x-entity.header entity="items" title="Items">
    <!-- Icon is automatically rendered -->
</x-entity.header>
```

{% endraw %}

## Best Practices

1. **Consistent sizing** - Use `w-5 h-5` for most icons
2. **Color context** - Use text colors to convey meaning
3. **Accessibility** - Include `aria-label` or `sr-only` text
4. **Icon-only buttons** - Always include screen reader text
5. **Loading states** - Use spinner icons for async actions
6. **Visual hierarchy** - Larger icons for headers, smaller for actions

## Related Documentation

- [Buttons]({{ '/frontend-blade/components/buttons' | relative_url }})
- [Entity Components]({{ '/frontend-blade/components/entity' | relative_url }})
- [Navigation]({{ '/frontend-blade/components/navigation' | relative_url }})
