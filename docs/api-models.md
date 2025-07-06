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
- Multi-language support via ProvinceLanguage pivot
- Internal name for system identification
- Support for localized names and descriptions

**API Endpoints:**

- `GET /api/provinces` - List all provinces
- `GET /api/provinces/{id}` - Get specific province
- `POST /api/provinces` - Create new province
- `PUT /api/provinces/{id}` - Update province
- `DELETE /api/provinces/{id}` - Delete province

### Location

Represents geographic locations within provinces (cities, towns, districts).

**Key Features:**

- UUID-based primary key
- Required relationships to both Country and Province
- Multi-language support via LocationLanguage pivot
- Hierarchical geographic organization
- Support for localized names and descriptions

**API Endpoints:**

- `GET /api/locations` - List all locations
- `GET /api/locations/{id}` - Get specific location
- `POST /api/locations` - Create new location
- `PUT /api/locations/{id}` - Update location
- `DELETE /api/locations/{id}` - Delete location

### Address

Represents detailed address information within locations.

**Key Features:**

- UUID-based primary key
- Required relationships to Country, Province, and Location
- Multi-language support via AddressLanguage pivot
- Complete geographic hierarchy
- Support for localized names and descriptions

**API Endpoints:**

- `GET /api/addresses` - List all addresses
- `GET /api/addresses/{id}` - Get specific address
- `POST /api/addresses` - Create new address
- `PUT /api/addresses/{id}` - Update address
- `DELETE /api/addresses/{id}` - Delete address

## Contact Models

### Contact

Represents individuals or organizations with contact information.

**Key Features:**

- UUID-based primary key
- Multi-language support via ContactLanguage pivot
- Flexible contact information storage
- Support for localized names and descriptions

**API Endpoints:**

- `GET /api/contacts` - List all contacts
- `GET /api/contacts/{id}` - Get specific contact
- `POST /api/contacts` - Create new contact
- `PUT /api/contacts/{id}` - Update contact
- `DELETE /api/contacts/{id}` - Delete contact

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
- Used in all language pivot tables
- Supports default language configuration

## Internationalization

All major content models support internationalization through dedicated pivot tables:

- **ContactLanguage** - Localized contact information
- **ProvinceLanguage** - Localized province names and descriptions
- **LocationLanguage** - Localized location names and descriptions
- **AddressLanguage** - Localized address names and descriptions

Each language pivot provides:

- `name` - Localized display name
- `description` - Localized description text
- Language-specific content organization

## Data Relationships

```
Country (1) ──→ (N) Province
Province (1) ──→ (N) Location
Location (1) ──→ (N) Address

Country (1) ──→ (N) Location (direct relationship)
Country (1) ──→ (N) Address (direct relationship)

Contact ←──→ ContactLanguage
Province ←──→ ProvinceLanguage
Location ←──→ LocationLanguage
Address ←──→ AddressLanguage
```

## Common Patterns

### UUID Primary Keys

Most models use UUID primary keys for:

- Distributed system compatibility
- Enhanced security
- Scalability

### Internationalization

Multi-language support follows consistent patterns:

- Pivot tables for language-specific content
- ISO standard language codes
- Consistent API response structure

### Timestamps

All models include:

- `created_at` - Record creation timestamp
- `updated_at` - Last modification timestamp

### Backward Compatibility

All tables include:

- `backward_compatibility` - Nullable string for migration support
- `internal_name` - System identification field

For detailed API specifications, see the [Interactive API Documentation]({{ '/swagger-ui.html' | relative_url }}).
