---
layout: default
title: Theme
parent: Database Models
---

# Theme

**Namespace:** `App\Models\Theme`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| exhibition_id          | uuid   | Foreign key to Exhibition   |
| parent_id              | uuid   | Parent theme ID             |
| internal_name          | string | Internal name               |
| backward_compatibility | string | Backward compatibility info |

**Relationships:**

- `exhibition()`: Belongs to `Exhibition`
- `parent()`: Belongs to `Theme`
- `subthemes()`: Has many `Theme`
- `translations()`: Has many `ThemeTranslation`
