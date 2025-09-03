---
layout: default
title: ProvinceTranslation
parent: Database Models
---

# ProvinceTranslation

**Namespace:** `App\Models\ProvinceTranslation`

| Property               | Type   | Description                 |
| ---------------------- | ------ | --------------------------- |
| province_id            | uuid   | Foreign key to Province     |
| language_id            | string | Foreign key to Language     |
| name                   | string | Name                        |
| description            | string | Description                 |
| backward_compatibility | string | Backward compatibility info |
