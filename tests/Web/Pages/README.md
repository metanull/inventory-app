# Web Pages Tests

## Purpose

Tests for individual Web pages/resources. Each file contains ALL tests for a specific resource's web pages (index, show, create, edit, store, update, destroy).

## Structure

One test file per Web resource:
- `ItemTest.php` - All Item web pages
- `PartnerTest.php` - All Partner web pages
- `CollectionTest.php` - All Collection web pages
- etc.

## What's Tested

Each page test file includes **14 tests** (via `TestsWebCrud` trait):

### Authentication Tests (7 tests)
1. `test_index_requires_authentication()` - Redirects to login
2. `test_show_requires_authentication()` - Redirects to login
3. `test_create_requires_authentication()` - Redirects to login
4. `test_edit_requires_authentication()` - Redirects to login
5. `test_store_requires_authentication()` - Redirects to login
6. `test_update_requires_authentication()` - Redirects to login
7. `test_destroy_requires_authentication()` - Redirects to login

### Functionality Tests (7 tests)
1. `test_index_page_displays()` - GET /web/{resource}
2. `test_show_page_displays()` - GET /web/{resource}/{id}
3. `test_create_page_displays()` - GET /web/{resource}/create
4. `test_edit_page_displays()` - GET /web/{resource}/{id}/edit
5. `test_store_creates_and_redirects()` - POST /web/{resource}
6. `test_update_modifies_and_redirects()` - PUT /web/{resource}/{id}
7. `test_destroy_deletes_and_redirects()` - DELETE /web/{resource}/{id}

## Usage Pattern

```php
<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class ItemTest extends TestCase
{
    use AuthenticatesWebRequests;  // Authenticates with all permissions
    use RefreshDatabase;
    use TestsWebCrud;              // Provides all 14 tests

    protected function getRouteName(): string
    {
        return 'items';  // Route name prefix (e.g., 'items.index', 'items.show')
    }

    protected function getModelClass(): string
    {
        return Item::class;  // Model class for factory
    }

    protected function getFormData(): array
    {
        return Item::factory()->make()->toArray();  // Form data for create/update
    }
    
    // Optional: Override view names if they don't match route name
    protected function getIndexView(): string
    {
        return 'items.index';  // Default: {routeName}.index
    }
}
```

## Authentication & Permissions

**Important:** All page tests use `AuthenticatesWebRequests` trait which creates a session-authenticated user with ALL permissions.

This means:
- ✅ Tests verify that unauthenticated users are redirected to login
- ✅ Tests verify that authenticated users CAN access pages
- ❌ Tests do NOT verify permission enforcement (VIEW_DATA vs CREATE_DATA)

**Why?** Page tests focus on page rendering, form submission, and redirects.

**Security Testing:** Permission enforcement is tested separately in `Web/Middleware/PermissionsTest.php`.

## View Name Conventions

By default, the trait expects views to follow Laravel conventions:
- index → `{routeName}.index`
- show → `{routeName}.show`
- create → `{routeName}.create`
- edit → `{routeName}.edit`

If your views don't match (e.g., route is `glossaries` but view is `glossary`), override:

```php
protected function getIndexView(): string { return 'glossary.index'; }
protected function getShowView(): string { return 'glossary.show'; }
protected function getCreateView(): string { return 'glossary.create'; }
protected function getEditView(): string { return 'glossary.edit'; }
```

## Form Data

The `getFormData()` method provides data for form submissions:

```php
protected function getFormData(): array
{
    return Item::factory()->make()->toArray();
}
```

For models with required relationships:

```php
protected function getFormData(): array
{
    return ItemTranslation::factory()->make()->toArray();
    // Factory already handles relationships like item_id, language_id
}
```

## Database Assertions

The trait automatically:
- Removes framework fields (`id`, `created_at`, `updated_at`, `_token`, `_method`)
- Asserts database contains submitted data (on store/update)
- Asserts database doesn't contain deleted data (on destroy)

Override if needed:

```php
protected function getDatabaseAssertions(array $data): array
{
    // Customize what fields to check in database
    return array_only($data, ['name', 'description']);
}
```

## Adding a New Page Test

1. Create new test file: `tests-new/Web/Pages/{ModelName}Test.php`
2. Use `AuthenticatesWebRequests` + `RefreshDatabase` + `TestsWebCrud` traits
3. Implement three required methods: `getRouteName()`, `getModelClass()`, `getFormData()`
4. Add resource to `Web/Middleware/AuthenticationTest` and `PermissionsTest`
5. Add custom tests for page-specific features if needed

## Special Cases

### Custom Validation
Add tests for validation errors:

```php
public function test_store_validates_required_fields(): void
{
    $this->actingAs($this->createAuthenticatedUser());
    
    $response = $this->post(route('items.store'), []);
    
    $response->assertSessionHasErrors(['name', 'collection_id']);
}
```

### Custom Redirects
Override if your redirects differ:

```php
public function test_store_creates_and_redirects(): void
{
    $this->actingAs($this->createAuthenticatedUser());
    $data = $this->getFormData();
    
    $response = $this->post(route($this->getRouteName().'.store'), $data);
    
    // Custom redirect assertion
    $response->assertRedirect(route('dashboard'));
}
```

### Resources with File Uploads
Override store/update tests to include files:

```php
public function test_store_creates_and_redirects(): void
{
    Storage::fake('public');
    $this->actingAs($this->createAuthenticatedUser());
    
    $data = array_merge($this->getFormData(), [
        'image' => UploadedFile::fake()->image('test.jpg'),
    ]);
    
    $response = $this->post(route($this->getRouteName().'.store'), $data);
    $response->assertRedirect();
}
```

## Excluded from Testing

These are tested in separate directories:
- **Authentication flows** - `Web/Auth/` (login, register, 2FA, etc.)
- **Permission enforcement** - `Web/Middleware/PermissionsTest.php`
- **Livewire components** - `Web/Components/` (tables, forms, etc.)
- **Admin pages** - `Web/Admin/` (user management, roles)
