---
layout: default
title: Database Models
nav_order: 6
has_children: true
---

# 🗄️ Database Models

{: .highlight }

> This documentation provides a comprehensive overview of all database models in the application, their properties, relationships, and usage patterns.

## 📊 Overview

- **📈 Total Models Documented:** 37
- **🔧 Common Features:**
  - 🆔 All models use UUIDs (except Language, Country, User)
  - 🏷️ All models have `internal_name` and `backward_compatibility` fields
  - 🔗 Relationships are defined using Eloquent ORM best practices
  - 🌍 Translations are handled via dedicated translation models
  - 🔄 Many models support polymorphic and many-to-many relationships

## 🎯 Key Model Categories

### 🌍 Geographic Models

- **📍 Address, Location, Province:** Represent geographical entities, each with translation models

### 👥 People & Organizations

- **🎨 Artist, Author, Partner:** Represent people/entities, with relationships to items and collections

### 📦 Core Content Models

- **🏛️ Item, Collection, Exhibition, Gallery, Theme:** Core content models, supporting translations, partners, and hierarchical relationships

### 🖼️ Media Models

- **📸 Picture, ImageUpload, AvailableImage:** Handle image storage and metadata

### 🏷️ Classification

- **🔖 Tag:** Supports tagging of items

### 👤 System Models

- **🔐 User:** Standard Laravel user model
- **🌐 Language, Country:** Use ISO codes as primary keys

## 🔗 Relationship Types

| Type                  | Icon | Description                | Example                       |
| --------------------- | ---- | -------------------------- | ----------------------------- |
| **BelongsTo**         | ⬆️   | Foreign key relationships  | Item → Partner                |
| **HasMany**           | ⬇️   | One-to-many relationships  | Country → Item                |
| **BelongsToMany**     | ↔️   | Many-to-many relationships | Artist ↔ Item                |
| **MorphTo/MorphMany** | 🔄   | Polymorphic relationships  | Picture → Item/Detail/Partner |

## 🌍 Translation System

- 🗣️ Most core models have a corresponding translation model (e.g., ItemTranslation, CollectionTranslation)
- 📝 Translation models include language, context, and extra metadata fields
- 🎯 Supports multi-language and multi-context content delivery

## ⚙️ Technical Features

### 📏 Scopes & Filtering

- 🔍 Models use Laravel scopes for filtering (e.g., `default`, `english`, `forItem`)
- ⚡ Optimized query performance through strategic scoping

### 🛠️ Traits & Patterns

- 🏭 All models use `HasFactory` and `HasUuids` traits where appropriate
- 📋 Consistent coding patterns across all models
- 🔒 Built-in security and validation features

---

# 📚 Complete Models Index

{: .fs-6 .fw-300 }
Click any model name below to view its detailed documentation with properties, relationships, and usage examples.

## 🔤 Alphabetical Model List

| Model                                                   | Category       | Description                                   |
| ------------------------------------------------------- | -------------- | --------------------------------------------- |
| 📍 [Address](Address)                                   | Geographic     | Physical addresses with country relationships |
| 🌍 [AddressTranslation](AddressTranslation)             | Translation    | Multi-language address translations           |
| 🎨 [Artist](Artist)                                     | People         | Artists who create items in collections       |
| ✍️ [Author](Author)                                     | People         | Authors of written content                    |
| 🖼️ [AvailableImage](AvailableImage)                     | Media          | Available images with metadata                |
| 📦 [Collection](Collection)                             | Content        | Collections of museum items                   |
| 🤝 [CollectionPartner](CollectionPartner)               | Relationship   | Collection-partner associations               |
| 🌍 [CollectionTranslation](CollectionTranslation)       | Translation    | Multi-language collection content             |
| 📞 [Contact](Contact)                                   | Communication  | Contact information storage                   |
| 🌍 [ContactTranslation](ContactTranslation)             | Translation    | Multi-language contact labels                 |
| ⚙️ [Context](Context)                                   | Configuration  | Application context settings                  |
| 🗺️ [Country](Country)                                   | Geographic     | Countries using ISO codes                     |
| 📋 [Detail](Detail)                                     | Content        | Detailed item descriptions                    |
| 🌍 [DetailTranslation](DetailTranslation)               | Translation    | Multi-language detail content                 |
| 🏛️ [Exhibition](Exhibition)                             | Content        | Museum exhibitions                            |
| 🌍 [ExhibitionTranslation](ExhibitionTranslation)       | Translation    | Multi-language exhibition content             |
| 🖼️ [Gallery](Gallery)                                   | Content        | Image galleries                               |
| 🔗 [Galleryable](Galleryable)                           | Relationship   | Gallery content associations                  |
| 🤝 [GalleryPartner](GalleryPartner)                     | Relationship   | Gallery-partner associations                  |
| 🌍 [GalleryTranslation](GalleryTranslation)             | Translation    | Multi-language gallery content                |
| 📤 [ImageUpload](ImageUpload)                           | Media          | Uploaded image metadata                       |
| 🏺 [Item](Item)                                         | Content        | Core museum items                             |
| 🌍 [ItemTranslation](ItemTranslation)                   | Translation    | Multi-language item content                   |
| 🌐 [Language](Language)                                 | Configuration  | Supported languages (ISO codes)               |
| 📍 [Location](Location)                                 | Geographic     | Specific locations                            |
| 🌍 [LocationTranslation](LocationTranslation)           | Translation    | Multi-language location names                 |
| 🏢 [Partner](Partner)                                   | Organization   | Institutional partners                        |
| 📸 [Picture](Picture)                                   | Media          | Images with metadata and relationships        |
| 🌍 [PictureTranslation](PictureTranslation)             | Translation    | Multi-language picture descriptions           |
| 📊 [Project](Project)                                   | Management     | Project management                            |
| 🗺️ [Province](Province)                                 | Geographic     | Administrative provinces                      |
| 🌍 [ProvinceTranslation](ProvinceTranslation)           | Translation    | Multi-language province names                 |
| 🏷️ [Tag](Tag)                                           | Classification | Content tagging system                        |
| 🎯 [Theme](Theme)                                       | Content        | Exhibition themes and subthemes               |
| 🌍 [ThemeTranslation](ThemeTranslation)                 | Translation    | Multi-language theme content                  |
| 👤 [User](User)                                         | System         | Application users                             |
| 🔨 [Workshop](Workshop)                                 | Content        | Workshop information                          |

---

{: .fs-3 .fw-300 }
💡 **Tip:** Use your browser's search function (Ctrl+F / Cmd+F) to quickly find specific models in this list.

For an overview of model relationships and architecture, see the summary above.
