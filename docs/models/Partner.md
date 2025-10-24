---
layout: default
title: Partner
parent: Database Models
---

# Partner

**Namespace:** `App\Models\Partner`

| Property               | Type    | Description                                       |
| ---------------------- | ------- | ------------------------------------------------- |
| internal_name          | string  | Internal name                                     |
| type                   | string  | Type of partner (museum, institution, individual) |
| backward_compatibility | string  | Backward compatibility info                       |
| country_id             | string  | Foreign key to Country (ISO 3166-1 alpha-3)       |
| latitude               | decimal | GPS latitude (-90 to 90)                          |
| longitude              | decimal | GPS longitude (-180 to 180)                       |
| map_zoom               | integer | Map zoom level (1-20)                             |
| project_id             | uuid    | Optional foreign key to Project                   |
| monument_item_id       | uuid    | Optional foreign key to Item (monument)           |
| visible                | boolean | Visibility flag (default: false)                  |

**Relationships:**

- `items()`: Has many `Item`
- `country()`: Belongs to `Country`
- `project()`: Belongs to `Project` (optional)
- `monumentItem()`: Belongs to `Item` (monument reference)
- `translations()`: Has many `PartnerTranslation`
- `partnerImages()`: Has many `PartnerImage` (ordered by display_order)
- `collections()`: Belongs to many `Collection` (via `collection_partner` pivot)

**Scopes:**

- `visible()`: Filters only visible partners (where `visible = true`)
