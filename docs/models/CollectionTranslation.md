---
layout: default
title: CollectionTranslation
parent: Database Models
---
# CollectionTranslation

**Namespace:** `App\Models\CollectionTranslation`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| collection_id          | uuid   | Foreign key to Collection   |
| language_id            | string | Foreign key to Language     |
| context_id             | uuid   | Foreign key to Context      |
| title                  | string | Title                       |
| description            | string | Description                 |
| url                    | string | URL                         |
| backward_compatibility | string | Backward compatibility info |
| extra                  | object | Extra data                  |
