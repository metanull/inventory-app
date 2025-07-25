# Item

**Namespace:** `App\Models\Item`

| Property               | Type   | Description                    |
|------------------------|--------|--------------------------------|
| partner_id             | int    | Foreign key to Partner         |
| internal_name          | string | Internal name                  |
| type                   | string | Type of item                   |
| backward_compatibility | string | Backward compatibility info    |
| country_id             | int    | Foreign key to Country         |
| project_id             | int    | Foreign key to Project         |
| collection_id          | int    | Foreign key to Collection      |
| owner_reference        | string | Owner reference                |
| mwnf_reference         | string | MWNF reference                 |

**Relationships:**
- Belongs to `Partner`, `Country`, `Project`, `Collection`
- Many-to-many with `Artist`, `Workshop`, `Tag`
- Has many `Picture`, `Detail`, etc.
