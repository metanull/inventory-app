# Country

**Namespace:** `App\Models\Country`

| Property               | Type    | Description                       |
|------------------------|---------|-----------------------------------|
| id                     | string  | ISO 3166-1 alpha-3 code (PK)      |
| internal_name          | string  | Internal name                     |
| backward_compatibility | string  | Backward compatibility info       |

**Relationships:**
- `items()`: Has many `Item`
- `partners()`: Has many `Partner`
