---
layout: default
title: Generated Model Documentation
nav_order: 7
---

# 🤖 Generated Model Documentation

{: .highlight }

> This documentation is automatically generated from the Laravel models and database schema. Last updated: 2025-10-24 UTC

## 📊 Overview

- **📈 Total Models:** 33
- **🗄️ Database Connection:** sqlite
- **🔧 Laravel Version:** 12.31.1

## 📚 Table of Contents

### Models

- [Address](#address)
- [AddressTranslation](#addresstranslation)
- [Artist](#artist)
- [Author](#author)
- [AvailableImage](#availableimage)
- [Collection](#collection)
- [CollectionImage](#collectionimage)
- [CollectionPartner](#collectionpartner)
- [CollectionTranslation](#collectiontranslation)
- [Contact](#contact)
- [ContactTranslation](#contacttranslation)
- [Context](#context)
- [Country](#country)
- [Glossary](#glossary)
- [GlossarySpelling](#glossaryspelling)
- [GlossaryTranslation](#glossarytranslation)
- [ImageUpload](#imageupload)
- [Item](#item)
- [ItemImage](#itemimage)
- [ItemTranslation](#itemtranslation)
- [Language](#language)
- [Location](#location)
- [LocationTranslation](#locationtranslation)
- [Partner](#partner)
- [Project](#project)
- [Province](#province)
- [ProvinceTranslation](#provincetranslation)
- [Setting](#setting)
- [Tag](#tag)
- [Theme](#theme)
- [ThemeTranslation](#themetranslation)
- [User](#user)
- [Workshop](#workshop)

### Pivot Tables

- [artist_item](#artist_item)
- [collection_item](#collection_item)
- [glossary_synonyms](#glossary_synonyms)
- [item_tag](#item_tag)
- [item_translation_spelling](#item_translation_spelling)
- [item_workshop](#item_workshop)

## Address {#address}

**Namespace:** `App\Models\Address`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `addresses`                      |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `country_id`             | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['internal_name', 'country_id']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`country()`**: BelongsTo [Country](#country)

#### Has Many

- **`translations()`**: HasMany [AddressTranslation](#addresstranslation)

## AddressTranslation {#addresstranslation}

**Namespace:** `App\Models\AddressTranslation`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `address_translations`           |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `address_id`             | varchar  | Unknown  | Unknown |       |
| `language_id`            | varchar  | Unknown  | Unknown |       |
| `address`                | text     | Unknown  | Unknown |       |
| `description`            | text     | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['address_id', 'language_id', 'address', 'description', 'backward_compatibility']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`address()`**: BelongsTo [Address](#address)
- **`language()`**: BelongsTo [Language](#language)

## Artist {#artist}

**Namespace:** `App\Models\Artist`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `artists`                        |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `name`                   | varchar  | Unknown  | Unknown |       |
| `place_of_birth`         | varchar  | Unknown  | Unknown |       |
| `place_of_death`         | varchar  | Unknown  | Unknown |       |
| `date_of_birth`          | varchar  | Unknown  | Unknown |       |
| `date_of_death`          | varchar  | Unknown  | Unknown |       |
| `period_of_activity`     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['name', 'place_of_birth', 'place_of_death', 'date_of_birth', 'date_of_death', 'period_of_activity', 'internal_name', 'backward_compatibility']
```

### 🔄 Attribute Casting

| Attribute    | Cast Type  |
| ------------ | ---------- |
| `created_at` | `datetime` |
| `updated_at` | `datetime` |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To Many

- **`items()`**: BelongsToMany [Item](#item)

## Author {#author}

**Namespace:** `App\Models\Author`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `authors`                        |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `name`                   | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['name', 'internal_name', 'backward_compatibility']
```

### 🔄 Attribute Casting

| Attribute    | Cast Type  |
| ------------ | ---------- |
| `created_at` | `datetime` |
| `updated_at` | `datetime` |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

## AvailableImage {#availableimage}

**Namespace:** `App\Models\AvailableImage`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `available_images`               |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column       | Type     | Nullable | Default | Extra |
| ------------ | -------- | -------- | ------- | ----- |
| `id`         | uuid     | No       |         |       |
| `path`       | varchar  | Yes      | null    |       |
| `comment`    | varchar  | Yes      | null    |       |
| `created_at` | datetime | Yes      |         |       |
| `updated_at` | datetime | Yes      |         |       |

### ✏️ Fillable Fields

```php
['path', 'comment']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

_No relationships defined_

## Collection {#collection}

**Namespace:** `App\Models\Collection`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `collections`                    |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | uuid     | No       |         |       |
| `internal_name`          | varchar  | No       |         |       |
| `language_id`            | varchar  | No       |         |       |
| `context_id`             | uuid     | No       |         |       |
| `backward_compatibility` | varchar  | Yes      | null    |       |
| `type`                   | varchar  | No       |         |       |
| `created_at`             | datetime | Yes      |         |       |
| `updated_at`             | datetime | Yes      |         |       |

### ✏️ Fillable Fields

```php
['internal_name', 'type', 'language_id', 'context_id', 'backward_compatibility']
```

### 📋 Model Constants

```php
const TYPE_COLLECTION = 'collection';
const TYPE_EXHIBITION = 'exhibition';
const TYPE_GALLERY = 'gallery';
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`language()`**: BelongsTo [Language](#language)
- **`context()`**: BelongsTo [Context](#context)

#### Has Many

- **`translations()`**: HasMany [CollectionTranslation](#collectiontranslation)
- **`items()`**: HasMany [Item](#item)
- **`collectionImages()`**: HasMany [CollectionImage](#collectionimage)

#### Belongs To Many

- **`attachedItems()`**: BelongsToMany [Item](#item)
- **`partners()`**: BelongsToMany [Partner](#partner)
- **`directPartners()`**: BelongsToMany [Partner](#partner)
- **`associatedPartners()`**: BelongsToMany [Partner](#partner)
- **`minorContributors()`**: BelongsToMany [Partner](#partner)

### 🔍 Query Scopes

- **`collections()`**
- **`exhibitions()`**
- **`galleries()`**

## CollectionImage {#collectionimage}

**Namespace:** `App\Models\CollectionImage`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `collection_images`              |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column          | Type     | Nullable | Default | Extra |
| --------------- | -------- | -------- | ------- | ----- |
| `id`            | uuid     | No       |         |       |
| `collection_id` | uuid     | No       |         |       |
| `path`          | varchar  | No       |         |       |
| `original_name` | varchar  | No       |         |       |
| `mime_type`     | varchar  | No       |         |       |
| `size`          | bigint   | No       |         |       |
| `alt_text`      | varchar  | Yes      | null    |       |
| `display_order` | integer  | No       | 0       |       |
| `created_at`    | datetime | Yes      |         |       |
| `updated_at`    | datetime | Yes      |         |       |

### ✏️ Fillable Fields

```php
['collection_id', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'display_order']
```

### 🔄 Attribute Casting

| Attribute       | Cast Type |
| --------------- | --------- |
| `size`          | `integer` |
| `display_order` | `integer` |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`collection()`**: BelongsTo [Collection](#collection)

## CollectionPartner {#collectionpartner}

**Namespace:** `App\Models\CollectionPartner`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `collection_partner`             |
| **Primary Key**  | Composite: `collection_id`, `collection_type`, `partner_id` |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column            | Type     | Nullable | Default | Extra |
| ----------------- | -------- | -------- | ------- | ----- |
| `collection_id`   | uuid     | No       |         |       |
| `collection_type` | varchar  | No       |         |       |
| `partner_id`      | uuid     | No       |         |       |
| `level`           | varchar  | No       |         |       |
| `created_at`      | datetime | Yes      |         |       |
| `updated_at`      | datetime | Yes      |         |       |

### ✏️ Fillable Fields

```php
['collection_id', 'collection_type', 'partner_id', 'level']
```

### 🔄 Attribute Casting

| Attribute | Cast Type |
| --------- | --------- |
| `level`   | `string`  |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`collection()`**: BelongsTo [Collection](#collection)
- **`partner()`**: BelongsTo [Partner](#partner)

## CollectionTranslation {#collectiontranslation}

**Namespace:** `App\Models\CollectionTranslation`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `collection_translations`        |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `collection_id`          | varchar  | Unknown  | Unknown |       |
| `language_id`            | varchar  | Unknown  | Unknown |       |
| `context_id`             | varchar  | Unknown  | Unknown |       |
| `title`                  | varchar  | Unknown  | Unknown |       |
| `description`            | text     | Unknown  | Unknown |       |
| `url`                    | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `extra`                  | text     | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['collection_id', 'language_id', 'context_id', 'title', 'description', 'url', 'backward_compatibility', 'extra']
```

### 🔄 Attribute Casting

| Attribute | Cast Type |
| --------- | --------- |
| `extra`   | `object`  |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`collection()`**: BelongsTo [Collection](#collection)
- **`language()`**: BelongsTo [Language](#language)
- **`context()`**: BelongsTo [Context](#context)

### 🔍 Query Scopes

- **`defaultContext()`**
- **`forLanguage()`**
- **`forContext()`**

## Contact {#contact}

**Namespace:** `App\Models\Contact`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `contacts`                       |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `phone_number`           | varchar  | Unknown  | Unknown |       |
| `fax_number`             | varchar  | Unknown  | Unknown |       |
| `email`                  | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['internal_name', 'phone_number', 'fax_number', 'email']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Has Many

- **`translations()`**: HasMany [ContactTranslation](#contacttranslation)

## ContactTranslation {#contacttranslation}

**Namespace:** `App\Models\ContactTranslation`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `contact_translations`           |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `contact_id`             | varchar  | Unknown  | Unknown |       |
| `language_id`            | varchar  | Unknown  | Unknown |       |
| `label`                  | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['contact_id', 'language_id', 'label', 'backward_compatibility']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`contact()`**: BelongsTo [Contact](#contact)
- **`language()`**: BelongsTo [Language](#language)

## Context {#context}

**Namespace:** `App\Models\Context`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `contexts`                       |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |
| `is_default`             | tinyint  | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['internal_name', 'backward_compatibility', 'is_default']
```

### 🔄 Attribute Casting

| Attribute    | Cast Type |
| ------------ | --------- |
| `is_default` | `boolean` |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

### 🔍 Query Scopes

- **`default()`**

## Country {#country}

**Namespace:** `App\Models\Country`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `countries`                      |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['id', 'internal_name', 'backward_compatibility']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Has Many

- **`items()`**: HasMany [Item](#item)
- **`partners()`**: HasMany [Partner](#partner)

## Glossary {#glossary}

**Namespace:** `App\Models\Glossary`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `glossaries`                     |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | uuid     | No       |         |       |
| `internal_name`          | varchar  | No       |         |       |
| `backward_compatibility` | varchar  | Yes      | null    |       |
| `created_at`             | datetime | Yes      |         |       |
| `updated_at`             | datetime | Yes      |         |       |

### ✏️ Fillable Fields

```php
['internal_name', 'backward_compatibility']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Has Many

- **`translations()`**: HasMany [GlossaryTranslation](#glossarytranslation)
- **`spellings()`**: HasMany [GlossarySpelling](#glossaryspelling)

#### Belongs To Many

- **`synonyms()`**: BelongsToMany [Glossary](#glossary)
- **`reverseSynonyms()`**: BelongsToMany [Glossary](#glossary)

## GlossarySpelling {#glossaryspelling}

**Namespace:** `App\Models\GlossarySpelling`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `glossary_spellings`             |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column        | Type     | Nullable | Default | Extra |
| ------------- | -------- | -------- | ------- | ----- |
| `id`          | uuid     | No       |         |       |
| `glossary_id` | uuid     | No       |         |       |
| `language_id` | varchar  | No       |         |       |
| `spelling`    | varchar  | No       |         |       |
| `created_at`  | datetime | Yes      |         |       |
| `updated_at`  | datetime | Yes      |         |       |

### ✏️ Fillable Fields

```php
['glossary_id', 'language_id', 'spelling']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`glossary()`**: BelongsTo [Glossary](#glossary)
- **`language()`**: BelongsTo [Language](#language)

#### Belongs To Many

- **`itemTranslations()`**: BelongsToMany [ItemTranslation](#itemtranslation)

### 🔍 Query Scopes

- **`forLanguage()`**
- **`forSpelling()`**

## GlossaryTranslation {#glossarytranslation}

**Namespace:** `App\Models\GlossaryTranslation`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `glossary_translations`          |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column        | Type     | Nullable | Default | Extra |
| ------------- | -------- | -------- | ------- | ----- |
| `id`          | uuid     | No       |         |       |
| `glossary_id` | uuid     | No       |         |       |
| `language_id` | varchar  | No       |         |       |
| `definition`  | text     | No       |         |       |
| `created_at`  | datetime | Yes      |         |       |
| `updated_at`  | datetime | Yes      |         |       |

### ✏️ Fillable Fields

```php
['glossary_id', 'language_id', 'definition']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`glossary()`**: BelongsTo [Glossary](#glossary)
- **`language()`**: BelongsTo [Language](#language)

### 🔍 Query Scopes

- **`forLanguage()`**

## ImageUpload {#imageupload}

**Namespace:** `App\Models\ImageUpload`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `image_uploads`                  |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column       | Type     | Nullable | Default | Extra |
| ------------ | -------- | -------- | ------- | ----- |
| `id`         | varchar  | Unknown  | Unknown |       |
| `path`       | varchar  | Unknown  | Unknown |       |
| `name`       | varchar  | Unknown  | Unknown |       |
| `extension`  | varchar  | Unknown  | Unknown |       |
| `mime_type`  | varchar  | Unknown  | Unknown |       |
| `size`       | integer  | Unknown  | Unknown |       |
| `created_at` | datetime | Unknown  | Unknown |       |
| `updated_at` | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['path', 'name', 'extension', 'mime_type', 'size']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

## Item {#item}

**Namespace:** `App\Models\Item`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `items`                          |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `type`                   | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |
| `partner_id`             | varchar  | Unknown  | Unknown |       |
| `country_id`             | varchar  | Unknown  | Unknown |       |
| `project_id`             | varchar  | Unknown  | Unknown |       |
| `owner_reference`        | varchar  | Unknown  | Unknown |       |
| `mwnf_reference`         | varchar  | Unknown  | Unknown |       |
| `collection_id`          | varchar  | Unknown  | Unknown |       |
| `parent_id`              | varchar  | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['partner_id', 'parent_id', 'internal_name', 'type', 'backward_compatibility', 'country_id', 'project_id', 'collection_id', 'owner_reference', 'mwnf_reference']
```

### 📋 Model Constants

```php
const TYPE_OBJECT = 'object';
const TYPE_MONUMENT = 'monument';
const TYPE_DETAIL = 'detail';
const TYPE_PICTURE = 'picture';
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`partner()`**: BelongsTo [Partner](#partner)
- **`country()`**: BelongsTo [Country](#country)
- **`project()`**: BelongsTo [Project](#project)
- **`collection()`**: BelongsTo [Collection](#collection)
- **`parent()`**: BelongsTo [Item](#item)

#### Has Many

- **`children()`**: HasMany [Item](#item)
- **`itemImages()`**: HasMany [ItemImage](#itemimage)
- **`translations()`**: HasMany [ItemTranslation](#itemtranslation)

#### Belongs To Many

- **`tags()`**: BelongsToMany [Tag](#tag)
- **`artists()`**: BelongsToMany [Artist](#artist)
- **`workshops()`**: BelongsToMany [Workshop](#workshop)
- **`attachedToCollections()`**: BelongsToMany [Collection](#collection)

### 🔍 Query Scopes

- **`objects()`**
- **`monuments()`**
- **`details()`**
- **`pictures()`**
- **`parents()`**
- **`children()`**
- **`forTag()`**
- **`withAllTags()`**
- **`withAnyTags()`**

## ItemImage {#itemimage}

**Namespace:** `App\Models\ItemImage`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `item_images`                    |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column          | Type     | Nullable | Default | Extra |
| --------------- | -------- | -------- | ------- | ----- |
| `id`            | varchar  | Unknown  | Unknown |       |
| `item_id`       | varchar  | Unknown  | Unknown |       |
| `path`          | varchar  | Unknown  | Unknown |       |
| `original_name` | varchar  | Unknown  | Unknown |       |
| `mime_type`     | varchar  | Unknown  | Unknown |       |
| `size`          | integer  | Unknown  | Unknown |       |
| `alt_text`      | varchar  | Unknown  | Unknown |       |
| `display_order` | integer  | Unknown  | Unknown |       |
| `created_at`    | datetime | Unknown  | Unknown |       |
| `updated_at`    | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['item_id', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'display_order']
```

### 🔄 Attribute Casting

| Attribute       | Cast Type |
| --------------- | --------- |
| `size`          | `integer` |
| `display_order` | `integer` |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`item()`**: BelongsTo [Item](#item)

## ItemTranslation {#itemtranslation}

**Namespace:** `App\Models\ItemTranslation`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `item_translations`              |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                       | Type     | Nullable | Default | Extra |
| ---------------------------- | -------- | -------- | ------- | ----- |
| `id`                         | varchar  | Unknown  | Unknown |       |
| `item_id`                    | varchar  | Unknown  | Unknown |       |
| `language_id`                | varchar  | Unknown  | Unknown |       |
| `context_id`                 | varchar  | Unknown  | Unknown |       |
| `name`                       | varchar  | Unknown  | Unknown |       |
| `alternate_name`             | varchar  | Unknown  | Unknown |       |
| `description`                | text     | Unknown  | Unknown |       |
| `type`                       | varchar  | Unknown  | Unknown |       |
| `holder`                     | text     | Unknown  | Unknown |       |
| `owner`                      | text     | Unknown  | Unknown |       |
| `initial_owner`              | text     | Unknown  | Unknown |       |
| `dates`                      | text     | Unknown  | Unknown |       |
| `location`                   | text     | Unknown  | Unknown |       |
| `dimensions`                 | text     | Unknown  | Unknown |       |
| `place_of_production`        | text     | Unknown  | Unknown |       |
| `method_for_datation`        | text     | Unknown  | Unknown |       |
| `method_for_provenance`      | text     | Unknown  | Unknown |       |
| `obtention`                  | text     | Unknown  | Unknown |       |
| `bibliography`               | text     | Unknown  | Unknown |       |
| `author_id`                  | varchar  | Unknown  | Unknown |       |
| `text_copy_editor_id`        | varchar  | Unknown  | Unknown |       |
| `translator_id`              | varchar  | Unknown  | Unknown |       |
| `translation_copy_editor_id` | varchar  | Unknown  | Unknown |       |
| `backward_compatibility`     | varchar  | Unknown  | Unknown |       |
| `extra`                      | text     | Unknown  | Unknown |       |
| `created_at`                 | datetime | Unknown  | Unknown |       |
| `updated_at`                 | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['item_id', 'language_id', 'context_id', 'name', 'alternate_name', 'description', 'type', 'holder', 'owner', 'initial_owner', 'dates', 'location', 'dimensions', 'place_of_production', 'method_for_datation', 'method_for_provenance', 'obtention', 'bibliography', 'author_id', 'text_copy_editor_id', 'translator_id', 'translation_copy_editor_id', 'backward_compatibility', 'extra']
```

### 🔄 Attribute Casting

| Attribute | Cast Type |
| --------- | --------- |
| `extra`   | `object`  |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`item()`**: BelongsTo [Item](#item)
- **`language()`**: BelongsTo [Language](#language)
- **`context()`**: BelongsTo [Context](#context)
- **`author()`**: BelongsTo [Author](#author)
- **`textCopyEditor()`**: BelongsTo [Author](#author)
- **`translator()`**: BelongsTo [Author](#author)
- **`translationCopyEditor()`**: BelongsTo [Author](#author)

#### Belongs To Many

- **`spellings()`**: BelongsToMany [GlossarySpelling](#glossaryspelling)

### 🔍 Query Scopes

- **`defaultContext()`**
- **`forLanguage()`**
- **`forContext()`**

## Language {#language}

**Namespace:** `App\Models\Language`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `languages`                      |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |
| `is_default`             | tinyint  | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['id', 'internal_name', 'backward_compatibility', 'is_default']
```

### 🔄 Attribute Casting

| Attribute    | Cast Type |
| ------------ | --------- |
| `is_default` | `boolean` |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

### 🔍 Query Scopes

- **`english()`**
- **`default()`**

## Location {#location}

**Namespace:** `App\Models\Location`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `locations`                      |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `country_id`             | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['internal_name', 'country_id']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`country()`**: BelongsTo [Country](#country)

#### Has Many

- **`translations()`**: HasMany [LocationTranslation](#locationtranslation)

## LocationTranslation {#locationtranslation}

**Namespace:** `App\Models\LocationTranslation`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `location_translations`          |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `location_id`            | varchar  | Unknown  | Unknown |       |
| `language_id`            | varchar  | Unknown  | Unknown |       |
| `name`                   | varchar  | Unknown  | Unknown |       |
| `description`            | text     | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['location_id', 'language_id', 'name', 'description', 'backward_compatibility']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`location()`**: BelongsTo [Location](#location)
- **`language()`**: BelongsTo [Language](#language)

## Partner {#partner}

**Namespace:** `App\Models\Partner`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `partners`                       |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `type`                   | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |
| `country_id`             | varchar  | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['internal_name', 'type', 'backward_compatibility', 'country_id']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`country()`**: BelongsTo [Country](#country)

#### Has Many

- **`items()`**: HasMany [Item](#item)

## Project {#project}

**Namespace:** `App\Models\Project`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `projects`                       |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `launch_date`            | date     | Unknown  | Unknown |       |
| `is_launched`            | tinyint  | Unknown  | Unknown |       |
| `is_enabled`             | tinyint  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |
| `context_id`             | varchar  | Unknown  | Unknown |       |
| `language_id`            | varchar  | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['internal_name', 'backward_compatibility', 'launch_date', 'is_launched', 'is_enabled', 'context_id', 'language_id']
```

### 🔄 Attribute Casting

| Attribute     | Cast Type        |
| ------------- | ---------------- |
| `launch_date` | `datetime:Y-m-d` |
| `is_launched` | `boolean`        |
| `is_enabled`  | `boolean`        |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`context()`**: BelongsTo [Context](#context)
- **`language()`**: BelongsTo [Language](#language)

### 🔍 Query Scopes

- **`isEnabled()`**
- **`isLaunched()`**
- **`isLaunchDatePassed()`**
- **`visible()`**
- **`enabled()`**

## Province {#province}

**Namespace:** `App\Models\Province`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `provinces`                      |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `country_id`             | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['internal_name', 'country_id']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`country()`**: BelongsTo [Country](#country)

#### Has Many

- **`translations()`**: HasMany [ProvinceTranslation](#provincetranslation)

## ProvinceTranslation {#provincetranslation}

**Namespace:** `App\Models\ProvinceTranslation`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `province_translations`          |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `province_id`            | varchar  | Unknown  | Unknown |       |
| `language_id`            | varchar  | Unknown  | Unknown |       |
| `name`                   | varchar  | Unknown  | Unknown |       |
| `description`            | text     | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['province_id', 'language_id', 'name', 'description', 'backward_compatibility']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`province()`**: BelongsTo [Province](#province)
- **`language()`**: BelongsTo [Language](#language)

## Setting {#setting}

**Namespace:** `App\Models\Setting`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `settings`                       |
| **Primary Key**  | `id`                             |
| **Key Type**     | Auto-incrementing Integer        |
| **Incrementing** | Yes                              |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column        | Type     | Nullable | Default  | Extra |
| ------------- | -------- | -------- | -------- | ----- |
| `id`          | integer  | No       |          |       |
| `key`         | varchar  | No       |          |       |
| `value`       | text     | Yes      | null     |       |
| `type`        | varchar  | No       | 'string' |       |
| `description` | text     | Yes      | null     |       |
| `created_at`  | datetime | Yes      |          |       |
| `updated_at`  | datetime | Yes      |          |       |

### ✏️ Fillable Fields

```php
['key', 'value', 'type', 'description']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

_No relationships defined_

### 📝 Static Methods

- **`get(string $key, mixed $default = null): mixed`** - Get a setting value by key
- **`set(string $key, mixed $value, string $type = 'string', ?string $description = null): void`** - Set a setting value by key

## Tag {#tag}

**Namespace:** `App\Models\Tag`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `tags`                           |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `description`            | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['internal_name', 'backward_compatibility', 'description']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To Many

- **`items()`**: BelongsToMany [Item](#item)

### 🔍 Query Scopes

- **`forItem()`**

## Theme {#theme}

**Namespace:** `App\Models\Theme`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `themes`                         |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `parent_id`              | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |
| `collection_id`          | varchar  | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['collection_id', 'parent_id', 'internal_name', 'backward_compatibility']
```

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`collection()`**: BelongsTo [Collection](#collection)
- **`parent()`**: BelongsTo [Theme](#theme)

#### Has Many

- **`subthemes()`**: HasMany [Theme](#theme)
- **`translations()`**: HasMany [ThemeTranslation](#themetranslation)

## ThemeTranslation {#themetranslation}

**Namespace:** `App\Models\ThemeTranslation`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `theme_translations`             |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `theme_id`               | varchar  | Unknown  | Unknown |       |
| `language_id`            | varchar  | Unknown  | Unknown |       |
| `context_id`             | varchar  | Unknown  | Unknown |       |
| `title`                  | varchar  | Unknown  | Unknown |       |
| `description`            | text     | Unknown  | Unknown |       |
| `introduction`           | text     | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `extra`                  | text     | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['theme_id', 'language_id', 'context_id', 'title', 'description', 'introduction', 'backward_compatibility', 'extra']
```

### 🔄 Attribute Casting

| Attribute | Cast Type |
| --------- | --------- |
| `extra`   | `object`  |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To

- **`theme()`**: BelongsTo [Theme](#theme)
- **`language()`**: BelongsTo [Language](#language)
- **`context()`**: BelongsTo [Context](#context)

### 🔍 Query Scopes

- **`defaultContext()`**
- **`forLanguage()`**
- **`forContext()`**

## User {#user}

**Namespace:** `App\Models\User`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `users`                          |
| **Primary Key**  | `id`                             |
| **Key Type**     | Auto-incrementing Integer        |
| **Incrementing** | Yes                              |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                      | Type     | Nullable | Default | Extra |
| --------------------------- | -------- | -------- | ------- | ----- |
| `id`                        | integer  | No       |         |       |
| `name`                      | varchar  | No       |         |       |
| `email`                     | varchar  | No       |         |       |
| `email_verified_at`         | datetime | Yes      | null    |       |
| `password`                  | varchar  | No       |         |       |
| `remember_token`            | varchar  | Yes      | null    |       |
| `current_team_id`           | integer  | Yes      | null    |       |
| `profile_photo_path`        | varchar  | Yes      | null    |       |
| `two_factor_secret`         | text     | Yes      | null    |       |
| `two_factor_recovery_codes` | text     | Yes      | null    |       |
| `two_factor_confirmed_at`   | datetime | Yes      | null    |       |
| `created_at`                | datetime | Yes      |         |       |
| `updated_at`                | datetime | Yes      |         |       |

### ✏️ Fillable Fields

```php
['name', 'email', 'password']
```

### 🔄 Attribute Casting

| Attribute           | Cast Type  |
| ------------------- | ---------- |
| `email_verified_at` | `datetime` |
| `password`          | `hashed`   |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

_No relationships defined in model (uses traits for API tokens and notifications)_

### 📝 Special Methods

- **`validateAndConsumeRecoveryCode(string $code): bool`** - Validate and consume a 2FA recovery code
- **`recoveryCodes(): array`** - Get the user's 2FA recovery codes
- **`hasSensitivePermissions(): bool`** - Check if user has sensitive permissions requiring MFA

### 🔌 Traits

- `Authenticatable`
- `Authorizable`
- `CanResetPassword`
- `HasApiTokens` (Laravel Sanctum)
- `HasProfilePhoto` (Laravel Jetstream)
- `HasRoles` (Spatie Permission)
- `MustVerifyEmail`
- `Notifiable`
- `TwoFactorAuthenticatable` (Laravel Fortify)

## Workshop {#workshop}

**Namespace:** `App\Models\Workshop`

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `workshops`                      |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column                   | Type     | Nullable | Default | Extra |
| ------------------------ | -------- | -------- | ------- | ----- |
| `id`                     | varchar  | Unknown  | Unknown |       |
| `name`                   | varchar  | Unknown  | Unknown |       |
| `internal_name`          | varchar  | Unknown  | Unknown |       |
| `backward_compatibility` | varchar  | Unknown  | Unknown |       |
| `created_at`             | datetime | Unknown  | Unknown |       |
| `updated_at`             | datetime | Unknown  | Unknown |       |

### ✏️ Fillable Fields

```php
['name', 'internal_name', 'backward_compatibility']
```

### 🔄 Attribute Casting

| Attribute    | Cast Type  |
| ------------ | ---------- |
| `created_at` | `datetime` |
| `updated_at` | `datetime` |

### 📋 Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### 🔗 Relationships

#### Belongs To Many

- **`items()`**: BelongsToMany [Item](#item)

---

## 🔗 Pivot Tables

The following pivot tables manage many-to-many relationships in the system. These tables don't have dedicated Model classes but are used through Eloquent's `belongsToMany` relationships.

## artist_item {#artist_item}

**Purpose:** Links Artists to Items (many-to-many relationship)

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `artist_item`                    |
| **Primary Key**  | Composite: `artist_id`, `item_id` |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column       | Type     | Nullable | Default | Extra |
| ------------ | -------- | -------- | ------- | ----- |
| `artist_id`  | uuid     | No       |         |       |
| `item_id`    | uuid     | No       |         |       |
| `created_at` | datetime | Yes      |         |       |
| `updated_at` | datetime | Yes      |         |       |

### 🔗 Foreign Keys

- `artist_id` → `artists.id` (cascade on delete)
- `item_id` → `items.id` (cascade on delete)

### 📝 Related Models

- [Artist](#artist) → `items()` relationship
- [Item](#item) → `artists()` relationship

## collection_item {#collection_item}

**Purpose:** Links Collections to Items through a many-to-many relationship (for attached items)

### 🗄️ Database Table

| Property         | Value                                  |
| ---------------- | -------------------------------------- |
| **Table Name**   | `collection_item`                      |
| **Primary Key**  | Composite: `collection_id`, `item_id`  |
| **Timestamps**   | Yes (`created_at`, `updated_at`)       |

### 🏗️ Database Schema

| Column          | Type     | Nullable | Default | Extra |
| --------------- | -------- | -------- | ------- | ----- |
| `collection_id` | uuid     | No       |         |       |
| `item_id`       | uuid     | No       |         |       |
| `created_at`    | datetime | Yes      |         |       |
| `updated_at`    | datetime | Yes      |         |       |

### 🔗 Foreign Keys

- `collection_id` → `collections.id` (cascade on delete)
- `item_id` → `items.id` (cascade on delete)

### 📝 Related Models

- [Collection](#collection) → `attachedItems()` relationship
- [Item](#item) → `attachedToCollections()` relationship

## glossary_synonyms {#glossary_synonyms}

**Purpose:** Links Glossary entries to their synonyms (self-referential many-to-many relationship)

### 🗄️ Database Table

| Property         | Value                                  |
| ---------------- | -------------------------------------- |
| **Table Name**   | `glossary_synonyms`                    |
| **Primary Key**  | Composite: `glossary_id`, `synonym_id` |
| **Timestamps**   | Yes (`created_at`, `updated_at`)       |

### 🏗️ Database Schema

| Column        | Type     | Nullable | Default | Extra |
| ------------- | -------- | -------- | ------- | ----- |
| `glossary_id` | uuid     | No       |         |       |
| `synonym_id`  | uuid     | No       |         |       |
| `created_at`  | datetime | Yes      |         |       |
| `updated_at`  | datetime | Yes      |         |       |

### 🔗 Foreign Keys

- `glossary_id` → `glossaries.id` (cascade on delete)
- `synonym_id` → `glossaries.id` (cascade on delete)

### 📝 Related Models

- [Glossary](#glossary) → `synonyms()` and `reverseSynonyms()` relationships

## item_tag {#item_tag}

**Purpose:** Links Items to Tags (many-to-many relationship for categorization)

### 🗄️ Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `item_tag`                       |
| **Primary Key**  | Composite: `item_id`, `tag_id`   |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### 🏗️ Database Schema

| Column       | Type     | Nullable | Default | Extra |
| ------------ | -------- | -------- | ------- | ----- |
| `item_id`    | uuid     | No       |         |       |
| `tag_id`     | uuid     | No       |         |       |
| `created_at` | datetime | Yes      |         |       |
| `updated_at` | datetime | Yes      |         |       |

### 🔗 Foreign Keys

- `item_id` → `items.id` (cascade on delete)
- `tag_id` → `tags.id` (cascade on delete)

### 📝 Related Models

- [Item](#item) → `tags()` relationship
- [Tag](#tag) → `items()` relationship

## item_translation_spelling {#item_translation_spelling}

**Purpose:** Links ItemTranslations to GlossarySpellings (many-to-many relationship for glossary term usage)

### 🗄️ Database Table

| Property         | Value                                                |
| ---------------- | ---------------------------------------------------- |
| **Table Name**   | `item_translation_spelling`                          |
| **Primary Key**  | Composite: `item_translation_id`, `spelling_id`      |
| **Timestamps**   | Yes (`created_at`, `updated_at`)                     |

### 🏗️ Database Schema

| Column                 | Type     | Nullable | Default | Extra |
| ---------------------- | -------- | -------- | ------- | ----- |
| `item_translation_id`  | uuid     | No       |         |       |
| `spelling_id`          | uuid     | No       |         |       |
| `created_at`           | datetime | Yes      |         |       |
| `updated_at`           | datetime | Yes      |         |       |

### 🔗 Foreign Keys

- `item_translation_id` → `item_translations.id` (cascade on delete)
- `spelling_id` → `glossary_spellings.id` (cascade on delete)

### 📝 Related Models

- [ItemTranslation](#itemtranslation) → `spellings()` relationship
- [GlossarySpelling](#glossaryspelling) → `itemTranslations()` relationship

## item_workshop {#item_workshop}

**Purpose:** Links Items to Workshops (many-to-many relationship)

### 🗄️ Database Table

| Property         | Value                                |
| ---------------- | ------------------------------------ |
| **Table Name**   | `item_workshop`                      |
| **Primary Key**  | Composite: `item_id`, `workshop_id`  |
| **Timestamps**   | Yes (`created_at`, `updated_at`)     |

### 🏗️ Database Schema

| Column        | Type     | Nullable | Default | Extra |
| ------------- | -------- | -------- | ------- | ----- |
| `item_id`     | uuid     | No       |         |       |
| `workshop_id` | uuid     | No       |         |       |
| `created_at`  | datetime | Yes      |         |       |
| `updated_at`  | datetime | Yes      |         |       |

### 🔗 Foreign Keys

- `item_id` → `items.id` (cascade on delete)
- `workshop_id` → `workshops.id` (cascade on delete)

### 📝 Related Models

- [Item](#item) → `workshops()` relationship
- [Workshop](#workshop) → `items()` relationship

---

🤖 _This documentation was automatically generated using `php artisan docs:models`_
