---
layout: default
title: API Models
nav_order: 2
---

# API Models

This document describes the main data models available in the Inventory Management API.

## Core Models

### Items

The primary inventory entities representing museum objects, artifacts, or collections.

### Details

Additional descriptive information linked to Items, supporting detailed cataloging.

### Pictures

Polymorphic image attachment system that allows attaching images to Items, Details, and Partners.

**Key Features:**

- UUID-based primary key
- Polymorphic relationships with Items, Details, and Partners via `pictureable_type` and `pictureable_id`
- Complete image metadata: filename, original_filename, mime_type, size, width, height
- File management with transactional operations
- Automatic file moves from AvailableImage pool on attachment
- Direct download and inline viewing capabilities

**API Endpoints:**

- `GET /api/picture` - List all pictures
- `GET /api/picture/{id}` - Get specific picture
- `POST /api/picture` - Create new picture (direct upload)
- `PUT /api/picture/{id}` - Update picture metadata
- `DELETE /api/picture/{id}` - Delete picture and associated file
- `GET /api/picture/{id}/download` - Download picture file
- `GET /api/picture/{id}/view` - View picture inline

**Attachment Endpoints:**

- `POST /api/picture/attach-to-item/{item}` - Attach AvailableImage to Item
- `POST /api/picture/attach-to-detail/{detail}` - Attach AvailableImage to Detail
- `POST /api/picture/attach-to-partner/{partner}` - Attach AvailableImage to Partner

**Storage Configuration:**

The Picture system uses a flexible storage configuration:

```bash
# Permanently attached pictures
PICTURES_DISK=local_pictures
PICTURES_PATH=pictures
```

**Attachment Workflow:**

1. Images are uploaded and processed into AvailableImage records
2. Use attachment endpoints to move images from available pool to permanent storage
3. File is moved atomically and AvailableImage record is deleted
4. Picture record is created with polymorphic relationship to target model

### ImageUpload

Upload tracking and processing status system for image uploads.

**Key Features:**

- UUID-based primary key
- Monitors image processing workflows from upload to AvailableImage creation
- Tracks upload metadata: filename, extension, mime_type, size, and storage path
- Automatic cleanup after successful processing (record deleted when AvailableImage created)
- Status polling capability to track processing progress
- Event-driven processing via ImageUploadEvent/ImageUploadListener

**API Endpoints:**

- `GET /api/image-upload` - List all image uploads
- `GET /api/image-upload/{id}` - Get specific image upload
- `POST /api/image-upload` - Upload new image for processing
- `DELETE /api/image-upload/{id}` - Delete image upload and associated file
- `GET /api/image-upload/{id}/status` - Check processing status and get AvailableImage when ready

**Status Polling:**

The status endpoint provides real-time processing status:

- `processing`: Upload exists, processing in progress
- `processed`: Processing complete, returns AvailableImage details
- `not_found`: No upload or processed image found

**Processing Workflow:**

1. Image uploaded via `POST /api/image-upload`
2. ImageUploadEvent triggered for background processing
3. Image validation, resizing, and optimization performed
4. AvailableImage created with same ID as ImageUpload
5. Original ImageUpload record deleted after successful processing
6. Clients can poll `/api/image-upload/{id}/status` to track progress

**Storage Configuration:**

```bash
# Temporary upload storage
UPLOAD_IMAGES_DISK=local_upload_images
UPLOAD_IMAGES_PATH=uploads/images
```

### AvailableImage

Processed image pool for attachment to models as Pictures.

**Key Features:**

- UUID-based primary key
- Temporary storage for processed images awaiting attachment
- Automatic cleanup when attached to models as Pictures
- Download and preview capabilities before attachment
- Support for multiple image formats and sizes

**API Endpoints:**

- `GET /api/available-image` - List all available images
- `GET /api/available-image/{id}` - Get specific available image
- `POST /api/available-image` - Create new available image (via upload processing)
- `PUT /api/available-image/{id}` - Update available image metadata
- `DELETE /api/available-image/{id}` - Delete available image and file
- `GET /api/available-image/{id}/download` - Download available image file
- `GET /api/available-image/{id}/view` - View available image inline

**Storage Configuration:**

```bash
# Processed images awaiting attachment
AVAILABLE_IMAGES_DISK=local_available_images
AVAILABLE_IMAGES_PATH=available/images
```

### Gallery

Flexible polymorphic collections that can contain both Items and Details, providing advanced content organization capabilities.

**Key Features:**

- UUID-based primary key with auto-generation via HasUuids trait
- Polymorphic many-to-many relationships with Items and Details via Galleryable model
- Multi-language support through GalleryTranslation model
- Partner relationships with contribution levels (Partner, Associated Partner, Minor Contributor)
- Default Language and Context associations for display purposes
- Internal naming system with unique constraints
- Backward compatibility support for legacy system migration

**API Endpoints:**

- `GET /api/gallery` - List all galleries with relationships and translations
- `GET /api/gallery/{id}` - Get specific gallery with full relationship data
- `POST /api/gallery` - Create new gallery with translations and partner relationships
- `PUT /api/gallery/{id}` - Update existing gallery
- `DELETE /api/gallery/{id}` - Delete gallery and associated relationships

**Relationship Structure:**

- `galleries` - Main gallery table with UUID primary key
- `gallery_translations` - Multi-language translations linked to galleries
- `gallery_partner` - Partner relationships with contribution levels
- `galleryables` - Polymorphic pivot table connecting galleries to Items and Details

**Translation Support:**

Gallery translations provide full internationalization capabilities:

- Required relationships to Gallery, Language, and Context
- Core fields: `name`, `description`
- Extended fields: `summary`, `link`, `main_character`
- Unique constraints prevent duplicate translations for same (gallery_id, language_id, context_id)
- Context-aware translations enabling different versions per language
- Fallback logic for retrieving translations with default context support

**Polymorphic Relationships:**

Galleries can contain mixed collections of Items and Details:

- Flexible content organization beyond traditional item-only collections
- Support for complex exhibition layouts mixing artifacts and detailed information
- Efficient database design using polymorphic many-to-many pattern
- Type-safe model relationships with proper eager loading support

### Exhibition

Hierarchical theme-based picture galleries providing sophisticated organization of visual content with comprehensive translation support.

**Key Features:**

- UUID-based primary key with auto-generation via HasUuids trait
- Hierarchical theme organization with two-level depth (main themes → subthemes)
- Polymorphic picture attachments supporting both Item and Detail pictures
- Multi-language support through ExhibitionTranslation model
- Theme-level translations via ThemeTranslation model
- Partner relationships with contribution levels (Partner, Associated Partner, Minor Contributor)
- Default Language and Context associations for display purposes
- Internal naming system with unique constraints
- Backward compatibility support for legacy system migration

**API Endpoints:**

- `GET /api/exhibition-translation` - List all exhibition translations with filtering
- `GET /api/exhibition-translation/{id}` - Get specific exhibition translation
- `POST /api/exhibition-translation` - Create new exhibition translation
- `PUT /api/exhibition-translation/{id}` - Update existing exhibition translation
- `DELETE /api/exhibition-translation/{id}` - Delete exhibition translation
- `GET /api/theme-translation` - List all theme translations with filtering
- `GET /api/theme-translation/{id}` - Get specific theme translation
- `POST /api/theme-translation` - Create new theme translation
- `PUT /api/theme-translation/{id}` - Update existing theme translation
- `DELETE /api/theme-translation/{id}` - Delete theme translation

**Relationship Structure:**

- `exhibitions` - Main exhibition table with UUID primary key
- `exhibition_translations` - Multi-language translations linked to exhibitions
- `themes` - Hierarchical theme organization with parent-child relationships
- `theme_translations` - Multi-language translations for themes
- `picture` relationships - Polymorphic connections to Item and Detail pictures

**Translation Support:**

Exhibition translations provide full internationalization capabilities:

- Required relationships to Exhibition, Language, and Context
- Core fields: `title`, `description`
- Optional fields: `url` for homepage links
- Unique constraints prevent duplicate translations for same (exhibition_id, language_id, context_id)
- Context-aware translations enabling different versions per language

Theme translations support hierarchical content organization:

- Required relationships to Theme, Language, and Context
- Core fields: `title`, `description`, `introduction`
- Unique constraints prevent duplicate translations for same (theme_id, language_id, context_id)
- Support for both main themes and subthemes with consistent translation structure

**Hierarchical Theme System:**

Exhibitions organize pictures into a two-level hierarchical structure:

- Main themes provide top-level categorization
- Subthemes enable detailed organization within main themes
- Polymorphic picture attachments to both Item and Detail pictures
- Flexible content organization supporting complex exhibition layouts
- Efficient database design with proper foreign key constraints and indexing

### Item Translations

Context-aware, multi-language translations for Items providing comprehensive internationalization support.

**Key Features:**

- UUID-based primary key
- Required relationships to Item, Language, and Context
- Multi-context support enabling different translations per language for different contexts
- Comprehensive field set: name, alternate_name, description, type, holder, owner, initial_owner, dates, location, dimensions, place_of_production, method_for_datation, method_for_provenance, obtention, bibliography
- Author relationships (author, text_copy_editor, translator, translation_copy_editor)
- Unique constraints on (item_id, language_id, context_id) combinations
- JSON extra field for extensible metadata storage
- Translation scopes for filtering and helper methods for retrieving context-specific translations

**API Endpoints:**

- `GET /api/item-translation` - List all item translations
- `GET /api/item-translation/{id}` - Get specific item translation
- `POST /api/item-translation` - Create new item translation
- `PUT /api/item-translation/{id}` - Update item translation
- `DELETE /api/item-translation/{id}` - Delete item translation

**Query Parameters:**

- `item_id` - Filter translations by specific item
- `language_id` - Filter translations by specific language
- `context_id` - Filter translations by specific context
- `default_context` - Filter translations for default context only

### Detail Translations

Context-aware, multi-language translations for Details providing focused internationalization support.

**Key Features:**

- UUID-based primary key
- Required relationships to Detail, Language, and Context
- Multi-context support enabling different translations per language for different contexts
- Focused field set: name, alternate_name, description
- Author relationships (author, text_copy_editor, translator, translation_copy_editor)
- Unique constraints on (detail_id, language_id, context_id) combinations
- JSON extra field for extensible metadata storage
- Translation scopes for filtering and helper methods for retrieving context-specific translations

**API Endpoints:**

- `GET /api/detail-translation` - List all detail translations
- `GET /api/detail-translation/{id}` - Get specific detail translation
- `POST /api/detail-translation` - Create new detail translation
- `PUT /api/detail-translation/{id}` - Update detail translation
- `DELETE /api/detail-translation/{id}` - Delete detail translation

**Query Parameters:**

- `detail_id` - Filter translations by specific detail
- `language_id` - Filter translations by specific language
- `context_id` - Filter translations by specific context
- `default_context` - Filter translations for default context only

### Partners

Organizations, institutions, or individuals associated with Items (donors, lenders, etc.).

### Projects

Research projects, exhibitions, or initiatives that group related Items.

### Contexts

Contextual information categories that enable multi-version translations for organizing and categorizing Items and Details.

**Key Features:**

- UUID-based primary key
- Internal name for system identification
- Default context designation for primary translations
- Enables multiple translation versions per language for different use cases
- Critical component of the translation system's unique constraint (model_id, language_id, context_id)

**API Endpoints:**

- `GET /api/contexts` - List all contexts
- `GET /api/contexts/{id}` - Get specific context
- `POST /api/contexts` - Create new context
- `PUT /api/contexts/{id}` - Update context
- `DELETE /api/contexts/{id}` - Delete context

**Translation Integration:**

- Required for all ItemTranslation and DetailTranslation records
- Supports fallback logic from specific context to default context
- Enables filtering translations by context in API endpoints

### Tags

Flexible labeling system for organizing and filtering Items.

## Geographic Models

The API provides comprehensive geographic data management with full internationalization support:

### Province

Represents administrative divisions within countries (states, provinces, regions).

**Key Features:**

- UUID-based primary key
- Required relationship to Country
- Multi-language support via ProvinceTranslation model
- Internal name for system identification
- Support for localized names and descriptions

**API Endpoints:**

- `GET /api/provinces` - List all provinces
- `GET /api/provinces/{id}` - Get specific province
- `POST /api/provinces` - Create new province
- `PUT /api/provinces/{id}` - Update province
- `DELETE /api/provinces/{id}` - Delete province

**Translation Endpoints:**

- `GET /api/province-translation` - List all province translations
- `GET /api/province-translation/{id}` - Get specific province translation
- `POST /api/province-translation` - Create new province translation
- `PUT /api/province-translation/{id}` - Update province translation
- `DELETE /api/province-translation/{id}` - Delete province translation

### Location

Represents geographic locations within provinces (cities, towns, districts).

**Key Features:**

- UUID-based primary key
- Required relationships to both Country and Province
- Multi-language support via LocationTranslation model
- Hierarchical geographic organization
- Support for localized names and descriptions

**API Endpoints:**

- `GET /api/locations` - List all locations
- `GET /api/locations/{id}` - Get specific location
- `POST /api/locations` - Create new location
- `PUT /api/locations/{id}` - Update location
- `DELETE /api/locations/{id}` - Delete location

**Translation Endpoints:**

- `GET /api/location-translation` - List all location translations
- `GET /api/location-translation/{id}` - Get specific location translation
- `POST /api/location-translation` - Create new location translation
- `PUT /api/location-translation/{id}` - Update location translation
- `DELETE /api/location-translation/{id}` - Delete location translation

### Address

Represents detailed address information within locations.

**Key Features:**

- UUID-based primary key
- Required relationships to Country, Province, and Location
- Multi-language support via AddressTranslation model
- Complete geographic hierarchy
- Support for localized names and descriptions

**API Endpoints:**

- `GET /api/addresses` - List all addresses
- `GET /api/addresses/{id}` - Get specific address
- `POST /api/addresses` - Create new address
- `PUT /api/addresses/{id}` - Update address
- `DELETE /api/addresses/{id}` - Delete address

**Translation Endpoints:**

- `GET /api/address-translation` - List all address translations
- `GET /api/address-translation/{id}` - Get specific address translation
- `POST /api/address-translation` - Create new address translation
- `PUT /api/address-translation/{id}` - Update address translation
- `DELETE /api/address-translation/{id}` - Delete address translation

## Contact Models

### Contact

Represents individuals or organizations with contact information.

**Key Features:**

- UUID-based primary key
- Multi-language support via ContactTranslation model
- Flexible contact information storage
- Support for localized names and descriptions

**API Endpoints:**

- `GET /api/contacts` - List all contacts
- `GET /api/contacts/{id}` - Get specific contact
- `POST /api/contacts` - Create new contact
- `PUT /api/contacts/{id}` - Update contact
- `DELETE /api/contacts/{id}` - Delete contact

**Translation Endpoints:**

- `GET /api/contact-translation` - List all contact translations
- `GET /api/contact-translation/{id}` - Get specific contact translation
- `POST /api/contact-translation` - Create new contact translation
- `PUT /api/contact-translation/{id}` - Update contact translation
- `DELETE /api/contact-translation/{id}` - Delete contact translation

## Reference Models

### Country

ISO 3166-1 alpha-3 country codes for geographic references.

**Key Features:**

- Three-letter primary key (ISO standard)
- Required for all geographic models
- Standardized country identification

### Language

ISO 639-1 language codes for internationalization.

**Key Features:**

- Three-letter primary key (ISO standard)
- Used in all translation models
- Supports default language configuration

## Internationalization

All major content models support internationalization through dedicated translation models:

### Core Content Translation Models

- **ItemTranslation** - Complete localized content for inventory items with extensive field set including type, holder, owner, initial_owner, dates, location, dimensions, place_of_production, method_for_datation, method_for_provenance, obtention, bibliography
- **DetailTranslation** - Localized descriptive information linked to items with focused field set (name, alternate_name, description)
- **GalleryTranslation** - Localized gallery content with name, description, summary, link, and main_character fields
- **ExhibitionTranslation** - Localized exhibition content with title, description, and optional URL fields
- **ThemeTranslation** - Localized theme content with title, description, and introduction fields for hierarchical theme organization

### Geographic Translation Models

- **ContactTranslation** - Localized contact information
- **ProvinceTranslation** - Localized province names and descriptions
- **LocationTranslation** - Localized location names and descriptions
- **AddressTranslation** - Localized address names and descriptions

### Translation Features

Each translation model provides:

- **Core Fields**: `name`, `description` (all models)
- **Extended Fields**: Specialized fields per model type
- **Context Support**: Multi-context translations enabling different versions per language
- **Author Relationships**: Support for author, text_copy_editor, translator, and translation_copy_editor
- **Unique Constraints**: Prevents duplicate translations for same (model_id, language_id, context_id) combination
- **Extensible Metadata**: JSON extra field for additional arbitrary data
- **Scopes and Helpers**: Built-in filtering by language, context, and default context
- **Fallback Logic**: Helper methods for retrieving translations with context fallback
- Direct foreign key relationships to parent model, language, and context
- Follows Laravel's recommended translation pattern

## Data Relationships

```
Country (1) ──→ (N) Province
Province (1) ──→ (N) Location
Location (1) ──→ (N) Address

Country (1) ──→ (N) Location (direct relationship)
Country (1) ──→ (N) Address (direct relationship)

Contact (1) ──→ (N) ContactTranslation
Province (1) ──→ (N) ProvinceTranslation
Location (1) ──→ (N) LocationTranslation
Address (1) ──→ (N) AddressTranslation
Item (1) ──→ (N) ItemTranslation
Detail (1) ──→ (N) DetailTranslation
Gallery (1) ──→ (N) GalleryTranslation
Exhibition (1) ──→ (N) ExhibitionTranslation
Theme (1) ──→ (N) ThemeTranslation

Exhibition (1) ──→ (N) Theme
Theme (1) ──→ (N) Theme (parent-child relationship)

Language (1) ──→ (N) ContactTranslation
Language (1) ──→ (N) ProvinceTranslation
Language (1) ──→ (N) LocationTranslation
Language (1) ──→ (N) AddressTranslation
Language (1) ──→ (N) ItemTranslation
Language (1) ──→ (N) DetailTranslation
Language (1) ──→ (N) GalleryTranslation

Context (1) ──→ (N) ItemTranslation
Context (1) ──→ (N) DetailTranslation
Context (1) ──→ (N) GalleryTranslation

# Polymorphic Picture Relationships
Item (1) ──→ (N) Picture (polymorphic: pictureable)
Detail (1) ──→ (N) Picture (polymorphic: pictureable)
Partner (1) ──→ (N) Picture (polymorphic: pictureable)

# Gallery Polymorphic Relationships
Gallery (N) ←──→ (N) Item (polymorphic: galleryable via galleryables)
Gallery (N) ←──→ (N) Detail (polymorphic: galleryable via galleryables)

# Gallery Partner Relationships
Gallery (N) ←──→ (N) Partner (via gallery_partner with contribution_level)
```

## Common Patterns

### UUID Primary Keys

Most models use UUID primary keys for:

- Distributed system compatibility
- Enhanced security
- Scalability

### Internationalization

Multi-language support follows Laravel's recommended translation pattern with context-aware enhancements:

- Dedicated translation models instead of pivot tables
- One-to-many relationships between main models and translations
- Each translation record includes foreign keys to parent model, language, and context
- **Context Support**: Multi-context translations enabling different versions per language for different use cases
- **Unique Constraints**: Prevents duplicate translations for same (model_id, language_id, context_id) combination
- **Fallback Logic**: Helper methods for retrieving translations with graceful fallback to default context
- **Default Context**: Special context designation for primary translations
- **Filtering Capabilities**: API endpoints support filtering by item/detail, language, and context
- ISO standard language codes
- Consistent API response structure with embedded translations
- Full CRUD operations available for both main models and translations

### Timestamps

All models include:

- `created_at` - Record creation timestamp
- `updated_at` - Last modification timestamp

### Backward Compatibility

All tables include:

- `backward_compatibility` - Nullable string for migration support
- `internal_name` - System identification field

For detailed API specifications, see the [Interactive API Documentation]({{ '/swagger-ui.html' | relative_url }}).
