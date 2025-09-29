---
layout: default
title: Theme
parent: Database Models
---

# 🎯 Theme Model

{: .highlight }

> The Theme model represents thematic groupings and concepts, supporting hierarchical organization and relationships with collections.

## 📊 Model Overview

| Property         | Type        | Description                 |
| ---------------- | ----------- | --------------------------- |
| **Model Name**   | Theme       | Thematic organization model |
| **Table Name**   | `themes`    | Database table              |
| **Primary Key**  | `id` (UUID) | Unique identifier           |
| **Timestamps**   | ✅ Yes      | `created_at`, `updated_at`  |
| **Soft Deletes** | ❌ No       | Hard deletes only           |

## 🏗️ Database Schema

| Column                 | Type      | Constraints           | Description                |
| ---------------------- | --------- | --------------------- | -------------------------- |
| id                     | uuid      | Primary Key           | Unique identifier (UUID)   |
| collection_id          | uuid      | Nullable, Foreign Key | Associated collection      |
| parent_id              | uuid      | Nullable, Foreign Key | Parent theme for hierarchy |
| internal_name          | string    | Required, Unique      | Internal reference name    |
| backward_compatibility | string    | Nullable              | Legacy system reference    |
| created_at             | timestamp | Auto-managed          | Creation timestamp         |
| updated_at             | timestamp | Auto-managed          | Last update timestamp      |

## 🔗 Relationships

### Belongs To

- **`collection()`**: Belongs to `Collection` model (nullable)
- **`parent()`**: Belongs to `Theme` model (self-referential, nullable)

### Has Many

- **`subthemes()`**: Has many `Theme` models (self-referential)
- **`translations()`**: Has many `ThemeTranslation` models

### Many-to-Many

- **`collections()`**: Many-to-many with `Collection` via `collection_theme` pivot table
