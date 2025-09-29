---
layout: default
title: Item
parent: Database Models
---

# 🏛️ Item Model

{: .highlight }

> The Item model represents physical objects or monuments in the museum collection, supporting hierarchical relationships and comprehensive metadata management.

## 📊 Model Overview

| Property         | Type        | Description                |
| ---------------- | ----------- | -------------------------- |
| **Model Name**   | Item        | Core content model         |
| **Table Name**   | `items`     | Database table             |
| **Primary Key**  | `id` (UUID) | Unique identifier          |
| **Timestamps**   | ✅ Yes      | `created_at`, `updated_at` |
| **Soft Deletes** | ❌ No       | Hard deletes only          |

## 🏗️ Database Schema

| Column                 | Type      | Constraints           | Description                     |
| ---------------------- | --------- | --------------------- | ------------------------------- |
| id                     | uuid      | Primary Key           | Unique identifier (UUID)        |
| internal_name          | string    | Required, Unique      | Internal reference name         |
| type                   | enum      | Required              | Item type: 'object', 'monument' |
| parent_id              | uuid      | Nullable, Foreign Key | Parent item for hierarchy       |
| partner_id             | uuid      | Foreign Key           | Owning partner                  |
| country_id             | string    | Foreign Key           | Associated country (ISO 3166-1) |
| project_id             | uuid      | Nullable, Foreign Key | Associated project              |
| owner_reference        | string    | Nullable              | Partner's reference number      |
| mwnf_reference         | string    | Nullable              | MWNF system reference           |
| backward_compatibility | string    | Nullable              | Legacy system reference         |
| created_at             | timestamp | Auto-managed          | Creation timestamp              |
| updated_at             | timestamp | Auto-managed          | Last update timestamp           |

## 🔗 Relationships

### Belongs To

- **`partner()`**: Belongs to `Partner` model
- **`country()`**: Belongs to `Country` model
- **`project()`**: Belongs to `Project` model (nullable)
- **`parent()`**: Belongs to `Item` model (self-referential, nullable)

### Has Many

- **`children()`**: Has many `Item` models (self-referential)
- **`itemImages()`**: Has many `ItemImage` models
- **`translations()`**: Has many `ItemTranslation` models

### Many-to-Many

- **`artists()`**: Many-to-many with `Artist` via `artist_item` pivot
- **`workshops()`**: Many-to-many with `Workshop` via `item_workshop` pivot
- **`tags()`**: Many-to-many with `Tag` via `item_tag` pivot
- **`collections()`**: Many-to-many with `Collection` via `collection_item` pivot

### Through Relationships

- **`images()`**: Has many `AvailableImage` through `ItemImage`

## 🎯 Key Features

### 📋 Item Types

- **`object`**: Physical museum artifacts, artworks, specimens
- **`monument`**: Large-scale architectural or sculptural works

### 🏗️ Hierarchical Structure

```php
// Parent-child relationships
$monument = Item::create(['type' => 'monument', 'internal_name' => 'Ancient Temple']);
$detail = Item::create([
    'type' => 'object',
    'internal_name' => 'Temple Frieze',
    'parent_id' => $monument->id
]);

// Query hierarchy
$children = $monument->children; // All child items
$parent = $detail->parent; // Parent item
$ancestors = $detail->ancestors(); // All parent items up the tree
$descendants = $monument->descendants(); // All child items down the tree
```

### 📸 Image Management

```php
// Attach images with display order
$item->attachImage($availableImageId, $displayOrder);

// Get ordered image gallery
$gallery = $item->images()->ordered()->get();

// Reorder images
$item->reorderImages([
    $imageId1 => 1,
    $imageId2 => 2,
    $imageId3 => 3
]);
```

## 🔧 Model Scopes

### Type Filtering

```php
public function scopeObjects(Builder $query): Builder // type = 'object'
public function scopeMonuments(Builder $query): Builder // type = 'monument'
```

### Hierarchy Scopes

```php
public function scopeRootItems(Builder $query): Builder // parent_id IS NULL
public function scopeChildItems(Builder $query): Builder // parent_id IS NOT NULL
public function scopeWithoutChildren(Builder $query): Builder // No children
```

### Relationship Scopes

```php
public function scopeWithImages(Builder $query): Builder // Has item images
public function scopeInCollections(Builder $query): Builder // Has collections
public function scopeByPartner(Builder $query, string $partnerId): Builder
```

## 🌍 API Integration

### Available Endpoints

- `GET /api/items` - List all items with filtering
- `GET /api/items/{item}` - Get specific item with relationships
- `POST /api/items` - Create new item
- `PUT /api/items/{item}` - Update item
- `DELETE /api/items/{item}` - Delete item
- `GET /api/items/{item}/children` - Get child items
- `GET /api/items/{item}/images` - Get item images
- `POST /api/items/{item}/images/attach` - Attach image

### Resource Structure

```json
{
  "id": "uuid",
  "internal_name": "Ancient Vase",
  "type": "object",
  "parent_id": null,
  "owner_reference": "PAR-2023-001",
  "mwnf_reference": "MWNF-VAE-001",
  "backward_compatibility": null,
  "created_at": "2023-01-01T00:00:00Z",
  "updated_at": "2023-01-01T00:00:00Z",
  "partner": {
    /* PartnerResource */
  },
  "country": {
    /* CountryResource */
  },
  "project": {
    /* ProjectResource */
  },
  "parent": {
    /* ItemResource */
  },
  "children": [
    /* ItemResource[] */
  ],
  "images": [
    /* ItemImageResource[] */
  ],
  "collections": [
    /* CollectionResource[] */
  ]
}
```

## ⚙️ Business Logic

### Validation Rules

```php
// Store/Update Request
[
    'internal_name' => 'required|string|max:255|unique:items,internal_name,' . $id,
    'type' => 'required|in:object,monument',
    'parent_id' => 'nullable|uuid|exists:items,id|not_in:' . $id,
    'partner_id' => 'required|uuid|exists:partners,id',
    'country_id' => 'nullable|string|size:3|exists:countries,id',
    'project_id' => 'nullable|uuid|exists:projects,id',
    'owner_reference' => 'nullable|string|max:255',
    'mwnf_reference' => 'nullable|string|max:255'
]
```

### Hierarchy Constraints

- Items cannot be their own parent
- Circular references are prevented
- Maximum hierarchy depth recommended: 3 levels
- Deleting parent items requires handling of children

## Usage Examples

### Creating Hierarchical Items

```php
// Create a monument (parent)
$temple = Item::create([
    'internal_name' => 'Temple of Artemis',
    'type' => 'monument',
    'partner_id' => $partner->id,
    'country_id' => 'TUR'
]);

// Create architectural elements (children)
$frieze = Item::create([
    'internal_name' => 'Eastern Frieze',
    'type' => 'object',
    'parent_id' => $temple->id,
    'partner_id' => $partner->id
]);

$column = Item::create([
    'internal_name' => 'Ionic Column Capital',
    'type' => 'object',
    'parent_id' => $temple->id,
    'partner_id' => $partner->id
]);
```

### Collection Management

```php
// Add item to multiple collections
$item->collections()->attach([
    $ancientArtCollection->id,
    $greekSculptureExhibition->id,
    $marblesGallery->id
]);

// Query items in specific collection
$collectionItems = $collection->items()
    ->with(['partner', 'country', 'images'])
    ->get();
```

### Complex Queries

```php
// Find all monuments with their architectural elements
$monumentsWithDetails = Item::monuments()
    ->with(['children', 'images', 'collections'])
    ->whereHas('children')
    ->get();

// Find items by partner and type
$partnerObjects = Item::objects()
    ->byPartner($partnerId)
    ->withImages()
    ->orderBy('internal_name')
    ->get();
```
