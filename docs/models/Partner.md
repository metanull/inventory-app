---
layout: default
title: Partner
parent: Database Models
---

# Partner

**Namespace:** `App\Models\Partner`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| internal_name          | string | Internal name               |
| type                   | string | Type of partner             |
| backward_compatibility | string | Backward compatibility info |
| country_id             | uuid   | Foreign key to Country      |

**Relationships:**

- `items()`: Has many `Item`
- `country()`: Belongs to `Country`
