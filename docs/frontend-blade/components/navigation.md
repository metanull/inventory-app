---
layout: default
title: Navigation
nav_order: 8
parent: Components
grand_parent: Blade/Livewire Frontend
---

# Navigation Components

Navigation components provide consistent navigation patterns throughout the application.

## App Navigation

The `<x-app-nav>` component is the main application navigation bar.

### Features

- Entity-specific navigation with color-coded sections
- Dropdown menus for grouped entities
- User menu with profile and logout
- Mobile-responsive hamburger menu
- Active state highlighting

### Structure

{% raw %}

```blade
<x-app-nav />
```

{% endraw %}

The component includes:

- Logo and home link
- Primary navigation (Items, Partners, Collections)
- Reference Data dropdown (Countries, Languages, Contexts, Glossary, Tags)
- Images dropdown (Available Images, Item Images, Partner Images)
- Admin dropdown (Settings, Projects, Users)
- User menu

## Nav Link

The `<x-nav-link>` component creates navigation links with active states.

### Props

| Prop     | Type    | Required | Description            |
| -------- | ------- | -------- | ---------------------- |
| `href`   | string  | Yes      | Link URL               |
| `active` | boolean | No       | Whether link is active |

### Usage

{% raw %}

```blade
<x-nav-link href="{{ route('items.index') }}" :active="request()->routeIs('items.*')">
    Items
</x-nav-link>
```

{% endraw %}

## Responsive Nav Link

For mobile navigation menus:

{% raw %}

```blade
<x-responsive-nav-link href="{{ route('items.index') }}" :active="request()->routeIs('items.*')">
    Items
</x-responsive-nav-link>
```

{% endraw %}

## Dropdown Navigation

Dropdown menus use Alpine.js for interactivity:

{% raw %}

```blade
<div x-data="{ openMenu: null }">
    <!-- Trigger -->
    <button @click="openMenu = openMenu === 'reference' ? null : 'reference'">
        Reference Data
    </button>

    <!-- Dropdown Menu -->
    <div x-show="openMenu === 'reference'"
         x-transition
         x-cloak
         @click.outside="openMenu = null"
         class="absolute mt-2 w-56 rounded-md bg-white shadow-lg">
        <a href="{{ route('countries.index') }}">Countries</a>
        <a href="{{ route('languages.index') }}">Languages</a>
    </div>
</div>
```

{% endraw %}

## Dropdown Link

The `<x-dropdown-link>` component creates dropdown menu items:

{% raw %}

```blade
<x-dropdown-link href="{{ route('profile.show') }}">
    Profile
</x-dropdown-link>
```

{% endraw %}

## Breadcrumb Navigation

Breadcrumbs are rendered in the default layout:

{% raw %}

```blade
<nav class="text-sm text-gray-600 mb-6">
    <a href="{{ '/api/' | relative_url }}">API Documentation</a>
    &nbsp;&#124;&nbsp;
    <a href="{{ '/models/' | relative_url }}">Database Models</a>
    <!-- ... more breadcrumb links -->
</nav>
```

{% endraw %}

## Related Documentation

- [Layouts]({{ '/frontend-blade/components/layouts' | relative_url }})
- [Alpine.js]({{ '/frontend-blade/alpine/' | relative_url }})
- [Entity Colors]({{ '/frontend-blade/styling/' | relative_url }}#entity-colors)
