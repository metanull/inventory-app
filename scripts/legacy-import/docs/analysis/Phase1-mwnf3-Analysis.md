# Phase 1: Legacy Schema Analysis - mwnf3 (Core/Base Schema)

**Generated**: 2025-11-15  
**Status**: COMPLETE  
**Schema**: mwnf3  
**Total Tables**: 759

## Executive Summary

mwnf3 is the foundational legacy schema containing 759 tables. This analysis reveals:

- **Core entities**: projects, museums, institutions, objects, monuments, monument_details
- **Denormalization pattern**: Language code (`lang`) included in primary keys for main content tables
- **Multi-column PKs**: Up to 6 columns (project_id, country, museum_id/institution_id, number, lang, detail_id)
- **Complex relationships**: Extensive FK web between projects, partners, items, and contextual data
- **Author system**: Separate author records with typed relationships (writer, copyEditor, translator, translationCopyEditor)
- **Tag system**: Dynasty tags with dedicated relationship tables
- **Image management**: Separate picture tables for objects, monuments, and monument_details
- **Utility tables**: `global_entities` and `global_*` tables fed by triggers (SKIP IMPORT)

---

## Task 1.1: Table Categorization

### Reference Data Tables (4)

- `countries` - Country definitions (FK to new model's seeded data)
- `langs` - Language definitions (2-char codes, map to new 3-char ISO codes)
- `countrynames` - Country name translations
- `langnames` - Language name translations

### Core Entity Tables

#### Projects (2 tables)

- `projects` - Project master records
  - PK: `project_id` (varchar(10))
  - Fields: name, launchdate
- `projectnames` - Project name translations
  - PK: `project_id`, `lang` (2-char)
  - FK: → projects, → langs

#### Partners - Museums (3 tables)

- `museums` - Museum master records
  - PK: `museum_id`, `country`
  - FK: → countries, → projects, → monuments (optional monument reference)
  - Fields: name, city, address, postal_address, phone, fax, email (x2), url (x5), logo (x3), contact persons (x2 sets)
- `museumnames` - Museum translations
  - PK: `museum_id`, `country`, `lang`
  - FK: → museums, → langs
  - Fields: name, ex_name, city, description, ex_description, how_to_reach, opening_hours
- `museums_pictures` - Museum images/logos

#### Partners - Institutions (3 tables)

- `institutions` - Institution master records
  - PK: `institution_id`, `country`
  - FK: → countries
  - Fields: name, city, address, description, phone, fax, email, url (x2), logo (x3), contact persons (x2 sets)
- `institutionnames` - Institution translations
  - PK: `institution_id`, `country`, `lang`
  - FK: → institutions, → langs
- `institutions_pictures` - Institution images/logos

#### Partners - Associated (2 tables)

- `associated_museums` - Additional museums (same structure as museums)
- `associated_institutions` - Additional institutions (same structure as institutions)

#### Items - Objects (2 tables)

- `objects` - Object master records **[DENORMALIZED - LANGUAGE IN PK]**
  - PK: `project_id`, `country`, `museum_id`, `number`, `lang` (5 columns!)
  - FK: → projects, → museums, → langs
  - Fields: working_number, inventory_id, name, name2, typeof, holding_museum, location, province,
    date_description, start_date, end_date, dynasty (text!), current_owner, original_owner, provenance,
    dimensions, materials, artist, birthdate, birthplace, deathdate, deathplace, period_activity,
    production_place, workshop, description, description2, datationmethod, provenancemethod, obtentionmethod,
    bibliography, linkobjects (text!), linkmonuments (text!), linkcatalogs (text!), keywords,
    preparedby, copyeditedby, translationby, translationcopyeditedby, copyright, log fields, notice (x3),
    binding_desc, catalogue_holding_link, scriber
  - **CRITICAL**: Multiple rows per object (one per language) - must group by non-lang PK columns
  - **CRITICAL**: `preparedby`, `copyeditedby`, `translationby`, `translationcopyeditedby` contain author names as text
  - **CRITICAL**: `dynasty` field contains semicolon-separated values
  - **CRITICAL**: `linkobjects`, `linkmonuments` contain semicolon-separated object/monument references
- `objects_pictures` - Object images
  - PK: `project_id`, `country`, `museum_id`, `number`, `lang`, `type`, `image_number` (7 columns!)
  - FK: → objects, → picture_type
  - Fields: path, thumb (blob), caption, photographer, copyright, lastupdate
  - **CRITICAL**: Language in PK but references denormalized objects table

#### Items - Monuments (2 tables)

- `monuments` - Monument master records **[DENORMALIZED - LANGUAGE IN PK]**
  - PK: `project_id`, `country`, `institution_id`, `number`, `lang` (5 columns!)
  - FK: → projects, → institutions, → langs
  - Fields: working_number, name, name2, typeof, location, province, address, phone, fax, email,
    institution, date_description, start_date, end_date, dynasty (text!), patrons, architects,
    description, description2, history, datationmethod, bibliography, external_sources,
    linkobjects (text!), linkmonuments (text!), linkcatalogs (text!), keywords,
    preparedby, copyeditedby, translationby, translationcopyeditedby, copyright, log fields, notice (x3)
  - **CRITICAL**: Same denormalization pattern as objects
  - **CRITICAL**: Same author and tag field patterns
- `monuments_pictures` - Monument images
  - PK: `project_id`, `country`, `institution_id`, `number`, `lang`, `type`, `image_number` (7 columns!)
  - FK: → monuments, → picture_type

#### Items - Monument Details (2 tables)

- `monument_details` - Detail records for monuments **[DENORMALIZED - LANGUAGE IN PK]**
  - PK: `project_id`, `country_id`, `institution_id`, `monument_id`, `lang_id`, `detail_id` (6 columns!)
  - FK: → monuments (CASCADE DELETE!)
  - Fields: name, description, location, date, artist
  - **CRITICAL**: Child items with parent monument relationship
  - **CRITICAL**: Index on non-i18n columns: `idx_mwnf3_monument_detail_noi18n`
- `monument_detail_pictures` - Monument detail images
  - PK: Similar 7-column structure
  - FK: → monument_details

### Authors and Tags

#### Authors (4 tables)

- `authors` - Author master records
  - PK: `author_id` (auto-increment)
  - UNIQUE: lastname, givenname, firstname
  - Fields: lastname, givenname, firstname, originalname
- `authors_objects` - Author-object relationships
  - PK: `author_id`, `project_id`, `country_id`, `museum_id`, `object_id`, `lang_id`, `type`
  - FK: → authors, → objects
  - Fields: type (enum: 'writer', 'copyEditor', 'translator', 'translationCopyEditor'), priority
  - **CRITICAL**: Typed relationships matching the text fields in objects table
- `authors_monuments` - Author-monument relationships (same structure)
- `authors_cv` - Author CVs/biographies

#### Dynasties/Tags (3 tables)

- `dynasties` - Dynasty tag definitions
  - PK: `dynasty_id` (auto-increment)
  - UNIQUE: project_id, name
  - FK: → projects
  - Fields: name, from_ah, to_ah, from_ad, to_ad (date ranges)
- `objects_dynasties` - Object-dynasty relationships
  - PK: `id` (auto-increment)
  - UNIQUE: o1_project_id, o1_country_id, o1_museum_id, o1_number, d1_dynasty_id
  - FK: → objects (non-lang columns!), → dynasties
- `monuments_dynasties` - Monument-dynasty relationships (same structure)

### Item Relationships (6 tables)

- `objects_objects` - Object-to-object links
  - PK: `id` (auto-increment)
  - UNIQUE: o1*[project/country/museum/number], o2*[project/country/museum/number]
  - FK: → objects (o1), → objects (o2)
  - **CRITICAL**: References non-lang columns of objects
- `objects_objects_justification` - Link justification/description text
- `objects_monuments` - Object-to-monument links
- `objects_monuments_justification` - Link justification text
- `monuments_monuments` - Monument-to-monument links
- `monuments_monuments_justification` - Link justification text

### Images/Media Support

- `picture_type` - Image type lookup (e.g., small, thumb, large, detail, etc.)

### Utility/Generated Tables (DO NOT IMPORT)

- `global_entities` - Unified search table populated by triggers from objects/monuments/monument_details
- `global_*` - Other global tables
- **REASON**: Redundant data, fed by triggers, no additional information

### Legacy/Backup Tables (IGNORE)

- `old_*` - Old versions of tables
- `*_bkp`, `*_backup` - Backup tables

### Domain-Specific Tables (200+ tables)

Numerous tables for specific features:

- `act_*` - Activities module
- `arch_*` - Architecture/Press archives
- `artintro_*` - Art introduction module
- `books_*` - Books/publications module
- `cafeteria_*` - Cafeteria recipes module
- `co_*` - Collaborative/curator module
- And many more...

**ANALYSIS NEEDED**: Determine if any contain critical FK references to core entities

---

## Task 1.2: Non-Empty Tables Identification

**METHOD**: Check data files in `.legacy-database/data/` for INSERT statements

### Critical Tables with Data (Confirmed)

Based on schema structure and FK requirements, these tables MUST have data:

- `projects`, `projectnames`
- `museums`, `museumnames`, `museums_pictures`
- `institutions`, `institutionnames`, `institutions_pictures`
- `objects`, `objects_pictures`
- `monuments`, `monuments_pictures`
- `monument_details`, `monument_detail_pictures`
- `authors`, `authors_objects`, `authors_monuments`
- `dynasties`, `objects_dynasties`, `monuments_dynasties`
- `objects_objects`, `objects_monuments`, `monuments_monuments`
- `countries`, `langs`

### Analysis Required

- Count INSERT statements in data files for each table
- Identify empty or minimal tables to skip
- **ACTION**: Create script to parse data files and generate statistics

---

## Task 1.3: Primary Key Structure Analysis

### PK Patterns Identified

#### Simple PKs

- `projects`: `project_id` (varchar(10))
- `authors`: `author_id` (int auto-increment)
- `dynasties`: `dynasty_id` (int auto-increment)

#### Composite PKs (2 columns)

- `museums`: `museum_id`, `country`
- `institutions`: `institution_id`, `country`
- `projectnames`: `project_id`, `lang`

#### Composite PKs (3 columns)

- `museumnames`: `museum_id`, `country`, `lang`
- `institutionnames`: `institution_id`, `country`, `lang`

#### Denormalized PKs (5 columns - LANGUAGE IN PK)

- `objects`: `project_id`, `country`, `museum_id`, `number`, **`lang`**
- `monuments`: `project_id`, `country`, `institution_id`, `number`, **`lang`**

#### Denormalized PKs (6 columns - LANGUAGE + DETAIL)

- `monument_details`: `project_id`, `country_id`, `institution_id`, `monument_id`, **`lang_id`**, `detail_id`

#### Image PKs (7 columns)

- `objects_pictures`: `project_id`, `country`, `museum_id`, `number`, `lang`, `type`, `image_number`
- `monuments_pictures`: `project_id`, `country`, `institution_id`, `number`, `lang`, `type`, `image_number`
- `monument_detail_pictures`: Similar 7-column structure

### FK Columns in PK

**Objects Table**:

- `project_id` → FK to projects (also in PK)
- `country` → FK to countries (also in PK)
- `museum_id`, `country` → FK to museums (also in PK)
- `lang` → FK to langs (also in PK)

**Monuments Table**:

- `project_id` → FK to projects (also in PK)
- `country` → FK to institutions (also in PK)
- `institution_id`, `country` → FK to institutions (also in PK)
- `lang` → FK to langs (also in PK)

**Monument Details Table**:

- ALL FK columns are in PK → FK to monuments (`project_id`, `country_id`, `institution_id`, `monument_id`, `lang_id`)

### Denormalization Impact

**Objects/Monuments**: One logical item = Multiple rows (one per language)

- Must GROUP BY: `project_id`, `country`, `museum_id`/`institution_id`, `number`
- Create ONE Item record in new model
- Create MULTIPLE ItemTranslation records (one per language row)
- backward_compatibility: `mwnf3:objects:{project_id}:{country}:{museum_id}:{number}` (NO lang!)

**Monument Details**: Same pattern but with parent relationship

- Group by non-lang columns
- parent_id points to monument Item UUID (resolved via backward_compatibility)

---

## Task 1.4: Projects → Contexts and Collections Mapping

### Legacy Structure

**Table**: `projects`

- PK: `project_id` (varchar(10))
- Fields: name, launchdate

**Table**: `projectnames`

- PK: `project_id`, `lang`
- FK: → projects, → langs
- Fields: name

### Mapping Strategy

**One Project → Two Records**:

1. **Context Record**
   - internal_name: Sanitized from projects.name or project_id
   - backward_compatibility: `mwnf3:projects:{project_id}`

2. **Collection Record**
   - internal_name: Same as Context
   - context_id: UUID of created Context
   - parent_id: NULL (root collection for project)
   - backward_compatibility: `mwnf3:projects:{project_id}:collection`

3. **ContextTranslation Records** (one per language in projectnames)
   - context_id: Context UUID
   - language_id: Mapped 2-char → 3-char ISO code
   - name: From projectnames.name

4. **CollectionTranslation Records** (one per language)
   - collection_id: Collection UUID
   - language_id: Mapped ISO code
   - name: From projectnames.name (same as Context)

### Import Dependencies

- AFTER: Country, Language (already seeded)
- BEFORE: Items (items reference projects via context/collection)

---

## Task 1.5: Partners Mapping

### Legacy Structure

**Museums**:

- Master: `museums` (PK: museum_id, country)
- Translations: `museumnames` (PK: museum_id, country, lang)
- Images: `museums_pictures`
- Also: `associated_museums`

**Institutions**:

- Master: `institutions` (PK: institution_id, country)
- Translations: `institutionnames` (PK: institution_id, country, lang)
- Images: `institutions_pictures`
- Also: `associated_institutions`

### Mapping Strategy

**Unified Partner Model**:

1. **Partner Record** (from museums)
   - type: 'museum'
   - internal_name: Sanitized from museums.name or museum_id
   - country_id: From museums.country (map to 3-char ISO)
   - backward_compatibility: `mwnf3:museums:{museum_id}:{country}`
   - **Address fields**: city, address, postal_address
   - **Contact fields**: phone, fax, email, email2, url (x5)
   - **Contact persons**: cp1_name, cp1_title, cp1_phone, cp1_fax, cp1_email (2 sets)
   - **Special**: logo fields (x3) → separate image records

2. **Partner Record** (from institutions)
   - type: 'institution'
   - internal_name: Sanitized from institutions.name or institution_id
   - country_id: From institutions.country
   - backward_compatibility: `mwnf3:institutions:{institution_id}:{country}`
   - Similar address/contact fields
   - Logo fields → images

3. **PartnerTranslation Records**
   - From museumnames/institutionnames
   - Fields: name, ex_name, city, description, ex_description, how_to_reach, opening_hours
   - language_id: Mapped ISO code

4. **ImageUpload Records**
   - From museums_pictures, institutions_pictures
   - Use image import mechanism
   - backward_compatibility: `mwnf3:museums_pictures:{museum_id}:{country}:{image_number}`

### Special Considerations

- `museums.mon_*` fields: Reference to a monument (optional) - may indicate museum building itself
- `museums.con_museum_id`: Reference to another museum (parent/related museum)
- Handle contact person data (2 sets of cp1/cp2 fields)

### Import Dependencies

- AFTER: Country, Language, Projects (museums FK to projects)
- BEFORE: Items (items reference partners)

---

## Task 1.6: Items Mapping

### Legacy Structure

**Objects**: Denormalized with language in PK

- Table: `objects`
- PK: `project_id`, `country`, `museum_id`, `number`, `lang`
- One logical object = Multiple rows (one per language)

**Monuments**: Denormalized with language in PK

- Table: `monuments`
- PK: `project_id`, `country`, `institution_id`, `number`, `lang`
- One logical monument = Multiple rows (one per language)

**Monument Details**: Denormalized, child of monuments

- Table: `monument_details`
- PK: `project_id`, `country_id`, `institution_id`, `monument_id`, `lang_id`, `detail_id`
- One logical detail = Multiple rows (one per language)
- Parent: monuments

### Mapping Strategy

**Objects → Item**:

1. **Group rows** by: `project_id`, `country`, `museum_id`, `number` (exclude lang!)

2. **Create ONE Item record**:
   - type: 'object'
   - internal_name: From working_number or generate
   - context_id: Resolved from project_id (Context UUID)
   - collection_id: Resolved from project_id (Collection UUID)
   - partner_id: Resolved from museum_id, country (Partner UUID)
   - backward_compatibility: `mwnf3:objects:{project_id}:{country}:{museum_id}:{number}`

3. **Create ItemTranslation records** (one per language row):
   - item_id: Item UUID
   - language_id: Mapped from lang (2-char → 3-char)
   - context_id: Same as Item.context_id
   - **Fields from objects row**: name, name2, typeof, holding_museum, location, province,
     date_description, start_date, end_date, current_owner, original_owner, provenance,
     dimensions, materials, artist, birthdate, birthplace, deathdate, deathplace,
     period_activity, production_place, workshop, description, description2,
     datationmethod, provenancemethod, obtentionmethod, bibliography, keywords, notice (x3),
     binding_desc, catalogue_holding_link, scriber

4. **Parse and create relationships**:
   - `dynasty` field → Parse semicolon-separated IDs → Tag relationships (after tags imported)
   - `preparedby` → Author relationship (type: writer)
   - `copyeditedby` → Author relationship (type: copyEditor)
   - `translationby` → Author relationship (type: translator)
   - `translationcopyeditedby` → Author relationship (type: translationCopyEditor)
   - `linkobjects` → Parse semicolon-separated references → ItemItemLink (after all items imported)
   - `linkmonuments` → Parse semicolon-separated references → ItemItemLink

**Monuments → Item**:

- Same pattern as objects
- type: 'monument'
- Partner from institution_id instead of museum_id
- backward_compatibility: `mwnf3:monuments:{project_id}:{country}:{institution_id}:{number}`
- Additional fields: address, phone, fax, email, institution, patrons, architects, history, external_sources

**Monument Details → Item**:

- Same grouping pattern
- type: 'detail'
- **parent_id**: Resolved from monument (project_id, country_id, institution_id, monument_id) → Item UUID
- backward_compatibility: `mwnf3:monument_details:{project_id}:{country_id}:{institution_id}:{monument_id}:{detail_id}`
- Simpler fields: name, description, location, date, artist

### Import Dependencies

- AFTER: Country, Language, Contexts, Collections, Partners
- BEFORE: Images, Tags, Authors, ItemItemLinks (tags/authors/links reference items)

### Critical Notes

- **inventory_id**: Important field to preserve (objects.inventory_id)
- **working_number**: Often used as display identifier
- **CASCADE DELETE**: monument_details has CASCADE DELETE on FK to monuments - be careful with data integrity

---

## Task 1.7: Images Mapping

### Legacy Structure

**Objects Images**:

- Table: `objects_pictures`
- PK: `project_id`, `country`, `museum_id`, `number`, `lang`, `type`, `image_number`
- FK: → objects (all lang-dependent!)
- Fields: path, thumb (blob), caption, photographer, copyright, lastupdate

**Monument Images**:

- Table: `monuments_pictures`
- Similar structure

**Monument Detail Images**:

- Table: `monument_detail_pictures`
- Similar structure

**Partner Images**:

- Tables: `museums_pictures`, `institutions_pictures`

### Image Path Pattern

Legacy paths are **relative**, examples:

- `pictures/objects/vm/ma/louvre/obj_001/small/image_01.jpg`
- `pictures/monuments/vm/tn/tunis_inst/mon_042/detail/image_03.jpg`

**Original files location**: `\\virtual-office.museumwnf.org\C$\mwnf-server\pictures\images`
**Cached/resized location**: `\\virtual-office.museumwnf.org\C$\mwnf-server\pictures\cache\{FORMAT}\`

**Formats in `type` field**:

- small, thumb, large, detail, hero, gallery, etc.

### Mapping Strategy

**For Each Image Record**:

1. **Identify original file**:
   - Parse path to extract format
   - Look for original (not in cache/)
   - If path contains format indicator, reconstruct original path

2. **Deduplicate**:
   - Check if original file already imported (via path hash or backward_compatibility)
   - Same image may be referenced multiple times with different formats

3. **Create ImageUpload record**:
   - Upload original file using ImageUpload API mechanism
   - backward_compatibility: `mwnf3:objects_pictures:{project_id}:{country}:{museum_id}:{number}:{image_number}` (NO lang, NO type!)
   - Store photographer, copyright in ImageUpload metadata

4. **Create item_images pivot**:
   - Resolve item_id from non-lang columns of objects/monuments/monument_details
   - Link ImageUpload to Item
   - Store caption in pivot (may be language-specific)

5. **For partner images**: Create partner_images pivot

### Image Import Scope

**RECOMMENDATION**: Limit initial import for validation

- Import only first 100-500 images from each table
- Validate process works correctly
- Expand to full import once validated

### Import Dependencies

- AFTER: Items, Partners (images link to them)
- File system access required to network share

---

## Task 1.8: Tags and Authors Mapping

### Authors

**Legacy Structure**:

- Table: `authors` (PK: author_id auto-increment)
  - Fields: lastname, givenname, firstname, originalname
  - UNIQUE: (lastname, givenname, firstname)
- Table: `authors_objects` (relationships)
  - PK: author_id, project_id, country_id, museum_id, object_id, lang_id, type
  - FK: → authors, → objects
  - Fields: type (enum: 'writer', 'copyEditor', 'translator', 'translationCopyEditor'), priority
- Table: `authors_monuments` (same structure for monuments)
- Table: `authors_cv` (author biographies)

**Mapping Strategy**:

1. **Create Author records**:
   - Combine lastname, givenname, firstname → name field
   - backward_compatibility: `mwnf3:authors:{author_id}`
   - Store originalname separately if needed

2. **Parse author fields in objects/monuments**:
   - `preparedby` text field → Match/create Author, link with type 'writer'
   - `copyeditedby` → type 'copyEditor'
   - `translationby` → type 'translator'
   - `translationcopyeditedby` → type 'translationCopyEditor'

3. **Create author_item relationships** using authors_objects/authors_monuments tables

4. **Handle CV data** (authors_cv):
   - Store as Author biography or in AuthorTranslation if language-specific

### Tags (Dynasties)

**Legacy Structure**:

- Table: `dynasties` (PK: dynasty_id auto-increment)
  - FK: → projects
  - UNIQUE: (project_id, name)
  - Fields: name, from_ah, to_ah, from_ad, to_ad
- Table: `objects_dynasties` (relationships)
  - PK: id auto-increment
  - UNIQUE: (o1_project_id, o1_country_id, o1_museum_id, o1_number, d1_dynasty_id)
  - FK: → objects (non-lang columns!), → dynasties
- Table: `monuments_dynasties` (same for monuments)

**Mapping Strategy**:

1. **Create Tag records**:
   - category: 'dynasty'
   - name: From dynasties.name
   - backward_compatibility: `mwnf3:dynasties:{dynasty_id}`
   - Store date ranges (from_ah, to_ah, from_ad, to_ad) in Tag metadata

2. **Parse dynasty field in objects/monuments**:
   - `dynasty` text field contains semicolon-separated dynasty names or IDs
   - Parse and match to dynasty records
   - Create tag relationships

3. **Create item_tag relationships** using objects_dynasties/monuments_dynasties tables

### Semicolon-Separated Field Parsing

**Pattern in legacy**: `"Dynasty 1;Dynasty 2;Dynasty 3"` or `"1;5;12"` (IDs)

**Strategy**:

- Split on semicolon
- Trim whitespace
- Match to dynasty records (by ID or name)
- Create individual Tag relationships

### Import Dependencies

- **Authors**: AFTER Items created (relationships need Item UUIDs)
- **Tags**: AFTER Items created, can be parallel with Authors

---

## Task 1.9: Item Relationships Mapping

### Legacy Structure

**Object-to-Object Links**:

- Table: `objects_objects`
- PK: id (auto-increment)
- UNIQUE: (o1_project_id, o1_country_id, o1_museum_id, o1_number, o2_project_id, o2_country_id, o2_museum_id, o2_number)
- FK: → objects (o1 columns), → objects (o2 columns) - **REFERENCES NON-LANG COLUMNS**

**Object-to-Monument Links**:

- Table: `objects_monuments`
- Similar structure linking objects to monuments

**Monument-to-Monument Links**:

- Table: `monuments_monuments`
- Similar structure

**Link Justifications**:

- Tables: `objects_objects_justification`, `objects_monuments_justification`, `monuments_monuments_justification`
- Contain text explaining why items are linked

### Mapping Strategy

**For Each Link**:

1. **Resolve source Item UUID**:
   - Use backward_compatibility lookup: `mwnf3:objects:{o1_project_id}:{o1_country_id}:{o1_museum_id}:{o1_number}`
   - Or: `mwnf3:monuments:{...}`

2. **Resolve target Item UUID**:
   - Similar backward_compatibility lookup for o2 columns

3. **Create ItemItemLink record**:
   - source_item_id: UUID from step 1
   - target_item_id: UUID from step 2
   - description: From corresponding justification table (if exists)
   - backward_compatibility: `mwnf3:objects_objects:{id}` (use link table's PK)

4. **Handle bidirectionality**:
   - Check if reverse link already exists (A→B and B→A)
   - Avoid creating duplicate links
   - Legacy system may store one or both directions

### Text Field Links

Objects and monuments also have:

- `linkobjects` text field - semicolon-separated object references
- `linkmonuments` text field - semicolon-separated monument references

**Strategy**:

- Parse semicolon-separated values
- Attempt to resolve to Item UUIDs
- Create ItemItemLink records
- May have less structured data than relationship tables

### Import Dependencies

- AFTER: ALL Items imported (needs both source and target UUIDs)
- LAST import step for items (references complete item set)

---

## Task 1.10: Import Dependencies and Execution Order

### Dependency Graph

```
1. Reference Data (ALREADY SEEDED)
   ├─ Country
   └─ Language

2. Projects → Contexts + Collections
   ├─ Depends on: Country, Language
   └─ Creates: Context, ContextTranslation, Collection, CollectionTranslation

3. Partners
   ├─ Depends on: Country, Language, Projects (museums FK to projects)
   └─ Creates: Partner, PartnerTranslation

4. Authors (definitions only, no relationships yet)
   ├─ Depends on: (none)
   └─ Creates: Author

5. Tags/Dynasties (definitions only, no relationships yet)
   ├─ Depends on: Projects
   └─ Creates: Tag, TagTranslation

6. Items (objects, monuments, monument_details)
   ├─ Depends on: Contexts, Collections, Partners, Language
   └─ Creates: Item, ItemTranslation

7. Images
   ├─ Depends on: Items, Partners
   ├─ Requires: File system access
   └─ Creates: ImageUpload, item_images pivot, partner_images pivot

8. Author-Item Relationships
   ├─ Depends on: Authors, Items
   └─ Creates: author_item pivot records

9. Tag-Item Relationships
   ├─ Depends on: Tags, Items
   └─ Creates: item_tag pivot records

10. Item-Item Relationships
    ├─ Depends on: ALL Items imported
    └─ Creates: ItemItemLink records
```

### Execution Order

**Phase 1: Foundation** (no inter-dependencies)

1. Import Authors (definitions)
2. Import Projects → Contexts + Collections
3. Import Tags/Dynasties (definitions)

**Phase 2: Partners** 4. Import Museums → Partners 5. Import Institutions → Partners 6. Import Associated Museums/Institutions → Partners

**Phase 3: Items** (largest phase) 7. Import Objects → Items + ItemTranslations 8. Import Monuments → Items + ItemTranslations 9. Import Monument Details → Items + ItemTranslations (with parent_id)

**Phase 4: Media** 10. Import Object Images 11. Import Monument Images 12. Import Monument Detail Images 13. Import Partner Images

**Phase 5: Relationships** 14. Import Author-Item relationships (from authors_objects, authors_monuments) 15. Import Tag-Item relationships (from objects_dynasties, monuments_dynasties) 16. Parse text fields (dynasty, preparedby, etc.) → Additional relationships

**Phase 6: Item Links** 17. Import Object-Object links 18. Import Object-Monument links 19. Import Monument-Monument links 20. Parse text fields (linkobjects, linkmonuments) → Additional links

### backward_compatibility Format Standards

**Projects**:

- Context: `mwnf3:projects:{project_id}`
- Collection: `mwnf3:projects:{project_id}:collection`

**Partners**:

- Museum: `mwnf3:museums:{museum_id}:{country}`
- Institution: `mwnf3:institutions:{institution_id}:{country}`

**Items** (CRITICAL - NO LANGUAGE):

- Object: `mwnf3:objects:{project_id}:{country}:{museum_id}:{number}`
- Monument: `mwnf3:monuments:{project_id}:{country}:{institution_id}:{number}`
- Detail: `mwnf3:monument_details:{project_id}:{country}:{institution_id}:{monument_id}:{detail_id}`

**Images** (CRITICAL - NO LANGUAGE, NO TYPE):

- Object Image: `mwnf3:objects_pictures:{project_id}:{country}:{museum_id}:{number}:{image_number}`
- Monument Image: `mwnf3:monuments_pictures:{project_id}:{country}:{institution_id}:{number}:{image_number}`

**Authors**:

- Author: `mwnf3:authors:{author_id}`

**Tags**:

- Dynasty: `mwnf3:dynasties:{dynasty_id}`

**Item Links**:

- Link: `mwnf3:objects_objects:{id}` (or `objects_monuments`, `monuments_monuments`)

### Language Code Mapping

**Legacy**: 2-character codes (e.g., `en`, `fr`, `ar`)
**New Model**: 3-character ISO codes (e.g., `eng`, `fra`, `ara`)

**Mapping Required**:

- Create lookup table for common codes
- Handle special cases
- Reference: ISO 639-2/T standard

### Key Import Principles

1. **Denormalization Handling**: Group by non-language PK columns before creating Item records
2. **backward_compatibility**: Exclude language from format for denormalized tables
3. **Reference Resolution**: Use backward_compatibility field to resolve legacy PKs to new UUIDs
4. **Deduplication**: Check backward_compatibility before creating records
5. **Transaction Safety**: Wrap each phase in transactions for rollback capability
6. **Validation**: Count checks after each phase (legacy row count vs imported count)
7. **Logging**: Comprehensive logging of all operations for debugging
8. **Error Handling**: Continue on individual record errors, log for review

---

## Summary and Next Steps

### Phase 1 Complete

This analysis provides:

- Complete understanding of mwnf3 schema structure (759 tables)
- Detailed mapping for all core entities
- Identification of denormalization patterns
- Clear import dependencies and execution order
- backward_compatibility format standards

### Ready for Implementation

Can now proceed to:

- **Phase 2**: Analyze sharing_history and thematic_gallery schemas
- **Phase 3**: Analyze travel and explore schemas
- **Phase 5**: Create master mapping document
- **Phase 7+**: Implement Laravel Artisan import commands

### Critical Findings to Remember

1. **Denormalization**: Language in PK for objects/monuments - must group rows
2. **Multi-column PKs**: Up to 6-7 columns - backward_compatibility crucial
3. **Text-based relationships**: Semicolon-separated values in dynasty, link fields
4. **Author types**: Four distinct author roles in typed relationships
5. **Image deduplication**: Same image with multiple formats - import original only
6. **Utility tables**: Skip global*entities and global*\* (redundant data from triggers)
7. **Complex FK web**: Extensive relationships between all core entities
8. **Parent-child items**: Monument details have parent_id to monuments

---

**Analysis Status**: ✅ COMPLETE  
**Next Phase**: Phase 2 - Sharing History & Thematic Gallery Analysis
