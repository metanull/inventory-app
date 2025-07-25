# DetailTranslation

**Namespace:** `App\Models\DetailTranslation`

| Property                   | Type   | Description                 |
| -------------------------- | ------ | --------------------------- |
| detail_id                  | uuid   | Foreign key to Detail       |
| language_id                | string | Foreign key to Language     |
| context_id                 | uuid   | Foreign key to Context      |
| name                       | string | Name                        |
| alternate_name             | string | Alternate name              |
| description                | string | Description                 |
| author_id                  | uuid   | Foreign key to Author       |
| text_copy_editor_id        | uuid   | Foreign key to User         |
| translator_id              | uuid   | Foreign key to User         |
| translation_copy_editor_id | uuid   | Foreign key to User         |
| backward_compatibility     | string | Backward compatibility info |
| extra                      | object | Extra data                  |
