---
layout: default
title: Artist
parent: Database Models
---

# Artist

**Namespace:** `App\Models\Artist`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| name                   | string | Name of the artist          |
| place_of_birth         | string | Birthplace                  |
| place_of_death         | string | Place of death              |
| date_of_birth          | date   | Date of birth               |
| date_of_death          | date   | Date of death               |
| period_of_activity     | string | Period of activity          |
| internal_name          | string | Internal name               |
| backward_compatibility | string | Backward compatibility info |

**Relationships:**

- Many-to-many with `Item`
