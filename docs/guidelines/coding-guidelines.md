---
layout: default
title: Coding Guidelines
nav_order: 1
parent: Guidelines
---

# Coding Guidelines

# Coding Guidelines

This document outlines the coding standards and best practices for the Inventory Management API project built with PHP 8.2+ and Laravel 12.

## 🎯 General Principles

### Code Quality

- Write clean, readable, and maintainable PHP code
- Follow the principle of least surprise
- Use meaningful names for variables, functions, classes, and methods
- Keep functions and classes small and focused
- Comment complex logic and business rules using PHPDoc

### Performance

- Optimize for database performance
- Use proper Eloquent relationships and eager loading
- Implement efficient caching strategies
- Minimize API response times

## 🔧 PHP & Laravel Standards

### Class Structure

- **Follow PSR-12 coding standards** for all PHP code
- **Use strict typing** where applicable
- **Laravel conventions** for naming and structure
- **Eloquent best practices** for database interactions

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller for managing inventory items.
 *
 * Provides CRUD operations for items with proper validation,
 * authorization, and resource transformation.
 */
class ItemController extends Controller
{
    /**
     * Display a listing of items.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $items = Item::with(['partner', 'project', 'country'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('internal_name', 'like', "%{$request->search}%");
            })
            ->paginate(15);

        return ItemResource::collection($items);
    }

    /**
     * Store a newly created item.
     */
    public function store(StoreItemRequest $request): ItemResource
    {
        $item = Item::create($request->validated());

        return new ItemResource($item->load(['partner', 'project', 'country']));
    }
}
```

### Code Organization

- **Keep controllers focused** - one responsibility per controller
- **Use service classes** for complex business logic
- **Proper naming** - follow Laravel naming conventions
- **Type hints** - use type hints for all method parameters and return types

```php
<?php

// ✅ Good - Organized imports
// 1. Laravel/Framework imports
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

// 2. Third-party imports
use Carbon\Carbon;

// 3. Internal imports - Models
use App\Models\Item;
use App\Models\Partner;

// 4. Internal imports - Resources
use App\Http\Resources\ItemResource;

// 5. Internal imports - Requests
use App\Http\Requests\StoreItemRequest;

// 6. Internal imports - Services
use App\Services\ImageProcessingService;
```

## 🎨 Laravel Guidelines

### Model Design

- Use UUID primary keys for scalability (except User, Language, Country)
- Implement proper relationships
- Use model factories for testing
- Add appropriate validation rules

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Item model representing inventory objects.
 *
 * @property string $id
 * @property string $internal_name
 * @property string $partner_id
 * @property string $project_id
 * @property string $country_id
 * @property string|null $backward_compatibility
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Item extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'internal_name',
        'partner_id',
        'project_id',
        'country_id',
        'backward_compatibility',
    ];

    /**
     * Get the columns that should receive a unique identifier.
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Get the partner that owns the item.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the project associated with the item.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the country associated with the item.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the pictures associated with the item.
     */
    public function pictures(): HasMany
    {
        return $this->hasMany(Picture::class);
    }
}
```

### Controller Design

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * ✅ Good - Resource controller with proper typing
 */
class ItemController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $items = Item::with(['partner', 'project', 'country'])->paginate(15);

        return ItemResource::collection($items);
    }

    public function show(Item $item): ItemResource
    {
        return new ItemResource($item->load(['partner', 'project', 'country']));
    }

    public function store(StoreItemRequest $request): ItemResource
    {
        $item = Item::create($request->validated());

        return new ItemResource($item->load(['partner', 'project', 'country']));
    }

    public function update(UpdateItemRequest $request, Item $item): ItemResource
    {
        $item->update($request->validated());

        return new ItemResource($item->load(['partner', 'project', 'country']));
    }

    public function destroy(Item $item): JsonResponse
    {
        $item->delete();

        return response()->json(null, 204);
    }
}
```

## ✅ Quality Controls

Before submitting code, ensure all quality controls pass:

### 1. Code Quality

```bash
# Run PHP CS Fixer (Pint)
composer ci-lint

# Run static analysis (if configured)
composer ci-audit
```
```

### 2. Testing

```bash
# Run the complete test suite
composer ci-test

# Run specific test types
php artisan test tests/Unit
php artisan test tests/Feature

```

### Enhanced CI Scripts

The project includes PowerShell scripts in the `scripts/` directory:

```powershell
# Generate API client from OpenAPI specification
.\scripts\generate-api-client.ps1

# Publish API client to GitHub Packages
.\scripts\publish-api-client.ps1 -Credential (Get-Credential)

# Download seed images for optimized development
.\scripts\download-seed-images.ps1
```

**Features:**

- **API Client Generation**: Automated TypeScript client generation
- **Package Publishing**: Streamlined GitHub Packages publishing
- **Flexible Testing**: Filter tests by class, method, or test suite
- **Configurable Linting**: Multiple validation levels and options

### 3. Build Verification

```bash
# Build frontend assets
composer ci-build

# Reset and seed database
composer ci-reset
composer ci-seed
```

### 4. Security & Dependencies

```bash
# Check for vulnerabilities
composer ci-audit

# Generate OpenAPI documentation
composer ci-openapi-doc
```

### 5. Git Hygiene

```bash
# Run all pre-commit checks
composer ci-before:pull-request

# Ensure no uncommitted changes
composer ci-git:assert-no-change
```

## 🏗️ Application Architecture

### Model Structure Pattern

Each entity follows a consistent Laravel pattern:

```
app/
├── Http/
│   ├── Controllers/
│   │   └── EntityNameController.php    # RESTful API controller
│   ├── Requests/
│   │   ├── StoreEntityNameRequest.php  # Validation for create
│   │   └── UpdateEntityNameRequest.php # Validation for update
│   └── Resources/
│       └── EntityNameResource.php      # API response formatting
├── Models/
│   └── EntityName.php                  # Eloquent model
database/
├── factories/
│   └── EntityNameFactory.php           # Test data generation
├── migrations/
│   └── create_entity_names_table.php   # Database schema
└── seeders/
    └── EntityNameSeeder.php             # Sample data
tests/
├── Feature/
│   └── Api/
│       └── EntityName/
│           ├── AnonymousTest.php        # Unauthenticated access tests
│           ├── IndexTest.php            # List endpoint tests
│           ├── ShowTest.php             # Show endpoint tests
│           ├── StoreTest.php            # Create endpoint tests
│           ├── UpdateTest.php           # Update endpoint tests
│           └── DestroyTest.php          # Delete endpoint tests
└── Unit/
    └── EntityName/
        └── FactoryTest.php              # Factory tests
```

### Adding New Entities

When adding new entities to the API:

1. **Migration** - Create database schema with proper constraints
2. **Model** - Create Eloquent model with relationships and UUIDs
3. **Factory** - Create model factory for testing and seeding
4. **Seeder** - Create seeder for sample data
5. **Controller** - Create RESTful API controller
6. **Requests** - Create form request classes for validation
7. **Resource** - Create API resource for response formatting
8. **Routes** - Add routes to `routes/api.php`
9. **Tests** - Add comprehensive test coverage
10. **Documentation** - Update API documentation (auto-generated via Scramble)

### Core Entities

#### Primary Entities

- **Items** - Central inventory management with complex relationships
- **Partners** - Museums, institutions, and individuals
- **Projects** - Collections with launch dates and status management
- **Tags** - Flexible categorization system with many-to-many relationships
- **Pictures** - Image management with automatic processing

#### Reference Data

- **Countries** - Geographic reference using ISO 3166-1 alpha-3 codes
- **Languages** - Language reference using ISO 639-1 codes
- **Contexts** - Content organization and categorization hierarchy

#### Supporting Models

- **Details** - Extended metadata and flexible information storage
- **ImageUploads** - File upload tracking and processing status
- **AvailableImages** - Image availability and accessibility management

## 🚫 Common Pitfalls

### Avoid These Mistakes

1. **Not following Laravel naming conventions**
2. **Missing type hints on method parameters and returns**
3. **Not using proper Eloquent relationships**
4. **Ignoring mass assignment protection**
5. **Not writing comprehensive tests**
6. **Forgetting to run code formatting (Pint)**
7. **Not following PSR-12 coding standards**
8. **Missing PHPDoc documentation**

---

Following these guidelines ensures consistent, maintainable, and high-quality PHP code across the entire API project.
