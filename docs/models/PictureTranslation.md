---
layout: default
title: PictureTranslation
parent: Database Models
---
# PictureTranslation

**Namespace:** `App\Models\PictureTranslation`

| Property                   | Type   | Description                 |
| -------------------------- | ------ | --------------------------- |
| picture_id                 | uuid   | Foreign key to Picture      |
| language_id                | string | Foreign key to Language     |
| context_id                 | uuid   | Foreign key to Context      |
| description                | string | Description                 |
| caption                    | string | Caption                     |
| author_id                  | uuid   | Foreign key to Author       |
| text_copy_editor_id        | uuid   | Foreign key to User         |
| translator_id              | uuid   | Foreign key to User         |
| translation_copy_editor_id | uuid   | Foreign key to User         |
| backward_compatibility     | string | Backward compatibility info |
| extra                      | object | Extra data                  |
