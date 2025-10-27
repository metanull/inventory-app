---
layout: default
title: Entity Components
nav_order: 6
parent: Components
grand_parent: Blade/Livewire Frontend
---

# Entity Components

Entity components provide visual consistency across different entity types using the entity color system.

## Entity Header

The `<x-entity.header>` component creates consistent page headers with entity-specific icons and colors.

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `entity` | string | Yes | Entity key (items, partners, collections, etc.) |
| `title` | string | No | Page title (auto-generated from entity if not provided) |
| `icon` | string | No | Override default icon |

### Usage

{% raw %}
```blade
<x-entity.header entity="items" title="Items">
    <a href="{{ route('items.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
        <x-heroicon-o-plus class="w-5 h-5 mr-1" />
        Add Item
    </a>
</x-entity.header>
```
{% endraw %}

### Features

- **Automatic icon** - Each entity has a default icon
- **Color-coded** - Uses entity color system
- **Action slot** - Add buttons or other actions
- **Responsive** - Adapts to mobile screens

### Entity Icon Map

```php
$iconMap = [
    'items' => 'archive-box',
    'partners' => 'user-group',
    'collections' => 'rectangle-stack',
    'countries' => 'globe-europe-africa',
    'languages' => 'language',
    'tags' => 'tag',
    'glossaries' => 'book-open',
    'contexts' => 'squares-2x2',
];
```

## Entity Color System

Access entity colors using the `$entityColor()` helper:

{% raw %}
```blade
@php($c = $entityColor('items'))

<!-- Use color classes -->
<div class="{{ $c['bg'] }}">Background</div>
<div class="{{ $c['text'] }}">Text</div>
<div class="{{ $c['button'] }}">Button</div>
<div class="{{ $c['badge'] }}">Badge</div>
<div class="{{ $c['accentLink'] }}">Link</div>
```
{% endraw %}

### Available Color Classes

Each entity provides these color variants:

- `bg` - Background color
- `text` - Text color
- `button` - Button styling
- `badge` - Badge/pill styling
- `accentLink` - Link styling
- `borderColor` - Border color
- `hoverBg` - Hover background

### Entity Color Palette

| Entity | Primary Color | Usage |
|--------|--------------|-------|
| items | Teal | `bg-teal-600` |
| partners | Yellow | `bg-yellow-500` |
| collections | Purple | `bg-purple-600` |
| countries | Indigo | `bg-indigo-600` |
| languages | Fuchsia | `bg-fuchsia-600` |
| tags | Green | `bg-green-600` |
| glossaries | Blue | `bg-blue-600` |
| contexts | Gray | `bg-gray-600` |

## Display Badge

The `<x-display.badge>` component creates entity-colored badges.

### Usage

{% raw %}
```blade
<x-display.badge entity="items">
    Legacy: {{ $item->backward_compatibility }}
</x-display.badge>

<x-display.badge entity="partners">
    Type: {{ $partner->type }}
</x-display.badge>
```
{% endraw %}

## Best Practices

1. **Consistent entity keys** - Use the same key throughout the app
2. **Color accessibility** - Ensure sufficient contrast
3. **Visual hierarchy** - Use colors to group related content
4. **User familiarity** - Keep colors consistent for user recognition

## Related Documentation

- [Styling Guide]({{ '/frontend-blade/styling/' | relative_url }})
- [Icons]({{ '/frontend-blade/components/icons' | relative_url }})
- [Buttons]({{ '/frontend-blade/components/buttons' | relative_url }})
