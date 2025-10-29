# Web Test Traits

## Purpose

Reusable test traits that provide standard test methods for common Web testing patterns. These traits eliminate repetitive test code across page and component tests.

## Available Traits

### AuthenticatesWebRequests.php
**Purpose:** Sets up session-based authentication for Web tests

**Provides:**
- Authenticated user with ALL permissions (VIEW_DATA, CREATE_DATA, UPDATE_DATA, DELETE_DATA)
- Laravel session-based acting-as authentication
- Automatic permission cache clearing

**Usage:**
```php
use Tests\Web\Traits\AuthenticatesWebRequests;

class ItemTest extends TestCase
{
    use AuthenticatesWebRequests;  // Automatically authenticates all requests
    
    // All Web requests in this test are now session-authenticated
}
```

**What it does in `setUp()`:**
```php
$user = User::factory()->create();
$user->givePermissionTo(Permission::VIEW_DATA);
$user->givePermissionTo(Permission::CREATE_DATA);
$user->givePermissionTo(Permission::UPDATE_DATA);
$user->givePermissionTo(Permission::DELETE_DATA);

$this->actingAs($user);  // Session authentication
```

**When to use:** Every Web page and component test

---

### TestsWebCrud.php
**Purpose:** Standard CRUD operation tests for Web pages

**Provides 14 test methods:**

**Authentication Tests (7):**
1. `test_index_requires_authentication()` - GET /web/{resource}
2. `test_show_requires_authentication()` - GET /web/{resource}/{id}
3. `test_create_requires_authentication()` - GET /web/{resource}/create
4. `test_edit_requires_authentication()` - GET /web/{resource}/{id}/edit
5. `test_store_requires_authentication()` - POST /web/{resource}
6. `test_update_requires_authentication()` - PUT /web/{resource}/{id}
7. `test_destroy_requires_authentication()` - DELETE /web/{resource}/{id}

**Functionality Tests (7):**
1. `test_index_page_displays()` - Renders index view
2. `test_show_page_displays()` - Renders show view
3. `test_create_page_displays()` - Renders create form
4. `test_edit_page_displays()` - Renders edit form
5. `test_store_creates_and_redirects()` - Creates record, redirects
6. `test_update_modifies_and_redirects()` - Updates record, redirects
7. `test_destroy_deletes_and_redirects()` - Deletes record, redirects

**Required methods to implement:**
```php
abstract protected function getRouteName(): string;   // Route prefix (e.g., 'items')
abstract protected function getModelClass(): string;  // Model class
abstract protected function getFormData(): array;     // Form submission data
```

**Optional methods to override:**
```php
protected function getIndexView(): string {
    return $this->getRouteName().'.index';  // Default view name
}

protected function getShowView(): string {
    return $this->getRouteName().'.show';
}

protected function getCreateView(): string {
    return $this->getRouteName().'.create';
}

protected function getEditView(): string {
    return $this->getRouteName().'.edit';
}

protected function getDatabaseAssertions(array $data): array {
    return array_diff_key($data, array_flip(['id', '_token', '_method']));
}
```

**Usage:**
```php
use Tests\Web\Traits\TestsWebCrud;

class ItemTest extends TestCase
{
    use AuthenticatesWebRequests;
    use TestsWebCrud;
    
    protected function getRouteName(): string { return 'items'; }
    protected function getModelClass(): string { return Item::class; }
    protected function getFormData(): array {
        return Item::factory()->make()->toArray();
    }
}
```

**What it tests:**
- ✅ Unauthenticated users redirected to login (302)
- ✅ Authenticated users can access pages (200)
- ✅ Correct views are rendered
- ✅ Forms create/update/delete database records
- ✅ Redirects work after form submission

**What it does NOT test:**
- ❌ Permission enforcement (VIEW_DATA vs CREATE_DATA) - See Web/Middleware
- ❌ Validation errors - Add custom tests
- ❌ Complex business logic - Add custom tests
- ❌ JavaScript interactions - Use browser tests

---

### TestsWebLivewire.php
**Purpose:** Standard tests for Livewire table components

**Provides 4 test methods:**
1. `test_component_can_render()` - Component loads (200)
2. `test_component_can_load_data()` - Component displays model data
3. `test_component_can_sort()` - Sorting works (optional, skipped if not supported)
4. `test_component_can_filter()` - Filtering works (optional, skipped if not supported)

**Required methods to implement:**
```php
abstract protected function getComponentClass(): string;  // Livewire component class
abstract protected function getModelClass(): string;      // Model displayed
abstract protected function getIdentifier($model): string; // Visible field
```

**Optional methods to enable additional tests:**
```php
protected function getSortableFields(): array {
    return ['name', 'created_at'];  // Enable sort test
}

protected function getFilterableFields(): array {
    return [
        'filters.search' => 'query',
        'filters.status' => 'active'
    ];  // Enable filter test
}
```

**Usage:**
```php
use Tests\Web\Traits\TestsWebLivewire;

class ItemsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use TestsWebLivewire;
    
    protected function getComponentClass(): string {
        return App\Livewire\Tables\ItemsTable::class;
    }
    
    protected function getModelClass(): string {
        return Item::class;
    }
    
    protected function getIdentifier($model): string {
        return $model->internal_name;  // Must be visible in component
    }
    
    // Optional: Enable sorting test
    protected function getSortableFields(): array {
        return ['name', 'created_at'];
    }
}
```

**What it tests:**
- ✅ Component renders without errors
- ✅ Component displays model data
- ✅ Sorting changes component state (if implemented)
- ✅ Filtering changes component state (if implemented)

**What it does NOT test:**
- ❌ Detailed Livewire interactions - Add custom tests
- ❌ Component events/listeners - Add custom tests
- ❌ Wire:model bindings - Add custom tests

---

## Creating New Traits

When creating a new trait for Web testing:

1. **Identify the pattern** - Find repetitive code in 3+ test files
2. **Choose the right namespace** - `Tests\Web\Traits`
3. **Create abstract trait** - Use abstract methods for customization points
4. **Use consistent naming** - `TestsWeb{Feature}` format
5. **Document thoroughly** - Add to this README
6. **Test the trait** - Use it in multiple test files

### Example: New Trait Structure
```php
<?php

namespace Tests\Web\Traits;

/**
 * Trait for testing {feature}
 * 
 * Provides: {what tests it provides}
 * Requires: {what methods must be implemented}
 */
trait TestsWeb{Feature}
{
    abstract protected function getRouteName(): string;
    
    public function test_feature_works(): void
    {
        // Test implementation
    }
}
```

## Best Practices

✅ **DO:**
- Use traits for common patterns
- Implement all required abstract methods
- Override trait methods when behavior differs
- Document trait usage in test classes
- Skip tests that don't apply (use `markTestSkipped()`)

❌ **DON'T:**
- Create traits for single-use code
- Mix API and Web authentication in same trait
- Test framework features
- Make traits too complex
- Forget to update this README

## Authentication Strategy

**Web vs API:**
- **Web traits** use `$this->actingAs($user)` - Session-based
- **API traits** use `Sanctum::actingAs($user)` - Token-based
- **Never mix** these authentication methods

**Permission strategy:**
- **Trait-based tests** - User has ALL permissions (super-user)
- **Middleware tests** - Test specific permission combinations
- This separation keeps tests focused and maintainable

## Trait Hierarchy

```
TestsWebCrud
├── Uses: AuthenticatesWebRequests (implicit via test class)
└── Tests: 14 CRUD operations

TestsWebLivewire  
├── Uses: AuthenticatesWebRequests (implicit via test class)
└── Tests: 4 component operations

AuthenticatesWebRequests
├── Standalone trait
└── Used by: All Web tests
```

No inheritance between traits - they're designed to be mixed and matched.
