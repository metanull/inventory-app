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

### Partners

Organizations, institutions, or individuals associated with Items (donors, lenders, etc.).

### Projects

Research projects, exhibitions, or initiatives that group related Items.

### Contexts

Contextual information categories for organizing and categorizing Items and Details.

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

- **ContactTranslation** - Localized contact information
- **ProvinceTranslation** - Localized province names and descriptions
- **LocationTranslation** - Localized location names and descriptions
- **AddressTranslation** - Localized address names and descriptions

Each translation model provides:

- `name` - Localized display name (where applicable)
- `description` - Localized description text
- `label` - Localized labels for contacts
- `address` - Localized address text
- Direct foreign key relationships to parent model and language
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

Language (1) ──→ (N) ContactTranslation
Language (1) ──→ (N) ProvinceTranslation
Language (1) ──→ (N) LocationTranslation
Language (1) ──→ (N) AddressTranslation
```

## Common Patterns

### UUID Primary Keys

Most models use UUID primary keys for:

- Distributed system compatibility
- Enhanced security
- Scalability

### Internationalization

Multi-language support follows Laravel's recommended translation pattern:

- Dedicated translation models instead of pivot tables
- One-to-many relationships between main models and translations
- Each translation record includes foreign keys to both parent model and language
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
