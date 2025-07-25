# Exhibition

**Namespace:** `App\Models\Exhibition`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| backward_compatibility | string | Backward compatibility info |
| internal_name          | string | Internal name               |

**Relationships:**

- `translations()`: Has many `ExhibitionTranslation`
- `partners()`: Belongs to many `Partner` (polymorphic)
- `themes()`: Has many `Theme`
