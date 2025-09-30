---
layout: default
title: Database Models
nav_order: 6
has_children: true
---

# 🗄️ Database Models

{: .hi| 📚 [Collection](Collection) | Content | Collections, exhibitions and galleries |hlight }

> This do| 🔨 [Workshop](Workshop) | People | Workshops and production studios |
> | 👤 [User](User) | System | Application user accounts |umentation provides a comprehensive overview of all database models in the application, their properties, relationships, and usage patterns after the recent model simplification initiative.

## 📊 Overview

- **📈 Total Models:** 28
- **🔧 Common Features:**
  - 🆔 All models use UUIDs (except Language, Country, User)
  - 🏷️ Most models have `internal_name` and `backward_compatibility` fields
  - 🔗 Relationships are defined using Eloquent ORM best practices
  - 🌍 Translations are handled via dedicated translation models
  - 🔄 Many models support hierarchical and many-to-many relationships

## 🎯 Key Model Categories

### 🌍 Geographic Models

- **📍 Address, Location, Province:** Represent geographical entities, each with translation models

### 👥 People & Organizations

- **🎨 Artist, Author, Partner:** Represent people/entities, with relationships to items and collections

### 📦 Core Content Models (SIMPLIFIED)

- **🏛️ Item:** Unified model for objects and monuments with hierarchical support
- **📚 Collection:** Unified model for collections, exhibitions, and galleries (type-based)
- **🎨 Theme:** Thematic groupings with collection relationships

### 🖼️ Media Models (RESTRUCTURED)

- **📸 ItemImage:** Direct item-to-image relationships with display ordering
- **🖼️ ImageUpload, AvailableImage:** Core image storage and metadata management

### 🏷️ Classification & Metadata

- **🔖 Tag:** Item tagging system
- **🏗️ Context:** Organizational contexts (museums, institutions)

### 👤 System Models

- **🔐 User:** Standard Laravel user model
- **🌐 Language, Country:** Use ISO codes as primary keys

## 🔗 Relationship Types

| Type                 | Icon | Description                | Example            |
| -------------------- | ---- | -------------------------- | ------------------ |
| **BelongsTo**        | ⬆️   | Foreign key relationships  | Item → Partner     |
| **HasMany**          | ⬇️   | One-to-many relationships  | Item → ItemImage   |
| **BelongsToMany**    | ↔️   | Many-to-many relationships | Collection ↔ Item |
| **Self-Referential** | 🔄   | Hierarchical relationships | Item → Parent Item |

## 🌍 Translation System

- 🗣️ Most core models have corresponding translation models (e.g., ItemTranslation, CollectionTranslation)
- 📝 Translation models include language-specific display names and descriptions
- 🎯 Supports multi-language content delivery with context awareness

## ⚙️ Key Architectural Changes

### 🚀 Model Simplification Benefits

- **📉 Reduced Complexity:** From 37+ models to 28 focused models
- **🎯 Unified Structure:** Single Item model with hierarchical support
- **📊 Type-Based Design:** Collection model handles collections/exhibitions/galleries via type field
- **🖼️ Direct Relationships:** ItemImage provides direct item-to-image relationships with ordering
- **⚡ Better Performance:** Eliminated polymorphic relationships for clearer, faster queries

### 📏 Scopes & Filtering

- 🔍 Enhanced scoping system with type-based filtering (e.g., `objects()`, `exhibitions()`)
- ⚡ Optimized query performance through strategic relationship loading
- 🏗️ Hierarchical scopes for parent/child item relationships

### 🛠️ Traits & Patterns

- 🏭 Consistent use of `HasFactory` and `HasUuids` traits
- 📋 Standardized validation patterns across all models
- 🔧 Enhanced business logic methods for common operations
- 🔒 Built-in security and validation features

---

# 📚 Complete Models Index

{: .fs-6 .fw-300 }
Click any model name below to view its detailed documentation with properties, relationships, and usage examples.

## 🔤 Current Model List (28 Models)

| Model                                             | Category       | Description                                                  |
| ------------------------------------------------- | -------------- | ------------------------------------------------------------ |
| 📍 [Address](Address)                             | Geographic     | Physical addresses with country relationships                |
| 🌍 [AddressTranslation](AddressTranslation)       | Translation    | Multi-language address translations                          |
| 🎨 [Artist](Artist)                               | People         | Artists who create items in collections                      |
| ✍️ [Author](Author)                               | People         | Authors of written content                                   |
| 🖼️ [AvailableImage](AvailableImage)               | Media          | Available images for item attachment                         |
| � [Collection](Collection)                        | Content        | **ENHANCED:** Collections, exhibitions & galleries (unified) |
| 🌍 [CollectionTranslation](CollectionTranslation) | Translation    | Multi-language collection content                            |
| 📞 [Contact](Contact)                             | Communication  | Contact information storage                                  |
| 🌍 [ContactTranslation](ContactTranslation)       | Translation    | Multi-language contact labels                                |
| ⚙️ [Context](Context)                             | Configuration  | Application context settings                                 |
| 🗺️ [Country](Country)                             | Geographic     | Countries using ISO 3166-1 codes                             |
| � [ImageUpload](ImageUpload)                      | Media          | Uploaded image metadata and processing                       |
| 🏛️ [Item](Item)                                   | Content        | Objects and monuments with hierarchical support              |
| 📸 [ItemImage](ItemImage)                         | Media          | Item-to-image relationships with ordering                    |
| 🌍 [ItemTranslation](ItemTranslation)             | Translation    | Multi-language item content                                  |
| 🌐 [Language](Language)                           | Configuration  | Supported languages (ISO 639-3 codes)                        |
| � [Location](Location)                            | Geographic     | Specific geographic locations                                |
| 🌍 [LocationTranslation](LocationTranslation)     | Translation    | Multi-language location names                                |
| � [Partner](Partner)                              | Organization   | Institutional partners and owners                            |
| 📊 [Project](Project)                             | Management     | Project organization and management                          |
| �️ [Province](Province)                           | Geographic     | Administrative provinces and regions                         |
| 🌍 [ProvinceTranslation](ProvinceTranslation)     | Translation    | Multi-language province names                                |
| �️ [Tag](Tag)                                     | Classification | Content tagging and categorization                           |
| 🎯 [Theme](Theme)                                 | Content        | Thematic groupings with collection relationships             |
| 🌍 [ThemeTranslation](ThemeTranslation)           | Translation    | Multi-language theme content                                 |
| � [User](User)                                    | System         | Application user accounts                                    |
| � [Workshop](Workshop)                            | People         | Workshops and production studios                             |

## 🚀 Recent Model Changes

### ✅ **Models Added/Enhanced**

- **📸 ItemImage:** New model for direct item-to-image relationships with display ordering
- **🏛️ Item:** Enhanced with `type` field (object/monument) and hierarchical `parent_id` support
- **📚 Collection:** Enhanced with `type` field (collection/exhibition/gallery) replacing 3 separate models

### ❌ **Models Removed**

- **~~📋 Detail~~:** Functionality integrated into Item model
- **~~📸 Picture~~:** Replaced by ItemImage model with better relationship design
- **~~�️ Exhibition~~:** Merged into Collection model (type='exhibition')
- **~~🖼️ Gallery~~:** Merged into Collection model (type='gallery')
- **~~🔗 Galleryable~~:** No longer needed with simplified relationships
- **~~🤝 GalleryPartner~~:** No longer needed with unified Collection model
- **~~Translation models~~:** Removed for Gallery, Exhibition, Detail, Picture

### 🎯 **Benefits of Simplification**

- **📉 37% Reduction:** From 37+ models to 28 focused models
- **⚡ Better Performance:** Eliminated polymorphic relationships
- **🎯 Clearer Logic:** Type-based design instead of separate models
- **🔧 Enhanced APIs:** Simplified endpoints with consistent patterns
- **📚 Better Documentation:** Focused, comprehensive model docs
  | 👤 [User](User) | System | Application users |
  | 🔨 [Workshop](Workshop) | Content | Workshop information |

---

{: .fs-3 .fw-300 }
💡 **Tip:** Use your browser's search function (Ctrl+F / Cmd+F) to quickly find specific models in this list.

For an overview of model relationships and architecture, see the summary above.
