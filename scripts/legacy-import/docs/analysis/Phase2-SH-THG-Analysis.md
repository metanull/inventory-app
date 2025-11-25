# Phase 2: Legacy Schema Analysis - Sharing History & Thematic Gallery

**Generated**: 2025-11-16  
**Status**: COMPLETE  
**Schemas**: mwnf3_sharing_history (148 tables), mwnf3_thematic_gallery (90 tables)  
**Total Tables**: 238

## Executive Summary

Sharing History (sh) and Thematic Gallery (thg) are extension schemas built on top of mwnf3. Key findings:

- **Model Difference**: sh uses denormalized model (like mwnf3), thg uses normalized model (separate base + texts tables)
- **Cross-schema References**: Both heavily reference mwnf3 entities (objects, monuments, partners, countries, langs)
- **New Items vs References**: Mix of new items specific to collections AND references to existing mwnf3 items
- **Hierarchical Collections**: sh has 3-level hierarchy (Exhibition→Theme→Subtheme), thg has self-referencing galleries
- **Contextual Translations**: Items can have different descriptions in different exhibition contexts
- **Partner Handling**: sh has own partners table, thg references sh_partners and mwnf3 entities
- **Tag System**: thg has extensive tag system (thg_tags, sh_tags, mwnf3_tags) - all can be applied to thg items
- **Deduplication Critical**: Must use backward_compatibility to avoid importing same data twice

---

## Sharing History (mwnf3_sharing_history) - 148 Tables

### Architecture Overview

**Purpose**: Online exhibitions with curated national and contextual presentations

**Model**: Follows mwnf3 denormalized pattern but uses NORMALIZED base + texts separation

- Base tables (sh_objects, sh_monuments): PK without language, common data
- Text tables (sh_objects_texts, sh_monuments_texts): PK with language, translated content
- **CRITICAL**: This is DIFFERENT from mwnf3 where language was in the main table PK!

### Core Entity Tables

#### Projects (2 tables)

- `sh_projects` - Project definitions
  - PK: `project_id` (varchar(11))
  - Fields: name, description, status
- `sh_project_names` - Project translations
  - PK: `project_id`, `lang`

#### Exhibitions - 3-Level Hierarchy (9 tables)

**Level 1: Exhibitions**

- `sh_exhibitions` - Exhibition master records
  - PK: `exhibition_id` (auto-increment)
  - FK: → sh_projects
  - Fields: project_id, user_id, name, sort, show (enum y/n), geoCoordinates, zoom,
    exh_thumb, logos (x3), urls (x3), homeimage, portal_image, new_status
- `sh_exhibitionnames` - Exhibition name translations
- `sh_exhibition_images` - Exhibition images

**Level 2: Themes**

- `sh_exhibition_themes` - Theme definitions
  - PK: `theme_id` (auto-increment)
  - FK: → sh_exhibitions
  - Fields: exhibition_id, name, sort, geoCoordinates, zoom
  - **Maps to**: Collection with parent_id = exhibition Collection
- `sh_exhibition_themenames` - Theme translations
- `sh_exhibition_theme_images` - Theme images

**Level 3: Subthemes**

- `sh_exhibition_subthemes` - Subtheme definitions
  - PK: `subtheme_id` (auto-increment)
  - FK: → sh_exhibition_themes
  - Fields: theme_id, name, sort
  - **Maps to**: Collection with parent_id = theme Collection
- `sh_exhibition_subthemenames` - Subtheme translations
- `sh_exhibition_subtheme_images` - Subtheme images
- `sh_exhibition_subtheme_video_audio` - Subtheme media

#### National Context Hierarchy (8 tables)

**Alternative hierarchy**: National Context → Exhibitions/Themes/Subthemes

- `sh_national_context_countries` - National context definitions by country
- `sh_national_context_exhibitions` - Exhibition-level national context
- `sh_national_context_exhibition_texts` - Translations
- `sh_national_context_exhibition_images` - Images
- `sh_national_context_themes` - Theme-level national context
- `sh_national_context_theme_texts` - Translations
- `sh_national_context_theme_images` - Images
- `sh_national_context_subthemes` - Subtheme-level national context
- `sh_national_context_subtheme_texts` - Translations
- `sh_national_context_subtheme_images` - Images

#### Partners (3 tables)

- `sh_partners` - Partner master records **[NEW PARTNERS, NOT REFERENCES]**
  - PK: `partners_id` (varchar(11))
  - FK: → mwnf3.countries
  - Fields: country, partner_category ('museums', 'archives', 'universities', 'cultural heritage authorities', 'libraries', 'Others'),
    name, city, address, phone, fax, email (x2), url (x5), title (x5), logo (x3),
    cp1/cp2 contact persons, region_id, geoCoordinates, zoom, portal_display
  - **CRITICAL**: Single PK column (partners_id), not composite like mwnf3.museums
- `sh_partner_names` - Partner name translations
- `sh_partner_associated` - Associated partner relationships

#### Items - Objects (6 tables)

**Base Table**: `sh_objects` - **[NORMALIZED - NO LANGUAGE IN PK]**

- PK: `project_id`, `country`, `number` (3 columns, NO lang!)
- FK: → sh_projects, → mwnf3.countries, → sh_partners, → mwnf3.countries (pd_country)
- Fields: partners_id, working_number, inventory_id, start_date, end_date,
  display_status (enum: 'A' Active, 'N' HB/HCR), pd_country
- **CRITICAL**: Normalized! Base data separate from translations

**Text Table**: `sh_objects_texts` - Language-specific content

- PK: `project_id`, `country`, `number`, `lang` (4 columns)
- FK: → sh_objects (project_id, country, number), → mwnf3.langs
- Fields: name, name2, second_name, third_name, archival, typeof, holding_museum,
  holding_institution_org, location, province, date_description, dynasty, current_owner,
  original_owner, provenance, dimensions, materials, artist, birthdate, birthplace,
  deathdate, deathplace, period_activity, production_place, workshop, description,
  description2, datationmethod, provenancemethod, obtentionmethod, bibliography,
  linkobjects, linkmonuments, linkcatalogs, keywords, preparedby, copyeditedby,
  translationby, translationcopyeditedby, copyright, log fields, notice (x3)
- **CRITICAL**: All translation content here, NOT in base table

**Related Tables**:

- `sh_object_images` - Object images
  - PK: `project_id`, `country`, `number`, `type`, `image_number` (NO lang in PK!)
- `sh_object_image_texts` - Image caption translations
- `sh_objects_document` - Object documents
- `sh_objects_video_audio` - Object media
- `sh_objects_ngm_projects` - Object-project relationships

#### Items - Monuments (6 tables)

**Base Table**: `sh_monuments` - **[NORMALIZED - NO LANGUAGE IN PK]**

- PK: `project_id`, `country`, `number` (3 columns, NO lang!)
- FK: → sh_projects, → mwnf3.countries, → sh_partners
- Fields: partners_id, working_number, start_date, end_date, display_status, pd_country

**Text Table**: `sh_monuments_texts` - Language-specific content

- PK: `project_id`, `country`, `number`, `lang` (4 columns)
- FK: → sh_monuments, → mwnf3.langs
- Fields: Similar to sh_objects_texts but with monument-specific fields (address, phone,
  fax, email, institution, patrons, architects, history, external_sources)

**Related Tables**:

- `sh_monument_images` - Monument images
- `sh_monument_image_texts` - Image caption translations
- `sh_monuments_document` - Monument documents
- `sh_monuments_video_audio` - Monument media
- `sh_monuments_ngm_projects` - Monument-project relationships

#### Items - Monument Details (4 tables)

**Base Table**: `sh_monument_details` - **[NORMALIZED]**

- PK: `project_id`, `country_id`, `partners_id`, `monument_id`, `detail_id` (NO lang!)
- FK: → sh_monuments (CASCADE DELETE)
- Fields: Basic non-translatable data

**Text Table**: `sh_monument_detail_texts`

- PK: Includes `lang`
- Fields: name, description, location, date, artist

**Related Tables**:

- `sh_monument_detail_pictures` - Detail images
- `sh_monument_detail_picture_texts` - Image captions

### Collection-Item Relationship Tables (6 tables)

**Pattern**: `rel_objects_nc_*` - "nc" likely means "national context"

- `rel_objects_nc_exhibitions` - Objects in national context exhibitions
  - FK: → sh_national_context_exhibitions, → sh_objects (project_id, country, number)
- `rel_objects_nc_exhibition_justifications` - Why object is in exhibition
- `rel_objects_nc_themes` - Objects in themes
- `rel_objects_nc_theme_justifications` - Why object is in theme
- `rel_objects_nc_subthemes` - Objects in subthemes
- `rel_objects_nc_subtheme_justifications` - Why object is in subtheme

**CRITICAL**: References sh_objects by non-lang columns (project_id, country, number)

### Hierarchical Cultural Records (HCR) (4 tables)

- `sh_hcr` - HCR master records
- `sh_hcr_events` - HCR events
- `sh_hcr_images` - HCR images
- `sh_hcr_image_texts` - HCR image captions

### Reference vs New Items

**Strategy Needed**: Determine if sh_objects/sh_monuments are:

1. **New items** specific to sharing_history exhibitions (most likely)
2. **References** to mwnf3 items with contextual descriptions
3. **Mix** of both

**Indicators**:

- sh_objects has own PK structure (project_id, country, number) - DIFFERENT from mwnf3
- sh_objects has `partners_id` FK to sh_partners (not mwnf3.museums)
- sh_objects_texts has full field set (not just contextual overrides)
- **Conclusion**: sh_objects/monuments are NEW ITEMS, not references

**However**: Some items may duplicate mwnf3 items semantically (same real-world object)

- Use working_number, inventory_id to detect duplicates
- Check partners_id resolution to mwnf3 partners
- May need manual reconciliation

---

## Thematic Gallery (mwnf3_thematic_gallery) - 90 Tables

### Architecture Overview

**Purpose**: Thematic exhibitions/galleries curating items from multiple sources

**Key Difference**: thg REFERENCES items from other schemas heavily

- Can include mwnf3 objects/monuments
- Can include sh objects/monuments
- Can include travel monuments
- Can have own thg-specific objects/monuments
- **Gallery pivot tables show this**: `thg_gallery_mwnf3_objects`, `thg_gallery_sh_objects`, etc.

### Core Entity Tables

#### Projects (3 tables)

- `thg_projects` - Project definitions
  - PK: `project_id` (varchar(10))
- `thg_project_names` - Project translations
- `thg_project_type` - Project type taxonomy

#### Galleries - Self-Referencing Hierarchy (3 tables)

**`thg_gallery`** - Gallery master records with hierarchical support

- PK: `gallery_id` (auto-increment)
- FK: → thg_projects, → thg_gallery (parent_gallery_id - SELF REFERENCE), → mwnf3.projects (mwnf3_project_id)
- Fields: parent_gallery_id (NULL for top-level), project_id, name, image, sort_order,
  status (enum: 'A' Active, 'H' Hidden), featured (enum: 'A', 'H'), link, banner_image,
  banner_item, new_expire_date, landing_url, portal_image, live_date, homepage_image,
  homepage_item, has_timeline (bit), has_country_timeline (bit), i18n_group_id,
  i18n_common_group_id, mwnf3_project_id
- **CRITICAL**: parent_gallery_id enables sub-galleries → Maps to Collection.parent_id
- **CRITICAL**: Can reference mwnf3 project (mwnf3_project_id FK)

**Related Tables**:

- `thg_gallery_names` - Gallery name translations
- `thg_gallery_logos` - Gallery logos
- `thg_gallery_url` - Gallery URLs
- `thg_gallery_sponsors` - Gallery sponsors

#### Gallery-Item Pivot Tables (6 tables)

**CRITICAL**: These show thg galleries REFERENCE items from other schemas

- `thg_gallery_mwnf3_objects` - Gallery includes mwnf3 objects
  - PK: gallery_id, objects_project_id, objects_country, objects_museum_id, objects_number
  - FK: → thg_gallery, → mwnf3.objects (non-lang columns!)
- `thg_gallery_mwnf3_monuments` - Gallery includes mwnf3 monuments
  - FK: → mwnf3.monuments (non-lang columns)
- `thg_gallery_sh_objects` - Gallery includes sh objects
  - FK: → mwnf3_sharing_history.sh_objects
- `thg_gallery_sh_monuments` - Gallery includes sh monuments
  - FK: → mwnf3_sharing_history.sh_monuments
- `thg_gallery_thg_objects` - Gallery includes thg-specific objects
- `thg_gallery_thg_monuments` - Gallery includes thg-specific monuments
- `thg_gallery_travel_monuments` - Gallery includes travel/explore monuments

**Import Strategy**: Create collection_item pivot records linking Collection (gallery) to Items (resolved via backward_compatibility)

#### Partners (4 tables)

- `thg_partners` - Partner master records **[NORMALIZED]**
  - PK: `partners_id` (varchar(10))
  - FK: → mwnf3.countries
  - Fields: Similar to sh_partners
  - **CRITICAL**: Single PK column, references mwnf3.countries
- `thg_partner_names` - Partner translations
- `thg_partner_categories` - Partner category taxonomy
- `thg_partner_category_lang` - Category translations
- `thg_partner_pictures` - Partner images

#### Items - Objects (7 tables)

**Base Table**: `thg_objects` - **[NORMALIZED]**

- PK: `partners_id`, `number` (2 columns, NO lang, NO project!)
- FK: → thg_partners, → mwnf3.countries
- Fields: country, working_number, inventory_id, start_date, end_date
- **CRITICAL**: Different PK structure from mwnf3 (no project_id!) and sh (no project_id, no country in PK!)

**Text Table**: `thg_objects_texts`

- PK: `partners_id`, `number`, `lang`
- FK: → thg_objects, → mwnf3.langs
- Fields: Full content fields similar to mwnf3/sh (name, description, etc.)

**Related Tables**:

- `thg_object_images` - Object images
- `thg_object_image_texts` - Image captions
- `thg_objects_objects` - Object-to-object links (within thg)
- `thg_objects_objects_justification` - Link justifications
- `thg_objects_monuments` - Object-to-monument links (within thg)
- `thg_objects_monuments_justification` - Link justifications

#### Items - Monuments (7 tables)

**Base Table**: `thg_monuments` - **[NORMALIZED]**

- PK: `partners_id`, `number` (2 columns)
- FK: → thg_partners, → mwnf3.countries
- Fields: Similar to thg_objects

**Text Table**: `thg_monuments_texts`

- PK: `partners_id`, `number`, `lang`
- Fields: Monument-specific content

**Related Tables**:

- `thg_monument_images` - Monument images
- `thg_monument_image_texts` - Image captions
- `thg_monument_details` - Monument details (child items)
- `thg_monument_detail_texts` - Detail translations
- `thg_monument_detail_pictures` - Detail images
- `thg_monument_detail_picture_texts` - Detail image captions
- `thg_monuments_objects` - Monument-to-object links
- `thg_monuments_objects_justification` - Link justifications
- `thg_monuments_monuments` - Monument-to-monument links
- `thg_monuments_monuments_justification` - Link justifications

### Tag System (7 tables)

**CRITICAL**: thg has comprehensive tag system allowing tags from multiple sources

- `thg_tags` - THG-specific tags
  - PK: `tag_id` (auto-increment)
  - FK: → thg_tag_types
  - Fields: tag_type_id, name, description, sort_order
- `thg_tag_types` - Tag type taxonomy
- `thg_tags_sequence` - Tag sequencing

**Object-Tag Relationships** (3 tables):

- `thg_objects_thg_tags` - thg_objects ↔ thg_tags
- `thg_objects_sh_tags` - thg_objects ↔ sharing_history tags (if sh has tags)
- `thg_objects_mwnf3_tags` - thg_objects ↔ mwnf3.dynasties (treating them as tags)

**Monument-Tag Relationships** (3 tables):

- `thg_monuments_thg_tags` - thg_monuments ↔ thg_tags
- `thg_monuments_sh_tags` - thg_monuments ↔ sh tags
- `thg_monuments_mwnf3_tags` - thg_monuments ↔ mwnf3.dynasties

**Import Strategy**:

1. Import all tag types as Tags in new model
2. Create item_tag relationships for all three tag sources
3. Use backward_compatibility to resolve tag references across schemas

### Sponsors (3 tables)

- `thg_sponsors` - Sponsor definitions
- `thg_sponsor_projects` - Sponsor-project relationships
- `thg_gallery_sponsors` - Gallery-sponsor relationships

---

## Cross-Schema Relationships Summary

### FK References to mwnf3

**Sharing History**:

- sh_objects → mwnf3.countries (country, pd_country)
- sh_objects → mwnf3.langs (via sh_objects_texts)
- sh_monuments → mwnf3.countries
- sh_partners → mwnf3.countries
- rel\_\* tables reference mwnf3.objects/monuments (indirectly, if sh items duplicate them)

**Thematic Gallery**:

- thg_gallery → mwnf3.projects (mwnf3_project_id - optional FK)
- thg_gallery_mwnf3_objects → mwnf3.objects (non-lang columns!)
- thg_gallery_mwnf3_monuments → mwnf3.monuments (non-lang columns!)
- thg_objects → mwnf3.countries
- thg_objects → mwnf3.langs (via thg_objects_texts)
- thg_monuments → mwnf3.countries
- thg_partners → mwnf3.countries
- thg_objects_mwnf3_tags → mwnf3.dynasties
- thg_monuments_mwnf3_tags → mwnf3.dynasties

### Cross-Schema Item References

**THG References SH**:

- thg_gallery_sh_objects → sh_objects
- thg_gallery_sh_monuments → sh_monuments

**Import Implication**: Must import in order: mwnf3 → sh → thg

---

## Mapping to New Model

### Projects → Contexts

**Sharing History**:

- sh_projects → Context
- backward_compatibility: `mwnf3_sharing_history:sh_projects:{project_id}`

**Thematic Gallery**:

- thg_projects → Context
- backward_compatibility: `mwnf3_thematic_gallery:thg_projects:{project_id}`

### Hierarchical Collections

**Sharing History - 3 Levels**:

1. **Exhibition** → Collection (parent_id: NULL or project Collection)
   - backward_compatibility: `mwnf3_sharing_history:sh_exhibitions:{exhibition_id}`
2. **Theme** → Collection (parent_id: Exhibition Collection UUID)
   - backward_compatibility: `mwnf3_sharing_history:sh_exhibition_themes:{theme_id}`
3. **Subtheme** → Collection (parent_id: Theme Collection UUID)
   - backward_compatibility: `mwnf3_sharing_history:sh_exhibition_subthemes:{subtheme_id}`

**Thematic Gallery - Self-Referencing**:

- **Gallery** → Collection (parent_id: NULL or parent gallery Collection UUID)
  - backward_compatibility: `mwnf3_thematic_gallery:thg_gallery:{gallery_id}`
  - If parent_gallery_id IS NULL: top-level Collection
  - If parent_gallery_id IS NOT NULL: child Collection with parent_id resolved via backward_compatibility

### Partners

**Sharing History**:

- sh_partners → Partner
- backward_compatibility: `mwnf3_sharing_history:sh_partners:{partners_id}`
- Map partner_category to Partner.type

**Thematic Gallery**:

- thg_partners → Partner
- backward_compatibility: `mwnf3_thematic_gallery:thg_partners:{partners_id}`

**Deduplication**: Check if sh_partners/thg_partners duplicate mwnf3.museums/institutions

- Compare name, country, address
- Use working_number, inventory_id if available
- May require manual reconciliation

### Items - Objects

**Sharing History**:

- sh_objects (base) + sh_objects_texts (translations) → Item + ItemTranslation
- **ONE Item** per sh_objects record (normalized model!)
- **MULTIPLE ItemTranslation** records per Item (one per sh_objects_texts row)
- backward_compatibility: `mwnf3_sharing_history:sh_objects:{project_id}:{country}:{number}`
- context_id: Resolved from project_id or exhibition/theme/subtheme if linked
- partner_id: Resolved from partners_id → sh_partners → Partner UUID

**Thematic Gallery**:

- thg_objects (base) + thg_objects_texts (translations) → Item + ItemTranslation
- backward_compatibility: `mwnf3_thematic_gallery:thg_objects:{partners_id}:{number}`
- **CRITICAL**: Different PK structure (no project_id!)
- context_id: Resolved from gallery membership
- partner_id: Resolved from partners_id → thg_partners → Partner UUID

### Items - Monuments

**Sharing History**:

- sh_monuments + sh_monuments_texts → Item + ItemTranslation
- backward_compatibility: `mwnf3_sharing_history:sh_monuments:{project_id}:{country}:{number}`

**Thematic Gallery**:

- thg_monuments + thg_monuments_texts → Item + ItemTranslation
- backward_compatibility: `mwnf3_thematic_gallery:thg_monuments:{partners_id}:{number}`

### Items - Monument Details

**Sharing History**:

- sh_monument_details + sh_monument_detail_texts → Item (type: detail) + ItemTranslation
- parent_id: Resolved from monument (project_id, country, partners_id, monument_id)
- backward_compatibility: `mwnf3_sharing_history:sh_monument_details:{project_id}:{country}:{partners_id}:{monument_id}:{detail_id}`

**Thematic Gallery**:

- thg_monument_details + thg_monument_detail_texts → Item (type: detail)
- parent_id: Resolved from monument (partners_id, number)
- backward_compatibility: `mwnf3_thematic_gallery:thg_monument_details:{partners_id}:{monument_number}:{detail_id}`

### Contextual Translations

**Key Concept**: Items in sh/thg may have CONTEXTUAL descriptions for specific exhibitions

**Strategy**:

1. When item is in multiple collections (exhibitions/galleries), same Item record
2. Create ItemTranslation with context_id = Collection UUID for that exhibition/gallery
3. Standard ItemTranslation: context_id = default project Context
4. Contextual ItemTranslation: context_id = specific exhibition/gallery Collection

**Example**:

- mwnf3 object "Vase XYZ" has standard description
- sh exhibition "Islamic Art" includes vase with adapted description
- Create 2 ItemTranslation sets:
  - context_id = mwnf3 project Context (standard description)
  - context_id = "Islamic Art" exhibition Collection (contextual description)

### Collection-Item Relationships

**Sharing History**:

- rel_objects_nc_exhibitions, rel_objects_nc_themes, rel_objects_nc_subthemes → collection_item pivot
- Resolve sh_objects → Item UUID via backward_compatibility
- Resolve exhibition/theme/subtheme → Collection UUID via backward_compatibility
- Create collection_item record

**Thematic Gallery**:

- thg_gallery_mwnf3_objects → collection_item pivot
  - Resolve mwnf3.objects → Item UUID: `mwnf3:objects:{project_id}:{country}:{museum_id}:{number}`
  - Resolve thg_gallery → Collection UUID: `mwnf3_thematic_gallery:thg_gallery:{gallery_id}`
- thg_gallery_sh_objects → collection_item pivot
  - Resolve sh_objects → Item UUID: `mwnf3_sharing_history:sh_objects:{project_id}:{country}:{number}`
- thg_gallery_thg_objects → collection_item pivot
  - Resolve thg_objects → Item UUID: `mwnf3_thematic_gallery:thg_objects:{partners_id}:{number}`
- Similar for monuments

### Images

**Sharing History**:

- sh_object_images, sh_monument_images, sh_monument_detail_pictures → ImageUpload
- backward_compatibility: `mwnf3_sharing_history:sh_object_images:{project_id}:{country}:{number}:{image_number}`
- Image captions from \*\_image_texts tables → per-language captions

**Thematic Gallery**:

- thg_object_images, thg_monument_images, thg_monument_detail_pictures → ImageUpload
- backward_compatibility: `mwnf3_thematic_gallery:thg_object_images:{partners_id}:{number}:{image_number}`

### Tags

**Sharing History**:

- dynasty field in sh_objects_texts/sh_monuments_texts → Parse and create tag relationships
- Use mwnf3.dynasties as source (if sh doesn't have own tags)

**Thematic Gallery**:

- thg_tags → Tag records
- backward_compatibility: `mwnf3_thematic_gallery:thg_tags:{tag_id}`
- thg_objects_thg_tags, thg_objects_sh_tags, thg_objects_mwnf3_tags → item_tag pivot
- Resolve tags from all three sources via backward_compatibility

---

## Import Dependencies and Execution Order

### Prerequisites

**MUST import BEFORE sh/thg**:

1. mwnf3 Projects → Contexts + Collections (sh/thg may reference)
2. mwnf3 Partners → Partners (sh/thg partners may duplicate)
3. mwnf3 Items → Items (thg galleries reference mwnf3 items)
4. mwnf3 Tags → Tags (thg can reference mwnf3.dynasties as tags)

### Sharing History Import Order

1. sh_projects → Contexts + Collections
2. sh_partners → Partners (check for duplicates with mwnf3)
3. sh_exhibitions, sh_exhibition_themes, sh_exhibition_subthemes → Hierarchical Collections
4. sh*national_context*\* → Additional Collections
5. sh_objects (base) + sh_objects_texts → Items + ItemTranslations
6. sh_monuments (base) + sh_monuments_texts → Items + ItemTranslations
7. sh_monument_details (base) + sh_monument_detail_texts → Items (type: detail)
8. rel*objects_nc*\* → collection_item pivot records
9. sh_object_images, sh_monument_images → ImageUpload + item_images pivot
10. sh_hcr → Handle HCR records (need to understand purpose)

### Thematic Gallery Import Order

**AFTER Sharing History** (thg references sh items):

1. thg_projects → Contexts + Collections
2. thg_partners → Partners (check for duplicates)
3. thg_gallery → Hierarchical Collections (self-referencing with parent_gallery_id)
4. thg_tags → Tags
5. thg_objects (base) + thg_objects_texts → Items + ItemTranslations
6. thg_monuments (base) + thg_monuments_texts → Items + ItemTranslations
7. thg_monument_details → Items (type: detail)
8. **Gallery pivot tables** → collection_item pivots:
   - thg_gallery_mwnf3_objects (references mwnf3 items)
   - thg_gallery_sh_objects (references sh items)
   - thg_gallery_thg_objects (references thg items)
   - Similar for monuments
9. **Tag pivot tables** → item_tag pivots:
   - thg_objects_thg_tags, thg_objects_sh_tags, thg_objects_mwnf3_tags
   - Similar for monuments
10. thg_object_images, thg_monument_images → ImageUpload
11. Item relationship tables (thg_objects_objects, etc.) → ItemItemLink

---

## Deduplication Strategy

### Critical Deduplication Points

1. **Partners**: sh_partners vs thg_partners vs mwnf3.museums/institutions
   - Check name + country + address
   - Use backward_compatibility to mark all instances
   - Create single Partner record, multiple backward_compatibility values if duplicates found

2. **Items**: sh_objects/monuments vs thg_objects/monuments vs mwnf3 objects/monuments
   - Check working_number + inventory_id + partner
   - Likely NOT duplicates (different PK structures suggest separate items)
   - BUT: Same real-world object may be recorded multiple times
   - Strategy: Import all, analyze afterward for semantic duplicates

3. **Collections**: Projects may overlap
   - sh_projects vs thg_projects vs mwnf3.projects
   - Use backward_compatibility, import all as separate Contexts

### Using backward_compatibility for Deduplication

**Before inserting**:

1. Check if backward_compatibility value already exists
2. If exists: Use existing UUID, don't create new record
3. If not exists: Create new record with backward_compatibility

**For cross-schema references**:

1. thg_gallery_mwnf3_objects references mwnf3.objects
2. Resolve item UUID: Look up Item where backward_compatibility = `mwnf3:objects:{project_id}:{country}:{museum_id}:{number}`
3. Create collection_item pivot with resolved UUIDs

---

## backward_compatibility Format Standards

### Sharing History

**Projects**: `mwnf3_sharing_history:sh_projects:{project_id}`

**Collections**:

- Exhibition: `mwnf3_sharing_history:sh_exhibitions:{exhibition_id}`
- Theme: `mwnf3_sharing_history:sh_exhibition_themes:{theme_id}`
- Subtheme: `mwnf3_sharing_history:sh_exhibition_subthemes:{subtheme_id}`
- National Context Exhibition: `mwnf3_sharing_history:sh_national_context_exhibitions:{nc_exhibition_id}`

**Partners**: `mwnf3_sharing_history:sh_partners:{partners_id}`

**Items** (NO language!):

- Object: `mwnf3_sharing_history:sh_objects:{project_id}:{country}:{number}`
- Monument: `mwnf3_sharing_history:sh_monuments:{project_id}:{country}:{number}`
- Detail: `mwnf3_sharing_history:sh_monument_details:{project_id}:{country}:{partners_id}:{monument_id}:{detail_id}`

**Images** (NO language, NO type):

- Object Image: `mwnf3_sharing_history:sh_object_images:{project_id}:{country}:{number}:{image_number}`
- Monument Image: `mwnf3_sharing_history:sh_monument_images:{project_id}:{country}:{number}:{image_number}`

### Thematic Gallery

**Projects**: `mwnf3_thematic_gallery:thg_projects:{project_id}`

**Collections**:

- Gallery: `mwnf3_thematic_gallery:thg_gallery:{gallery_id}`

**Partners**: `mwnf3_thematic_gallery:thg_partners:{partners_id}`

**Items** (NO language!):

- Object: `mwnf3_thematic_gallery:thg_objects:{partners_id}:{number}`
- Monument: `mwnf3_thematic_gallery:thg_monuments:{partners_id}:{number}`
- Detail: `mwnf3_thematic_gallery:thg_monument_details:{partners_id}:{monument_number}:{detail_id}`

**Images**:

- Object Image: `mwnf3_thematic_gallery:thg_object_images:{partners_id}:{number}:{image_number}`

**Tags**: `mwnf3_thematic_gallery:thg_tags:{tag_id}`

---

## Key Differences from mwnf3

### Normalization

**mwnf3**: Denormalized - language in PK, all content in single table

- objects PK: project_id, country, museum_id, number, **lang**

**sh**: Normalized - base table + separate texts table

- sh_objects PK: project_id, country, number (NO lang!)
- sh_objects_texts PK: project_id, country, number, lang

**thg**: Normalized like sh

- thg_objects PK: partners_id, number (even simpler - no project!)
- thg_objects_texts PK: partners_id, number, lang

### PK Structures

**mwnf3 objects**: 5 columns (project_id, country, museum_id, number, lang)
**sh objects**: 3 base + 4 texts (project_id, country, number + lang in texts)
**thg objects**: 2 base + 3 texts (partners_id, number + lang in texts)

**Impact on backward_compatibility**:

- mwnf3: Exclude lang from format
- sh: Base PK is already without lang - use as-is
- thg: Base PK is simplest - use as-is

### Partner References

**mwnf3**: Separate museums and institutions tables (composite PK with country)
**sh**: Unified sh_partners table (single PK, partner_category field)
**thg**: Unified thg_partners table (single PK)

### Collection Hierarchies

**mwnf3**: Flat - projects only, no hierarchy
**sh**: 3-level hierarchy (Exhibition → Theme → Subtheme)
**thg**: Self-referencing hierarchy (Gallery → Sub-gallery → Sub-sub-gallery, unlimited depth)

---

## Summary and Next Steps

### Phase 2 Complete

This analysis provides:

- Complete understanding of sh (148 tables) and thg (90 tables)
- Identification of normalized vs denormalized patterns
- Cross-schema FK relationships documented
- Hierarchical collection structures mapped
- Contextual translation strategy defined
- Deduplication strategy outlined

### Critical Findings

1. **Normalization**: sh and thg use base + texts pattern (DIFFERENT from mwnf3!)
2. **Cross-schema references**: thg heavily references mwnf3 AND sh items
3. **Hierarchical collections**: Both support multi-level organization
4. **Contextual translations**: Same item can have different descriptions in different contexts
5. **Tag system**: thg can use tags from thg, sh, AND mwnf3 (comprehensive tagging)
6. **Import order critical**: mwnf3 → sh → thg (dependencies)

### Ready for Phase 3

Can now proceed to:

- **Phase 3**: Analyze travel and explore schemas
- **Phase 4**: Quick scan of remaining schemas
- **Phase 5**: Create master mapping document consolidating all phases

---

**Analysis Status**: ✅ COMPLETE  
**Next Phase**: Phase 3 - Travel & Explore Analysis
