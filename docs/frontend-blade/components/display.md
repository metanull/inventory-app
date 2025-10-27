---
layout: default
title: Display Components
nav_order: 9
parent: Components
grand_parent: Blade/Livewire Frontend
---

# Display Components

Display components format and present data consistently across the application.

## Display Badge

The `<x-display.badge>` component creates colored badges for labels and status indicators.

### Usage

{% raw %}
```blade
<x-display.badge entity="items">
    Legacy: {{ $item->backward_compatibility }}
</x-display.badge>

<x-display.badge entity="tags">
    {{ $tag->internal_name }}
</x-display.badge>
```
{% endraw %}

## System Properties

The `<x-system-properties>` component displays metadata like created/updated timestamps.

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `created` | datetime | No | Created timestamp |
| `updated` | datetime | No | Updated timestamp |

### Usage

{% raw %}
```blade
<x-system-properties 
    :created="$item->created_at"
    :updated="$item->updated_at"
/>
```
{% endraw %}

## Format Components

Format components are in `resources/views/components/format/`:

### Date Formatting

{% raw %}
```blade
<!-- Display formatted date -->
{{ $item->created_at?->format('Y-m-d H:i') }}

<!-- Or use Carbon methods -->
{{ $item->created_at?->diffForHumans() }}
```
{% endraw %}

## Related Documentation

- [Entity Components]({{ '/frontend-blade/components/entity' | relative_url }})
- [Styling]({{ '/frontend-blade/styling/' | relative_url }})
