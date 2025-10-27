---
layout: default
title: Coding Guidelines
nav_order: 7
parent: Blade/Livewire Frontend
---

# Blade/Livewire Coding Guidelines

This guide documents coding standards and best practices for Blade templates and Livewire components.

## Blade Template Conventions

### File Naming

- Use kebab-case: `item-translation.blade.php`
- Partials start with underscore: `_form.blade.php`
- Layout files describe their purpose: `form-page.blade.php`

### Template Structure

{% raw %}
```blade
{{-- Comments --}}
@extends('layouts.app')

@section('content')
    <div class="container">
        {{-- Template content --}}
    </div>
@endsection
```
{% endraw %}

### Avoid Shorthand Notation

**Critical**: Do not use shorthand component notation as it can cause parsing issues.

{% raw %}
```blade
<!-- Good: Full syntax -->
<x-slot name="header">Header Content</x-slot>

<!-- Bad: Shorthand -->
<x-slot:header>Header Content</x-slot:header>
```
{% endraw %}

### Variable Usage in Components

Use variables instead of string interpolation to avoid nesting issues:

{% raw %}
```blade
<!-- Good: Variable -->
@php($routePrefix = 'items')
<x-component :route="$routePrefix . '.create'" />

<!-- Bad: String interpolation in component -->
<x-component route="{{ $entity }}.create" />
```
{% endraw %}

### Blade Directives

#### Control Structures

{% raw %}
```blade
@if($condition)
    {{-- Content --}}
@elseif($otherCondition)
    {{-- Content --}}
@else
    {{-- Content --}}
@endif

@foreach($items as $item)
    {{ $item->name }}
@endforeach

@forelse($items as $item)
    {{ $item->name }}
@empty
    <p>No items found.</p>
@endforelse
```
{% endraw %}

#### Authentication

{% raw %}
```blade
@auth
    {{-- User is authenticated --}}
@endauth

@guest
    {{-- User is not authenticated --}}
@endguest
```
{% endraw %}

#### Authorization

{% raw %}
```blade
@can(\App\Enums\Permission::VIEW_DATA->value)
    <a href="{{ route('items.index') }}">View Items</a>
@endcan

@cannot(\App\Enums\Permission::DELETE_DATA->value)
    <p>You cannot delete items.</p>
@endcannot
```
{% endraw %}

### PHP in Blade

Keep PHP blocks at the top of sections:

{% raw %}
```blade
@section('content')
    @php($c = $entityColor('items'))
    @php($title = 'Items List')
    
    <div class="{{ $c['bg'] }}">
        <h1>{{ $title }}</h1>
    </div>
@endsection
```
{% endraw %}

**Don't nest `@php` blocks** inside control structures:

{% raw %}
```blade
<!-- Bad: PHP block inside @if -->
@if($condition)
    @php($variable = 'value')
    {{ $variable }}
@endif

<!-- Good: PHP block before control structure -->
@php($variable = $condition ? 'value' : 'default')
@if($condition)
    {{ $variable }}
@endif
```
{% endraw %}

## Component Development

### Component Properties

Document component props with `@props`:

```blade
@props([
    'entity' => '',
    'title' => '',
    'backRoute' => '',
])
```

### Default Values

Provide sensible defaults:

```blade
@props([
    'size' => 'md',
    'variant' => 'primary',
    'entity' => null,
])
```

### Required vs Optional

Use prop validation for required props:

```php
@props([
    'entity',  // Required (no default)
    'title' => '',  // Optional (has default)
])
```

### Slots

Use named slots for complex components:

{% raw %}
```blade
<x-modal>
    <x-slot name="title">Modal Title</x-slot>
    <x-slot name="content">Modal Content</x-slot>
    <x-slot name="footer">Modal Footer</x-slot>
</x-modal>
```
{% endraw %}

## Livewire Component Conventions

### Component Structure

```php
class ItemsTable extends Component
{
    use WithPagination;

    // Public properties (wire:model)
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $search = '';

    // Query string parameters
    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
    ];

    // Methods
    public function sortBy($field)
    {
        // Logic
    }

    public function render()
    {
        return view('livewire.tables.items-table', [
            'items' => $this->getItems(),
        ]);
    }
}
```

### Property Naming

- Use camelCase: `$sortField`, `$searchQuery`
- Boolean prefixes: `$isActive`, `$hasItems`
- Collections suffix: `$items`, `$users`

### Lifecycle Hooks

```php
public function mount()
{
    // Called when component is initialized
}

public function updated($propertyName)
{
    // Called when any property is updated
}

public function updatedSearch()
{
    // Called when specific property is updated
    $this->resetPage();
}
```

## Entity Color System

Always use the entity color helper:

{% raw %}
```blade
@php($c = $entityColor('items'))

<div class="{{ $c['bg'] }}">Background</div>
<a href="#" class="{{ $c['accentLink'] }}">Link</a>
<button class="{{ $c['button'] }}">Button</button>
```
{% endraw %}

### Available Color Properties

- `bg` - Background color
- `text` - Text color
- `button` - Button styling
- `badge` - Badge/pill styling
- `accentLink` - Link styling
- `borderColor` - Border color
- `hoverBg` - Hover background

## Styling Conventions

### Tailwind Classes

- Use utility classes directly in templates
- Order classes logically: layout → sizing → spacing → colors → typography
- Use responsive prefixes: `sm:`, `md:`, `lg:`

### Class Order Example

```blade
<div class="flex items-center justify-between p-4 bg-white rounded-lg shadow-sm hover:shadow-md">
```

### Responsive Design

```blade
<div class="flex flex-col sm:flex-row gap-4">
    <!-- Mobile: stacked, Desktop: horizontal -->
</div>

<td class="hidden lg:table-cell px-4 py-3">
    <!-- Hidden on mobile, visible on desktop -->
</td>
```

## Error Handling

### Validation Errors

Display validation errors automatically with form components:

{% raw %}
```blade
<x-form.field label="Name" name="internal_name">
    <x-form.input name="internal_name" :value="old('internal_name')" />
</x-form.field>
```
{% endraw %}

### Flash Messages

Show success/error messages:

{% raw %}
```blade
@if(session('status'))
    <x-ui.alert :message="session('status')" type="success" entity="items" />
@endif
```
{% endraw %}

## Performance

### Eager Loading

Prevent N+1 queries:

```php
$items = Item::with(['partner', 'country', 'translations'])->get();
```

### Pagination

Always paginate large datasets:

```php
$items = Item::paginate(15);
```

### Livewire Lazy Loading

```php
<livewire:items-table lazy />
```

## Security

### Mass Assignment Protection

Define fillable or guarded in models:

```php
protected $fillable = ['internal_name', 'type'];
```

### CSRF Protection

Always include CSRF token in forms:

{% raw %}
```blade
<form method="POST">
    @csrf
</form>
```
{% endraw %}

### Authorization

Check permissions in views:

{% raw %}
```blade
@can(\App\Enums\Permission::CREATE_DATA->value)
    <a href="{{ route('items.create') }}">Add Item</a>
@endcan
```
{% endraw %}

## Best Practices

1. **DRY Principle** - Extract reusable components and partials
2. **Entity Colors** - Use color system for consistency
3. **Responsive Design** - Mobile-first approach
4. **Accessibility** - Semantic HTML, ARIA labels, keyboard navigation
5. **Performance** - Eager loading, pagination, caching
6. **Security** - CSRF tokens, authorization, validation
7. **Testing** - Write tests for components and workflows
8. **Documentation** - Comment complex logic

## Related Documentation

- [Components]({{ '/frontend-blade/components/' | relative_url }})
- [Livewire]({{ '/frontend-blade/livewire/' | relative_url }})
- [Styling]({{ '/frontend-blade/styling/' | relative_url }})
- [Testing]({{ '/frontend-blade/testing' | relative_url }})
