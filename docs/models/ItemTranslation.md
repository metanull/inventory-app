# ItemTranslation

**Namespace:** `App\Models\ItemTranslation`

| Property              | Type   | Description             |
| --------------------- | ------ | ----------------------- |
| item_id               | uuid   | Foreign key to Item     |
| language_id           | string | Foreign key to Language |
| context_id            | uuid   | Foreign key to Context  |
| name                  | string | Name                    |
| alternate_name        | string | Alternate name          |
| description           | string | Description             |
| type                  | string | Type                    |
| holder                | string | Holder                  |
| owner                 | string | Owner                   |
| initial_owner         | string | Initial owner           |
| dates                 | string | Dates                   |
| location              | string | Location                |
| dimensions            | string | Dimensions              |
| place_of_production   | string | Place of production     |
| method_for_datation   | string | Method for datation     |
| method_for_provenance | string | Method for provenance   |
| obtention             | string | Obtention               |
| bibliography          | string | Bibliography            |
