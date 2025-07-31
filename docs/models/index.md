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
| 📍 [Address](Address.md)                                | Geographic     | Physical addresses with country relationships |
| 🌍 [AddressTranslation](AddressTranslation.md)          | Translation    | Multi-language address translations           |
| 🎨 [Artist](Artist.md)                                  | People         | Artists who create items in collections       |
| ✍️ [Author](Author.md)                                  | People         | Authors of written content                    |
| 🖼️ [AvailableImage](AvailableImage.md)                  | Media          | Available images with metadata                |
| 📦 [Collection](Collection.md)                          | Content        | Collections of museum items                   |
| 🤝 [CollectionPartner](CollectionPartner.md)            | Relationship   | Collection-partner associations               |
| 🌍 [CollectionTranslation](CollectionTranslation.md)    | Translation    | Multi-language collection content             |
| 📞 [Contact](Contact.md)                                | Communication  | Contact information storage                   |
| 🌍 [ContactTranslation](ContactTranslation.md)          | Translation    | Multi-language contact labels                 |
| ⚙️ [Context](Context.md)                                | Configuration  | Application context settings                  |
| 🗺️ [Country](Country.md)                                | Geographic     | Countries using ISO codes                     |
| 📋 [Detail](Detail.md)                                  | Content        | Detailed item descriptions                    |
| 🌍 [DetailTranslation](DetailTranslation.md)            | Translation    | Multi-language detail content                 |
| 🏛️ [Exhibition](Exhibition.md)                          | Content        | Museum exhibitions                            |
| 🌍 [ExhibitionTranslation](ExhibitionTranslation.md)    | Translation    | Multi-language exhibition content             |
| 🖼️ [Gallery](Gallery.md)                                | Content        | Image galleries                               |
| 🔗 [Galleryable](Galleryable.md)                        | Relationship   | Gallery content associations                  |
| 🤝 [GalleryPartner](GalleryPartner.md)                  | Relationship   | Gallery-partner associations                  |
| 🌍 [GalleryTranslation](GalleryTranslation.md)          | Translation    | Multi-language gallery content                |
| 📤 [ImageUpload](ImageUpload.md)                        | Media          | Uploaded image metadata                       |
| 🏺 [Item](Item.md)                                      | Content        | Core museum items                             |
| 🌍 [ItemTranslation](ItemTranslation.md)                | Translation    | Multi-language item content                   |
| 🌐 [Language](Language.md)                              | Configuration  | Supported languages (ISO codes)               |
| 📍 [Location](Location.md)                              | Geographic     | Specific locations                            |
| 🌍 [LocationTranslation](LocationTranslation.md)        | Translation    | Multi-language location names                 |
| 🏢 [Partner](Partner.md)                                | Organization   | Institutional partners                        |
| 📸 [Picture](Picture.md)                                | Media          | Images with metadata and relationships        |
| 🌍 [PictureTranslation](PictureTranslation.md)          | Translation    | Multi-language picture descriptions           |
| 📊 [Project](Project.md)                                | Management     | Project management                            |
| 🗺️ [Province](Province.md)                              | Geographic     | Administrative provinces                      |
| 🌍 [ProvinceTranslation](ProvinceTranslation.md)        | Translation    | Multi-language province names                 |
| 🏷️ [Tag](Tag.md)                                        | Classification | Content tagging system                        |
| 🎯 [Theme](Theme.md)                                    | Content        | Exhibition themes and subthemes               |
| 🌍 [ThemeTranslation](ThemeTranslation.md)              | Translation    | Multi-language theme content                  |
| 👤 [User](User.md)                                      | System         | Application users                             |
| 🔨 [Workshop](Workshop.md)                              | Content        | Workshop information                          |

---

{: .fs-3 .fw-300 }
💡 **Tip:** Use your browser's search function (Ctrl+F / Cmd+F) to quickly find specific models in this list.

For an overview of model relationships and architecture, see the summary above.
