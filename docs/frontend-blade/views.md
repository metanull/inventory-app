---
layout: default
title: Views and CRUD Patterns
nav_order: 4
parent: Blade/Livewire Frontend
---

# Views and CRUD Patterns

This guide documents the standard patterns for entity CRUD (Create, Read, Update, Delete) views.

## Directory Structure

Entity views follow a consistent structure:

```
resources/views/
├── items/
│   ├── index.blade.php    # List view
│   ├── show.blade.php     # Detail view
│   ├── create.blade.php   # Create form
│   ├── edit.blade.php     # Edit form
│   └── _form.blade.php    # Shared form partial
├── partners/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── _form.blade.php
└── ...
```

## Index View Pattern

List views display all records with filtering, sorting, and pagination.

### Standard Structure

{% raw %}
```blade
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @php($c = $entityColor('items'))
    
    <x-entity.header entity="items" title="Items">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <a href="{{ route('items.create') }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                <x-heroicon-o-plus class="w-5 h-5 mr-1" />
                Add Item
            </a>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="items" />
    @endif

    <livewire:tables.items-table />
</div>
@endsection
```
{% endraw %}

### Key Features

- Entity color integration
- Permission-based "Add" button
- Success/error alerts
- Livewire table component for data

## Show View Pattern

Detail views display a single record with all its information.

### Standard Structure

{% raw %}
```blade
@extends('layouts.app')

@section('content')
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
            <h3 class="text-lg font-medium text-gray-900">Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.field label="Name" name="name">
                    <div class="mt-1 text-sm text-gray-900">{{ $item->internal_name }}</div>
                </x-form.field>
                
                <!-- More fields -->
            </div>
        </div>
    </div>
    
    <x-system-properties 
        :created="$item->created_at"
        :updated="$item->updated_at"
    />
</x-layout.show-page>
@endsection
```
{% endraw %}

## Create View Pattern

Create views present a form for new records.

### Standard Structure

{% raw %}
```blade
@extends('layouts.app')

@section('content')
<x-layout.form-page
    entity="items"
    title="Create Item"
    :backRoute="route('items.index')"
    :submitRoute="route('items.store')"
    method="POST"
>
    @csrf
    
    <div class="px-4 py-5 sm:p-6 space-y-6">
        @include('items._form')
    </div>
    
    <x-form.actions 
        :cancel-route="route('items.index')"
        entity="items"
    />
</x-layout.form-page>
@endsection
```
{% endraw %}

## Edit View Pattern

Edit views present a form for updating existing records.

### Standard Structure

{% raw %}
```blade
@extends('layouts.app')

@section('content')
<x-layout.form-page
    entity="items"
    :title="'Edit: ' . $item->internal_name"
    :backRoute="route('items.show', $item)"
    :submitRoute="route('items.update', $item)"
    method="PUT"
>
    @csrf
    @method('PUT')
    
    <div class="px-4 py-5 sm:p-6 space-y-6">
        @include('items._form')
    </div>
    
    <x-form.actions 
        :cancel-route="route('items.show', $item)"
        entity="items"
    />
</x-layout.form-page>
@endsection
```
{% endraw %}

## Form Partial Pattern

Form partials (`_form.blade.php`) contain shared form fields for create/edit views.

### Standard Structure

{% raw %}
```blade
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <x-form.field label="Name *" name="internal_name">
        <x-form.input 
            name="internal_name" 
            :value="old('internal_name', $item->internal_name ?? '')" 
            required 
        />
    </x-form.field>
    
    <x-form.field label="Type *" name="type">
        <x-form.select name="type" :value="old('type', $item->type ?? '')">
            <option value="">Select type</option>
            <option value="monument">Monument</option>
            <option value="object">Object</option>
        </x-form.select>
    </x-form.field>
</div>
```
{% endraw %}

## Validation and Errors

Form validation errors are displayed automatically:

{% raw %}
```blade
<x-form.field label="Name" name="internal_name">
    <x-form.input name="internal_name" :value="old('internal_name')" />
</x-form.field>

<!-- Error is displayed automatically below the input -->
```
{% endraw %}

## Best Practices

1. **Use layout components** - Leverage `form-page` and `show-page` layouts
2. **Share form partials** - Extract common fields to `_form.blade.php`
3. **Entity colors** - Always use entity color system
4. **Permissions** - Check permissions for create/edit/delete
5. **Validation** - Use FormRequest classes for validation
6. **Flash messages** - Show success/error feedback
7. **Back navigation** - Provide clear back links

## Related Documentation

- [Forms]({{ '/frontend-blade/components/forms' | relative_url }})
- [Layouts]({{ '/frontend-blade/components/layouts' | relative_url }})
- [Entity Components]({{ '/frontend-blade/components/entity' | relative_url }})
- [Routing]({{ '/frontend-blade/routing' | relative_url }})
