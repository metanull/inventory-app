---
layout: default
title: Location
parent: Database Models
---

# Location

**Namespace:** `App\Models\Location`

| Property      | Type   | Description            |
| ------------- | ------ | ---------------------- |
| internal_name | string | Internal name          |
| country_id    | int    | Foreign key to Country |

**Relationships:**

- `country()`: Belongs to `Country`
