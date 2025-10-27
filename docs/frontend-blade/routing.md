---
layout: default
title: Routing
nav_order: 5
parent: Blade/Livewire Frontend
---

# Routing

This guide documents the routing patterns for the Blade/Livewire frontend.

## Route Structure

Web routes are defined in `routes/web.php` and follow Laravel's resource routing conventions.

## Resource Routes

Most entities use standard resource routes:

```php
Route::resource('items', ItemController::class)
    ->middleware(['auth', 'verified']);
```

This creates the following routes:

| Verb | URI | Action | Route Name |
|------|-----|--------|------------|
| GET | `/items` | index | items.index |
| GET | `/items/create` | create | items.create |
| POST | `/items` | store | items.store |
| GET | `/items/{item}` | show | items.show |
| GET | `/items/{item}/edit` | edit | items.edit |
| PUT/PATCH | `/items/{item}` | update | items.update |
| DELETE | `/items/{item}` | destroy | items.destroy |

## Route Naming Conventions

Always use named routes for consistency:

{% raw %}
```blade
<!-- Good: Named route -->
<a href="{{ route('items.index') }}">Items</a>
<a href="{{ route('items.show', $item) }}">View Item</a>

<!-- Bad: Hard-coded URL -->
<a href="/items">Items</a>
```
{% endraw %}

## Route Prefixes

Web routes use the `/web` prefix:

```php
Route::prefix('web')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', function () {
        return view('home');
    })->name('home');
    
    Route::resource('items', ItemController::class);
    Route::resource('partners', PartnerController::class);
    // ...
});
```

## Middleware

Common middleware applied to routes:

### Authentication

```php
Route::middleware(['auth'])->group(function () {
    // Routes requiring authentication
});
```

### Email Verification

```php
Route::middleware(['verified'])->group(function () {
    // Routes requiring verified email
});
```

### Permission-Based

```php
Route::middleware(['permission:view-data'])->group(function () {
    // Routes requiring specific permissions
});
```

## Custom Routes

For non-standard actions, use custom routes:

```php
// Attach/detach relationships
Route::post('items/{item}/tags/attach', [ItemController::class, 'attachTag'])
    ->name('items.tags.attach');
Route::delete('items/{item}/tags/{tag}', [ItemController::class, 'detachTag'])
    ->name('items.tags.detach');

// Image management
Route::post('items/{item}/images', [ItemImageController::class, 'store'])
    ->name('items.images.store');
Route::delete('items/{item}/images/{image}', [ItemImageController::class, 'destroy'])
    ->name('items.images.destroy');
```

## Route Parameters

### Required Parameters

{% raw %}
```blade
<a href="{{ route('items.show', $item) }}">View</a>
<a href="{{ route('items.edit', $item->id) }}">Edit</a>
```
{% endraw %}

### Multiple Parameters

{% raw %}
```blade
<a href="{{ route('items.tags.detach', [$item, $tag]) }}">Remove Tag</a>
```
{% endraw %}

### Query Parameters

{% raw %}
```blade
<a href="{{ route('items.index', ['search' => 'query', 'page' => 2]) }}">Search</a>
```
{% endraw %}

## Redirects

After form submissions, redirect with flash messages:

```php
public function store(StoreItemRequest $request)
{
    $item = Item::create($request->validated());
    
    return redirect()
        ->route('items.show', $item)
        ->with('status', 'Item created successfully.');
}

public function update(UpdateItemRequest $request, Item $item)
{
    $item->update($request->validated());
    
    return redirect()
        ->route('items.show', $item)
        ->with('status', 'Item updated successfully.');
}

public function destroy(Item $item)
{
    $item->delete();
    
    return redirect()
        ->route('items.index')
        ->with('status', 'Item deleted successfully.');
}
```

## Route Model Binding

Laravel automatically resolves models in route parameters:

```php
Route::get('items/{item}', [ItemController::class, 'show']);

// Controller receives the resolved model
public function show(Item $item)
{
    return view('items.show', compact('item'));
}
```

## Best Practices

1. **Always use named routes** - Never hard-code URLs
2. **Follow REST conventions** - Use resource routes when possible
3. **Group related routes** - Use route groups with common middleware
4. **Meaningful names** - Route names should be descriptive
5. **Consistent patterns** - Follow the same patterns across entities
6. **Flash messages** - Always provide user feedback after actions
7. **Type-hint models** - Use route model binding

## Related Documentation

- [Controllers]({{ '/guidelines/coding-guidelines' | relative_url }}#controllers)
- [Views]({{ '/frontend-blade/views' | relative_url }})
- [Middleware]({{ '/guidelines/coding-guidelines' | relative_url }}#middleware)
