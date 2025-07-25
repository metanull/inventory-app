# ThemeTranslation

**Namespace:** `App\Models\ThemeTranslation`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| theme_id               | uuid   | Foreign key to Theme        |
| language_id            | string | Foreign key to Language     |
| context_id             | uuid   | Foreign key to Context      |
| title                  | string | Title                       |
| description            | string | Description                 |
| introduction           | string | Introduction                |
| backward_compatibility | string | Backward compatibility info |
| extra                  | object | Extra data                  |
