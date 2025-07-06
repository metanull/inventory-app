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

Language (1) ──→ (N) ContactTranslation
Language (1) ──→ (N) ProvinceTranslation
Language (1) ──→ (N) LocationTranslation
Language (1) ──→ (N) AddressTranslation
Language (1) ──→ (N) ItemTranslation
Language (1) ──→ (N) DetailTranslation

Context (1) ──→ (N) ItemTranslation
Context (1) ──→ (N) DetailTranslation
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
