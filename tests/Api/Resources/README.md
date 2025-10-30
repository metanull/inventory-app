# API Resources Tests

## Purpose

Tests for individual API resources/endpoints. Each file contains ALL tests for a specific resource's API operations.

## Structure

One test file per API resource model:
- `ItemTest.php` - All Item API endpoints
- `PartnerTest.php` - All Partner API endpoints
- `CollectionTest.php` - All Collection API endpoints
- etc.

## What's Tested

Each resource test file typically includes:
1. **CRUD Operations** (via `TestsApiCrud` trait)
   - `test_can_list_resources()` - GET /api/{resource}
   - `test_index_returns_empty_when_no_resources()` 
   - `test_can_show_single_resource()` - GET /api/{resource}/{id}
   - `test_show_returns_404_for_nonexistent_resource()`
   - `test_can_create_resource()` - POST /api/{resource}
   - `test_can_update_resource()` - PUT /api/{resource}/{id}
   - `test_update_returns_404_for_nonexistent_resource()`
   - `test_can_delete_resource()` - DELETE /api/{resource}/{id}
   - `test_destroy_returns_404_for_nonexistent_resource()`

2. **Tag Management** (via `TestsApiTagManagement` trait, if applicable)
   - `test_can_attach_tags_to_resource()`
   - `test_can_detach_tags_from_resource()`
   - `test_can_sync_tags_on_resource()`

3. **Image Resources** (via `TestsApiImageResource` + `TestsApiImageViewing` traits)
   - Standard CRUD operations
   - `test_can_move_image_up()`
   - `test_can_move_image_down()`
   - `test_can_tighten_ordering()`
   - `test_can_download_image()`
   - `test_can_view_image()`

4. **Special Operations** (resource-specific, not in traits)
   - `test_can_set_default()` - PATCH /api/{resource}/{id}/set-default
   - `test_can_get_default()` - GET /api/{resource}/get-default
   - Custom scopes, filters, includes

## Usage Pattern

```php
<?php

namespace Tests\Api\Resources;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\Api\Traits\TestsApiTagManagement;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use AuthenticatesApiRequests;  // Sets up authenticated user with all permissions
    use RefreshDatabase;
    use TestsApiCrud;              // Provides standard CRUD tests
    use TestsApiTagManagement;     // Provides tag operation tests

    protected function getResourceName(): string
    {
        return 'item';  // Route name prefix
    }

    protected function getModelClass(): string
    {
        return Item::class;  // Model class for factory
    }

    // Optional: Override trait methods for custom behavior
    protected function getFactoryData(): array
    {
        return ['collection_id' => Collection::factory()];
    }
    
    // Add resource-specific tests here
    public function test_can_filter_by_collection(): void
    {
        // Custom test for this resource
    }
}
```

## Authentication & Permissions

**Important:** All resource tests use `AuthenticatesApiRequests` trait which creates a user with ALL permissions (VIEW_DATA, CREATE_DATA, UPDATE_DATA, DELETE_DATA).

This means:
- ✅ Tests verify that authenticated users CAN perform operations
- ❌ Tests do NOT verify permission enforcement
- ❌ Tests do NOT verify unauthenticated rejection

**Why?** Resource tests focus on business logic, data validation, and response structure.

**Security Testing:** Permission enforcement and authentication are tested separately in `Api/Middleware/` directory.

## Adding a New Resource Test

1. Create new test file: `tests/Api/Resources/{ModelName}Test.php`
2. Use appropriate traits based on resource type:
   - Standard resource → `TestsApiCrud`
   - Taggable resource → Add `TestsApiTagManagement`
   - Image resource → Use `TestsApiImageResource` + `TestsApiImageViewing`
3. Implement required abstract methods from traits
4. Add resource to `Api/Middleware/AuthenticationTest` and `PermissionsTest`
5. Add custom tests for resource-specific features

## Special Cases

### Nested Resources
For nested routes like `/api/items/{item}/images`:
```php
protected function getResourceName(): string
{
    return 'items.images';  // Nested route
}

protected function getFactoryData(): array
{
    return [
        'item_id' => Item::factory(),  // Parent resource
    ];
}
```

### Resources with Required Relationships
```php
protected function getFactoryData(): array
{
    return [
        'language_id' => Language::factory(),
        'context_id' => Context::factory(),
    ];
}
```

### Resources Requiring Special IDs
For Country, Language (3-char ISO codes):
```php
// Override the trait method to keep the ID
protected function test_can_create_resource(): void
{
    $data = $this->getModelClass()::factory()->make()->toArray();
    // Don't remove 'id' from data
    
    $response = $this->postJson(route($this->getResourceName().'.store'), $data);
    $response->assertCreated();
}
```
