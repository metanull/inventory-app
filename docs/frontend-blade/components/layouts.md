---
layout: default
title: Layouts
nav_order: 7
parent: Components
grand_parent: Blade/Livewire Frontend
---

# Layout Components

Layout components provide consistent page structures for forms, detail views, and lists.

## Form Page Layout

The `<x-layout.form-page>` component provides standard layout for create/edit forms.

### Props

| Prop          | Type   | Required | Description                   |
| ------------- | ------ | -------- | ----------------------------- |
| `entity`      | string | Yes      | Entity key for color theming  |
| `title`       | string | Yes      | Page title                    |
| `backRoute`   | string | No       | URL for back link             |
| `submitRoute` | string | Yes      | Form action URL               |
| `method`      | string | No       | HTTP method (default: 'POST') |

### Usage

{% raw %}

```blade
<x-layout.form-page
    entity="items"
    title="Create Item"
    :backRoute="route('items.index')"
    :submitRoute="route('items.store')"
    method="POST"
>
    @csrf

    <div class="px-4 py-5 sm:p-6 space-y-6">
        <!-- Form fields -->
        <x-form.field label="Name" name="name">
            <x-form.input name="name" :value="old('name')" />
        </x-form.field>
    </div>

    <x-form.actions
        :cancel-route="route('items.index')"
        entity="items"
    />
</x-layout.form-page>
```

{% endraw %}

## Show Page Layout

The `<x-layout.show-page>` component provides standard layout for detail/show views.

### Props

| Prop                    | Type   | Required | Description                  |
| ----------------------- | ------ | -------- | ---------------------------- |
| `entity`                | string | Yes      | Entity key                   |
| `title`                 | string | Yes      | Page title                   |
| `backRoute`             | string | No       | URL for back link            |
| `editRoute`             | string | No       | URL for edit button          |
| `deleteRoute`           | string | No       | URL for delete action        |
| `deleteConfirm`         | string | No       | Delete confirmation message  |
| `backwardCompatibility` | string | No       | Legacy ID to display         |
| `badges`                | array  | No       | Additional badges to display |

### Usage

{% raw %}

```blade
<x-layout.show-page
    entity="items"
    :title="$item->internal_name"
    :backRoute="route('items.index')"
    :editRoute="route('items.edit', $item)"
    :deleteRoute="route('items.destroy', $item)"
    deleteConfirm="Delete this item?"
    :backwardCompatibility="$item->backward_compatibility"
>
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6 space-y-6">
            <!-- Display content -->
        </div>
    </div>
</x-layout.show-page>
```

{% endraw %}

## Pagination

The `<x-layout.pagination>` component provides consistent pagination UI.

### Props

| Prop        | Type   | Required | Description                        |
| ----------- | ------ | -------- | ---------------------------------- |
| `paginator` | object | Yes      | Laravel paginator object           |
| `entity`    | string | No       | Entity for styling                 |
| `paramPage` | string | No       | Query param name (default: 'page') |

### Usage

{% raw %}

```blade
<x-layout.pagination
    :paginator="$items"
    entity="items"
    param-page="page"
/>
```

{% endraw %}

## Main App Layout

The main application layout is in `resources/views/layouts/app.blade.php`.

### Structure

{% raw %}

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Meta tags, title, Vite assets -->
</head>
<body>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <x-app-nav />

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <x-app-footer />
    </div>
</body>
</html>
```

{% endraw %}

### Using the Layout

{% raw %}

```blade
@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Page content -->
    </div>
@endsection
```

{% endraw %}

## Related Documentation

- [Forms]({{ '/frontend-blade/components/forms' | relative_url }})
- [Navigation]({{ '/frontend-blade/components/navigation' | relative_url }})
- [Entity Components]({{ '/frontend-blade/components/entity' | relative_url }})
