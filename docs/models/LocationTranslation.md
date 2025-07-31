---
layout: default
title: LocationTranslation
parent: Database Models
---
# LocationTranslation

**Namespace:** `App\Models\LocationTranslation`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| location_id            | uuid   | Foreign key to Location     |
| language_id            | string | Foreign key to Language     |
| name                   | string | Name                        |
| description            | string | Description                 |
| backward_compatibility | string | Backward compatibility info |
