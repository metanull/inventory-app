---
layout: default
title: PartnerImage
parent: Database Models
---

# 📸 PartnerImage Model

{: .highlight }

> The PartnerImage model manages partner-level images (logos, photos) with display ordering capabilities. These images are shown across all contexts and languages unless overridden by translation-specific images.

## 📊 Model Overview

| Property         | Type             | Description                    |
| ---------------- | ---------------- | ------------------------------ |
| **Model Name**   | PartnerImage     | Partner image management model |
| **Table Name**   | `partner_images` | Database table                 |
| **Primary Key**  | `id` (UUID)      | Unique identifier              |
| **Timestamps**   | ✅ Yes           | `created_at`, `updated_at`     |
| **Soft Deletes** | ❌ No            | Hard deletes only              |

## 🏗️ Database Schema

| Column                 | Type      | Constraints           | Description                      |
| ---------------------- | --------- | --------------------- | -------------------------------- |
| id                     | uuid      | Primary Key           | Unique identifier (UUID)         |
| partner_id             | uuid      | Foreign Key, Required | References `partners.id`         |
| available_image_id     | uuid      | Foreign Key, Required | References `available_images.id` |
| display_order          | integer   | Required, Default: 1  | Order for display (1 = first)    |
| backward_compatibility | string    | Nullable              | Legacy system reference          |
| created_at             | timestamp | Auto-managed          | Creation timestamp               |
| updated_at             | timestamp | Auto-managed          | Last update timestamp            |

## 🔗 Relationships

### Belongs To

- **`partner()`**: Belongs to `Partner` model
- **`availableImage()`**: Belongs to `AvailableImage` model

### Scopes

- **`ordered()`**: Orders by `display_order` ASC
- **`forPartner($partnerId)`**: Filters by specific partner ID

## 🎯 Key Features

### 📋 Display Order Management

```php
// Attach image with specific display order
$partner->attachImage($availableImageId, $displayOrder);

// Get ordered images for a partner
$orderedImages = $partner->partnerImages()->ordered()->get();
```

## 🌍 API Integration

### Available Endpoints

- `GET /api/partner-image` - List all partner images
- `GET /api/partner-image/{id}` - Show specific partner image
- `POST /api/partner-image` - Create new partner image
- `PATCH /api/partner-image/{id}` - Update partner image
- `DELETE /api/partner-image/{id}` - Delete partner image

### Resource Structure

```json
{
  "id": "uuid",
  "partner_id": "uuid",
  "available_image_id": "uuid",
  "display_order": 1,
  "backward_compatibility": null,
  "created_at": "2025-01-01T00:00:00Z",
  "updated_at": "2025-01-01T00:00:00Z"
}
```

## ⚙️ Business Logic

### Validation Rules

```php
// Store Request
[
    'partner_id' => 'required|uuid|exists:partners,id',
    'available_image_id' => 'required|uuid|exists:available_images,id',
    'display_order' => 'sometimes|integer|min:1',
    'backward_compatibility' => 'nullable|string|max:255'
]
```

## Usage Pattern

Follows the same pattern as `ItemImage` - see [ItemImage documentation](ItemImage) for detailed usage examples.
