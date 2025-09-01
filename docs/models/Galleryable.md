---
layout: default
title: Galleryable
parent: Database Models
---

# Galleryable

**Namespace:** `App\Models\Galleryable`

| Property         | Type   | Description                       |
| ---------------- | ------ | --------------------------------- |
| gallery_id       | uuid   | Foreign key to Gallery            |
| galleryable_id   | uuid   | Foreign key to Item/Detail        |
| galleryable_type | string | Type of galleryable (Item/Detail) |
| order            | int    | Ordering in gallery               |
