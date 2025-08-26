---
layout: default
title: Tag
parent: Database Models
---

# Tag

**Namespace:** `App\Models\Tag`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| internal_name          | string | Internal name               |
| backward_compatibility | string | Backward compatibility info |
| description            | string | Description                 |

**Relationships:**

- `items()`: Belongs to many `Item`

**Scopes:**

- `forItem`: Get tags for a specific item
