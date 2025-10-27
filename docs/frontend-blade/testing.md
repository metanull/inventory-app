---
layout: default
title: Testing
nav_order: 6
parent: Blade/Livewire Frontend
---

# Testing Blade and Livewire

This guide covers testing strategies for Blade templates and Livewire components.

## Testing Tools

- **PHPUnit** - Core testing framework
- **Laravel Dusk** - Browser testing (when needed)
- **Livewire Testing** - Livewire component testing utilities

## Feature Tests

Feature tests verify entire user workflows including views, routing, and database interactions.

### Basic View Test

```php
public function test_items_index_displays_items()
{
    $items = Item::factory()->count(3)->create();
    
    $response = $this->actingAs($this->user)
        ->get(route('items.index'));
    
    $response->assertOk();
    $response->assertViewIs('items.index');
    $response->assertSee($items->first()->internal_name);
}
```

### Form Submission Test

```php
public function test_can_create_item()
{
    $data = [
        'internal_name' => 'Test Item',
        'type' => 'monument',
        'partner_id' => Partner::factory()->create()->id,
    ];
    
    $response = $this->actingAs($this->user)
        ->post(route('items.store'), $data);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('items', [
        'internal_name' => 'Test Item',
    ]);
}
```

### Validation Test

```php
public function test_item_name_is_required()
{
    $data = ['type' => 'monument'];
    
    $response = $this->actingAs($this->user)
        ->post(route('items.store'), $data);
    
    $response->assertSessionHasErrors('internal_name');
}
```

## Livewire Component Tests

Test Livewire components using Livewire's testing utilities.

### Basic Component Test

```php
use Livewire\Livewire;

public function test_items_table_renders()
{
    Item::factory()->count(3)->create();
    
    Livewire::actingAs($this->user)
        ->test(ItemsTable::class)
        ->assertStatus(200)
        ->assertSee('Items');
}
```

### Testing Sorting

```php
public function test_can_sort_items_by_name()
{
    Item::factory()->create(['internal_name' => 'Zebra']);
    Item::factory()->create(['internal_name' => 'Apple']);
    
    Livewire::actingAs($this->user)
        ->test(ItemsTable::class)
        ->call('sortBy', 'internal_name')
        ->assertSeeInOrder(['Apple', 'Zebra']);
}
```

### Testing Pagination

```php
public function test_items_table_paginates()
{
    Item::factory()->count(20)->create();
    
    Livewire::actingAs($this->user)
        ->test(ItemsTable::class)
        ->assertSet('items.perPage', 15)
        ->assertSee('Next');
}
```

### Testing Search

```php
public function test_can_search_items()
{
    Item::factory()->create(['internal_name' => 'Findable']);
    Item::factory()->create(['internal_name' => 'Hidden']);
    
    Livewire::actingAs($this->user)
        ->test(ItemsTable::class)
        ->set('search', 'Findable')
        ->assertSee('Findable')
        ->assertDontSee('Hidden');
}
```

## Testing Blade Components

Test Blade components by rendering them in isolation.

### Component Rendering Test

```php
public function test_entity_header_renders_correctly()
{
    $view = $this->blade(
        '<x-entity.header entity="items" title="Test Items" />'
    );
    
    $view->assertSee('Test Items');
    $view->assertSee('items'); // Entity class
}
```

### Component with Slots Test

```php
public function test_modal_renders_with_slots()
{
    $view = $this->blade(
        '<x-modal name="test">
            <x-slot name="title">Test Title</x-slot>
            Content here
        </x-modal>'
    );
    
    $view->assertSee('Test Title');
    $view->assertSee('Content here');
}
```

## Testing Permissions

Verify that views respect permission gates.

```php
public function test_create_button_only_visible_with_permission()
{
    // User without permission
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->get(route('items.index'));
    
    $response->assertDontSee('Add Item');
    
    // User with permission
    $user->givePermissionTo(Permission::CREATE_DATA);
    
    $response = $this->actingAs($user)
        ->get(route('items.index'));
    
    $response->assertSee('Add Item');
}
```

## Testing Redirects and Flash Messages

```php
public function test_item_creation_redirects_with_success_message()
{
    $data = ['internal_name' => 'Test', 'type' => 'monument'];
    
    $response = $this->actingAs($this->user)
        ->post(route('items.store'), $data);
    
    $response->assertRedirect(route('items.show', Item::latest()->first()));
    $response->assertSessionHas('status', 'Item created successfully.');
}
```

## Test Organization

Tests are organized by feature:

```
tests/Feature/
├── Http/
│   └── Controllers/
│       ├── ItemControllerTest.php
│       ├── PartnerControllerTest.php
│       └── ...
└── Livewire/
    └── Tables/
        ├── ItemsTableTest.php
        ├── PartnersTableTest.php
        └── ...
```

## Running Tests

### Run all feature tests

```bash
php artisan test --testsuite=Feature
```

### Run specific test file

```bash
php artisan test tests/Feature/Http/Controllers/ItemControllerTest.php
```

### Run with coverage

```bash
php artisan test --coverage
```

## Best Practices

1. **Test user workflows** - Test complete user journeys, not just code
2. **Use factories** - Create test data with model factories
3. **Test permissions** - Verify authorization on every action
4. **Test validation** - Ensure validation rules work correctly
5. **Test edge cases** - Test empty states, errors, and boundaries
6. **Keep tests fast** - Use database transactions, avoid external calls
7. **Descriptive names** - Test names should describe what they test
8. **AAA pattern** - Arrange, Act, Assert

## Related Documentation

- [Backend Testing]({{ '/development/testing' | relative_url }})
- [Livewire Components]({{ '/frontend-blade/livewire/' | relative_url }})
- [Views]({{ '/frontend-blade/views' | relative_url }})
