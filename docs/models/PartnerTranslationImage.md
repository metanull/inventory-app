---
layout: default
title: PartnerTranslationImage
parent: Database Models
---

# 📸 PartnerTranslationImage Model

{: .highlight }

> The PartnerTranslationImage model manages context/language-specific images for partner translations. These images override partner-level images for specific language and context combinations.

## 📊 Model Overview

| Property         | Type                         | Description                           |
| ---------------- | ---------------------------- | ------------------------------------- |
| **Model Name**   | PartnerTranslationImage      | Translation-specific image management |
| **Table Name**   | `partner_translation_images` | Database table                        |
| **Primary Key**  | `id` (UUID)                  | Unique identifier                     |
| **Timestamps**   | ✅ Yes                       | `created_at`, `updated_at`            |
| **Soft Deletes** | ❌ No                        | Hard deletes only                     |

## 🏗️ Database Schema

| Column                 | Type      | Constraints           | Description                          |
| ---------------------- | --------- | --------------------- | ------------------------------------ |
| id                     | uuid      | Primary Key           | Unique identifier (UUID)             |
| partner_translation_id | uuid      | Foreign Key, Required | References `partner_translations.id` |
| available_image_id     | uuid      | Foreign Key, Required | References `available_images.id`     |
| display_order          | integer   | Required, Default: 1  | Order for display (1 = first)        |
| backward_compatibility | string    | Nullable              | Legacy system reference              |
| created_at             | timestamp | Auto-managed          | Creation timestamp                   |
| updated_at             | timestamp | Auto-managed          | Last update timestamp                |

## 🔗 Relationships

### Belongs To

- **`partnerTranslation()`**: Belongs to `PartnerTranslation` model
- **`availableImage()`**: Belongs to `AvailableImage` model

### Scopes

- **`ordered()`**: Orders by `display_order` ASC
- **`forPartnerTranslation($partnerTranslationId)`**: Filters by specific partner translation ID

## 🎯 Use Cases

### Context-Specific Images

Partner translation images allow different images for different contexts:

- Arabic context may show Arabic signage
- English context may show English signage
- Different projects may require different branding

## 🌍 API Integration

### Available Endpoints

- `GET /api/partner-translation-image` - List all partner translation images
- `GET /api/partner-translation-image/{id}` - Show specific partner translation image
- `POST /api/partner-translation-image` - Create new partner translation image
- `PATCH /api/partner-translation-image/{id}` - Update partner translation image
- `DELETE /api/partner-translation-image/{id}` - Delete partner translation image

### Resource Structure

```json
{
  "id": "uuid",
  "partner_translation_id": "uuid",
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
    'partner_translation_id' => 'required|uuid|exists:partner_translations,id',
    'available_image_id' => 'required|uuid|exists:available_images,id',
    'display_order' => 'sometimes|integer|min:1',
    'backward_compatibility' => 'nullable|string|max:255'
]
```

## Usage Pattern

Follows the same pattern as `ItemImage` and `PartnerImage` - see [ItemImage documentation](ItemImage) for detailed usage examples.
