---
layout: default
title: Generated Model Documentation
---

# Generated Model Documentation

{: .highlight }

> This documentation is automatically generated from the Laravel models and database schema. Last updated: 2025-10-26 23:52:46 UTC

## Overview

- Total Models: 32
- Database Connection: mariadb
- Laravel Version: 12.34.0

## Table of Contents

- [Artist](#artist)
- [Author](#author)
- [AvailableImage](#availableimage)
- [Collection](#collection)
- [CollectionImage](#collectionimage)
- [CollectionPartner](#collectionpartner)
- [CollectionTranslation](#collectiontranslation)
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
- [PartnerImage](#partnerimage)
- [PartnerTranslation](#partnertranslation)
- [PartnerTranslationImage](#partnertranslationimage)
- [Project](#project)
- [Province](#province)
- [ProvinceTranslation](#provincetranslation)
- [Setting](#setting)
- [Tag](#tag)
- [Theme](#theme)
- [ThemeTranslation](#themetranslation)
- [User](#user)
- [Workshop](#workshop)

## Artist {#artist}

**Namespace:** `App\Models\Artist`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `artists`                        |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `name`                   | varchar   | Unknown  | Unknown |       |
| `place_of_birth`         | varchar   | Unknown  | Unknown |       |
| `place_of_death`         | varchar   | Unknown  | Unknown |       |
| `date_of_birth`          | varchar   | Unknown  | Unknown |       |
| `date_of_death`          | varchar   | Unknown  | Unknown |       |
| `period_of_activity`     | varchar   | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['name', 'place_of_birth', 'place_of_death', 'date_of_birth', 'date_of_death', 'period_of_activity', 'internal_name', 'backward_compatibility']
```

### Attribute Casting

| Attribute    | Cast Type  |
| ------------ | ---------- |
| `created_at` | `datetime` |
| `updated_at` | `datetime` |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To Many

- **`items()`**: BelongsToMany [Item](#item)

## Author {#author}

**Namespace:** `App\Models\Author`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `authors`                        |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `name`                   | varchar   | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['name', 'internal_name', 'backward_compatibility']
```

### Attribute Casting

| Attribute    | Cast Type  |
| ------------ | ---------- |
| `created_at` | `datetime` |
| `updated_at` | `datetime` |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

## AvailableImage {#availableimage}

**Namespace:** `App\Models\AvailableImage`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `available_images`               |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column       | Type      | Nullable | Default | Extra |
| ------------ | --------- | -------- | ------- | ----- |
| `id`         | uuid      | Unknown  | Unknown |       |
| `path`       | varchar   | Unknown  | Unknown |       |
| `comment`    | varchar   | Unknown  | Unknown |       |
| `created_at` | timestamp | Unknown  | Unknown |       |
| `updated_at` | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['path', 'comment']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

## Collection {#collection}

**Namespace:** `App\Models\Collection`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `collections`                    |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `type`                   | enum      | Unknown  | Unknown |       |
| `language_id`            | varchar   | Unknown  | Unknown |       |
| `context_id`             | uuid      | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['internal_name', 'type', 'language_id', 'context_id', 'backward_compatibility']
```

### Model Constants

```php
const TYPE_COLLECTION = 'collection';
const TYPE_EXHIBITION = 'exhibition';
const TYPE_GALLERY = 'gallery';
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

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

### Query Scopes

- **`collections()`**
- **`exhibitions()`**
- **`galleries()`**

## CollectionImage {#collectionimage}

**Namespace:** `App\Models\CollectionImage`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `collection_images`              |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column          | Type      | Nullable | Default | Extra |
| --------------- | --------- | -------- | ------- | ----- |
| `id`            | uuid      | Unknown  | Unknown |       |
| `collection_id` | uuid      | Unknown  | Unknown |       |
| `path`          | varchar   | Unknown  | Unknown |       |
| `original_name` | varchar   | Unknown  | Unknown |       |
| `mime_type`     | varchar   | Unknown  | Unknown |       |
| `size`          | bigint    | Unknown  | Unknown |       |
| `alt_text`      | varchar   | Unknown  | Unknown |       |
| `display_order` | int       | Unknown  | Unknown |       |
| `created_at`    | timestamp | Unknown  | Unknown |       |
| `updated_at`    | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['collection_id', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'display_order']
```

### Attribute Casting

| Attribute       | Cast Type |
| --------------- | --------- |
| `size`          | `integer` |
| `display_order` | `integer` |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`collection()`**: BelongsTo [Collection](#collection)

## CollectionPartner {#collectionpartner}

**Namespace:** `App\Models\CollectionPartner`

### Database Table

| Property       | Value                |
| -------------- | -------------------- |
| **Table Name** | `collection_partner` |

⚠️ **Error generating documentation for this model:** Array to string conversion

## CollectionTranslation {#collectiontranslation}

**Namespace:** `App\Models\CollectionTranslation`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `collection_translations`        |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `collection_id`          | uuid      | Unknown  | Unknown |       |
| `language_id`            | varchar   | Unknown  | Unknown |       |
| `context_id`             | uuid      | Unknown  | Unknown |       |
| `title`                  | varchar   | Unknown  | Unknown |       |
| `description`            | text      | Unknown  | Unknown |       |
| `url`                    | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `extra`                  | longtext  | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['collection_id', 'language_id', 'context_id', 'title', 'description', 'url', 'backward_compatibility', 'extra']
```

### Attribute Casting

| Attribute | Cast Type |
| --------- | --------- |
| `extra`   | `object`  |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`collection()`**: BelongsTo [Collection](#collection)
- **`language()`**: BelongsTo [Language](#language)
- **`context()`**: BelongsTo [Context](#context)

### Query Scopes

- **`defaultContext()`**
- **`forLanguage()`**
- **`forContext()`**

## Context {#context}

**Namespace:** `App\Models\Context`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `contexts`                       |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |
| `is_default`             | tinyint   | Unknown  | Unknown |       |

### Fillable Fields

```php
['internal_name', 'backward_compatibility', 'is_default']
```

### Attribute Casting

| Attribute    | Cast Type |
| ------------ | --------- |
| `is_default` | `boolean` |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

### Query Scopes

- **`default()`**

## Country {#country}

**Namespace:** `App\Models\Country`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `countries`                      |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | varchar   | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['id', 'internal_name', 'backward_compatibility']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Has Many

- **`items()`**: HasMany [Item](#item)
- **`partners()`**: HasMany [Partner](#partner)

## Glossary {#glossary}

**Namespace:** `App\Models\Glossary`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `glossaries`                     |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['internal_name', 'backward_compatibility']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Has Many

- **`translations()`**: HasMany [GlossaryTranslation](#glossarytranslation)
- **`spellings()`**: HasMany [GlossarySpelling](#glossaryspelling)

#### Belongs To Many

- **`synonyms()`**: BelongsToMany [Glossary](#glossary)
- **`reverseSynonyms()`**: BelongsToMany [Glossary](#glossary)

## GlossarySpelling {#glossaryspelling}

**Namespace:** `App\Models\GlossarySpelling`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `glossary_spellings`             |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column        | Type      | Nullable | Default | Extra |
| ------------- | --------- | -------- | ------- | ----- |
| `id`          | uuid      | Unknown  | Unknown |       |
| `glossary_id` | uuid      | Unknown  | Unknown |       |
| `language_id` | varchar   | Unknown  | Unknown |       |
| `spelling`    | varchar   | Unknown  | Unknown |       |
| `created_at`  | timestamp | Unknown  | Unknown |       |
| `updated_at`  | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['glossary_id', 'language_id', 'spelling']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`glossary()`**: BelongsTo [Glossary](#glossary)
- **`language()`**: BelongsTo [Language](#language)

#### Belongs To Many

- **`itemTranslations()`**: BelongsToMany [ItemTranslation](#itemtranslation)

### Query Scopes

- **`forLanguage()`**
- **`forSpelling()`**

## GlossaryTranslation {#glossarytranslation}

**Namespace:** `App\Models\GlossaryTranslation`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `glossary_translations`          |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column        | Type      | Nullable | Default | Extra |
| ------------- | --------- | -------- | ------- | ----- |
| `id`          | uuid      | Unknown  | Unknown |       |
| `glossary_id` | uuid      | Unknown  | Unknown |       |
| `language_id` | varchar   | Unknown  | Unknown |       |
| `definition`  | text      | Unknown  | Unknown |       |
| `created_at`  | timestamp | Unknown  | Unknown |       |
| `updated_at`  | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['glossary_id', 'language_id', 'definition']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`glossary()`**: BelongsTo [Glossary](#glossary)
- **`language()`**: BelongsTo [Language](#language)

### Query Scopes

- **`forLanguage()`**

## ImageUpload {#imageupload}

**Namespace:** `App\Models\ImageUpload`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `image_uploads`                  |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column       | Type      | Nullable | Default | Extra |
| ------------ | --------- | -------- | ------- | ----- |
| `id`         | uuid      | Unknown  | Unknown |       |
| `path`       | varchar   | Unknown  | Unknown |       |
| `name`       | varchar   | Unknown  | Unknown |       |
| `extension`  | varchar   | Unknown  | Unknown |       |
| `mime_type`  | varchar   | Unknown  | Unknown |       |
| `size`       | bigint    | Unknown  | Unknown |       |
| `created_at` | timestamp | Unknown  | Unknown |       |
| `updated_at` | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['path', 'name', 'extension', 'mime_type', 'size']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

## Item {#item}

**Namespace:** `App\Models\Item`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `items`                          |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `partner_id`             | uuid      | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `type`                   | enum      | Unknown  | Unknown |       |
| `parent_id`              | uuid      | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |
| `country_id`             | varchar   | Unknown  | Unknown |       |
| `project_id`             | uuid      | Unknown  | Unknown |       |
| `collection_id`          | uuid      | Unknown  | Unknown |       |
| `owner_reference`        | varchar   | Unknown  | Unknown |       |
| `mwnf_reference`         | varchar   | Unknown  | Unknown |       |

### Fillable Fields

```php
['partner_id', 'parent_id', 'internal_name', 'type', 'backward_compatibility', 'country_id', 'project_id', 'collection_id', 'owner_reference', 'mwnf_reference']
```

### Model Constants

```php
const TYPE_OBJECT = 'object';
const TYPE_MONUMENT = 'monument';
const TYPE_DETAIL = 'detail';
const TYPE_PICTURE = 'picture';
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

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

### Query Scopes

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

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `item_images`                    |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column          | Type      | Nullable | Default | Extra |
| --------------- | --------- | -------- | ------- | ----- |
| `id`            | uuid      | Unknown  | Unknown |       |
| `item_id`       | uuid      | Unknown  | Unknown |       |
| `path`          | varchar   | Unknown  | Unknown |       |
| `original_name` | varchar   | Unknown  | Unknown |       |
| `mime_type`     | varchar   | Unknown  | Unknown |       |
| `size`          | bigint    | Unknown  | Unknown |       |
| `alt_text`      | varchar   | Unknown  | Unknown |       |
| `display_order` | int       | Unknown  | Unknown |       |
| `created_at`    | timestamp | Unknown  | Unknown |       |
| `updated_at`    | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['item_id', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'display_order']
```

### Attribute Casting

| Attribute       | Cast Type |
| --------------- | --------- |
| `size`          | `integer` |
| `display_order` | `integer` |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`item()`**: BelongsTo [Item](#item)

## ItemTranslation {#itemtranslation}

**Namespace:** `App\Models\ItemTranslation`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `item_translations`              |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                       | Type      | Nullable | Default | Extra |
| ---------------------------- | --------- | -------- | ------- | ----- |
| `id`                         | uuid      | Unknown  | Unknown |       |
| `item_id`                    | uuid      | Unknown  | Unknown |       |
| `language_id`                | varchar   | Unknown  | Unknown |       |
| `context_id`                 | uuid      | Unknown  | Unknown |       |
| `name`                       | varchar   | Unknown  | Unknown |       |
| `alternate_name`             | varchar   | Unknown  | Unknown |       |
| `description`                | text      | Unknown  | Unknown |       |
| `type`                       | varchar   | Unknown  | Unknown |       |
| `holder`                     | text      | Unknown  | Unknown |       |
| `owner`                      | text      | Unknown  | Unknown |       |
| `initial_owner`              | text      | Unknown  | Unknown |       |
| `dates`                      | text      | Unknown  | Unknown |       |
| `location`                   | text      | Unknown  | Unknown |       |
| `dimensions`                 | text      | Unknown  | Unknown |       |
| `place_of_production`        | text      | Unknown  | Unknown |       |
| `method_for_datation`        | text      | Unknown  | Unknown |       |
| `method_for_provenance`      | text      | Unknown  | Unknown |       |
| `obtention`                  | text      | Unknown  | Unknown |       |
| `bibliography`               | text      | Unknown  | Unknown |       |
| `author_id`                  | uuid      | Unknown  | Unknown |       |
| `text_copy_editor_id`        | uuid      | Unknown  | Unknown |       |
| `translator_id`              | uuid      | Unknown  | Unknown |       |
| `translation_copy_editor_id` | uuid      | Unknown  | Unknown |       |
| `backward_compatibility`     | varchar   | Unknown  | Unknown |       |
| `extra`                      | longtext  | Unknown  | Unknown |       |
| `created_at`                 | timestamp | Unknown  | Unknown |       |
| `updated_at`                 | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['item_id', 'language_id', 'context_id', 'name', 'alternate_name', 'description', 'type', 'holder', 'owner', 'initial_owner', 'dates', 'location', 'dimensions', 'place_of_production', 'method_for_datation', 'method_for_provenance', 'obtention', 'bibliography', 'author_id', 'text_copy_editor_id', 'translator_id', 'translation_copy_editor_id', 'backward_compatibility', 'extra']
```

### Attribute Casting

| Attribute | Cast Type |
| --------- | --------- |
| `extra`   | `object`  |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

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

### Query Scopes

- **`defaultContext()`**
- **`forLanguage()`**
- **`forContext()`**

## Language {#language}

**Namespace:** `App\Models\Language`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `languages`                      |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | varchar   | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |
| `is_default`             | tinyint   | Unknown  | Unknown |       |

### Fillable Fields

```php
['id', 'internal_name', 'backward_compatibility', 'is_default']
```

### Attribute Casting

| Attribute    | Cast Type |
| ------------ | --------- |
| `is_default` | `boolean` |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

### Query Scopes

- **`english()`**
- **`default()`**

## Location {#location}

**Namespace:** `App\Models\Location`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `locations`                      |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `country_id`             | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['internal_name', 'country_id']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`country()`**: BelongsTo [Country](#country)

#### Has Many

- **`translations()`**: HasMany [LocationTranslation](#locationtranslation)

## LocationTranslation {#locationtranslation}

**Namespace:** `App\Models\LocationTranslation`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `location_translations`          |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `location_id`            | uuid      | Unknown  | Unknown |       |
| `language_id`            | varchar   | Unknown  | Unknown |       |
| `name`                   | varchar   | Unknown  | Unknown |       |
| `description`            | text      | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['location_id', 'language_id', 'name', 'description', 'backward_compatibility']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`location()`**: BelongsTo [Location](#location)
- **`language()`**: BelongsTo [Language](#language)

## Partner {#partner}

**Namespace:** `App\Models\Partner`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `partners`                       |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `type`                   | enum      | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |
| `country_id`             | varchar   | Unknown  | Unknown |       |

### Fillable Fields

```php
['internal_name', 'type', 'backward_compatibility', 'country_id', 'latitude', 'longitude', 'map_zoom', 'project_id', 'monument_item_id', 'visible']
```

### Attribute Casting

| Attribute   | Cast Type   |
| ----------- | ----------- |
| `latitude`  | `decimal:8` |
| `longitude` | `decimal:8` |
| `map_zoom`  | `integer`   |
| `visible`   | `boolean`   |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`country()`**: BelongsTo [Country](#country)
- **`project()`**: BelongsTo [Project](#project)
- **`monumentItem()`**: BelongsTo [Item](#item)

#### Has Many

- **`items()`**: HasMany [Item](#item)
- **`translations()`**: HasMany [PartnerTranslation](#partnertranslation)
- **`partnerImages()`**: HasMany [PartnerImage](#partnerimage)

#### Belongs To Many

- **`collections()`**: BelongsToMany [Collection](#collection)

### Query Scopes

- **`visible()`**

## PartnerImage {#partnerimage}

**Namespace:** `App\Models\PartnerImage`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `partner_images`                 |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Fillable Fields

```php
['partner_id', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'display_order']
```

### Attribute Casting

| Attribute       | Cast Type |
| --------------- | --------- |
| `size`          | `integer` |
| `display_order` | `integer` |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`partner()`**: BelongsTo [Partner](#partner)

## PartnerTranslation {#partnertranslation}

**Namespace:** `App\Models\PartnerTranslation`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `partner_translations`           |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Fillable Fields

```php
['partner_id', 'language_id', 'context_id', 'name', 'description', 'city_display', 'address_line_1', 'address_line_2', 'postal_code', 'address_notes', 'contact_name', 'contact_email_general', 'contact_email_press', 'contact_phone', 'contact_website', 'contact_notes', 'contact_emails', 'contact_phones', 'backward_compatibility', 'extra']
```

### Attribute Casting

| Attribute        | Cast Type |
| ---------------- | --------- |
| `contact_emails` | `array`   |
| `contact_phones` | `array`   |
| `extra`          | `object`  |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`partner()`**: BelongsTo [Partner](#partner)
- **`language()`**: BelongsTo [Language](#language)
- **`context()`**: BelongsTo [Context](#context)

#### Has Many

- **`partnerTranslationImages()`**: HasMany [PartnerTranslationImage](#partnertranslationimage)

### Query Scopes

- **`defaultContext()`**
- **`forLanguage()`**
- **`forContext()`**

## PartnerTranslationImage {#partnertranslationimage}

**Namespace:** `App\Models\PartnerTranslationImage`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `partner_translation_images`     |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Fillable Fields

```php
['partner_translation_id', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'display_order']
```

### Attribute Casting

| Attribute       | Cast Type |
| --------------- | --------- |
| `size`          | `integer` |
| `display_order` | `integer` |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`partnerTranslation()`**: BelongsTo [PartnerTranslation](#partnertranslation)

## Project {#project}

**Namespace:** `App\Models\Project`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `projects`                       |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `launch_date`            | date      | Unknown  | Unknown |       |
| `is_launched`            | tinyint   | Unknown  | Unknown |       |
| `is_enabled`             | tinyint   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |
| `context_id`             | uuid      | Unknown  | Unknown |       |
| `language_id`            | varchar   | Unknown  | Unknown |       |

### Fillable Fields

```php
['internal_name', 'backward_compatibility', 'launch_date', 'is_launched', 'is_enabled', 'context_id', 'language_id']
```

### Attribute Casting

| Attribute     | Cast Type        |
| ------------- | ---------------- |
| `launch_date` | `datetime:Y-m-d` |
| `is_launched` | `boolean`        |
| `is_enabled`  | `boolean`        |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`context()`**: BelongsTo [Context](#context)
- **`language()`**: BelongsTo [Language](#language)

### Query Scopes

- **`isEnabled()`**
- **`isLaunched()`**
- **`isLaunchDatePassed()`**
- **`visible()`**
- **`enabled()`**

## Province {#province}

**Namespace:** `App\Models\Province`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `provinces`                      |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `country_id`             | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['internal_name', 'country_id']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`country()`**: BelongsTo [Country](#country)

#### Has Many

- **`translations()`**: HasMany [ProvinceTranslation](#provincetranslation)

## ProvinceTranslation {#provincetranslation}

**Namespace:** `App\Models\ProvinceTranslation`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `province_translations`          |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `province_id`            | uuid      | Unknown  | Unknown |       |
| `language_id`            | varchar   | Unknown  | Unknown |       |
| `name`                   | varchar   | Unknown  | Unknown |       |
| `description`            | text      | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['province_id', 'language_id', 'name', 'description', 'backward_compatibility']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`province()`**: BelongsTo [Province](#province)
- **`language()`**: BelongsTo [Language](#language)

## Setting {#setting}

**Namespace:** `App\Models\Setting`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `settings`                       |
| **Primary Key**  | `id`                             |
| **Key Type**     | Auto-incrementing Integer        |
| **Incrementing** | Yes                              |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column        | Type      | Nullable | Default | Extra |
| ------------- | --------- | -------- | ------- | ----- |
| `id`          | bigint    | Unknown  | Unknown |       |
| `key`         | varchar   | Unknown  | Unknown |       |
| `value`       | text      | Unknown  | Unknown |       |
| `type`        | varchar   | Unknown  | Unknown |       |
| `description` | text      | Unknown  | Unknown |       |
| `created_at`  | timestamp | Unknown  | Unknown |       |
| `updated_at`  | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['key', 'value', 'type', 'description']
```

### Attribute Casting

| Attribute | Cast Type |
| --------- | --------- |
| `id`      | `int`     |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

## Tag {#tag}

**Namespace:** `App\Models\Tag`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `tags`                           |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `description`            | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['internal_name', 'backward_compatibility', 'description']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To Many

- **`items()`**: BelongsToMany [Item](#item)

### Query Scopes

- **`forItem()`**

## Theme {#theme}

**Namespace:** `App\Models\Theme`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `themes`                         |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `collection_id`          | uuid      | Unknown  | Unknown |       |
| `parent_id`              | uuid      | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['collection_id', 'parent_id', 'internal_name', 'backward_compatibility']
```

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`collection()`**: BelongsTo [Collection](#collection)
- **`parent()`**: BelongsTo [Theme](#theme)

#### Has Many

- **`subthemes()`**: HasMany [Theme](#theme)
- **`translations()`**: HasMany [ThemeTranslation](#themetranslation)

## ThemeTranslation {#themetranslation}

**Namespace:** `App\Models\ThemeTranslation`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `theme_translations`             |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `theme_id`               | uuid      | Unknown  | Unknown |       |
| `language_id`            | varchar   | Unknown  | Unknown |       |
| `context_id`             | uuid      | Unknown  | Unknown |       |
| `title`                  | varchar   | Unknown  | Unknown |       |
| `description`            | text      | Unknown  | Unknown |       |
| `introduction`           | text      | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `extra`                  | longtext  | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['theme_id', 'language_id', 'context_id', 'title', 'description', 'introduction', 'backward_compatibility', 'extra']
```

### Attribute Casting

| Attribute | Cast Type |
| --------- | --------- |
| `extra`   | `object`  |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To

- **`theme()`**: BelongsTo [Theme](#theme)
- **`language()`**: BelongsTo [Language](#language)
- **`context()`**: BelongsTo [Context](#context)

### Query Scopes

- **`defaultContext()`**
- **`forLanguage()`**
- **`forContext()`**

## User {#user}

**Namespace:** `App\Models\User`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `users`                          |
| **Primary Key**  | `id`                             |
| **Key Type**     | Auto-incrementing Integer        |
| **Incrementing** | Yes                              |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                      | Type      | Nullable | Default | Extra |
| --------------------------- | --------- | -------- | ------- | ----- |
| `id`                        | bigint    | Unknown  | Unknown |       |
| `name`                      | varchar   | Unknown  | Unknown |       |
| `email`                     | varchar   | Unknown  | Unknown |       |
| `email_verified_at`         | timestamp | Unknown  | Unknown |       |
| `password`                  | varchar   | Unknown  | Unknown |       |
| `two_factor_secret`         | text      | Unknown  | Unknown |       |
| `two_factor_recovery_codes` | text      | Unknown  | Unknown |       |
| `two_factor_confirmed_at`   | timestamp | Unknown  | Unknown |       |
| `remember_token`            | varchar   | Unknown  | Unknown |       |
| `current_team_id`           | bigint    | Unknown  | Unknown |       |
| `profile_photo_path`        | varchar   | Unknown  | Unknown |       |
| `created_at`                | timestamp | Unknown  | Unknown |       |
| `updated_at`                | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['name', 'email', 'password']
```

### Attribute Casting

| Attribute           | Cast Type  |
| ------------------- | ---------- |
| `id`                | `int`      |
| `email_verified_at` | `datetime` |
| `password`          | `hashed`   |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Morph Many

- **`tokens()`**: MorphMany [PersonalAccessToken](#personalaccesstoken)
- **`notifications()`**: MorphMany [DatabaseNotification](#databasenotification)
- **`readNotifications()`**: MorphMany [DatabaseNotification](#databasenotification)
- **`unreadNotifications()`**: MorphMany [DatabaseNotification](#databasenotification)

### Query Scopes

- **`role()`**
- **`withoutRole()`**
- **`permission()`**
- **`withoutPermission()`**

## Workshop {#workshop}

**Namespace:** `App\Models\Workshop`

### Database Table

| Property         | Value                            |
| ---------------- | -------------------------------- |
| **Table Name**   | `workshops`                      |
| **Primary Key**  | `id`                             |
| **Key Type**     | String (UUID)                    |
| **Incrementing** | No                               |
| **Timestamps**   | Yes (`created_at`, `updated_at`) |

### Database Schema

| Column                   | Type      | Nullable | Default | Extra |
| ------------------------ | --------- | -------- | ------- | ----- |
| `id`                     | uuid      | Unknown  | Unknown |       |
| `name`                   | varchar   | Unknown  | Unknown |       |
| `internal_name`          | varchar   | Unknown  | Unknown |       |
| `backward_compatibility` | varchar   | Unknown  | Unknown |       |
| `created_at`             | timestamp | Unknown  | Unknown |       |
| `updated_at`             | timestamp | Unknown  | Unknown |       |

### Fillable Fields

```php
['name', 'internal_name', 'backward_compatibility']
```

### Attribute Casting

| Attribute    | Cast Type  |
| ------------ | ---------- |
| `created_at` | `datetime` |
| `updated_at` | `datetime` |

### Model Constants

```php
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';
```

### Relationships

#### Belongs To Many

- **`items()`**: BelongsToMany [Item](#item)

---

_This documentation was automatically generated using `php artisan docs:models`_
