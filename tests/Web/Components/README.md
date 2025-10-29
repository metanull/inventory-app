# Web Components Tests

## Purpose

Tests for Livewire components. Each file contains tests for a specific Livewire component's functionality.

## Structure

One test file per Livewire component:
- `ItemsTableTest.php` - ItemsTable Livewire component
- `PartnersTableTest.php` - PartnersTable Livewire component
- `GlossaryTableTest.php` - GlossaryTable Livewire component
- etc.

## What's Tested

Each component test file includes **4 tests** (via `TestsWebLivewire` trait):

1. `test_component_can_render()` - Component loads without errors
2. `test_component_can_load_data()` - Component displays model data
3. `test_component_can_sort()` - Sorting functionality (if supported)
4. `test_component_can_filter()` - Filtering functionality (if supported)

**Note:** Tests 3 and 4 are automatically skipped if the component doesn't implement those features.

## Usage Pattern

```php
<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\ItemsTable;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class ItemsTableTest extends TestCase
{
    use AuthenticatesWebRequests;  // Authenticated session
    use RefreshDatabase;
    use TestsWebLivewire;          // Provides all 4 tests

    protected function getComponentClass(): string
    {
        return ItemsTable::class;  // Livewire component class
    }

    protected function getModelClass(): string
    {
        return Item::class;  // Model that component displays
    }

    protected function getIdentifier($model): string
    {
        return $model->internal_name;  // Field to assert visibility
    }
}
```

## Test Details

### 1. Component Rendering
```php
public function test_component_can_render(): void
{
    $component = \Livewire\Livewire::test($this->getComponentClass());
    $component->assertOk();
}
```

Tests that the component loads without errors.

### 2. Data Loading
```php
public function test_component_can_load_data(): void
{
    $models = $this->getModelClass()::factory()->count(3)->create();
    $component = \Livewire\Livewire::test($this->getComponentClass());
    
    foreach ($models as $model) {
        $component->assertSee($this->getIdentifier($model));
    }
}
```

Tests that component displays the model data by checking for visible identifiers.

### 3. Sorting (Optional)
```php
public function test_component_can_sort(): void
{
    if (!method_exists($this, 'getSortableFields')) {
        $this->markTestSkipped('Component does not support sorting');
    }
    
    $component = \Livewire\Livewire::test($this->getComponentClass());
    
    foreach ($this->getSortableFields() as $field) {
        $component->call('sortBy', $field)->assertOk();
    }
}
```

**Skipped by default.** To enable, implement `getSortableFields()`:

```php
protected function getSortableFields(): array
{
    return ['name', 'created_at', 'updated_at'];
}
```

### 4. Filtering (Optional)
```php
public function test_component_can_filter(): void
{
    if (!method_exists($this, 'getFilterableFields')) {
        $this->markTestSkipped('Component does not support filtering');
    }
    
    $component = \Livewire\Livewire::test($this->getComponentClass());
    
    foreach ($this->getFilterableFields() as $field => $value) {
        $component->set($field, $value)->assertOk();
    }
}
```

**Skipped by default.** To enable, implement `getFilterableFields()`:

```php
protected function getFilterableFields(): array
{
    return [
        'filters.search' => 'test query',
        'filters.status' => 'active',
    ];
}
```

## Choosing the Identifier

The `getIdentifier()` method should return a **unique, visible** value from each model:

```php
// ✅ Good identifiers
return $model->internal_name;  // Unique slug
return $model->name;           // Display name
return $model->email;          // Email address

// ❌ Bad identifiers
return $model->id;             // UUIDs not typically visible
return $model->created_at;     // Dates are formatted
return null;                   // Will cause type error
```

**For models with nullable names:** Use a fallback:

```php
protected function getIdentifier($model): string
{
    return $model->name ?? $model->id;  // Fallback to ID
}
```

## Authentication

All component tests use `AuthenticatesWebRequests` which creates a session-authenticated user with ALL permissions.

Components typically require authentication to render, so this trait is essential.

## Adding a New Component Test

1. Identify the Livewire component class (e.g., `App\Livewire\Tables\ItemsTable`)
2. Create test file: `tests-new/Web/Components/{ComponentName}Test.php`
3. Use `AuthenticatesWebRequests` + `RefreshDatabase` + `TestsWebLivewire` traits
4. Implement three required methods:
   - `getComponentClass()` - Livewire component class
   - `getModelClass()` - Model class the component displays
   - `getIdentifier($model)` - Visible field to assert
5. (Optional) Implement `getSortableFields()` and/or `getFilterableFields()`

## Advanced Testing

### Testing Component Properties
```php
public function test_component_has_correct_default_properties(): void
{
    $component = \Livewire\Livewire::test(ItemsTable::class);
    
    $component->assertSet('perPage', 15)
              ->assertSet('sortField', 'name')
              ->assertSet('sortDirection', 'asc');
}
```

### Testing Component Methods
```php
public function test_can_select_all_items(): void
{
    Item::factory()->count(5)->create();
    
    $component = \Livewire\Livewire::test(ItemsTable::class);
    
    $component->call('selectAll')
              ->assertSet('selectedIds', function($ids) {
                  return count($ids) === 5;
              });
}
```

### Testing Component Events
```php
public function test_component_emits_delete_event(): void
{
    $item = Item::factory()->create();
    
    $component = \Livewire\Livewire::test(ItemsTable::class);
    
    $component->call('delete', $item->id)
              ->assertEmitted('item-deleted');
}
```

## Components Not Covered

These component types have separate test locations:
- **Form components** - Test via Page tests (form submission)
- **Modal components** - Test via parent component that triggers them
- **Profile components** - `Web/Auth/ProfileTest.php`
- **Admin components** - `Web/Admin/`

## Best Practices

✅ **DO:**
- Test component rendering
- Test data display with real factories
- Test user interactions (sorting, filtering, selecting)
- Skip tests for features not implemented

❌ **DON'T:**
- Test Livewire framework features
- Test every possible state combination
- Duplicate tests that belong in Page tests
- Make tests depend on specific CSS classes
