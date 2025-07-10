---
layout: default
title: Testing
nav_order: 3
parent: Guidelines
has_children: true
---

# Testing Guide

This document provides comprehensive guidance on testing in the Inventory Management API application built with PHP 8.2+ and Laravel 12, including unit tests, feature tests, and integration tests.

## 🧪 Testing Requirements

- **Write tests** for all new functionality
- **Update existing tests** when modifying code
- **Maintain high test coverage** (currently 690+ tests with comprehensive coverage)
- **Follow Laravel testing best practices**

### Running Tests

```bash
# Run all tests
composer ci-test

# Run tests with coverage
php artisan test --coverage

# Run specific test suites
php artisan test tests/Unit
php artisan test tests/Feature
php artisan test tests/Integration

# Run tests in parallel for speed
php artisan test --parallel

# Run specific test file
php artisan test tests/Feature/Api/Item/IndexTest.php
```

## 📋 Testing Guidelines

### Unit Tests

- **Test model factories** and database constraints
- **Test business logic** in service classes
- **Mock external dependencies** when needed
- **Use descriptive test names** that explain the scenario

```php
<?php

namespace Tests\Unit\Item;

use App\Models\Item;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * ✅ Good: Descriptive test structure for model factories
 */
class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory_creates_valid_item_with_required_relationships(): void
    {
        $item = Item::factory()->create();

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'internal_name' => $item->internal_name,
        ]);

        $this->assertInstanceOf(Partner::class, $item->partner);
        $this->assertInstanceOf(Project::class, $item->project);
        $this->assertInstanceOf(Country::class, $item->country);
    }

    public function test_factory_respects_database_constraints(): void
    {
        $item = Item::factory()->create();

        // Test internal_name is required and unique
        $this->assertNotNull($item->internal_name);
        $this->assertTrue(strlen($item->internal_name) > 0);
    }
}
```

### Feature Tests

- **Test API endpoints** with proper authentication
- **Test validation rules** and error responses
- **Test business logic** through HTTP requests
- **Use Laravel's testing methods** for assertions

```php
<?php

namespace Tests\Feature\Api\Item;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * ✅ Good: Feature test for API endpoints
 */
class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_index_returns_paginated_items(): void
    {
        Item::factory()->count(3)->create();

        $response = $this->getJson(route('item.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'internal_name',
                        'partner',
                        'project',
                        'country',
                    ]
                ],
                'links',
                'meta',
            ]);
    }

    public function test_index_filters_items_by_search_parameter(): void
    {
        Item::factory()->create(['internal_name' => 'Special Item']);
        Item::factory()->create(['internal_name' => 'Regular Item']);

        $response = $this->getJson(route('item.index', ['search' => 'Special']));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.internal_name', 'Special Item');
    }
}
```

### Integration Tests

- **Test complex workflows** across multiple models
- **Test event handling** and job processing
- **Verify system integration** points

```php
<?php

namespace Tests\Integration;

use App\Models\Item;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * ✅ Good: Integration test for complex workflows
 */
class ImageUploadWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Storage::fake('public');
        Event::fake();
    }

    public function test_complete_image_upload_workflow(): void
    {
        $item = Item::factory()->create();
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        // Upload image
        $response = $this->postJson(route('picture.store'), [
            'file' => $file,
            'internal_name' => 'Test Picture',
            'item_id' => $item->id,
        ]);

        $response->assertCreated();

        // Verify database record
        $this->assertDatabaseHas('pictures', [
            'internal_name' => 'Test Picture',
            'item_id' => $item->id,
        ]);

        // Verify file storage
        $picture = Picture::where('internal_name', 'Test Picture')->first();
        Storage::disk('public')->assertExists($picture->file_path);
    }
}
```

## 🔍 Code Review Testing Criteria

### What We Look For

1. **Testing Coverage**
   - Comprehensive test coverage for new functionality
   - Tests pass consistently without flaky behavior
   - Edge cases and error conditions covered

2. **Test Quality**
   - Tests are maintainable and easy to understand
   - Clear test descriptions that explain the scenario
   - Proper use of Laravel testing features

3. **Database Testing**
   - Proper use of RefreshDatabase trait
   - Database assertions verify expected state
   - Factory usage for test data generation

4. **API Testing**
   - All HTTP status codes tested
   - JSON structure validation
   - Authentication and authorization testing

## Test Organization

### Directory Structure

```
tests/
├── Feature/
│   └── Api/
│       ├── Item/
│       │   ├── AnonymousTest.php      # Unauthenticated access
│       │   ├── IndexTest.php          # GET /api/item
│       │   ├── ShowTest.php           # GET /api/item/{id}
│       │   ├── StoreTest.php          # POST /api/item
│       │   ├── UpdateTest.php         # PUT/PATCH /api/item/{id}
│       │   └── DestroyTest.php        # DELETE /api/item/{id}
│       └── Markdown/
│           ├── AnonymousTest.php      # Public endpoints
│           └── ConversionTest.php     # Markdown processing
├── Integration/
│   ├── ImageUploadWorkflowTest.php    # Complex workflows
│   └── AuthenticationFlowTest.php     # End-to-end auth
└── Unit/
    ├── Item/
    │   └── FactoryTest.php             # Model factory tests
    └── Services/
        └── MarkdownServiceTest.php     # Service layer tests
```

### Test Categories

1. **Anonymous Tests** - Test public endpoints without authentication
2. **CRUD Tests** - Test standard resource operations (Index, Show, Store, Update, Destroy)
3. **Factory Tests** - Test model factories and database constraints
4. **Integration Tests** - Test complex workflows across multiple components
5. **Service Tests** - Test business logic in service classes

## Best Practices

### Unit Testing

1. **Mock external dependencies** (API calls, file system, etc.)
2. **Test behavior, not implementation** details
3. **Use descriptive test names** that explain the expected behavior
4. **Group related tests** with `describe` blocks
5. **Keep tests isolated** - each test should be independent

### Integration Testing

1. **Always use test databases** - never production data
2. **Run destructive tests sparingly** - they're slower and riskier
3. **Use the guided runner** for safety and convenience
4. **Clean up test data** - don't leave orphaned records
5. **Test realistic scenarios** - use actual data structures

### General Testing

1. **Write tests first** when fixing bugs
2. **Maintain test coverage** above minimum thresholds
3. **Run tests before committing** code changes
4. **Document complex test scenarios** with comments
5. **Use TypeScript** for type safety in tests

## Getting Help

- **Unit Test Issues**: Check existing tests in similar components
- **Integration Test Setup**: See `docs/integration-testing.md`
- **CI/CD Issues**: Check GitHub Actions logs
- **API Issues**: Verify API documentation and endpoints
- **Coverage Issues**: Use `npm run test:coverage` for detailed reports

---

For more detailed information, see:

- [Integration Testing Guide](integration-testing)
- [Contributing Guide](../contributing)
- [API Integration Documentation](api-integration)
