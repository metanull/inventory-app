# Workshop

**Namespace:** `App\Models\Workshop`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| name                   | string | Name of the workshop        |
| internal_name          | string | Internal name               |
| backward_compatibility | string | Backward compatibility info |

**Relationships:**

- `items()`: Belongs to many `Item`
