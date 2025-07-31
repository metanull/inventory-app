---
layout: default
title: Language
parent: Database Models
---
# Language

**Namespace:** `App\Models\Language`

| Property               | Type    | Description                 |
| ---------------------- | ------- | --------------------------- |
| id                     | string  | ISO 639-1 code (PK)         |
| internal_name          | string  | Internal name               |
| backward_compatibility | string  | Backward compatibility info |
| is_default             | boolean | Is default language         |

**Scopes:**

- `english`: Returns only English language
- `default`: Returns only default language
