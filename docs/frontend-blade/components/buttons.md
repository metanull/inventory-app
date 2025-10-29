---
layout: default
title: Buttons
nav_order: 2
parent: Components
grand_parent: Blade/Livewire Frontend
---

# Button Components

Button components provide consistent, accessible buttons throughout the application with entity-specific color theming.

## Component Files

Button components are in `resources/views/components/`:

```
components/
├── button.blade.php           # Primary button
├── danger-button.blade.php    # Danger/destructive button
├── secondary-button.blade.php # Secondary/cancel button
└── ui/
    └── mobile-button.blade.php # Mobile-optimized button
```

## Primary Button

The `<x-button>` component is the main call-to-action button.

### Props

| Prop                                | Type   | Required | Description                                                  |
| ----------------------------------- | ------ | -------- | ------------------------------------------------------------ |
| `type`                              | string | No       | Button type: 'button', 'submit', 'reset' (default: 'submit') |
| All standard HTML button attributes |        | No       | class, disabled, etc.                                        |

### Usage

{% raw %}

```blade
<!-- Simple button -->
<x-button>
    Save
</x-button>

<!-- Button with type -->
<x-button type="button">
    Click Me
</x-button>

<!-- Disabled button -->
<x-button disabled>
    Disabled
</x-button>

<!-- Button with icon -->
<x-button>
    <x-heroicon-o-plus class="w-5 h-5 mr-1" />
    Add Item
</x-button>

<!-- Button with custom classes -->
<x-button class="w-full">
    Full Width
</x-button>
```

{% endraw %}

### Styling

Default styling (Indigo theme):

```css
bg-indigo-600 hover:bg-indigo-700 text-white
px-4 py-2 rounded-md text-sm font-medium
shadow-sm disabled:opacity-75
```

## Danger Button

The `<x-danger-button>` component is for destructive actions like deletions.

### Usage

{% raw %}

```blade
<!-- Delete button -->
<x-danger-button type="button" onclick="confirmDelete()">
    Delete
</x-danger-button>

<!-- With Livewire -->
<x-danger-button wire:click="deleteItem">
    Delete Item
</x-danger-button>

<!-- With icon -->
<x-danger-button>
    <x-heroicon-o-trash class="w-4 h-4 mr-1" />
    Delete
</x-danger-button>
```

{% endraw %}

### Styling

Red theme for destructive actions:

```css
bg-red-600 hover:bg-red-700 text-white
px-4 py-2 rounded-md text-sm font-medium
shadow-sm disabled:opacity-75
```

## Secondary Button

The `<x-secondary-button>` component is for secondary actions like "Cancel".

### Usage

{% raw %}

```blade
<!-- Cancel button -->
<x-secondary-button type="button" onclick="goBack()">
    Cancel
</x-secondary-button>

<!-- Close modal -->
<x-secondary-button @click="$dispatch('close-modal', 'modal-name')">
    Close
</x-secondary-button>

<!-- Back button -->
<x-secondary-button>
    <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" />
    Back
</x-secondary-button>
```

{% endraw %}

### Styling

Gray theme for neutral actions:

```css
bg-white hover:bg-gray-50 text-gray-700
border border-gray-300 px-4 py-2 rounded-md
text-sm font-medium shadow-sm
```

## Entity-Colored Buttons

Buttons can use entity-specific colors for consistency:

{% raw %}

```blade
@php($c = $entityColor('items'))

<a href="{{ route('items.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
    <x-heroicon-o-plus class="w-5 h-5 mr-1" />
    Add Item
</a>
```

{% endraw %}

### Entity Color Variants

The entity color system provides these button classes:

- **items**: `bg-teal-600 hover:bg-teal-700 text-white`
- **partners**: `bg-yellow-500 hover:bg-yellow-600 text-white`
- **collections**: `bg-purple-600 hover:bg-purple-700 text-white`
- **countries**: `bg-indigo-600 hover:bg-indigo-700 text-white`
- **languages**: `bg-fuchsia-600 hover:bg-fuchsia-700 text-white`
- **tags**: `bg-green-600 hover:bg-green-700 text-white`

## Mobile-Optimized Button

The `<x-ui.mobile-button>` component adapts for touch interfaces.

### Props

| Prop              | Type    | Required | Description                                  |
| ----------------- | ------- | -------- | -------------------------------------------- |
| `size`            | string  | No       | 'xs', 'sm', 'md', 'lg', 'xl' (default: 'md') |
| `variant`         | string  | No       | 'primary', 'secondary', 'danger'             |
| `entity`          | string  | No       | Entity key for color                         |
| `mobileOptimized` | boolean | No       | Enable mobile optimization (default: true)   |

### Usage

{% raw %}

```blade
<x-ui.mobile-button
    size="lg"
    variant="primary"
    entity="items"
>
    Large Touch-Friendly Button
</x-ui.mobile-button>
```

{% endraw %}

### Mobile Touch Targets

On touch devices, buttons automatically increase in size:

```css
/* Desktop */
px-4 py-2 text-sm

/* Mobile/Touch */
touch:px-5 touch:py-3 touch:text-base
```

## Button Groups

Buttons can be grouped together for related actions:

{% raw %}

```blade
<div class="flex gap-2">
    <x-button type="submit">
        Save
    </x-button>
    <x-secondary-button type="button" onclick="goBack()">
        Cancel
    </x-secondary-button>
</div>
```

{% endraw %}

### Responsive Button Groups

{% raw %}

```blade
<div class="flex flex-col sm:flex-row gap-2">
    <!-- Stacks vertically on mobile, horizontally on desktop -->
    <x-button class="w-full sm:w-auto">
        Primary Action
    </x-button>
    <x-secondary-button class="w-full sm:w-auto">
        Secondary Action
    </x-secondary-button>
</div>
```

{% endraw %}

## Button States

### Loading State

{% raw %}

```blade
<x-button wire:loading.attr="disabled" wire:target="save">
    <x-ui.loading size="sm" color="white" wire:loading wire:target="save" class="mr-2" />
    <span wire:loading.remove wire:target="save">Save</span>
    <span wire:loading wire:target="save">Saving...</span>
</x-button>
```

{% endraw %}

### Disabled State

{% raw %}

```blade
<x-button disabled>
    Disabled Button
</x-button>

<!-- Conditional disable -->
<x-button :disabled="!$hasChanges">
    Save Changes
</x-button>
```

{% endraw %}

### Active/Selected State

{% raw %}

```blade
<button class="{{ $isActive ? 'bg-indigo-700' : 'bg-indigo-600' }} text-white px-4 py-2 rounded-md">
    {{ $label }}
</button>
```

{% endraw %}

## Icons in Buttons

### Leading Icon

{% raw %}

```blade
<x-button>
    <x-heroicon-o-plus class="w-5 h-5 mr-2" />
    Add New
</x-button>
```

{% endraw %}

### Trailing Icon

{% raw %}

```blade
<x-button>
    Continue
    <x-heroicon-o-arrow-right class="w-5 h-5 ml-2" />
</x-button>
```

{% endraw %}

### Icon-Only Button

{% raw %}

```blade
<button type="button" class="p-2 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white">
    <x-heroicon-o-pencil class="w-5 h-5" />
    <span class="sr-only">Edit</span>
</button>
```

{% endraw %}

## Link Buttons

Buttons styled as links:

{% raw %}

```blade
<!-- Back link styled as button -->
@php($c = $entityColor('items'))

<a href="{{ route('items.index') }}" class="text-sm {{ $c['accentLink'] }}">
    &larr; Back to list
</a>
```

{% endraw %}

## Form Action Buttons

The `<x-form.actions>` component provides standard form buttons:

{% raw %}

```blade
<x-form.actions
    :cancel-route="route('items.index')"
    entity="items"
/>
```

{% endraw %}

This generates:

- Cancel button (routes back)
- Save button (with loading state)

### Props

| Prop          | Type   | Required | Description                            |
| ------------- | ------ | -------- | -------------------------------------- |
| `cancelRoute` | string | Yes      | URL for cancel button                  |
| `cancelLabel` | string | No       | Cancel button text (default: 'Cancel') |
| `saveLabel`   | string | No       | Save button text (default: 'Save')     |
| `entity`      | string | No       | Entity for button color                |

## Accessibility

All button components follow accessibility best practices:

- **Semantic HTML**: Use `<button>` for actions, `<a>` for navigation
- **Type attribute**: Specify button type (submit, button, reset)
- **Disabled state**: Use `disabled` attribute, not just styling
- **Focus indicators**: Clear focus outlines for keyboard navigation
- **Screen readers**: Include `sr-only` text for icon-only buttons
- **Loading states**: Indicate when actions are processing

## Best Practices

1. **Use the right button type** - Primary for main actions, secondary for cancel
2. **Always include text** - Even for icon buttons (use sr-only if needed)
3. **Disable during loading** - Prevent double submissions
4. **Group related actions** - Keep buttons together logically
5. **Mobile-first sizing** - Ensure buttons are tap-friendly on mobile
6. **Confirm destructive actions** - Use modals for dangerous operations
7. **Consistent styling** - Use entity colors for consistency
8. **Keyboard accessible** - Test with keyboard-only navigation

## Related Documentation

- [Forms]({{ '/frontend-blade/components/forms' | relative_url }})
- [Modals]({{ '/frontend-blade/components/modals' | relative_url }})
- [Icons]({{ '/frontend-blade/components/icons' | relative_url }})
- [Entity Colors]({{ '/frontend-blade/styling/' | relative_url }}#entity-colors)
