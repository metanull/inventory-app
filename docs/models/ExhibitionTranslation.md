---
layout: default
title: ExhibitionTranslation
parent: Database Models
---
# ExhibitionTranslation

**Namespace:** `App\Models\ExhibitionTranslation`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| exhibition_id          | uuid   | Foreign key to Exhibition   |
| language_id            | string | Foreign key to Language     |
| context_id             | uuid   | Foreign key to Context      |
| title                  | string | Title                       |
| description            | string | Description                 |
| url                    | string | URL                         |
| backward_compatibility | string | Backward compatibility info |
| extra                  | object | Extra data                  |
