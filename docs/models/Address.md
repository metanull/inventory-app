# Address

**Namespace:** `App\Models\Address`

| Property      | Type   | Description              |
|--------------|--------|--------------------------|
| internal_name| string | Internal name of address |
| country_id   | int    | Foreign key to Country   |

**Relationships:**
- `country()`: Belongs to `Country`
