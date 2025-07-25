# GalleryTranslation

**Namespace:** `App\Models\GalleryTranslation`

| Property               | Type    | Description                       |
|------------------------|---------|-----------------------------------|
| gallery_id             | uuid    | Foreign key to Gallery            |
| language_id            | string  | Foreign key to Language           |
| context_id             | uuid    | Foreign key to Context            |
| title                  | string  | Title                             |
| description            | string  | Description                       |
| url                    | string  | URL                               |
| backward_compatibility | string  | Backward compatibility info       |
| extra                  | object  | Extra data                        |
