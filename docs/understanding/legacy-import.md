---
layout: default
title: Legacy Import
parent: Understanding the Inventory
nav_order: 3
---

# Legacy Import

The importer reads selected legacy content, transforms it, and writes it into the Inventory model. It does not copy legacy databases table by table.

## Source areas

| Source area | What it contributes |
|---|---|
| JSON reference files | Languages and Countries used by the new application. |
| `mwnf3` | Projects, museums, institutions, schools, objects, monuments, monument details, pictures, links, dynasties, authors, exhibitions, glossary, HCR timelines, and item media. |
| `mwnf3_sharing_history` | Sharing History projects, partners, objects, monuments, details, images, exhibitions, national context, historical background, bibliography, HCR timelines, item documents, media, and authors. |
| `mwnf3_explore` | Explore thematic cycles, countries, regions, locations, monuments, itineraries, filters, images, translations, and cross-references. |
| `mwnf3_travels` | Travels trails, itineraries, locations, travel-specific monuments, images, and translations. |
| `mwnf3_thematic_gallery` | Thematic galleries, contexts, themes, item assignments, contextual item descriptions, related items, tags, contributors, and collection media. |

## Import sequence

| Stage | What lands in Inventory |
|---|---|
| Reference data | Default Context, Languages, Language names, Countries, Country names. |
| Core `mwnf3` content | Projects, Contexts, Collections, Partners, Items, Item Translations, Item Links, Authors, Dynasties. |
| `mwnf3` images | Item Images, picture Items, Partner Images, Partner Logos. |
| Sharing History | Projects, Contexts, Collections, Partners, Items, Translations, Images, Collection links. |
| Glossary | Glossary entries, definitions, and spellings. |
| Timelines | Timelines, Timeline Events, translations, images, and item links. |
| Media and documents | Item Media and Item Documents. |
| Explore | Collections, Items, Translations, Images, Tags, Item Links. |
| Travels | Collections, Items, Translations, Collection Images, Item Images. |
| Thematic Galleries | Collections, contextual Item Translations, Collection Item links, Item Links, Tags, Contributors, Collection Media. |
| Final linking and cleanup | Partner monument references, Collection Media, and removal of projects without Items. |

## Key transformation rules

### Language rows become translations

Many legacy tables store one row per language. The importer groups rows that describe the same source record, creates one Inventory parent record, and writes one Translation per usable language row.

### Projects create three Inventory records

Each imported project creates a Project, a Context, and a Collection. These records share the same legacy reference.

### Codes are normalized

Legacy language and country codes are mapped to new identifiers. A source two-letter code does not always appear literally in the Inventory.

### Text is cleaned

Transformers convert legacy HTML-like content into the text format used by the new system. Formatting changes are expected when the meaning remains intact.

### Extra data is preserved when no normalized field exists

Some source fields have no stable Inventory field. The importer stores selected residual data in `extra` fields instead of expanding the model for every legacy exception.

### Images use database import plus file synchronization

The database import creates image records. The image synchronization tool then copies or symlinks files from legacy storage and updates image paths and sizes.

Object, monument, and detail pictures also create child Items of type `picture`.

## Traceability

Imported records use `backward_compatibility` values in this format:

```text
{legacy_schema}:{legacy_table}:{legacy_primary_key_parts}
```

Examples:

| New record | Source reference pattern |
|---|---|
| Project, project Context, project Collection | `mwnf3:projects:{project_id}` |
| `mwnf3` object Item | `mwnf3:objects:{project_id}:{country}:{museum_id}:{number}` |
| `mwnf3` monument Item | `mwnf3:monuments:{project_id}:{country}:{institution_id}:{number}` |
| `mwnf3` monument detail Item | `mwnf3:monument_details:{project_id}:{country_id}:{institution_id}:{monument_id}:{detail_id}` |
| Partner from a museum | `mwnf3:museums:{museum_id}:{country}` |
| Partner from an institution | `mwnf3:institutions:{institution_id}:{country}` |
| Sharing History object Item | `mwnf3_sharing_history:sh_objects:{project_id}:{country}:{number}` |
| Sharing History monument Item | `mwnf3_sharing_history:sh_monuments:{project_id}:{country}:{number}` |
| Glossary entry | `mwnf3:glossary:{word_id}` |
| `mwnf3` HCR country timeline | `mwnf3:hcr:country:{country_id}` |

## Related reference

- [Validation Guide](validation-guide) gives practical checks for customer review.
- [Legacy URL Mapping](legacy-url-mapping) explains how `backward_compatibility` values map back to legacy public and back-office URLs.
- [Importer Orientation](../collaborators/importer) gives developer-level file locations.
