# AddressTranslation

**Namespace:** `App\Models\AddressTranslation`

| Property               | Type   | Description                       |
|------------------------|--------|-----------------------------------|
| address_id             | uuid   | Foreign key to Address            |
| language_id            | string | Foreign key to Language           |
| address                | string | Translated address                |
| description            | string | Description                       |
| backward_compatibility | string | Backward compatibility info       |

**Relationships:**
- `address()`: Belongs to `Address`
