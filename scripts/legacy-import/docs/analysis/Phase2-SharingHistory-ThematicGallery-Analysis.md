# Phase 2: Legacy Schema Analysis - Sharing History & Thematic Gallery

**Generated**: 2025-11-16  
**Status**: COMPLETE  
**Schemas**: mwnf3_sharing_history (148 tables), mwnf3_thematic_gallery (90 tables)  
**Total Tables**: 238

## Executive Summary

Sharing History (SH) and Thematic Gallery (THG) are extension schemas built on top of mwnf3. Key findings:

- **Different normalization**: SH/THG separate base records from translations (more normalized than mwnf3)
- **Cross-schema references**: Both schemas extensively reference mwnf3 entities
- **Hierarchical collections**: SH exhibitions with themes/subthemes, THG galleries with themes
- **Deduplication critical**: Many tables link to existing mwnf3 items - must NOT duplicate
- **Contextual translations**: Same items with different descriptions per exhibition/gallery
- **Gallery item associations**: Multiple tables linking galleries to items from mwnf3, sh, and explore schemas
- **Own entities**: Both schemas have their own objects/monuments that are NOT in mwnf3

---

## Task 2.1: Sharing History Tables Catalog

### Core Entities (Sharing History)

#### Projects (2 tables)

- `sh_projects` - SH-specific projects
  - PK: `project_id` (varchar(11))
  - Fields: name, addeddate, new_status, show, category ('SP'|'PP'), exhibition_landing_url, portal_image
  - **NO FK to mwnf3.projects** - independent project definitions
- `sh_project_names` - Project name translations
  - PK: `project_id`, `lang`

#### Exhibitions (National Context) - Hierarchical Collections

- `sh_national_context_countries` - Top-level: Countries in exhibition
  - PK: `country`
  - FK: → mwnf3.countries
- `sh_national_context_exhibitions` - Level 2: Exhibitions per country
  - PK: `country`, `exhibition_id`
  - FK: → sh_national_context_countries, → sh_exhibitions
- `sh_national_context_themes` - Level 3: Themes per exhibition
  - PK: `country`, `exhibition_id`, `theme_id`
- `sh_national_context_subthemes` - Level 4: Subthemes per theme
  - PK: `country`, `exhibition_id`, `theme_id`, `subtheme_id`
- Translation/image tables for each level: `*_texts`, `*_images`

**Hierarchy**: Country → Exhibition → Theme → Subtheme (4 levels)

#### Exhibitions (My Exhibitions) - User-Created

- `myexh_users` - User accounts for custom exhibitions
- `myexh_exhibitions`, `myexh_exhibitionnames` - User exhibitions
- `myexh_exhibition_themes`, `myexh_exhibition_themenames` - Themes
- `myexh_exhibition_subthemes`, `myexh_exhibition_subthemenames` - Subthemes
- `myexh_exhibition_images`, `myexh_exhibition_theme_images`, `myexh_exhibition_subtheme_images`
- `myexh_curator_team` - Team members for exhibitions

**Same 4-level hierarchy** as national context

#### Partners (3 tables)

- `sh_partners` - **NORMALIZED** partner master
  - PK: `partners_id` (varchar(11))
  - FK: → mwnf3.countries
  - Fields: country, partner_category, name, city, address, phone, fax, email (x2), url (x5), logo (x3), contact persons (x2 sets), geoCoordinates, zoom
  - **CRITICAL**: Similar structure to mwnf3.museums/institutions but INDEPENDENT records
- `sh_partner_names` - Partner translations
  - PK: `partners_id`, `lang`
  - Fields: name, ex_name, city, description, ex_description, how_to_reach, opening_hours
- `sh_partner_pictures` - Partner images
- `sh_partner_associated`, `sh_partner_further_associated` - Related partners

### Items (SH) - Normalized Structure

#### Objects (3 tables)

- `sh_objects` - **NORMALIZED** object master
  - PK: `project_id`, `country`, `number` (**NO LANG**)
  - FK: → sh_projects, → sh_partners, → mwnf3.countries
  - Fields: partners_id, working_number, inventory_id, start_date, end_date, display_status, pd_country
  - **CRITICAL**: No language in PK - more normalized than mwnf3.objects
- `sh_objects_texts` - Object translations (separate table)
  - PK: `project_id`, `country`, `number`, `lang`
  - FK: → sh_objects, → mwnf3.langs
  - Fields: All text fields from mwnf3.objects PLUS second_name, third_name, archival
  - **Same fields as mwnf3.objects**: name, name2, typeof, description, dynasty, keywords, preparedby, etc.
- `sh_object_images` - Object images
  - PK: `project_id`, `country`, `number`, `type`, `image_number`
  - FK: → sh_objects
- `sh_object_image_texts` - Image captions per language

#### Monuments (3 tables)

- `sh_monuments` - **NORMALIZED** monument master
  - PK: `project_id`, `country`, `number`
  - FK: → sh_projects, → sh_partners, → mwnf3.countries
  - **Same normalization** as sh_objects
- `sh_monuments_texts` - Monument translations
  - PK: `project_id`, `country`, `number`, `lang`
  - FK: → sh_monuments, → mwnf3.langs
  - Fields: Similar to mwnf3.monuments
- `sh_monument_images`, `sh_monument_image_texts` - Monument images

#### Monument Details (2 tables)

- `sh_monument_details` - Normalized detail master
  - PK: `project_id`, `country`, `number`, `detail_id`
  - FK: → sh_monuments
- `sh_monument_detail_texts` - Detail translations
  - PK: `project_id`, `country`, `number`, `detail_id`, `lang`

#### Item Document Links

- `sh_objects_document` - Links objects to external documents/PDFs
- `sh_objects_ngm_projects` - Links to NGM (Next Generation Museum) projects
- `sh_objects_video_audio` - Video/audio media attachments

### Relationships (SH)

#### Item-to-Collection Relationships

Pattern: `rel_[object|monument]_[exhibitions|themes|subthemes]`

- `rel_objects_exhibitions` - Object → Exhibition
- `rel_objects_themes` - Object → Theme
- `rel_objects_subthemes` - Object → Subtheme
- `rel_objects_nc_exhibitions` - Object → National Context Exhibition
- `rel_objects_nc_themes` - Object → NC Theme
- `rel_objects_nc_subthemes` - Object → NC Subtheme
- Similar tables for monuments

Each has corresponding `*_justification` table with descriptive text

#### Item-to-Item Relationships

- `rel_object_objects` - Object to object links
- `rel_object_monuments` - Object to monument links
- `rel_monuments_object` - Monument to object links
- `rel_monuments_monuments` - Monument to monument links
- Each with `*_justifications` table

#### MyExh Relationships

Similar relationship tables prefixed with `myexh_rel_*` for user exhibitions

### Authors & Team

- `sh_authors` - **CRITICAL: References mwnf3.authors**
  - Contains author IDs that match mwnf3.authors (deduplication via backward_compatibility)
- `rel_curatorteam_exhibition` - Curator assignments to exhibitions
- `myexh_curator_team` - Teams for user exhibitions

### Supporting Content

- `sh_project_about_*` - About pages: team, topics, historical background
- `sh_sponsor*` - Sponsors and sponsorship categories
- `sh_user_login` - User authentication (probably skip)
- `sh_projects_styles` - CSS/styling definitions

---

## Task 2.2: Thematic Gallery Tables Catalog

### Core Entities (Thematic Gallery)

#### Projects (3 tables)

- `thg_projects` - THG-specific projects
  - PK: `project_id` (varchar(10))
  - Fields: name, addeddate, status
  - **Independent from mwnf3.projects**
- `thg_project_names` - Project translations
- `thg_project_type` - Project type classifications

#### Galleries - Hierarchical (with parent_id)

- `thg_gallery` - **HIERARCHICAL** gallery structure
  - PK: `gallery_id` (auto-increment)
  - FK: → thg_projects, → thg_gallery (self-reference), → mwnf3.projects (optional), → mwnf3.trsl_groups
  - Fields:
    - `parent_gallery_id` - Self-referencing for sub-galleries
    - `project_id` - THG project
    - `mwnf3_project_id` - Optional reference to corresponding mwnf3 project
    - name, sort_order, status, featured, new_expire_date, live_date
    - Images: image, banner_image, portal_image, homepage_image
    - Items: banner_item, homepage_item
    - Flags: has_timeline, has_country_timeline
    - Translation groups: i18n_group_id, i18n_common_group_id
- `thg_gallery_lang` - Gallery name translations
  - PK: `gallery_id`, `lang`

**Hierarchy**: Gallery can have parent_gallery_id → creates tree structure

#### Themes - Within Galleries

- `theme` - Themes and sub-themes
  - PK: `gallery_id`, `theme_id`
  - FK: → thg_gallery, → theme (self-reference for parent_theme_id)
  - Fields: parent_theme_id, display_order
  - **CRITICAL**: Parent/child relationship via parent_theme_id
- `theme_i18n` - Theme translations
- `theme_item` - Items within themes
  - PK: `gallery_id`, `theme_id`, `item_id`
- `theme_item_i18n` - Item descriptions per language
- `theme_item_related` - Related items
- `theme_item_related_i18n` - Related item descriptions
- `theme_cover_image`, `theme_audio`, `theme_video` - Theme media
- `theme_tag` - Tags for themes

**Hierarchy**: Gallery → Theme → Sub-theme (via parent_theme_id) → Items

#### Partners (4 tables)

- `thg_partners` - THG partner master
  - PK: `partners_id` (varchar(10))
  - FK: → mwnf3.countries
  - **Similar structure to sh_partners**
- `thg_partner_names` - Partner translations
- `thg_partner_pictures` - Partner images
- `thg_partner_categories`, `thg_partner_category_lang` - Partner categorization

### Items (THG) - Normalized Structure

#### Objects (5 tables)

- `thg_objects` - **NORMALIZED** object master
  - PK: `partners_id`, `number` (**NO project_id, NO lang**)
  - FK: → thg_partners, → mwnf3.countries
  - Fields: country, working_number, inventory_id, start_date, end_date
  - **CRITICAL**: Simpler PK than sh_objects (no project_id)
- `thg_objects_texts` - Object translations
  - PK: `partners_id`, `number`, `lang`
  - Fields: Same as mwnf3.objects (no preparedby/copyeditedby fields)
- `thg_object_images` - Object images
- `thg_object_image_texts` - Image captions per language
- `thg_objects_document` - Document attachments (similar to SH)

#### Monuments (5 tables)

- `thg_monuments` - Normalized monument master
  - PK: `partners_id`, `number`
  - **Same normalization as thg_objects**
- `thg_monuments_texts` - Monument translations
- `thg_monument_images`, `thg_monument_image_texts` - Images
- `thg_monument_details`, `thg_monument_detail_texts` - Details
- `thg_monument_detail_pictures`, `thg_monument_detail_picture_texts` - Detail images

### Gallery-Item Associations (THG)

**CRITICAL**: THG galleries can contain items from multiple schemas:

- `thg_gallery_mwnf3_objects` - Gallery → mwnf3.objects
  - FK: → mwnf3.objects (non-lang columns)
- `thg_gallery_mwnf3_monuments` - Gallery → mwnf3.monuments
- `thg_gallery_sh_objects` - Gallery → sh_objects
- `thg_gallery_sh_monuments` - Gallery → sh_monuments
- `thg_gallery_thg_objects` - Gallery → thg_objects
- `thg_gallery_thg_monuments` - Gallery → thg_monuments
- `thg_gallery_explore_monuments` - Gallery → explore_exploremonument
- `thg_gallery_travel_monuments` - Gallery → travel monuments

**Pattern**: Gallery can reference items from ANY schema (mwnf3, sh, thg, explore, travel)

### Relationships (THG)

#### Item-to-Item Links

- `thg_objects_objects`, `thg_objects_objects_justification` - Object-object links
- `thg_objects_monuments`, `thg_objects_monuments_justification` - Object-monument links
- `thg_monuments_objects`, `thg_monuments_objects_justification` - Monument-object links
- `thg_monuments_monuments`, `thg_monuments_monuments_justification` - Monument-monument links

### Tags (THG)

- `thg_tags` - THG-specific tag definitions
  - PK: `tag_id` (auto-increment)
  - FK: → thg_tag_types
  - Fields: name, description, translations
- `thg_tag_types` - Tag type classifications
- `thg_tags_sequence` - Tag ordering
- `thg_objects_thg_tags` - Object-tag relationships (THG tags)
- `thg_objects_sh_tags` - Object-tag relationships (SH tags)
- `thg_objects_mwnf3_tags` - Object-tag relationships (MWNF3 tags/dynasties)
- Similar tables for monuments

**CRITICAL**: Items can be tagged with tags from multiple schemas

### Authors (THG)

- `thg_authors` - THG author definitions
  - **Similar to mwnf3.authors** - check for duplicates via backward_compatibility
- `thg_authors_cv` - Author biographies
- `thg_authors_objects` - Author-object relationships
- `thg_authors_monuments` - Author-monument relationships
- `thg_authors_projects` - Author-project relationships

### Supporting Content

- `thg_sponsors`, `thg_sponsor_projects` - Sponsorship
- `thg_visitor_feedback` - User feedback (probably skip)
- Exhibition/contributor modules - similar to SH structure

---

## Task 2.3: Non-Empty Tables Analysis

**Method**: Cross-reference with data files

### High Priority Tables (Likely Populated)

**Sharing History**:

- sh_projects, sh_project_names
- sh_partners, sh_partner_names
- sh_objects, sh_objects_texts, sh_object_images
- sh_monuments, sh_monuments_texts, sh_monument_images
- sh*national_context*\* hierarchy tables
- rel\_\* relationship tables

**Thematic Gallery**:

- thg_projects, thg_project_names
- thg_partners, thg_partner_names
- thg_gallery, thg_gallery_lang
- theme, theme_i18n, theme_item
- thg_objects, thg_objects_texts, thg_object_images
- thg_monuments, thg_monuments_texts, thg_monument_images
- thg*gallery*_*objects, thg_gallery*_\_monuments (cross-schema associations)
- thg*tags, thg*\*\_tags relationship tables

### Lower Priority (May Be Empty)

- myexh\_\* tables (user-generated content - may be minimal)
- Visitor feedback tables
- Some sponsor/team tables

---

## Task 2.4: Projects → Contexts and Collections Mapping

### Sharing History Projects

**Table**: `sh_projects`

- PK: `project_id` (varchar(11))
- **Independent from mwnf3.projects** - separate project definitions

**Mapping Strategy**:

1. **Context Record**:
   - internal_name: From sh_projects.name
   - backward_compatibility: `sh:projects:{project_id}`
   - **CHECK**: Does project_id match any mwnf3.projects.project_id?
     - If YES: Consider linking or merging contexts
     - If NO: Create new independent Context

2. **Collection Record** (root collection):
   - context_id: SH Context UUID
   - parent_id: NULL
   - backward_compatibility: `sh:projects:{project_id}:collection`

### Sharing History Exhibitions - Hierarchical Collections

**National Context Hierarchy**:

```
sh_national_context_countries (Level 1)
  └─ sh_national_context_exhibitions (Level 2)
      └─ sh_national_context_themes (Level 3)
          └─ sh_national_context_subthemes (Level 4)
```

**Mapping**:

1. **Country Collection**:
   - parent_id: Project Collection UUID
   - backward_compatibility: `sh:nc_countries:{country}`
   - CollectionTranslation: From country names

2. **Exhibition Collection**:
   - parent_id: Country Collection UUID
   - backward_compatibility: `sh:nc_exhibitions:{country}:{exhibition_id}`
   - CollectionTranslation: From sh_national_context_exhibition_texts

3. **Theme Collection**:
   - parent_id: Exhibition Collection UUID
   - backward_compatibility: `sh:nc_themes:{country}:{exhibition_id}:{theme_id}`
   - CollectionTranslation: From sh_national_context_theme_texts

4. **Subtheme Collection**:
   - parent_id: Theme Collection UUID
   - backward_compatibility: `sh:nc_subthemes:{country}:{exhibition_id}:{theme_id}:{subtheme_id}`
   - CollectionTranslation: From sh_national_context_subtheme_texts

**My Exhibitions**: Same pattern with `myexh_` prefix

### Thematic Gallery Projects

**Table**: `thg_projects`

- Similar mapping to sh_projects
- backward_compatibility: `thg:projects:{project_id}`

### Thematic Gallery Galleries - Hierarchical Collections

**Table**: `thg_gallery` with `parent_gallery_id` (self-referencing)

**Mapping**:

1. **Gallery Collection** (parent_gallery_id = NULL):
   - parent_id: Project Collection UUID (if project_id set)
   - backward_compatibility: `thg:gallery:{gallery_id}`
   - **CRITICAL**: Check `mwnf3_project_id` field
     - If set: Link to corresponding mwnf3 Context/Collection
     - Creates relationship between THG gallery and mwnf3 project

2. **Sub-Gallery Collection** (parent_gallery_id != NULL):
   - parent_id: Resolved from parent_gallery_id → Gallery Collection UUID
   - backward_compatibility: `thg:gallery:{gallery_id}`
   - Creates tree structure matching legacy hierarchy

### Thematic Gallery Themes

**Table**: `theme` with `parent_theme_id` (self-referencing within gallery)

**Mapping**:

1. **Theme Collection** (parent_theme_id = NULL):
   - parent_id: Gallery Collection UUID
   - backward_compatibility: `thg:theme:{gallery_id}:{theme_id}`
   - CollectionTranslation: From theme_i18n

2. **Sub-Theme Collection** (parent_theme_id != NULL):
   - parent_id: Resolved from parent_theme_id → Theme Collection UUID
   - backward_compatibility: `thg:theme:{gallery_id}:{theme_id}`
   - Creates sub-theme hierarchy

### Import Dependencies

- AFTER: mwnf3 Contexts/Collections (for cross-references)
- BEFORE: Items (items link to collections)

---

## Task 2.5: Partners Mapping

### Sharing History Partners

**Table**: `sh_partners`

- PK: `partners_id` (varchar(11))
- **Independent records** (not references to mwnf3)

**Deduplication Strategy**:

1. **Check for duplicates**:
   - Compare sh_partners.name, country with existing mwnf3.museums/institutions
   - Use fuzzy matching or exact name+country match
   - Check partner_category field for type (museums/archives/universities/etc.)

2. **If duplicate found**:
   - DO NOT create new Partner
   - Use existing Partner UUID from mwnf3
   - Store backward_compatibility: `sh:partners:{partners_id}` as alternate reference
   - Link sh_objects/monuments to existing Partner

3. **If new partner**:
   - Create Partner record
   - type: Based on partner_category (museum/institution/archive/library/other)
   - backward_compatibility: `sh:partners:{partners_id}`

**Mapping**:

- Similar fields to mwnf3.museums: name, city, address, phone, email, urls, logos, contact persons
- PartnerTranslation: From sh_partner_names

### Thematic Gallery Partners

**Table**: `thg_partners`

- Similar structure to sh_partners
- **Same deduplication strategy**
- backward_compatibility: `thg:partners:{partners_id}`

**Cross-Schema Checks**:

1. Check against mwnf3.museums/institutions
2. Check against sh_partners (avoid duplicating SH partners)
3. Only create new Partner if genuinely new

### Import Dependencies

- AFTER: mwnf3 Partners (for deduplication checks)
- BEFORE: Items (items reference partners)

---

## Task 2.6: Items Mapping

### Sharing History Items - Normalized Structure

**Critical Difference from mwnf3**: SH uses separate master/translation tables

#### Objects

**Master**: `sh_objects`

- PK: `project_id`, `country`, `number` (NO language)
- Already normalized!

**Translations**: `sh_objects_texts`

- PK: `project_id`, `country`, `number`, `lang`
- One row per language per object

**Mapping Strategy**:

1. **Check if referencing mwnf3 object**:
   - Look for matching: project_id (if matches mwnf3), country, partner/museum, working_number, inventory_id
   - If match found: **DO NOT create new Item** - use existing mwnf3 Item UUID
   - Store backward_compatibility: `sh:objects:{project_id}:{country}:{number}` as alternate reference

2. **If new object** (not in mwnf3):
   - Create ONE Item record:
     - type: 'object'
     - context_id: Resolved from sh project_id
     - collection*id: Resolved from exhibitions/themes (via rel*\* tables)
     - partner_id: Resolved from sh_partners.partners_id
     - backward_compatibility: `sh:objects:{project_id}:{country}:{number}`

3. **Create ItemTranslation records**:
   - One per row in sh_objects_texts
   - **CRITICAL**: context_id should reflect exhibition/theme context
   - If item is in multiple exhibitions: Create multiple ItemTranslations with different context_ids
   - Fields: name, name2, typeof, description, etc. (from sh_objects_texts)

**Special Fields**:

- second_name, third_name, archival - SH-specific fields
- Store in ItemTranslation or in custom metadata

#### Monuments & Details

Same normalized pattern:

- sh_monuments (master) + sh_monuments_texts (translations)
- sh_monument_details (master) + sh_monument_detail_texts (translations)
- Same deduplication check against mwnf3.monuments
- backward_compatibility: `sh:monuments:{project_id}:{country}:{number}`

### Thematic Gallery Items - Normalized Structure

**Master**: `thg_objects`

- PK: `partners_id`, `number` (simpler than SH - no project_id)

**Translations**: `thg_objects_texts`

- PK: `partners_id`, `number`, `lang`

**Mapping Strategy**:

1. **Check if referencing mwnf3/sh object**:
   - Match via: partners_id, working_number, inventory_id
   - **THG galleries can reference items from multiple schemas** (see gallery association tables)
   - Check thg_gallery_mwnf3_objects, thg_gallery_sh_objects for existing references

2. **If new object**:
   - Create Item with backward_compatibility: `thg:objects:{partners_id}:{number}`
   - context_id: Resolved from associated gallery
   - partner_id: Resolved from thg_partners.partners_id

3. **Contextual Translations**:
   - THG items appear in galleries with gallery-specific descriptions
   - Create ItemTranslation per gallery context
   - context_id: Gallery Collection UUID (from thg_gallery)

**Similar for monuments**: `thg_monuments` + `thg_monuments_texts`

### Collection-Item Relationships

**Sharing History**: Via `rel_*` tables

- rel_objects_exhibitions, rel_objects_themes, rel_objects_subthemes
- Create collection_item pivot records
- Store justification text from `*_justification` tables

**Thematic Gallery**: Via `thg_gallery_*` tables

- thg_gallery_mwnf3_objects → Link gallery to existing mwnf3 items
- thg_gallery_sh_objects → Link gallery to existing sh items
- thg_gallery_thg_objects → Link gallery to thg items
- thg_gallery_explore_monuments, thg_gallery_travel_monuments → Cross-schema links

**Pattern**: One item can belong to multiple collections with different contextual descriptions

### Import Dependencies

- AFTER: mwnf3 Items (for deduplication)
- AFTER: SH/THG Contexts, Collections, Partners
- BEFORE: Images, Tags, Authors, Relationships

---

## Task 2.7: Images Mapping

### Sharing History Images

**Objects**:

- `sh_object_images` - Image records
  - PK: `project_id`, `country`, `number`, `type`, `image_number`
  - FK: → sh_objects
  - Fields: path, photographer, copyright, lastupdate
- `sh_object_image_texts` - Image captions per language
  - PK: `project_id`, `country`, `number`, `type`, `image_number`, `lang`
  - Fields: caption, copyright

**Monuments**: Similar structure (sh_monument_images, sh_monument_image_texts)

**Partners**:

- `sh_partner_pictures` - Partner logos/images

**Collections**:

- `sh_national_context_exhibition_images` - Exhibition images
- `sh_national_context_theme_images` - Theme images
- `sh_national_context_subtheme_images` - Subtheme images
- Similar for myexh\_\* tables

**Mapping Strategy**:

1. **Resolve item_id**:
   - From sh_objects/sh_monuments via backward_compatibility
   - May reference mwnf3 item if object was deduplicated

2. **Check for duplicate image**:
   - Parse path to get original file
   - Check if same file already imported from mwnf3
   - Deduplicate via file path hash

3. **Create ImageUpload** (if new):
   - Upload original file
   - backward_compatibility: `sh:object_images:{project_id}:{country}:{number}:{image_number}`
   - Store photographer, copyright

4. **Create item_images pivot**:
   - Link ImageUpload to Item
   - Store caption from sh_object_image_texts (language-specific)

### Thematic Gallery Images

**Objects**:

- `thg_object_images` + `thg_object_image_texts`
  - Simpler PK: `partners_id`, `number`, `type`, `image_number`

**Monuments**: Similar structure

**Partners**:

- `thg_partner_pictures`

**Galleries**:

- `thg_gallery` table has multiple image fields:
  - image (thumbnail)
  - banner_image
  - portal_image
  - homepage_image
- `thg_gallery_logos` - Gallery logos/branding

**Themes**:

- `theme_cover_image` - Theme cover images
- `theme_audio`, `theme_video` - Theme media

**Mapping**: Same deduplication and import strategy as SH

### Import Dependencies

- AFTER: Items, Partners, Collections (images link to them)
- File system access required

---

## Task 2.8: Tags and Authors Mapping

### Sharing History Authors

**Table**: `sh_authors`

- **CRITICAL**: Check structure - likely references mwnf3.authors

**Deduplication Strategy**:

1. Check sh_authors for author_id values
2. Match against mwnf3.authors via backward_compatibility
3. If match: Use existing Author UUID
4. If new: Create Author with backward_compatibility: `sh:authors:{author_id}`

**Relationships**: Same author tables as mwnf3

- sh_authors_objects, sh_authors_monuments (if they exist)
- Type: writer, copyEditor, translator, translationCopyEditor

### Sharing History Tags

**Structure**: May use dynasties or have SH-specific tags

- Check for sh_dynasties or similar tag tables
- Create Tag records with category
- backward_compatibility: `sh:tags:{tag_id}` or `sh:dynasties:{dynasty_id}`

**Relationships**:

- Parse dynasty, keywords fields from sh_objects_texts, sh_monuments_texts
- Create item_tag relationships

### Thematic Gallery Authors

**Table**: `thg_authors`

- Similar to mwnf3.authors structure
- **Deduplication critical**: Check against mwnf3.authors AND sh_authors
- backward_compatibility: `thg:authors:{author_id}`

**Relationships**:

- thg_authors_objects, thg_authors_monuments
- thg_authors_projects - Author contributions to projects

### Thematic Gallery Tags

**Tables**:

- `thg_tags` - THG-specific tag definitions
  - PK: `tag_id` (auto-increment)
  - FK: → thg_tag_types
  - Fields: name, description, translations
- `thg_tag_types` - Tag type classifications

**Relationships** - **CRITICAL CROSS-SCHEMA TAGGING**:

- `thg_objects_thg_tags` - THG items → THG tags
- `thg_objects_sh_tags` - THG items → SH tags (cross-schema!)
- `thg_objects_mwnf3_tags` - THG items → MWNF3 dynasties (cross-schema!)
- Similar for monuments

**Mapping Strategy**:

1. Create Tag records for thg_tags
   - category: From thg_tag_types
   - backward_compatibility: `thg:tags:{tag_id}`

2. Create tag relationships:
   - Resolve item_id from THG, SH, or MWNF3 via backward_compatibility
   - Resolve tag_id from appropriate schema
   - Create item_tag pivot records

### Import Dependencies

- **Authors**: AFTER Items (relationships need Item UUIDs), check mwnf3/sh authors for deduplication
- **Tags**: AFTER Items, check all schemas for existing tags

---

## Task 2.9: Hierarchical Collections Mapping

### Sharing History Collections

**National Context** - 4-level hierarchy:

```
Country (sh_national_context_countries)
  └─ Exhibition (sh_national_context_exhibitions)
      └─ Theme (sh_national_context_themes)
          └─ Subtheme (sh_national_context_subthemes)
```

**Collection Records**:

1. **Country Collection**:
   - internal_name: Country name
   - parent_id: SH Project Collection UUID
   - type: 'country'
   - backward_compatibility: `sh:nc_countries:{country}`

2. **Exhibition Collection**:
   - internal_name: From sh_national_context_exhibition_texts
   - parent_id: Country Collection UUID
   - type: 'exhibition'
   - backward_compatibility: `sh:nc_exhibitions:{country}:{exhibition_id}`
   - CollectionTranslation: Multiple languages from \*\_texts table

3. **Theme Collection**:
   - parent_id: Exhibition Collection UUID
   - type: 'theme'
   - backward_compatibility: `sh:nc_themes:{country}:{exhibition_id}:{theme_id}`

4. **Subtheme Collection**:
   - parent_id: Theme Collection UUID
   - type: 'subtheme'
   - backward_compatibility: `sh:nc_subthemes:{country}:{exhibition_id}:{theme_id}:{subtheme_id}`

**My Exhibitions**: Same 4-level pattern

- myexh_exhibitions → myexh_exhibition_themes → myexh_exhibition_subthemes
- backward_compatibility: `sh:myexh:exhibitions:{exhibition_id}`

### Thematic Gallery Collections

**Galleries** - Self-referencing hierarchy via `parent_gallery_id`:

```
Gallery (parent_gallery_id = NULL)
  └─ Sub-Gallery (parent_gallery_id = {parent_id})
      └─ Sub-Sub-Gallery (recursive)
```

**Collection Records**:

1. **Root Gallery**:
   - parent_id: THG Project Collection UUID (if project_id set)
   - backward_compatibility: `thg:gallery:{gallery_id}`
   - **Check mwnf3_project_id**: If set, also link to corresponding mwnf3 Context

2. **Sub-Gallery**:
   - parent_id: Resolved from parent_gallery_id
   - backward_compatibility: `thg:gallery:{gallery_id}`
   - Creates tree structure

**Themes within Galleries** - Self-referencing via `parent_theme_id`:

```
Gallery
  └─ Theme (parent_theme_id = NULL)
      └─ Sub-Theme (parent_theme_id = {theme_id})
```

**Collection Records**:

1. **Theme**:
   - parent_id: Gallery Collection UUID
   - backward_compatibility: `thg:theme:{gallery_id}:{theme_id}`

2. **Sub-Theme**:
   - parent_id: Theme Collection UUID
   - backward_compatibility: `thg:theme:{gallery_id}:{theme_id}`

### Collection-Item Associations

**Sharing History**: Via `rel_*` tables

- Parse all rel*objects*_, rel*monuments*_ tables
- Create collection_item pivot records
- Store justification text

**Thematic Gallery**: Via `thg_gallery_*` association tables

- thg_gallery_mwnf3_objects, thg_gallery_mwnf3_monuments
- thg_gallery_sh_objects, thg_gallery_sh_monuments
- thg_gallery_thg_objects, thg_gallery_thg_monuments
- thg_gallery_explore_monuments, thg_gallery_travel_monuments

**Pattern**: Resolve item_id via backward_compatibility from ANY schema

### Import Dependencies

- AFTER: Projects, Parents (for root collections)
- Import in tree order: Level 1 → Level 2 → Level 3 → Level 4
- BEFORE: Collection-item associations (need collection UUIDs)

---

## Task 2.10: Import Dependencies and Execution Order

### Dependency Graph

```
1. Projects & Contexts
   ├─ sh_projects → Context + Collection
   └─ thg_projects → Context + Collection

2. Partners (with deduplication)
   ├─ sh_partners → Partner (check mwnf3 duplicates)
   └─ thg_partners → Partner (check mwnf3 + sh duplicates)

3. Authors (with deduplication)
   ├─ sh_authors → Author (check mwnf3 duplicates)
   └─ thg_authors → Author (check mwnf3 + sh duplicates)

4. Tags (with cross-schema support)
   ├─ SH tags (if separate from dynasties)
   └─ thg_tags → Tag (category from thg_tag_types)

5. Hierarchical Collections
   ├─ SH National Context: Countries → Exhibitions → Themes → Subthemes
   ├─ SH My Exhibitions: Exhibitions → Themes → Subthemes
   ├─ THG Galleries: Gallery → Sub-Gallery (recursive)
   └─ THG Themes: Theme → Sub-Theme (within galleries)

6. Items (with deduplication and contextual translations)
   ├─ sh_objects → Item (check mwnf3 duplicates) + ItemTranslation (per context)
   ├─ sh_monuments → Item (check mwnf3 duplicates) + ItemTranslation (per context)
   ├─ sh_monument_details → Item (parent_id from sh_monuments) + ItemTranslation
   ├─ thg_objects → Item (check mwnf3 + sh duplicates) + ItemTranslation (per gallery)
   ├─ thg_monuments → Item (check mwnf3 + sh duplicates) + ItemTranslation (per gallery)
   └─ thg_monument_details → Item (parent_id from thg_monuments) + ItemTranslation

7. Collection-Item Associations
   ├─ Parse rel_* tables (SH)
   ├─ Parse thg_gallery_* tables (THG)
   └─ Create collection_item pivot records

8. Images (with deduplication)
   ├─ sh_object_images, sh_monument_images, sh_partner_pictures
   ├─ sh_national_context_*_images (collection images)
   ├─ thg_object_images, thg_monument_images, thg_partner_pictures
   ├─ thg_gallery images (various image fields)
   └─ theme images (theme_cover_image, etc.)

9. Author-Item Relationships
   ├─ sh_authors_objects, sh_authors_monuments
   └─ thg_authors_objects, thg_authors_monuments, thg_authors_projects

10. Tag-Item Relationships (with cross-schema support)
    ├─ Parse dynasty/keywords fields from *_texts tables
    ├─ thg_objects_thg_tags, thg_objects_sh_tags, thg_objects_mwnf3_tags
    └─ Similar for monuments

11. Item-Item Relationships
    ├─ SH: rel_object_objects, rel_object_monuments, rel_monuments_*
    ├─ THG: thg_objects_objects, thg_objects_monuments, thg_monuments_*
    └─ Create ItemItemLink records with justification text
```

### Execution Order

**Phase 2A: Foundation**

1. Import SH projects → Contexts + Collections
2. Import THG projects → Contexts + Collections

**Phase 2B: Partners (Deduplicated)** 3. Import sh_partners → Check mwnf3, create Partners 4. Import thg_partners → Check mwnf3 + sh, create Partners

**Phase 2C: Authors & Tags (Deduplicated)** 5. Import sh_authors → Check mwnf3, create Authors 6. Import thg_authors → Check mwnf3 + sh, create Authors 7. Import thg_tags → Create Tags

**Phase 2D: Hierarchical Collections** 8. Import SH National Context hierarchy (4 levels) 9. Import SH My Exhibitions hierarchy (3-4 levels) 10. Import THG Galleries hierarchy (recursive) 11. Import THG Themes hierarchy (within galleries)

**Phase 2E: Items (Deduplicated with Contextual Translations)** 12. Import sh_objects → Check mwnf3, create Items + ItemTranslations 13. Import sh_monuments → Check mwnf3, create Items + ItemTranslations 14. Import sh_monument_details → Parent from sh_monuments 15. Import thg_objects → Check mwnf3 + sh, create Items + ItemTranslations 16. Import thg_monuments → Check mwnf3 + sh, create Items + ItemTranslations 17. Import thg_monument_details → Parent from thg_monuments

**Phase 2F: Collection-Item Associations** 18. Parse SH rel*\* tables → collection_item pivots 19. Parse THG thg_gallery*\* tables → collection_item pivots

**Phase 2G: Images (Deduplicated)** 20. Import SH item images → Deduplicate with mwnf3 images 21. Import SH collection images 22. Import THG item images → Deduplicate with mwnf3 + SH images 23. Import THG collection images

**Phase 2H: Relationships** 24. Import SH author-item relationships 25. Import THG author-item relationships (including cross-schema tags) 26. Import SH tag-item relationships 27. Import THG tag-item relationships (cross-schema)

**Phase 2I: Item Links** 28. Import SH item-item relationships 29. Import THG item-item relationships

### backward_compatibility Format Standards

**Sharing History**:

- Project: `sh:projects:{project_id}`
- Collection: `sh:projects:{project_id}:collection`
- NC Country: `sh:nc_countries:{country}`
- NC Exhibition: `sh:nc_exhibitions:{country}:{exhibition_id}`
- NC Theme: `sh:nc_themes:{country}:{exhibition_id}:{theme_id}`
- NC Subtheme: `sh:nc_subthemes:{country}:{exhibition_id}:{theme_id}:{subtheme_id}`
- MyExh Exhibition: `sh:myexh:exhibitions:{exhibition_id}`
- Partner: `sh:partners:{partners_id}`
- Object: `sh:objects:{project_id}:{country}:{number}` (NO lang)
- Monument: `sh:monuments:{project_id}:{country}:{number}`
- Detail: `sh:monument_details:{project_id}:{country}:{number}:{detail_id}`
- Image: `sh:object_images:{project_id}:{country}:{number}:{image_number}` (NO lang, NO type)
- Author: Check if references mwnf3.authors, else `sh:authors:{author_id}`

**Thematic Gallery**:

- Project: `thg:projects:{project_id}`
- Collection: `thg:projects:{project_id}:collection`
- Gallery: `thg:gallery:{gallery_id}`
- Theme: `thg:theme:{gallery_id}:{theme_id}`
- Partner: `thg:partners:{partners_id}`
- Object: `thg:objects:{partners_id}:{number}` (NO lang)
- Monument: `thg:monuments:{partners_id}:{number}`
- Detail: `thg:monument_details:{partners_id}:{number}:{detail_id}`
- Image: `thg:object_images:{partners_id}:{number}:{image_number}`
- Author: `thg:authors:{author_id}` (check mwnf3 + sh first)
- Tag: `thg:tags:{tag_id}`

### Key Import Principles

1. **Deduplication is Critical**: Check backward_compatibility before creating records
2. **Cross-Schema References**: THG and SH can reference mwnf3 items - use existing Item UUIDs
3. **Contextual Translations**: Same item can have multiple ItemTranslations with different context_ids
4. **Hierarchical Collections**: Import parent collections before children
5. **Normalized Structure**: SH/THG separate master from translations - simpler than mwnf3 denormalization
6. **Collection-Item Associations**: One item can belong to multiple collections (exhibitions/themes/galleries)
7. **Cross-Schema Tagging**: THG items can be tagged with tags from mwnf3, sh, or thg
8. **Gallery Associations**: THG galleries can contain items from ANY schema (mwnf3, sh, thg, explore, travel)

---

## Summary and Next Steps

### Phase 2 Complete

This analysis provides:

- Complete understanding of SH (148 tables) and THG (90 tables) schemas
- Identification of normalized vs denormalized structures
- Detailed deduplication strategies for Partners, Authors, Items, Images
- Hierarchical collection mappings (4-level SH, recursive THG)
- Cross-schema reference patterns
- Contextual translation strategies
- backward_compatibility format standards

### Critical Findings

1. **Normalized Structure**: SH/THG separate base records from translations (cleaner than mwnf3)
2. **Cross-Schema References**: Both schemas extensively reference mwnf3 entities
3. **Deduplication Essential**: Must check existing mwnf3 records before creating new Partners/Items
4. **Contextual Translations**: Same item with different descriptions per exhibition/gallery (multiple ItemTranslations with different context_ids)
5. **Hierarchical Collections**:
   - SH: Country → Exhibition → Theme → Subtheme (4 levels)
   - THG: Gallery → Sub-Gallery (recursive), Theme → Sub-Theme
6. **Cross-Schema Tagging**: THG items can be tagged with tags from any schema
7. **Gallery Associations**: THG galleries can contain items from mwnf3, sh, thg, explore, travel

### Ready for Next Phase

Can now proceed to:

- **Phase 3**: Analyze travel and explore schemas (critical monument data)
- **Phase 4**: Analyze remaining schemas
- **Phase 5**: Create master mapping document
- **Phase 7+**: Implement Laravel Artisan import commands

---

**Analysis Status**: ✅ COMPLETE  
**Next Phase**: Phase 3 - Travel & Explore Schemas Analysis
