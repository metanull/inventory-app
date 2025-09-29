---
layout: default
title: ItemImage
parent: Database Models
---

# 📸 ItemImage Model

{: .highlight }

> The ItemImage model manages the association between Items and their images, providing image galleries with display ordering and management capabilities.

## 📊 Model Overview

| Property         | Type          | Description                    |
| ---------------- | ------------- | ------------------------------ |
| **Model Name**   | ItemImage     | Primary image management model |
| **Table Name**   | `item_images` | Database table                 |
| **Primary Key**  | `id` (UUID)   | Unique identifier              |
| **Timestamps**   | ✅ Yes        | `created_at`, `updated_at`     |
| **Soft Deletes** | ❌ No         | Hard deletes only              |

## 🏗️ Database Schema

| Column                 | Type      | Constraints           | Description                      |
| ---------------------- | --------- | --------------------- | -------------------------------- |
| id                     | uuid      | Primary Key           | Unique identifier (UUID)         |
| item_id                | uuid      | Foreign Key, Required | References `items.id`            |
| available_image_id     | uuid      | Foreign Key, Required | References `available_images.id` |
| display_order          | integer   | Required, Default: 1  | Order for display (1 = first)    |
| backward_compatibility | string    | Nullable              | Legacy system reference          |
| created_at             | timestamp | Auto-managed          | Creation timestamp               |
| updated_at             | timestamp | Auto-managed          | Last update timestamp            |

## 🔗 Relationships

### Belongs To

- **`item()`**: Belongs to `Item` model
- **`availableImage()`**: Belongs to `AvailableImage` model

### Scopes

- **`ordered()`**: Orders by `display_order` ASC
- **`forItem($itemId)`**: Filters by specific item ID

## 🎯 Key Features

### 📋 Display Order Management

```php
// Attach image with specific display order
$item->attachImage($availableImageId, $displayOrder);

// Reorder images
$item->reorderImages([
    $imageId1 => 1,  // First position
    $imageId2 => 2,  // Second position
    $imageId3 => 3,  // Third position
]);

// Get ordered images for an item
$orderedImages = $item->images()->ordered()->get();
```

### 🔄 Image Management Operations

```php
// Attach new image (automatically sets display order)
$item->itemImages()->create([
    'available_image_id' => $availableImageId,
    'display_order' => $item->itemImages()->max('display_order') + 1
]);

// Detach image
$item->itemImages()
    ->where('available_image_id', $availableImageId)
    ->delete();

// Move image to first position
$itemImage = $item->itemImages()->find($imageId);
$itemImage->moveToPosition(1);
```

## 🔧 Model Methods

### Display Order Management

```php
public function moveToPosition(int $position): void
public function moveUp(): void
public function moveDown(): void
public function moveToTop(): void
public function moveToBottom(): void
```

### Query Helpers

```php
public function scopeOrdered(Builder $query): Builder
public function scopeForItem(Builder $query, string $itemId): Builder
```

## 🌍 API Integration

### Available Endpoints

- `GET /api/items/{item}/images` - List item images
- `POST /api/items/{item}/images/attach` - Attach image to item
- `DELETE /api/items/{item}/images/{image}/detach` - Detach image
- `POST /api/items/{item}/images/reorder` - Reorder images

### Resource Structure

```json
{
  "id": "uuid",
  "item_id": "uuid",
  "available_image_id": "uuid",
  "display_order": 1,
  "backward_compatibility": null,
  "created_at": "2023-01-01T00:00:00Z",
  "updated_at": "2023-01-01T00:00:00Z",
  "item": {
    /* ItemResource */
  },
  "available_image": {
    /* AvailableImageResource */
  }
}
```

## ⚙️ Business Logic

### Unique Constraints

- One image can be attached to an item multiple times (different display orders)
- Display order must be unique per item
- Automatic reordering when images are added/removed

### Validation Rules

```php
// Store Request
[
    'available_image_id' => 'required|uuid|exists:available_images,id',
    'display_order' => 'sometimes|integer|min:1'
]

// Reorder Request
[
    'images' => 'required|array',
    'images.*' => 'uuid|exists:item_images,id'
]
```

## Usage Examples

### Basic Image Management

```php
// Create new item with images
$item = Item::create(['internal_name' => 'Ancient Vase']);

// Attach images in order
$item->attachImage($image1Id, 1);
$item->attachImage($image2Id, 2);
$item->attachImage($image3Id, 3);

// Get all images for display
$gallery = $item->images()
    ->ordered()
    ->with('availableImage')
    ->get();
```

### Advanced Ordering

```php
// Reorder entire gallery
$item->reorderImages([
    $imageId3 => 1,  // Move third image to first
    $imageId1 => 2,  // Move first image to second
    $imageId2 => 3,  // Move second image to third
]);

// Insert image at specific position
$item->insertImageAt($newImageId, 2); // Insert at position 2
```
