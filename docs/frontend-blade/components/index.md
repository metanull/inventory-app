---
layout: default
title: Components
nav_order: 1
parent: Blade/Livewire Frontend
has_children: true
---

# Blade Components

This section documents the reusable Blade components used throughout the application.

## Component Organization

Blade components are organized in `resources/views/components/`:

```
resources/views/components/
├── button.blade.php           # Button component
├── card.blade.php             # Card container
├── form/                      # Form-related components
│   ├── input.blade.php
│   ├── select.blade.php
│   ├── textarea.blade.php
│   └── error.blade.php
├── table/                     # Table components
│   ├── table.blade.php
│   ├── header.blade.php
│   ├── row.blade.php
│   └── cell.blade.php
└── ...
```

## Using Components

Components are called using the `<x-component-name>` syntax:

{% raw %}
```blade
{{-- Simple component --}}
<x-button>Click Me</x-button>

{{-- Component with attributes --}}
<x-button color="blue" size="lg">
    Large Blue Button
</x-button>

{{-- Nested components --}}
<x-card>
    <x-slot name="header">
        Card Title
    </x-slot>
    
    Card content goes here
    
    <x-slot name="footer">
        <x-button>Action</x-button>
    </x-slot>
</x-card>
```
{% endraw %}

## Common Component Patterns

### Entity Color Integration

Many components accept entity-based colors:

```blade
<x-badge :color="$entityColor('item')">
    Item Badge
</x-badge>
```

### Conditional Rendering

```blade
<x-button 
    :disabled="$isDisabled"
    :loading="$isLoading"
>
    Submit
</x-button>
```

### Slots

```blade
<x-modal>
    <x-slot name="title">
        Modal Title
    </x-slot>
    
    <x-slot name="content">
        Modal body content
    </x-slot>
    
    <x-slot name="footer">
        <x-button>Close</x-button>
    </x-slot>
</x-modal>
```

## Component Categories

- **[Forms](forms)** - Input fields, selects, textareas
- **[Tables](tables)** - Data tables and grids
- **[Modals](modals)** - Dialog boxes and overlays
- **[Navigation](navigation)** - Menus, breadcrumbs, pagination

## Best Practices

1. **Keep components focused** - One responsibility per component
2. **Use entity colors** - Leverage the entity color system
3. **Document props** - Add PHPDoc comments for component attributes
4. **Test thoroughly** - Include component tests
5. **Follow naming conventions** - Use kebab-case for component names

## Related Documentation

- [Livewire Components]({{ '/frontend-blade/livewire/' | relative_url }})
- [Styling Guide]({{ '/frontend-blade/styling/' | relative_url }})
- [Alpine.js Patterns]({{ '/frontend-blade/alpine/' | relative_url }})
