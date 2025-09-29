---
layout: default
title: Collection
parent: Database Models
---

# 📚 Collection Model

{: .highlight }

> The Collection model represents organized groupings of items, supporting different collection types (collections, exhibitions, galleries) with multi-language capabilities and item relationships.

## 📊 Model Overview

| Property         | Type          | Description                |
| ---------------- | ------------- | -------------------------- |
| **Model Name**   | Collection    | Content organization model |
| **Table Name**   | `collections` | Database table             |
| **Primary Key**  | `id` (UUID)   | Unique identifier          |
| **Timestamps**   | ✅ Yes        | `created_at`, `updated_at` |
| **Soft Deletes** | ❌ No         | Hard deletes only          |

## 🏗️ Database Schema

| Column                 | Type      | Constraints      | Description                                            |
| ---------------------- | --------- | ---------------- | ------------------------------------------------------ |
| id                     | uuid      | Primary Key      | Unique identifier (UUID)                               |
| internal_name          | string    | Required, Unique | Internal reference name                                |
| type                   | enum      | Required         | Collection type: 'collection', 'exhibition', 'gallery' |
| language_id            | string    | Foreign Key      | Primary language (ISO 639-3)                           |
| context_id             | uuid      | Foreign Key      | Associated context                                     |
| backward_compatibility | string    | Nullable         | Legacy system reference                                |
| created_at             | timestamp | Auto-managed     | Creation timestamp                                     |
| updated_at             | timestamp | Auto-managed     | Last update timestamp                                  |

## 🔗 Relationships

### Belongs To

- **`language()`**: Belongs to `Language` model
- **`context()`**: Belongs to `Context` model

### Has Many

- **`translations()`**: Has many `CollectionTranslation` models

### Many-to-Many

- **`items()`**: Many-to-many with `Item` via `collection_item` pivot table
- **`themes()`**: Many-to-many with `Theme` via `collection_theme` pivot table

## 🎯 Key Features

### 📋 Collection Types

- **`collection`**: Permanent museum collections and groupings
- **`exhibition`**: Temporary exhibitions and displays
- **`gallery`**: Gallery spaces and room-based organizations

### 🌍 Multi-language Support

```php
// Create collection with translations
$collection = Collection::create([
    'internal_name' => 'ancient_ceramics',
    'type' => 'collection',
    'language_id' => 'ENG',
    'context_id' => $context->id
]);

// Add translations
$collection->translations()->create([
    'language_id' => 'FRA',
    'display_name' => 'Céramiques Antiques',
    'description' => 'Collection de céramiques...'
]);
```

### 📦 Item Management

```php
// Add items to collection
$collection->items()->attach([
    $vase->id,
    $bowl->id,
    $amphora->id
]);

// Remove items from collection
$collection->items()->detach($vase->id);

// Get all items in collection
$collectionItems = $collection->items()
    ->with(['partner', 'country', 'images'])
    ->orderBy('internal_name')
    ->get();
```

## 🔧 Model Scopes

### Type Filtering

```php
public function scopeCollections(Builder $query): Builder // type = 'collection'
public function scopeExhibitions(Builder $query): Builder // type = 'exhibition'
public function scopeGalleries(Builder $query): Builder // type = 'gallery'
```

### Content Scopes

```php
public function scopeWithItems(Builder $query): Builder // Has items
public function scopeWithoutItems(Builder $query): Builder // No items
public function scopeByLanguage(Builder $query, string $languageId): Builder
public function scopeByContext(Builder $query, string $contextId): Builder
```

## 🌍 API Integration

### Available Endpoints

- `GET /api/collections` - List all collections with filtering
- `GET /api/collections/{collection}` - Get specific collection with items
- `POST /api/collections` - Create new collection
- `PUT /api/collections/{collection}` - Update collection
- `DELETE /api/collections/{collection}` - Delete collection
- `GET /api/collections/{collection}/items` - Get collection items
- `POST /api/collections/{collection}/items/attach` - Add items to collection
- `DELETE /api/collections/{collection}/items/{item}/detach` - Remove item

### Resource Structure

```json
{
  "id": "uuid",
  "internal_name": "ancient_ceramics",
  "type": "collection",
  "language_id": "ENG",
  "context_id": "uuid",
  "backward_compatibility": null,
  "created_at": "2023-01-01T00:00:00Z",
  "updated_at": "2023-01-01T00:00:00Z",
  "language": {
    /* LanguageResource */
  },
  "context": {
    /* ContextResource */
  },
  "translations": [
    /* CollectionTranslationResource[] */
  ],
  "items": [
    /* ItemResource[] */
  ],
  "themes": [
    /* ThemeResource[] */
  ]
}
```

## ⚙️ Business Logic

### Validation Rules

```php
// Store/Update Request
[
    'internal_name' => 'required|string|max:255|unique:collections,internal_name,' . $id,
    'type' => 'required|in:collection,exhibition,gallery',
    'language_id' => 'required|string|size:3|exists:languages,id',
    'context_id' => 'required|uuid|exists:contexts,id',
    'backward_compatibility' => 'nullable|string|max:255'
]

// Item Attachment
[
    'item_ids' => 'required|array',
    'item_ids.*' => 'uuid|exists:items,id'
]
```

### Business Constraints

- Collection internal names must be unique
- Items can belong to multiple collections
- Collections can contain items of any type (object/monument)
- Themes can be associated with multiple collections

## Usage Examples

### Creating Different Collection Types

```php
// Permanent collection
$ceramics = Collection::create([
    'internal_name' => 'ancient_ceramics',
    'type' => 'collection',
    'language_id' => 'ENG',
    'context_id' => $museum->id
]);

// Temporary exhibition
$exhibition = Collection::create([
    'internal_name' => 'treasures_of_pompeii',
    'type' => 'exhibition',
    'language_id' => 'ENG',
    'context_id' => $museum->id
]);

// Gallery space
$gallery = Collection::create([
    'internal_name' => 'greek_sculpture_hall',
    'type' => 'gallery',
    'language_id' => 'ENG',
    'context_id' => $museum->id
]);
```

### Multi-language Collections

```php
// Create base collection
$collection = Collection::create([
    'internal_name' => 'islamic_art',
    'type' => 'collection',
    'language_id' => 'ENG',
    'context_id' => $context->id
]);

// Add multiple translations
$collection->translations()->createMany([
    [
        'language_id' => 'FRA',
        'display_name' => 'Art Islamique',
        'description' => 'Collection d\'art islamique...'
    ],
    [
        'language_id' => 'ARA',
        'display_name' => 'الفن الإسلامي',
        'description' => 'مجموعة الفن الإسلامي...'
    ]
]);
```

### Advanced Queries

```php
// Find exhibitions with items from specific partner
$partnerExhibitions = Collection::exhibitions()
    ->whereHas('items', function($query) use ($partnerId) {
        $query->where('partner_id', $partnerId);
    })
    ->with(['items.partner', 'translations'])
    ->get();

// Get collections by type with item counts
$collectionsWithCounts = Collection::withCount('items')
    ->galleries()
    ->having('items_count', '>', 0)
    ->orderBy('items_count', 'desc')
    ->get();
```
