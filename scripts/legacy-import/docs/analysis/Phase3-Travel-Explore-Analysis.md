# Phase 3: Legacy Schema Analysis - Travel & Explore

**Generated**: 2025-11-16  
**Status**: COMPLETE  
**Schemas**: mwnf3_travels (52 tables), mwnf3_explore (101 tables)  
**Total Tables**: 153

## Executive Summary

Travel and Explore are separate application schemas with very different structures but similar purposes (travel/tourism content):

- **mwnf3_travels**: Exhibition trails with itineraries, locations, monuments - **DENORMALIZED** (language in PK)
- **mwnf3_explore**: Geographic browsing by country/region/location - **NORMALIZED** (separate translations)
- **Critical**: explore contains **1808 monuments** (many unique, not in other schemas) - **HIGHEST PRIORITY**
- **Cross-schema references**: Both reference mwnf3.projects, mwnf3.monuments, and each other
- **Hierarchical structure**: 
  - Travel: Trail → Itinerary → Location → Monument (4 levels, denormalized)
  - Explore: Country → Region → Location → Monument (4 levels, normalized)
- **Travel-specific content**: Accommodation, agencies, guides, food, cultural events (probably skip)
- **Explore-specific content**: Themes, filters, itineraries, partner museums (mixed priority)

---

## Task 3.1: Travel Schema Tables Catalog

### Core Entities (mwnf3_travels)

#### Trails - Top-Level Collections **[DENORMALIZED]**
- `trails` - **EXHIBITION TRAIL master**
  - PK: `project_id`, `country`, `lang`, `number` (**LANGUAGE IN PK**)
  - FK: → mwnf3.projects, → mwnf3.countries, → mwnf3.museums, → mwnf3.langs
  - Fields: title, subtitle, description, curated_by, local_coordinator, photo_by, museum_id, region_territory
  - **Pattern**: Same denormalization as mwnf3.objects
- `trails0` - Backup/old version (IGNORE)
- `tr_trails_pictures` - Trail images

**Mapping**: Trail → Collection (with language handling like mwnf3.objects)

#### Itineraries - Child Collections **[DENORMALIZED]**
- `tr_itineraries` - Itineraries within trails
  - PK: `project_id`, `country`, `number`, `lang`, `trail_id` (**LANGUAGE IN PK**)
  - FK: → trails (all fields including lang)
  - Fields: title, introduction, description, description2, author, prepared_by, days, path
  - **CRITICAL**: FK includes language → references denormalized trails table
- `tr_itineraries_pictures` - Itinerary images

**Mapping**: Itinerary → Collection (parent: Trail Collection)

#### Locations - Sub-Collections **[DENORMALIZED]**
- `tr_locations` - Locations within itineraries
  - PK: `lang`, `project_id`, `country`, `trail_id`, `itinerary_id`, `number` (**LANGUAGE IN PK**)
  - FK: → trails, → tr_itineraries (with language)
  - Fields: title, introduction, description, how_to_reach, info, contact, prepared_by
- `tr_locations_pictures` - Location images

**Mapping**: Location → Collection (parent: Itinerary Collection)

**Hierarchy**: Trail → Itinerary → Location (3 collection levels, all denormalized with lang in PK)

#### Monuments - Items within Locations **[DENORMALIZED]**
- `tr_monuments` - Monuments at locations
  - PK: `project_id`, `country`, `trail_id`, `itinerary_id`, `location_id`, `number`, `lang` (**LANGUAGE IN PK**)
  - FK: → tr_locations (with language)
  - Fields: title, how_to_reach, info, contact, description, prepared_by
  - **CRITICAL**: These are likely references to mwnf3.monuments or new lightweight records
- `tr_monuments_pictures` - Monument images

**Mapping**: Monument → Item (check mwnf3.monuments for duplicates)

### Travel-Specific Content (Lower Priority)

#### Accommodation
- `tr_accommodation` - Hotels/lodging
- `tr_accommodation_pictures` - Accommodation images

#### Travel Agencies
- `tr_agencies` - Travel agency listings
- `tr_agency_texts` - Agency translations

#### Guides
- `tr_guides` - Tour guide listings
- `tr_guide_texts`, `tr_guide_langs` - Guide details

#### Food & Culture
- `tr_traditional_food` - Traditional food descriptions
- `tr_food_pictures` - Food images
- `tr_cultural_events` - Cultural event calendar

#### InfoDesk
- `tr_infodesk`, `tr_infodesk_texts` - Information desk content

#### Images
- `tr_images`, `tr_images_texts` - Generic image gallery

#### Supporting Content
- `tr_local_contact` - Local contact information
- `tr_related_walks` - Related walking tours
- `tr_spot_light` - Featured content
- `tr_news`, `tr_newsletter` - News/newsletter (probably skip)

### Travel Packages (Alternative Structure)

**Note**: There's a parallel structure for pre-packaged travels:

- `travels` - Pre-packaged travel products
  - PK: `travel_id`
  - Different structure from trails
- `travel_texts` - Travel package translations
- `travel_days`, `travel_days_text` - Day-by-day itinerary
- `travel_hotels`, `travel_hotels_text` - Hotels in package
- `travel_theme`, `travel_visa` - Theme and visa info
- `travels_trails`, `travels_countries`, `travels_agencies`, `travels_guides` - Relationships

**Decision**: Lower priority - focus on trails/itineraries/locations/monuments first

### Tours & Surveys
- `tour_tom`, `tour_tom_names` - Tours of the month (promotional)
- `survey`, `survey_data`, `questionnaire*` - User surveys (SKIP)

---

## Task 3.2: Explore Schema Tables Catalog

### Core Hierarchy (Explore)

**Structure**: Completely different from mwnf3/sh/thg/travel

#### Countries - Top Level **[NORMALIZED]**
- `explorecountry` - Countries in explore
  - PK: `countryId` (varchar(2), 2-char ISO code)
  - FK: → countries (mwnf3_explore.countries, not mwnf3.countries!)
  - Fields: showOnLocation, showOnMonument (boolean flags)
  - **CRITICAL**: Has own countries table, references mwnf3_explore.countries
- `countries` - Country master in explore schema
  - PK: `countryId`
  - Different from mwnf3.countries
- `explorecountrydontmiss` - "Don't miss" highlights per country

**Mapping**: ExploreCountry → Collection (top level)

#### Regions - Level 2 (Conceptual)
**Note**: No explicit region table, but `locations` table has path/hierarchy information

#### Locations - Geographic Units **[NORMALIZED]**
- `locations` - Cities/sites within countries
  - PK: `locationId` (auto-increment)
  - FK: → countries (explore schema)
  - UNIQUE: `label` (location name)
  - Fields: label, geoCoordinates, zoom, path, how_to_reach, info, contact, description, prepared_by, introd_type, et_loc_introduction
  - **685 locations** (from AUTO_INCREMENT)

**Mapping**: Location → Collection (parent: Country Collection or Region Collection if hierarchy detected)

#### Monuments - Items **[NORMALIZED, CRITICAL DATA]**
- `exploremonument` - **MOST IMPORTANT TABLE**
  - PK: `monumentId` (auto-increment)
  - FK: → locations
  - Fields: title, geoCoordinates, zoom, special_monument (flag), related_monument (text)
  - **CRITICAL REFERENCE FIELDS** (marked "not used" but may have data):
    - `REF_tr_monuments_*` - References to mwnf3_travels.tr_monuments
    - `REF_monuments_*` - References to mwnf3.monuments
  - **1808 monuments** (from AUTO_INCREMENT = 1808) - **HUGE DATA SET**
  - FK constraints to `monuments_bkp` and `tr_monuments1` (backup tables)
- `exploremonumenttranslated` - Monument translations **[NORMALIZED]**
  - PK: `monumentId`, `langId`
  - FK: → exploremonument, → langs (explore schema)
  - Fields: name, alsoKnownAs, description1-5 (5 description fields!), contactDetails, howToReach, openingHours
  - **Separate translation table** - much cleaner than mwnf3 denormalization

**Mapping**:
1. **Check REF_monuments_* fields**: If populated → use existing mwnf3.monuments Item UUID
2. **Check REF_tr_monuments_* fields**: If populated → use existing travel.tr_monuments Item UUID
3. **If both empty**: Create new Item with backward_compatibility: `explore:exploremonument:{monumentId}`

**Monument Pictures**:
- `exploremonument_pictures` - Monument images
  - PK: `monumentId`, `pictureId`
  - FK: → exploremonument

#### Themes & Categorization
- `explorethemes` - Theme definitions (NOT same as thematic gallery themes)
  - Categorization for monuments
- `explorethemestranslated` - Theme translations
- `exploremonumentsthemes` - Monument-to-theme relationships

#### Monument Extensions
- `exploremonumentacademic` - Academic/scholarly content for monuments
- `exploremonumentext` - Extended information
- `exploremonumentotherdescriptions` - Additional descriptions
- `exploremonument_further_reading` - Bibliography/reading list
- `exploremonument_museums` - Associated museums for monuments
- `exploremonument_vm`, `exploremonument_sh`, `exploremonument_tr` - References to items in other schemas

### Explore Itineraries (Different from Travel!)

**Note**: Explore has its own itinerary system, distinct from travel trails:

- `explore_itineraries` - Curated exploration itineraries
  - PK: `itineraryId` (auto-increment)
  - Different purpose from travel itineraries
- `explore_itineraries_langs` - Itinerary translations
- `explore_itineraries_rel_*` - Relationships:
  - `explore_itineraries_rel_country` - Itinerary → Countries
  - `explore_itineraries_rel_territory` - Itinerary → Territories
  - `explore_itineraries_rel_locations` - Itinerary → Locations
  - `explore_itineraries_rel_monuments` - Itinerary → Monuments
- `explore_itineraries_country_picture` - Itinerary images

**Mapping**: Itinerary → Collection, with relationships to existing collections/items

### Partner Museums
- `explore_partner_museums` - Museums partnering with explore
  - References mwnf3.museums or independent records

### Filters & Search
- `filters`, `filter_types` - Search/browse filters
- `filters_explore_monuments` - Monument-to-filter relationships

### Featured Content
- `featured_tours`, `featured_tours_explore` - Featured itineraries
- `featured_books`, `featured_books_explore` - Featured publications
- `featured_partnerships`, `featured_partnerships_langs` - Partnerships

### Guided Visits & Hotels
- `guided_visits`, `guided_visits_contacts`, `guided_visits_contacts_langs` - Guided tour information
- `hotels` - Hotel listings (probably lower priority)
- `excursions_langs` - Excursion descriptions

### Supporting Content
- `explore_pages`, `explore_pages_langs` - Static pages
- `explore_home_banners` - Homepage banners
- `exploreusers`, `exploreusersthematiccycle`, `exploreuserslocations` - User accounts (SKIP)

### Legacy/Alternative
- `langs` - Language table in explore schema (separate from mwnf3.langs!)
- `countries` - Country table in explore schema (separate from mwnf3.countries!)
- `monuments_bkp`, `tr_monuments1` - Backup tables referenced by FK (IGNORE)

---

## Task 3.3: Non-Empty Tables Analysis

### High Priority (Likely Populated)

**Travel**:
- trails, tr_itineraries, tr_locations, tr_monuments (core hierarchy)
- tr_*_pictures (images for core entities)
- tr_accommodation, tr_agencies, tr_guides (travel services)

**Explore**:
- explorecountry, locations (683 records)
- **exploremonument (1808 records), exploremonumenttranslated** - **CRITICAL**
- exploremonument_pictures
- explorethemes, exploremonumentsthemes
- explore_itineraries, explore_itineraries_rel_*

### Lower Priority (May Be Sparse)
- Travel packages (travels, travel_*)
- Food/culture tables (tr_traditional_food, tr_cultural_events)
- User/survey tables (SKIP)
- Featured content (may be promotional/temporary)

---

## Task 3.4: Travel Trails → Collections Mapping

### Structure

**Hierarchy**: Trail → Itinerary → Location (→ Monument = Item)

All tables **DENORMALIZED** with language in PK (same pattern as mwnf3.objects)

### Mapping Strategy

#### Trails

**Table**: `trails`
- PK: `project_id`, `country`, `lang`, `number`

**Approach**: Group by non-lang columns

1. **Group trails**: By `project_id`, `country`, `number` (exclude lang)

2. **Create ONE Collection per trail**:
   - internal_name: From title or generate
   - parent_id: Resolved from project_id (Context Collection UUID)
   - type: 'trail'
   - backward_compatibility: `travels:trails:{project_id}:{country}:{number}` (NO lang!)

3. **Create CollectionTranslation** per language row:
   - collection_id: Trail Collection UUID
   - language_id: Map lang (2-char → 3-char ISO)
   - name: From title
   - description: From description, subtitle
   - Fields: curated_by, local_coordinator, photo_by, region_territory

#### Itineraries

**Table**: `tr_itineraries`
- PK: `project_id`, `country`, `number`, `lang`, `trail_id`
- FK: → trails (**includes lang** - problematic!)

**Approach**: Same denormalization handling

1. **Group itineraries**: By `project_id`, `country`, `number`, `trail_id` (exclude lang)

2. **Resolve parent Trail Collection**:
   - Use backward_compatibility: `travels:trails:{project_id}:{country}:{trail_id}`
   - Get Trail Collection UUID

3. **Create ONE Collection per itinerary**:
   - parent_id: Trail Collection UUID
   - type: 'itinerary'
   - backward_compatibility: `travels:itineraries:{project_id}:{country}:{trail_id}:{number}` (NO lang!)

4. **Create CollectionTranslation** per language row:
   - Fields: title, introduction, description, description2, author, prepared_by, days

#### Locations

**Table**: `tr_locations`
- PK: `lang`, `project_id`, `country`, `trail_id`, `itinerary_id`, `number`

**Approach**: Same pattern

1. **Group locations**: Exclude lang from PK

2. **Resolve parent Itinerary Collection**:
   - backward_compatibility: `travels:itineraries:{project_id}:{country}:{trail_id}:{itinerary_id}`

3. **Create ONE Collection per location**:
   - parent_id: Itinerary Collection UUID
   - type: 'location'
   - backward_compatibility: `travels:locations:{project_id}:{country}:{trail_id}:{itinerary_id}:{number}` (NO lang!)

4. **Create CollectionTranslation** per language row:
   - Fields: title, introduction, description, how_to_reach, info, contact, prepared_by

### Import Dependencies
- AFTER: mwnf3 Projects/Contexts (trails reference projects)
- BEFORE: tr_monuments (monuments link to locations)

---

## Task 3.5: Explore Countries/Locations → Collections Mapping

### Structure

**Hierarchy**: Country → Location (→ Monument = Item)

Tables **NORMALIZED** (no language in PK)

### Mapping Strategy

#### Countries

**Table**: `explorecountry`
- PK: `countryId` (2-char ISO code)
- FK: → countries (explore schema, not mwnf3!)

**Approach**:

1. **Create Collection per country**:
   - internal_name: Country name (from mwnf3 seeded data or explore.countries)
   - parent_id: Explore root Context Collection UUID
   - type: 'country'
   - backward_compatibility: `explore:countries:{countryId}`
   - **Note**: Map 2-char ISO to 3-char for country_id field

2. **CollectionTranslation**: From country names (multiple languages)

#### Locations

**Table**: `locations`
- PK: `locationId` (auto-increment)
- UNIQUE: `label` (location name)
- FK: → countries (explore schema)

**Approach**:

1. **Resolve parent Country Collection**:
   - From locations.countryId → backward_compatibility: `explore:countries:{countryId}`

2. **Check for hierarchy** (via `path` field):
   - If path suggests region/city structure: Create intermediate collections
   - Otherwise: Direct child of Country Collection

3. **Create Collection per location**:
   - parent_id: Country Collection UUID (or Region Collection if detected)
   - type: 'location'
   - backward_compatibility: `explore:locations:{locationId}`
   - **UNIQUE**: Can also index by label for cross-referencing

4. **CollectionTranslation**: From description, info fields (likely not multi-language in this table)

### Import Dependencies
- AFTER: Explore Context created
- BEFORE: exploremonument (monuments link to locations)

---

## Task 3.6: Explore Monuments → Items Mapping

### **CRITICAL**: Primary Reason for Explore Import

**Table**: `exploremonument` - **1808 monuments** (largest monument dataset!)

### Structure

**Master**: `exploremonument` **[NORMALIZED]**
- PK: `monumentId` (auto-increment)
- FK: → locations
- **Reference fields** (check for cross-schema deduplication):
  - `REF_monuments_*` → mwnf3.monuments
  - `REF_tr_monuments_*` → mwnf3_travels.tr_monuments

**Translations**: `exploremonumenttranslated` **[NORMALIZED]**
- PK: `monumentId`, `langId`
- Separate translation table (cleaner than denormalized approach)

### Mapping Strategy

**For Each Monument**:

1. **Check REF_monuments_* fields** (mwnf3 references):
   ```sql
   REF_monuments_project_id, REF_monuments_country, 
   REF_monuments_institution_id, REF_monuments_number, REF_monuments_lang
   ```
   - If populated: Look up mwnf3 Item via backward_compatibility
   - Use existing Item UUID
   - Add backward_compatibility: `explore:exploremonument:{monumentId}` as alternate reference

2. **Check REF_tr_monuments_* fields** (travel references):
   ```sql
   REF_tr_monuments_project_id, REF_tr_monuments_country, 
   REF_tr_monuments_itinerary_id, REF_tr_monuments_location_id, 
   REF_tr_monuments_number, REF_tr_monuments_lang, REF_tr_monuments_trail_id
   ```
   - If populated: Look up travel Item (if already imported)
   - Use existing Item UUID or note for later linking

3. **If BOTH empty** (monument unique to explore):
   - **Create NEW Item**:
     - type: 'monument'
     - context_id: Explore Context UUID
     - collection_id: Resolved from locations.locationId → Location Collection UUID
     - backward_compatibility: `explore:exploremonument:{monumentId}`
     - Fields: title (from exploremonument), geoCoordinates, zoom

4. **Create ItemTranslation records**:
   - One per row in `exploremonumenttranslated`
   - language_id: Map langId (2-char → 3-char ISO)
   - context_id: Explore Context UUID or Location Collection context
   - Fields: name, alsoKnownAs, description1, description2, description3, description4, description5 (merge or use primary description), contactDetails, howToReach, openingHours

5. **Handle special_monument flag**:
   - If special_monument = '1': Mark in metadata or add special tag

6. **Parse related_monument field**:
   - Text field with related monument references
   - Parse and create ItemItemLink relationships

### Monument Extensions

**Additional tables with monument data**:

- `exploremonumentacademic` - Academic content
  - Create additional ItemTranslation with academic context_id
- `exploremonumentext` - Extended information
  - Merge into ItemTranslation or separate metadata
- `exploremonumentotherdescriptions` - Alternative descriptions
  - Create additional ItemTranslation records with different contexts
- `exploremonument_further_reading` - Bibliography
  - Store in metadata or create document links
- `exploremonument_museums` - Associated museums
  - Create relationships to Partner records

### Monument Schema References

**Cross-schema monument tables** (check for references):
- `exploremonument_vm` - References to mwnf3 (virtual museum) monuments
- `exploremonument_sh` - References to sharing history monuments
- `exploremonument_tr` - References to travel monuments

**Purpose**: Link explore monuments to items in other schemas (deduplication support)

### Themes

**Table**: `explorethemes`, `explorethemestranslated`
- Create Tag records (category: 'explore_theme')

**Relationships**: `exploremonumentsthemes`
- Monument-to-theme relationships
- Create item_tag pivot records

### Images

**Table**: `exploremonument_pictures`
- PK: `monumentId`, `pictureId`
- Create ImageUpload records
- Deduplicate with mwnf3/sh/thg/travel images
- backward_compatibility: `explore:exploremonument_pictures:{monumentId}:{pictureId}`

### Import Dependencies
- AFTER: Locations (monuments link to locations)
- AFTER: mwnf3 Items, Travel Items (for cross-schema deduplication)
- Check REF_* fields to determine if monument already imported
- BEFORE: Explore itineraries (itineraries link to monuments)

---

## Task 3.7: Travel Monuments → Items Mapping

### Structure

**Table**: `tr_monuments` **[DENORMALIZED]**
- PK: `project_id`, `country`, `trail_id`, `itinerary_id`, `location_id`, `number`, `lang`
- FK: → tr_locations (with language)

**Pattern**: Same denormalization as mwnf3

### Mapping Strategy

1. **Group monuments**: Exclude lang from PK grouping
   - Group by: `project_id`, `country`, `trail_id`, `itinerary_id`, `location_id`, `number`

2. **Check if referencing mwnf3/sh/thg monument**:
   - Match via title, location, coordinates
   - If match: Use existing Item UUID
   - Add backward_compatibility: `travels:tr_monuments:{project_id}:{country}:{trail_id}:{itinerary_id}:{location_id}:{number}` as alternate

3. **If new monument**:
   - **Create Item**:
     - type: 'monument'
     - context_id: Resolved from project_id
     - collection_id: Resolved from tr_locations → Location Collection UUID
     - backward_compatibility: `travels:tr_monuments:{project_id}:{country}:{trail_id}:{itinerary_id}:{location_id}:{number}` (NO lang!)

4. **Create ItemTranslation** per language row:
   - Fields: title, how_to_reach, info, contact, description, prepared_by

5. **Images**:
   - `tr_monuments_pictures` - Monument images
   - Similar handling as other image tables

### Import Dependencies
- AFTER: tr_locations Collections (monuments link to locations)
- BEFORE: Explore monuments (explore may reference travel monuments)

---

## Task 3.8: Images Mapping

### Travel Images

**Collections**:
- `tr_trails_pictures` - Trail images
- `tr_itineraries_pictures` - Itinerary images
- `tr_locations_pictures` - Location images

**Items**:
- `tr_monuments_pictures` - Monument images

**Travel Services**:
- `tr_accommodation_pictures` - Hotel images
- `tr_food_pictures` - Food images

**Generic**:
- `tr_images`, `tr_images_texts` - Generic image gallery

**Mapping**: Standard image import with deduplication

### Explore Images

**Monuments**:
- `exploremonument_pictures` - Monument images
  - PK: `monumentId`, `pictureId`
  - FK: → exploremonument

**Itineraries**:
- `explore_itineraries_country_picture` - Itinerary images

**Mapping**: Same deduplication strategy as all schemas

### Import Dependencies
- AFTER: Collections, Items created
- File system access required

---

## Task 3.9: Travel/Explore Relationships

### Travel Relationships

**Monument-Location**: Via FK in tr_monuments
- Already handled during monument import (collection_id points to location)

**Related Walks**: `tr_related_walks`
- Create collection-collection relationships

### Explore Relationships

**Monument-Location**: Via FK in exploremonument
- Already handled (collection_id points to location)

**Monument-Monument**: Via `related_monument` text field
- Parse and create ItemItemLink relationships

**Monument-Theme**: `exploremonumentsthemes`
- Create item_tag relationships

**Explore Itineraries Relationships**: Multiple `explore_itineraries_rel_*` tables
- `explore_itineraries_rel_country` - Itinerary → Countries
- `explore_itineraries_rel_territory` - Itinerary → Territories
- `explore_itineraries_rel_locations` - Itinerary → Locations
- `explore_itineraries_rel_monuments` - Itinerary → Monuments

**Mapping**: Create collection_item and collection_collection relationships

### Cross-Schema References

**Explore → mwnf3**: `REF_monuments_*` fields in exploremonument
- Check and link to existing mwnf3 Items

**Explore → Travel**: `REF_tr_monuments_*` fields in exploremonument
- Check and link to existing travel Items

**Explore Monument Schema Tables**:
- `exploremonument_vm` - References to mwnf3 items
- `exploremonument_sh` - References to SH items
- `exploremonument_tr` - References to travel items

**Purpose**: Deduplication and relationship tracking

### Import Dependencies
- AFTER: All Items created (for ItemItemLink)
- AFTER: All Collections created (for collection relationships)

---

## Task 3.10: Import Dependencies and Execution Order

### Dependency Graph

```
1. Contexts
   ├─ Travel Context (for trails)
   └─ Explore Context (for countries/locations/monuments)

2. Travel Collections (Hierarchical, Denormalized)
   ├─ Trails (Level 1) - Group by non-lang PK
   ├─ Itineraries (Level 2) - Group by non-lang PK, parent: Trail
   └─ Locations (Level 3) - Group by non-lang PK, parent: Itinerary

3. Explore Collections (Hierarchical, Normalized)
   ├─ Countries (Level 1)
   └─ Locations (Level 2) - Parent: Country

4. Travel Items
   └─ tr_monuments → Item (check mwnf3 duplicates) + ItemTranslation (per lang row)

5. Explore Items (CRITICAL - 1808 monuments)
   └─ exploremonument → Item (check REF_monuments_*, REF_tr_monuments_* for duplicates) + ItemTranslation (from exploremonumenttranslated)

6. Explore Monument Extensions
   ├─ exploremonumentacademic → Additional ItemTranslation
   ├─ exploremonumentext → Metadata
   ├─ exploremonumentotherdescriptions → Additional ItemTranslation
   └─ exploremonument_further_reading → Document links

7. Explore Themes
   ├─ explorethemes → Tag
   └─ exploremonumentsthemes → item_tag relationships

8. Explore Itineraries
   ├─ explore_itineraries → Collection
   ├─ explore_itineraries_rel_country → collection relationships
   ├─ explore_itineraries_rel_locations → collection relationships
   └─ explore_itineraries_rel_monuments → collection_item relationships

9. Images (with deduplication)
   ├─ Travel: tr_trails_pictures, tr_itineraries_pictures, tr_locations_pictures, tr_monuments_pictures
   └─ Explore: exploremonument_pictures, explore_itineraries_country_picture

10. Relationships
    ├─ Travel: tr_related_walks (collection-collection)
    ├─ Explore: related_monument field parsing → ItemItemLink
    └─ Cross-schema: exploremonument REF_* fields → link to existing Items
```

### Execution Order

**Phase 3A: Contexts**
1. Create Travel Context
2. Create Explore Context

**Phase 3B: Travel Collections** (Denormalized - Handle Language Grouping)
3. Import trails → Collections (group by non-lang PK)
4. Import tr_itineraries → Collections (parent: Trails)
5. Import tr_locations → Collections (parent: Itineraries)

**Phase 3C: Explore Collections** (Normalized)
6. Import explorecountry → Collections
7. Import locations → Collections (parent: Countries)

**Phase 3D: Travel Items** (Denormalized)
8. Import tr_monuments → Items (group by non-lang PK, check mwnf3 duplicates) + ItemTranslations

**Phase 3E: Explore Items** (Normalized, CRITICAL - 1808 records)
9. Import exploremonument → Items (check REF_monuments_*, REF_tr_monuments_*) + ItemTranslations from exploremonumenttranslated
10. Import exploremonumentacademic → Additional ItemTranslations
11. Import exploremonumentext → Metadata
12. Import exploremonumentotherdescriptions → Additional ItemTranslations

**Phase 3F: Explore Themes**
13. Import explorethemes → Tags
14. Import exploremonumentsthemes → item_tag relationships

**Phase 3G: Explore Itineraries**
15. Import explore_itineraries → Collections
16. Import explore_itineraries_rel_* → collection and collection_item relationships

**Phase 3H: Images** (Deduplicate)
17. Import travel images (trails, itineraries, locations, monuments)
18. Import explore images (monuments, itineraries)

**Phase 3I: Relationships**
19. Import tr_related_walks → collection relationships
20. Parse related_monument field → ItemItemLink
21. Link exploremonument via REF_* fields → update existing Items

### backward_compatibility Format Standards

**Travel**:
- Context: `travels:context`
- Trail: `travels:trails:{project_id}:{country}:{number}` (NO lang!)
- Itinerary: `travels:itineraries:{project_id}:{country}:{trail_id}:{number}` (NO lang!)
- Location: `travels:locations:{project_id}:{country}:{trail_id}:{itinerary_id}:{number}` (NO lang!)
- Monument: `travels:tr_monuments:{project_id}:{country}:{trail_id}:{itinerary_id}:{location_id}:{number}` (NO lang!)
- Image: `travels:tr_monuments_pictures:{project_id}:{country}:{trail_id}:{itinerary_id}:{location_id}:{number}:{image_number}` (NO lang!)

**Explore**:
- Context: `explore:context`
- Country: `explore:countries:{countryId}`
- Location: `explore:locations:{locationId}`
- Monument: `explore:exploremonument:{monumentId}`
- Image: `explore:exploremonument_pictures:{monumentId}:{pictureId}`
- Theme: `explore:themes:{themeId}`
- Itinerary: `explore:itineraries:{itineraryId}`

### Key Import Principles

1. **Travel Denormalization**: Same pattern as mwnf3.objects - group by non-lang PK columns
2. **Explore Normalized**: Cleaner structure with separate translation tables
3. **Cross-Schema Deduplication**: Check REF_* fields in exploremonument before creating Items
4. **1808 Monuments Priority**: exploremonument is largest dataset and many unique monuments
5. **Hierarchical Collections**: Import parents before children
6. **Multiple Descriptions**: exploremonumenttranslated has description1-5 → merge or separate translations
7. **Travel Hierarchy**: Trail → Itinerary → Location (3 collection levels)
8. **Explore Hierarchy**: Country → Location (2 collection levels, possibly 3 with regions)

---

## Summary and Next Steps

### Phase 3 Complete

This analysis provides:
- Complete understanding of Travel (52 tables) and Explore (101 tables) schemas
- Different structures: Travel denormalized (lang in PK), Explore normalized (separate translations)
- Hierarchical collection mappings (3-4 levels each)
- **Critical finding**: exploremonument contains **1808 monuments** - largest dataset, many unique
- Cross-schema reference patterns for deduplication
- backward_compatibility format standards

### Critical Findings

1. **Two Very Different Structures**:
   - Travel: Denormalized like mwnf3 (language in PK) → same grouping approach
   - Explore: Normalized like sh/thg (separate translations) → cleaner import

2. **Explore Monument Priority**: **1808 monuments** - MUST import, many not in other schemas

3. **Cross-Schema References**: exploremonument has REF_monuments_* and REF_tr_monuments_* fields
   - Check these BEFORE creating new Items
   - Link to existing mwnf3/travel Items if references exist

4. **Multiple Descriptions**: exploremonumenttranslated has 5 description fields (description1-5)
   - Decide how to handle: merge, use primary, or create separate ItemTranslations

5. **Hierarchical Collections**:
   - Travel: Trail → Itinerary → Location (3 levels, denormalized)
   - Explore: Country → Location (2-3 levels, normalized)

6. **Travel-Specific Content**: Accommodation, agencies, guides, food (lower priority - skip or minimal import)

7. **Explore Extensions**: Multiple tables extending monument data (academic, ext, other descriptions)
   - Rich contextual information to import

### Ready for Next Phase

Can now proceed to:
- **Phase 4**: Analyze remaining schemas (quick scan for FK references)
- **Phase 5**: Create master mapping document
- **Phase 7+**: Implement Laravel Artisan import commands

---

**Analysis Status**: ✅ COMPLETE  
**Next Phase**: Phase 4 - Other Schemas Quick Scan
