---
layout: default
title: Detail
parent: Database Models
---

# Detail

**Namespace:** `App\Models\Detail`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| item_id                | uuid   | Foreign key to Item         |
| internal_name          | string | Internal name               |
| backward_compatibility | string | Backward compatibility info |

**Relationships:**

- `item()`: Belongs to `Item`
- `translations()`: Has many `DetailTranslation`
- `pictures()`: Morph many `Picture`
