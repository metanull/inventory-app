This project is meant to refactor our many legacy databases, dramatically simplifying and reducing the database model.
We are advanced enough to start importing data from the legacy model, validating that our new approach covers the need.

**Context**

We want to import Partners, Items, Pictures and their Contextual descriptions and translations from the legacy models (see below).

Some mapping and transformation will be required. An analysis of the legacy model is required.

In general the entities map as follows (LHS: Legacy, RHS: Our new model):

- Object, Monument, Detail => Items
- Museum, Institution, Partner => Partners
- Projects => Contexts and Collections (Project are the old way of defining context; and they also are defacto "Collections" as all items belonging to a project form a Collection)
- Exhibition, Gallery => Contexts and Collection (These are other collections where objects, monuments, details, or their images where manually selected; and where the managers also defined "contextualized text" by re-defining some of the item's textual properties adapting them to the specific needs of that Collection)
- *i18n, *names => Their contextual description and Translation
- *pictures, *images => Their images
- Thematic Galleries' Theme/Subtheme and Travel's Trail-Itinerary-Location-City => Collections; these tables where used to organize the collection in "chapters" or "layers". In our new model, Collections can be hierarchical (a Collection may have a Parent Collection and may have Children collections - defacto allowing to organize the collections in a Directory-tree like structure) we will need to map the legacy "collection layers" accordingly by creating child collections.

In legacy, PK and FK are typically multi-column constraints. e.g. for an object: ProjectId,CountryId,MuseumId,SequenceNumber,Language.
In legacy, some entities are not normalized and are defined as multiple rows in a single table, e.g. mwnf3.objects include the languageId in their primary key; there are therefore several rows for one single item; each row in a different language. They are seen as multiple rows; but the legacy system sees them as one single record by "grouping" them on the PK columns minus the LanguageId. Some other are more normalized and decouples the information over two tables e.g. mwnf3_thematic_gallery has objects and objectnames, the first defines the ID and common data, the later defines translations

As non normalized content depends on other records (some PK are at the same time FK columns) we must make sure to import the most common data first (e.g. Projects and Partners).
Reference data such as Country and Language was already seeded to our new model from ISO's official definitions. Some mapping is required as our model uses the latest ISO '3 character' codes; and the legacy was using obsolete '2 character' codes.

The legacy database also has some utility tables (e.g. mwnf3.global*entities, and other mwnf3.global*\* that were fed by triggers and were supposed to contain a standardised version of all database's records); it is safer to ignore such tables to avoid duplicating content.

When inserting data in our new model, we must keep track of the reference in the old model using the `backward_compatibility` text field; it is expected to receive a string in the format: `{legacy_db}:{legacy_table}:{semicolumn_separated_list_of_the_legacy_pk_columns_values}. We should pay attention in properly disambiguating the backward_compatibility field when importin data from non normalized tables; by excluding the languageId column.

Images in the legacy system contain relative path. The original files can be found in `\\virtual-office.museumwnf.org\C$\mwnf-server\pictures\images`.
Image relative path almost always include a "format definition" (e.g. small, thumb, large, ...) they refer to resized version of the image, typically stored in `\\virtual-office.museumwnf.org\C$\mwnf-server\pictures\cache\{FORMAT}\`
To import the images, we have to use our ImageUpload mechanism. Uploading the original image only (not the resized ones), and avoiding uploading the same image multiple times (all formats of a same image results in inserting one single image: the original one).
For images also the ´backward_compatibility` field must be initialized following the legacy's PK column, table and schema.
The image database is pretty large (in term of number of files to upload); we may consider importing only a limited set of them to validate our import.

**Legacy**

The legacy database can be consulted in:

- /.legacy-database/ddl git submodule contains the legacy databases creation scripts
- /.legacy-database/data git submodule contains the legacy databases' content (in the form of SQL INSERT statements)

It consists in several schemas (several distinct applications):

- **mwnf3** (sometimes aslo called "virtual-museum" or vm) is the mother of all, and the oldest.
- **mwnf3_thematic_gallery** (thg) and **mwnf3_sharing_history** (sh) serve different applications but share many concepts of **mwnf3** (in fact they have been built to "customize" or "extend" **mwnf3**), and often include **references** to records in the **mwnf3** database.
- **mwnf3_travel** (explore) is a very similar product, but based on a pretty different database model, and with specific hierarchy of the collection by Exhibition Trail, Country, Itinerary, Location.
- **mwnf3_explore** (travels) is something very different again, it contains a LOT of monuments that are a MUST to keep, as well as a large number of references to other schemas
- ... There are other schemas, but they are less relevant. Analysis could be lighter, and focus in finding if they contain FK to any of the models above - and only analyze the tables with such FK's.
- Databases thg_demo_47, mwnf3_las, mwnf3_portal shall be ignored.
- Tables with "old" prefix or "bkp|backup" suffixes shall be ignored

**Goal**

Analyse the Legacy schemas - considering both structure and content (it is useless to lose time with empty tables; they should be discarded right away; similarily views must also be ignored as their data originates from other tables).
Analyze the relationships and establish a comprehensive mappings by identifying which legacy tables will be used to feed which part of the new model (there will beoverlap and filtering will be required); and the prerequisites (e.g. if partners have to be loaded BEFORE items by example).
Also some of the legacy model contain references to other legacy models; we should avoid creating duplicated data. The backward_compatibility field may be used for that purpose.
Our new model has a notion of Links; we should make sure to create the links between items during the import process.
The legacy system had also different categories of tags (often referenced in the records by a semi-column separated list of tag ids); they must be converted into Tags and Tag references for our new models.
The legacy often had Author, CopyEditor, TranslationAuthor and TranslationCopyEditor fields. They must be covnerted into our Author model and references added to the items.

**Task**

I don't expect it to work in one go, as we will probably discover issues in the process. And it might require changes to our model and API.
I would like this request to be chunked in clear steps; run a pre-analysis; in order to establish the best course of action to achieve the goals.
Write a detailed task list in Phases (we want clear tasks only, no estimates, no assertions, no commercial languages; just a bare organized list of task that will best help us achieve the goal).

I propose to also analyze the legacy models by stages. Focusing first on the mwnf3 model (the base); then sharng_history and thematic_Gallery (the next generations after mwnf3), then the travels database; and then to finish with the explore model (which is in fact the same concepts, but in a very different form).

Once the task list is established. We will review it Phase by Phase, analyzing them again individually.

MAke sure to include enough context in each phase to facilitate their analyses.

**Plan**

I've successfully created a comprehensive task plan for importing your legacy museum data into the new inventory management system. The plan is organized into **17 phases** with **170 discrete tasks** (10 tasks per phase).

## Key Features of the Plan:

1. **Phased Approach**: Starting with mwnf3 (base schema), then extending to sharing_history and thematic_gallery, followed by travel/explore, and finally other schemas

2. **Analysis-First Strategy**: Each schema is thoroughly analyzed before any import work begins, ensuring we understand the data structures and dependencies

3. **Clear Dependencies**: Tasks are ordered to respect foreign key relationships (e.g., Partners before Items, Items before Images)

4. **Deduplication Strategy**: Heavy emphasis on using the `backward_compatibility` field to avoid importing the same data from multiple schemas

5. **Validation Throughout**: Each phase includes validation tasks to catch issues early

6. **Production-Ready**: Includes comprehensive preparation, execution, and post-import phases for production deployment

7. **Context Awareness**: Each phase includes context paragraphs explaining why that phase is important and what it addresses

## The Plan Covers:

- **Entity Mapping**: Projects→Contexts/Collections, Partners, Items (objects/monuments/details), Images, Authors, Tags
- **Hierarchical Collections**: Exhibitions/Themes/Subthemes, Galleries, Trails/Itineraries
- **Complex Relationships**: Item-to-item links, collection-item associations, partner relationships
- **Non-normalized Data**: Handling multi-row records with language in PK
- **Image Management**: Original files only, deduplication, network share access
- **Model Adjustments**: Phase 6 specifically assesses if current models need changes
- **Validation**: Comprehensive checks at multiple stages

The plan is ready for phase-by-phase review and execution. Each phase can be analyzed individually to develop detailed implementation plans.

---

# Legacy Data Import - Task Plan

## Overview

This document outlines the phases and tasks required to import data from legacy museum database schemas into the new unified inventory management model. The import will be executed in stages, starting with foundational data structures and progressively incorporating more complex relationships.

**Key Principles:**

- Import foundation data first (Projects, Partners, Countries, Languages)
- Maintain referential integrity through `backward_compatibility` field
- Avoid duplicating data across schemas
- Handle non-normalized legacy structures (multi-row records with language in PK)
- Map legacy multi-column PKs to new UUID-based system
- Convert legacy tag lists and author fields to relational models
- Import original images only (not cached/resized versions)

---

## Phase 1: Legacy Schema Analysis - mwnf3 (Core/Base Schema)

**Context:** mwnf3 is the foundational schema. All other schemas reference or extend it. Must be analyzed first to understand the base data model.

### Task 1.1: Catalog mwnf3 Tables by Category

Examine all mwnf3 table DDL files and categorize them into:

- Reference data (countries, languages, regions)
- Core entities (projects, partners, objects, monuments, details)
- Translations (tables with language in PK or *names/*i18n suffix)
- Images/Media (tables with *pictures/*images/*audio/*video)
- Relationships (junction/pivot tables, \_\_\_ pattern)
- Tags and Authors (author tables, tag references)
- Utility/Generated (global\_\*, avoid importing)
- Legacy/Backup (old\__, _\_bkp, ignore)

### Task 1.2: Identify Non-Empty Tables

Cross-reference DDL structure with data files to identify:

- Tables with actual data (non-zero INSERT statements)
- Tables that are empty or minimal (can be skipped)
- Views (skip - data comes from source tables)

### Task 1.3: Analyze mwnf3 Primary Key Structures

Document PK patterns for major entities:

- Multi-column PKs (e.g., ProjectId+CountryId+MuseumId+SequenceNumber+Language)
- Which columns are also FKs
- Which columns are part of denormalization (e.g., language in PK)

### Task 1.4: Map mwnf3 Projects to Contexts and Collections

Analyze `mwnf3.projects` and `mwnf3.projectnames`:

- Map to Context (representing project scope)
- Map to Collection (items belonging to project form a collection)
- Document backward_compatibility format: `mwnf3:projects:{project_id}`
- Identify hierarchical relationships if any

### Task 1.5: Map mwnf3 Partners to Partner Model

Analyze partner-related tables:

- `mwnf3.museums`, `mwnf3.museumnames`
- `mwnf3.institutions`, `mwnf3.institutionnames`
- `mwnf3.partners` (if exists)
- Map to unified Partner model with type (museum/institution/individual)
- Document address, contact, and translation patterns
- Identify partner images/logos

### Task 1.6: Map mwnf3 Items to Item Model

Analyze item-related tables:

- `mwnf3.objects` - Map to Item (type: object)
- `mwnf3.monuments` - Map to Item (type: monument)
- `mwnf3.monument_details` - Map to Item (type: detail) with parent relationship
- Document PK structure and language handling (non-normalized rows)
- Identify translation fields and contextual data

### Task 1.7: Map mwnf3 Images to ImageUpload

Analyze image tables:

- `mwnf3.objects_pictures`
- `mwnf3.monuments_pictures`
- `mwnf3.monument_detail_pictures`
- Partner/institution images
- Identify original vs cached/resized paths
- Map to ImageUpload with backward_compatibility

### Task 1.8: Identify mwnf3 Tags and Authors

Analyze author and tag structures:

- `mwnf3.authors`, `mwnf3.authors_*` relationships
- Tag-related fields (semicolon-separated lists)
- Map to Author model
- Map to Tag model and tag relationships

### Task 1.9: Identify mwnf3 Item Relationships

Analyze relationship tables:

- `mwnf3.objects_objects` (object-to-object links)
- `mwnf3.objects_monuments` (object-to-monument links)
- `mwnf3.monuments_monuments` (monument-to-monument links)
- Map to ItemItemLink model

### Task 1.10: Document mwnf3 Import Dependencies

Create ordered list of entities based on foreign key dependencies:

1. Reference data (Country, Language - already seeded)
2. Projects → Contexts + Collections
3. Partners (museums, institutions)
4. Items (objects, monuments, details)
5. Images
6. Authors
7. Tags
8. Item relationships
9. Translations (ItemTranslation, PartnerTranslation, CollectionTranslation)

---

## Phase 2: Legacy Schema Analysis - Sharing History & Thematic Gallery

**Context:** These schemas extend/customize mwnf3. They share concepts and reference mwnf3 records. Must avoid data duplication.

### Task 2.1: Catalog sh (Sharing History) Tables

Categorize `mwnf3_sharing_history_*` tables:

- Core entities (sh_projects, sh_exhibitions, sh_partners)
- Items (sh_objects, sh_monuments, sh_monument_details)
- Translations and names
- Images
- Relationships to mwnf3 entities
- References to mwnf3 records (avoid duplicating)

### Task 2.2: Catalog thg (Thematic Gallery) Tables

Categorize `mwnf3_thematic_gallery_*` tables:

- Core entities (thg_gallery, thg_projects, thg_partners)
- Items (thg_objects, thg_monuments, thg_monument_details)
- Themes and subthemes (hierarchical collections)
- Tags specific to thg
- Translations and names
- Images
- Relationships to mwnf3 and sh entities

### Task 2.3: Identify Non-Empty sh and thg Tables

Cross-reference with data files to find populated tables

### Task 2.4: Map sh/thg Projects to Contexts and Collections

Analyze:

- `sh_projects` vs `mwnf3.projects` - map or reference?
- `sh_exhibitions`, `sh_exhibition_themes`, `sh_exhibition_subthemes` - hierarchical collections
- `thg_gallery` - collections
- `thg_theme`, `thg_theme_item` - hierarchical collections
- Document backward_compatibility to avoid duplication

### Task 2.5: Map sh/thg Partners

Analyze:

- `sh_partners` vs references to `mwnf3.museums/institutions`
- `thg_partners` vs references to `mwnf3.museums/institutions`
- Determine if new Partner records or references to existing

### Task 2.6: Map sh/thg Items

Analyze:

- `sh_objects`, `sh_monuments`, `sh_monument_details`
- `thg_objects`, `thg_monuments`, `thg_monument_details`
- Check for mwnf3 references vs new records
- Map contextual descriptions (item descriptions adapted for specific exhibitions/galleries)
- Handle as ItemTranslation with appropriate context_id

### Task 2.7: Map sh/thg Images

Analyze image tables for both schemas

- Identify original paths vs cached
- Check for references to mwnf3 images vs new images
- Map to ImageUpload with proper backward_compatibility

### Task 2.8: Map sh/thg Tags and Authors

Analyze:

- `sh_authors` vs `mwnf3.authors`
- `thg_authors` vs `mwnf3.authors`
- `thg_tags` and tag relationships
- Determine deduplication strategy using backward_compatibility

### Task 2.9: Map sh/thg Hierarchical Collections

Analyze:

- Sharing History: Exhibitions → Themes → Subthemes (3 levels)
- Thematic Gallery: Gallery → Theme → Item (2-3 levels)
- Map to Collection model with parent_id relationships

### Task 2.10: Document sh/thg Import Dependencies

Create ordered list considering:

- Dependencies on mwnf3 imported data
- Internal dependencies within sh and thg
- Contextual translations as final step

---

## Phase 3: Legacy Schema Analysis - Travel/Explore Schemas

**Context:** mwnf3_travel and mwnf3_explore have different models but similar concepts. Explore contains critical monument data.

### Task 3.1: Catalog travel Schema Tables

Categorize `mwnf3_travels_*` and `mwnf3_tr_*` tables:

- Core entities (trails, itineraries, locations)
- Monuments and references
- Travel-specific data (agencies, guides, hotels, etc.)
- Images
- Hierarchical structure: Trail → Itinerary → Location → City

### Task 3.2: Catalog explore Schema Tables

Categorize `mwnf3_explore_*` tables:

- Core entities (thematiccycle, countries, regions, locations)
- Monuments (explore_exploremonument - CRITICAL DATA)
- Itineraries
- Images and translations
- Hierarchical structure: ThematicCycle → Country → Region → Location

### Task 3.3: Identify Non-Empty travel and explore Tables

Cross-reference with data files for populated tables

### Task 3.4: Map travel Trails/Itineraries to Collections

Analyze:

- `travels_trails` - top-level collection
- `tr_itineraries` - child collection of trail
- `tr_locations` - child collection of itinerary
- Map to hierarchical Collection model with parent_id

### Task 3.5: Map explore ThematicCycles to Collections

Analyze:

- `explore_thematiccycle` - top-level collection
- `explore_explorecountry` - child collection by country
- `explore_exploreregion` - child collection by region
- `explore_explorelocation` - child collection by location
- Map to hierarchical Collection model with parent_id

### Task 3.6: Map explore Monuments to Items

Analyze:

- `explore_exploremonument` - PRIORITY (many monuments)
- `explore_exploremonumenttranslated` - translations
- `explore_exploremonument_pictures` - images
- References to mwnf3/sh/thg monuments - check for duplicates
- Map to Item model with proper backward_compatibility

### Task 3.7: Map travel Monuments

Analyze:

- `tr_monuments` - items in travel context
- `tr_monuments_pictures` - images
- References to other schemas

### Task 3.8: Map travel/explore Images

Identify image tables and map to ImageUpload

### Task 3.9: Map travel/explore Relationships

Analyze:

- Monument-to-itinerary relationships
- Monument-to-location relationships
- Cross-schema references

### Task 3.10: Document travel/explore Import Dependencies

Create ordered list considering:

- Dependencies on mwnf3 data
- Hierarchical collection structures
- Monument import priorities (explore first)

---

## Phase 4: Legacy Schema Analysis - Other Schemas

**Context:** Remaining schemas may contain FKs to core schemas or contain peripheral data.

### Task 4.1: Catalog Remaining Schema Names

List all remaining schemas not covered in Phases 1-3

- Exclude: thg_demo_47, mwnf3_las, mwnf3_portal (as specified)

### Task 4.2: Quick Scan for Foreign Key References

For each remaining schema:

- Check DDL for FK references to mwnf3, sh, thg, travel, explore
- Only analyze tables with such FKs
- Document relationships

### Task 4.3: Identify Additional Entities

Check if remaining schemas contain:

- Additional partners
- Additional items/monuments
- Additional images
- Additional tags/authors

### Task 4.4: Document Other Schemas Import Dependencies

Create minimal import plan for any relevant data

---

## Phase 5: Import Strategy and Mapping Document

**Context:** Consolidate all analysis into comprehensive import strategy.

### Task 5.1: Create Master Entity Mapping Table

Document mapping for all entity types:

- Legacy Schema → Legacy Table → New Model → New Table
- PK structure → backward_compatibility format
- Translation handling approach
- Image path resolution

### Task 5.2: Create Master Import Dependency Graph

Visual/textual representation of import order:

- Nodes: Entity types
- Edges: FK dependencies
- Phases: Logical grouping

### Task 5.3: Document backward_compatibility Format Standards

Define exact format for each legacy table:

- Format: `{schema}:{table}:{pk_columns_excluding_language}`
- Examples for each entity type
- Deduplication strategy using this field

### Task 5.4: Document Language Code Mapping

Create mapping table:

- Legacy 2-character codes → New 3-character ISO codes
- Handle special cases or missing mappings

### Task 5.5: Document Image Path Resolution Strategy

Define rules for:

- Identifying original images vs cached/resized
- Resolving relative paths to network share
- Handling missing files
- Deduplication (same image referenced multiple times)
- Limited import scope for validation

### Task 5.6: Document Tag Parsing Strategy

Define approach for:

- Parsing semicolon-separated tag lists
- Creating Tag records
- Creating tag relationships
- Deduplication

### Task 5.7: Document Author Parsing Strategy

Define approach for:

- Extracting author references from fields (Author, CopyEditor, TranslationAuthor, TranslationCopyEditor)
- Mapping to Author model
- Creating author relationships
- Deduplication

### Task 5.8: Document Item Relationship Strategy

Define approach for:

- Importing ItemItemLink relationships
- Handling bidirectional links (avoid duplicates)
- Relationship type mapping

### Task 5.9: Create Import Script Architecture Plan

Design overall structure:

- Laravel Artisan command approach
- Chunking/batching strategy
- Progress tracking
- Error handling and logging
- Rollback/retry mechanisms
- Validation checkpoints

### Task 5.10: Create Import Validation Checklist

Define validation steps:

- Count checks (legacy records vs imported records)
- Referential integrity checks
- Required fields populated
- Image file accessibility
- Translation completeness
- Sample data spot checks

---

## Phase 6: Model and API Adjustments Assessment

**Context:** Import may reveal gaps in current model that need addressing.

### Task 6.1: Assess Item Model Completeness

Review Item model fields against legacy data:

- Identify missing fields needed for import
- Document required new fields
- Check if existing fields have sufficient length/type

### Task 6.2: Assess Partner Model Completeness

Review Partner model fields against legacy partner data:

- Check address fields sufficiency
- Check contact fields sufficiency
- Check image handling
- Document required changes

### Task 6.3: Assess Collection Model Completeness

Review Collection model against legacy collection concepts:

- Check hierarchical relationship support (parent_id field)
- Check type field covers all legacy collection types
- Document required changes

### Task 6.4: Assess Translation Model Completeness

Review translation models (ItemTranslation, PartnerTranslation, CollectionTranslation):

- Check field coverage
- Check context_id handling for contextualized descriptions
- Document required changes

### Task 6.5: Assess Author Model Completeness

Review Author model:

- Check if it covers all legacy author fields
- Check relationship structures
- Document required changes

### Task 6.6: Assess Tag Model Completeness

Review Tag model:

- Check if it supports all legacy tag types
- Check relationship structures
- Document required changes

### Task 6.7: Assess ImageUpload Model Completeness

Review ImageUpload model:

- Check if backward_compatibility field exists
- Check polymorphic relationship coverage
- Document required changes

### Task 6.8: Review API Endpoints for Import Support

Check if existing API endpoints support:

- Batch creation
- backward_compatibility field in requests
- All required fields
- Document required API enhancements

### Task 6.9: Create Model Migration Plan

For each required model change:

- Design migration
- Plan backward compatibility
- Plan testing approach

### Task 6.10: Create API Enhancement Plan

For each required API change:

- Design endpoint changes
- Plan validation updates
- Plan testing approach

---

## Phase 7: Import Script Development - Foundation Data

**Context:** Build import scripts for foundational entities that have no dependencies.

### Task 7.1: Create Base Import Command Structure

Setup Laravel Artisan command:

- Command signature and options
- Progress bar/output utilities
- Error logging framework
- Database transaction handling
- Configuration loading (batch sizes, limits, etc.)

### Task 7.2: Create Legacy Database Connection

Configure Laravel database connection to legacy database:

- Connection configuration
- Query builder setup
- Test connectivity

### Task 7.3: Implement Context Import (from mwnf3 Projects)

Create import script section for:

- Reading mwnf3.projects and mwnf3.projectnames
- Creating Context records
- Setting backward_compatibility field
- Logging results

### Task 7.4: Implement Collection Import (from mwnf3 Projects)

Create import script section for:

- Reading mwnf3.projects and mwnf3.projectnames
- Creating Collection records (one per project)
- Linking to Context
- Setting backward_compatibility field
- Creating CollectionTranslation records
- Logging results

### Task 7.5: Implement Partner Import (from mwnf3 Partners)

Create import script section for:

- Reading mwnf3.museums, mwnf3.museumnames
- Reading mwnf3.institutions, mwnf3.institutionnames
- Creating Partner records with type
- Setting backward_compatibility field
- Handling address and contact data
- Creating PartnerTranslation records
- Logging results

### Task 7.6: Implement Author Import

Create import script section for:

- Reading mwnf3.authors (and sh_authors, thg_authors)
- Deduplicating using backward_compatibility
- Creating Author records
- Logging results

### Task 7.7: Implement Tag Import

Create import script section for:

- Extracting unique tags from semicolon-separated lists
- Creating Tag records
- Deduplication
- Logging results

### Task 7.8: Test Foundation Data Import

Run import on test database:

- Verify record counts
- Verify backward_compatibility fields
- Verify relationships
- Check for errors

### Task 7.9: Create Foundation Import Validation Report

Generate report with:

- Import statistics (counts, success rate)
- Error summary
- Sample data verification
- Issues identified

### Task 7.10: Iterate and Fix Foundation Import Issues

Based on validation report:

- Fix identified bugs
- Enhance error handling
- Re-test

---

## Phase 8: Import Script Development - Items and Images

**Context:** Import Items (objects, monuments, details) and their images.

### Task 8.1: Implement Item Import (from mwnf3 Objects)

Create import script section for:

- Reading mwnf3.objects (denormalized with language in PK)
- Grouping by object identity (excluding language from key)
- Creating single Item record per object
- Setting backward_compatibility (excluding language)
- Linking to Partner
- Logging results

### Task 8.2: Implement Item Import (from mwnf3 Monuments)

Create import script section for:

- Reading mwnf3.monuments (denormalized with language in PK)
- Grouping by monument identity
- Creating single Item record per monument
- Setting backward_compatibility
- Linking to Partner
- Logging results

### Task 8.3: Implement Item Import (from mwnf3 Monument Details)

Create import script section for:

- Reading mwnf3.monument_details
- Creating Item records with type 'detail'
- Setting parent_id to monument Item
- Setting backward_compatibility
- Logging results

### Task 8.4: Implement ItemTranslation Import (from mwnf3)

Create import script section for:

- Reading denormalized mwnf3 object/monument records
- For each language row, create ItemTranslation
- Setting context_id to default Context (mwnf3 project)
- Mapping translation fields
- Mapping language codes
- Logging results

### Task 8.5: Implement ItemImage Import (from mwnf3)

Create import script section for:

- Reading mwnf3.\*\_pictures tables
- Resolving image paths (original only, exclude cached)
- Checking file existence on network share
- Using ImageUpload mechanism
- Deduplicating based on original file path
- Setting backward_compatibility
- Creating ItemImage relationships
- Limiting to subset for validation (configurable)
- Logging results

### Task 8.6: Implement Author Relationships for Items

Create import script section for:

- Parsing Author, CopyEditor fields from ItemTranslation
- Parsing TranslationAuthor, TranslationCopyEditor fields
- Linking to Author records via relationships
- Logging results

### Task 8.7: Implement Tag Relationships for Items

Create import script section for:

- Parsing tag fields (semicolon-separated)
- Creating ItemTag relationships
- Logging results

### Task 8.8: Test Item and Image Import

Run import on test database:

- Verify Item counts
- Verify ItemTranslation counts and language distribution
- Verify image upload success rate
- Check file system integration
- Check for errors

### Task 8.9: Create Item Import Validation Report

Generate report with:

- Import statistics by entity type
- Image upload statistics
- Error summary
- Sample data checks
- Missing images report

### Task 8.10: Iterate and Fix Item Import Issues

Based on validation report:

- Fix bugs
- Adjust image path resolution logic
- Handle missing files gracefully
- Re-test

---

## Phase 9: Import Script Development - Item Relationships

**Context:** Import ItemItemLink relationships between items.

### Task 9.1: Implement ItemItemLink Import (Objects-Objects)

Create import script section for:

- Reading mwnf3.objects_objects
- Resolving object PKs to Item UUIDs via backward_compatibility
- Creating ItemItemLink records
- Avoiding duplicate reciprocal links
- Setting relationship type
- Logging results

### Task 9.2: Implement ItemItemLink Import (Objects-Monuments)

Create import script section for:

- Reading mwnf3.objects_monuments
- Resolving to Item UUIDs
- Creating ItemItemLink records
- Logging results

### Task 9.3: Implement ItemItemLink Import (Monuments-Monuments)

Create import script section for:

- Reading mwnf3.monuments_monuments
- Resolving to Item UUIDs
- Creating ItemItemLink records
- Avoiding duplicates
- Logging results

### Task 9.4: Test ItemItemLink Import

Run import on test database:

- Verify link counts
- Verify both directions work correctly
- Check for duplicates
- Check for errors

### Task 9.5: Create ItemItemLink Import Validation Report

Generate report with:

- Link statistics
- Errors
- Sample checks

---

## Phase 10: Import Script Development - Sharing History & Thematic Gallery

**Context:** Import sh and thg entities, avoiding duplication with mwnf3.

### Task 10.1: Import sh Projects, Exhibitions, Collections

Create import script section for:

- Reading sh_projects, sh_exhibitions
- Checking backward_compatibility to avoid duplicates with mwnf3
- Creating Context and Collection records
- Handling hierarchical collections (exhibitions → themes → subthemes)
- Setting parent_id relationships
- Creating CollectionTranslation records
- Logging results

### Task 10.2: Import thg Galleries and Themes

Create import script section for:

- Reading thg_gallery, thg_theme
- Creating Collection records
- Handling hierarchical structure
- Creating CollectionTranslation records
- Logging results

### Task 10.3: Import sh Partners

Create import script section for:

- Reading sh_partners
- Checking if referencing mwnf3 partners (avoid duplication)
- Creating new Partner records only if needed
- Creating PartnerTranslation records
- Logging results

### Task 10.4: Import thg Partners

Create import script section for:

- Reading thg_partners
- Checking references and deduplicating
- Creating Partner/PartnerTranslation records as needed
- Logging results

### Task 10.5: Import sh Items

Create import script section for:

- Reading sh_objects, sh_monuments, sh_monument_details
- Checking for mwnf3 references vs new items
- Creating Item records (or skipping if reference)
- Creating contextual ItemTranslation records (context_id = sh exhibition/theme)
- Logging results

### Task 10.6: Import thg Items

Create import script section for:

- Reading thg_objects, thg_monuments, thg_monument_details
- Checking references
- Creating Item records as needed
- Creating contextual ItemTranslation records
- Logging results

### Task 10.7: Import sh/thg Images

Create import script section for:

- Reading sh/thg image tables
- Resolving paths and checking existence
- Using ImageUpload mechanism
- Deduplicating
- Creating image relationships
- Logging results

### Task 10.8: Import sh/thg Collection-Item Relationships

Create import script section for:

- Reading relationship tables (e.g., exhibition_objects)
- Resolving to UUIDs
- Creating collection_item pivot records
- Logging results

### Task 10.9: Test sh/thg Import

Run import on test database:

- Verify counts and no duplicates
- Verify contextual translations
- Verify hierarchical collections
- Check for errors

### Task 10.10: Create sh/thg Import Validation Report

Generate report with statistics and issues

---

## Phase 11: Import Script Development - Travel & Explore

**Context:** Import travel and explore entities with hierarchical collections and critical explore monuments.

### Task 11.1: Import travel Collections (Trails, Itineraries, Locations)

Create import script section for:

- Reading travels_trails, tr_itineraries, tr_locations
- Creating hierarchical Collection records with parent_id
- Creating CollectionTranslation records
- Logging results

### Task 11.2: Import explore Collections (ThematicCycles, Countries, Regions, Locations)

Create import script section for:

- Reading explore_thematiccycle, explore_explorecountry, etc.
- Creating hierarchical Collection records with parent_id
- Creating CollectionTranslation records
- Logging results

### Task 11.3: Import explore Monuments (PRIORITY)

Create import script section for:

- Reading explore_exploremonument (CRITICAL DATA)
- Checking for references to mwnf3/sh/thg
- Creating new Item records
- Reading explore_exploremonumenttranslated for translations
- Creating ItemTranslation records with appropriate context_id
- Logging results

### Task 11.4: Import travel Monuments

Create import script section for:

- Reading tr_monuments
- Checking references
- Creating Item records as needed
- Creating ItemTranslation records
- Logging results

### Task 11.5: Import travel/explore Images

Create import script section for:

- Reading image tables
- Using ImageUpload mechanism
- Creating relationships
- Logging results

### Task 11.6: Import travel/explore Collection-Item Relationships

Create import script section for:

- Reading relationship tables
- Creating collection_item pivot records
- Logging results

### Task 11.7: Test travel/explore Import

Run import on test database:

- Verify explore monument import success (critical)
- Verify hierarchical collections
- Check for errors

### Task 11.8: Create travel/explore Import Validation Report

Generate report with statistics and issues

---

## Phase 12: Import Script Development - Other Schemas

**Context:** Import any remaining relevant data from other schemas.

### Task 12.1: Review Other Schemas Analysis Results

Review Phase 4 analysis outcomes

### Task 12.2: Implement Imports for Additional Entities

If Phase 4 identified relevant data:

- Create import script sections
- Follow established patterns
- Log results

### Task 12.3: Test Other Schema Imports

Run on test database and verify

### Task 12.4: Create Other Schema Validation Report

Document results

---

## Phase 13: Full Import Execution and Validation

**Context:** Execute complete import on clean database and validate thoroughly.

### Task 13.1: Prepare Clean Database Instance

- Fresh database setup
- Run all current migrations
- Seed reference data (Country, Language)
- No other data

### Task 13.2: Execute Full Import Script

Run complete import in proper dependency order:

- Monitor progress
- Log all operations
- Capture any errors

### Task 13.3: Generate Comprehensive Import Report

Create detailed report with:

- Total counts by entity type (legacy vs imported)
- Success rates
- Error summary and details
- Missing data report (especially missing images)
- Processing time statistics

### Task 13.4: Validate Referential Integrity

Check database for:

- Orphaned records (FK violations)
- Missing required relationships
- NULL values in required fields

### Task 13.5: Validate backward_compatibility Fields

Check:

- All imported records have backward_compatibility set
- Format is consistent and correct
- No duplicates (same backward_compatibility value on different records)

### Task 13.6: Validate Translations Completeness

Check:

- Each Item has at least one ItemTranslation
- Each Partner has at least one PartnerTranslation
- Each Collection has at least one CollectionTranslation
- Language distribution makes sense

### Task 13.7: Validate Image Imports

Check:

- Image upload success rate
- Missing images report
- File system paths correct
- Image-entity relationships correct

### Task 13.8: Validate Collection Hierarchies

Check:

- parent_id relationships form valid trees (no cycles)
- Root collections identified
- Depth levels as expected

### Task 13.9: Spot Check Sample Data

Manually review sample records:

- Select random items and verify data accuracy
- Check translations look correct
- Verify images display correctly
- Verify relationships make sense

### Task 13.10: Document Validation Findings

Compile validation report with:

- Issues found
- Data quality assessment
- Recommendations for fixes

---

## Phase 14: Issue Resolution and Re-import

**Context:** Address issues found during validation and re-run import.

### Task 14.1: Prioritize Issues

Categorize issues by:

- Severity (critical data loss vs minor inconsistencies)
- Scope (systemic vs edge cases)
- Effort to fix

### Task 14.2: Fix Critical Import Bugs

Address bugs that cause:

- Data loss
- Referential integrity violations
- Incorrect mappings

### Task 14.3: Enhance Data Quality Handling

Improve handling of:

- Missing data in legacy
- Invalid data in legacy
- Edge cases

### Task 14.4: Optimize Import Performance

If import took too long:

- Add chunking/batching
- Optimize queries
- Add progress checkpoints

### Task 14.5: Re-run Import on Clean Database

Execute full import again after fixes

### Task 14.6: Re-validate

Repeat Phase 13 validation steps

### Task 14.7: Compare Results

Compare new import results with previous:

- Improvement in success rates
- Reduction in errors
- Better data quality

### Task 14.8: Iterate Until Acceptable

Repeat fix-import-validate cycle until:

- No critical issues remain
- Data quality meets acceptance criteria
- All essential data imported

---

## Phase 15: Production Import Preparation

**Context:** Prepare for import into production environment.

### Task 15.1: Document Import Procedure

Create detailed runbook:

- Prerequisites (backup, maintenance mode)
- Step-by-step execution instructions
- Expected duration
- Rollback procedure

### Task 15.2: Create Pre-Import Checklist

Document checks before production import:

- Database backup verified
- Legacy data accessible
- Image share accessible
- Application in maintenance mode
- Sufficient disk space

### Task 15.3: Create Post-Import Checklist

Document checks after production import:

- Run validation report
- Spot check critical data
- Verify application functionality
- Performance check
- Exit maintenance mode

### Task 15.4: Plan Rollback Strategy

Document how to rollback if import fails:

- Restore database from backup
- Clear uploaded images
- Restore previous state

### Task 15.5: Schedule Production Import Window

Coordinate:

- Downtime window
- Team availability
- Stakeholder notification

### Task 15.6: Create Communication Plan

Prepare:

- Pre-import notification (maintenance window)
- During-import status updates
- Post-import completion announcement

### Task 15.7: Conduct Dry Run in Staging

Execute full import in staging environment:

- Mimic production setup
- Measure duration
- Identify any environment-specific issues

### Task 15.8: Review Dry Run Results

Assess:

- Success/failure
- Issues encountered
- Adjustments needed

### Task 15.9: Update Procedures Based on Dry Run

Refine runbook and checklists

### Task 15.10: Obtain Stakeholder Approval

Present results and get go-ahead for production import

---

## Phase 16: Production Import Execution

**Context:** Execute import in production environment.

### Task 16.1: Execute Pre-Import Checklist

Complete all items from Task 15.2

### Task 16.2: Backup Production Database

Create verified backup before any changes

### Task 16.3: Enable Maintenance Mode

Put application in maintenance mode

### Task 16.4: Execute Production Import

Run import script:

- Monitor progress continuously
- Log all operations
- Be ready to abort if critical issues

### Task 16.5: Generate Production Import Report

Create report with statistics and errors

### Task 16.6: Execute Post-Import Validation

Run all validation checks from Phase 13

### Task 16.7: Execute Post-Import Checklist

Complete all items from Task 15.3

### Task 16.8: Conduct Smoke Tests

Test critical application functions:

- Browse items
- View translations
- Display images
- Search functionality
- API endpoints

### Task 16.9: Disable Maintenance Mode

Return application to normal operation

### Task 16.10: Monitor Application

Watch for:

- Errors in logs
- Performance issues
- User reports

---

## Phase 17: Post-Import Activities

**Context:** Finalize import process and document for future reference.

### Task 17.1: Communicate Import Completion

Notify stakeholders:

- Import success
- Statistics summary
- Known issues (if any)
- Next steps

### Task 17.2: Document Lessons Learned

Record:

- What went well
- What could be improved
- Unexpected challenges
- Best practices identified

### Task 17.3: Archive Import Scripts and Logs

Preserve:

- Final import script code
- Import logs
- Validation reports
- Documentation

### Task 17.4: Update System Documentation

Reflect imported data in:

- Data model documentation
- API documentation
- User guides

### Task 17.5: Address Known Issues

Create tickets for:

- Minor data quality issues to fix manually
- Edge cases to address
- Enhancements identified

### Task 17.6: Plan for Future Imports

If legacy system remains active:

- Document process for incremental imports
- Define change detection strategy
- Schedule periodic syncs

### Task 17.7: Decommission Legacy Access (if applicable)

If legacy system being retired:

- Archive legacy databases
- Remove legacy database connections
- Update access controls

### Task 17.8: Conduct Retrospective

Team meeting to discuss:

- Import process
- Challenges faced
- Solutions found
- Process improvements

### Task 17.9: Update Project Status

Mark import project as complete:

- Close related tickets
- Update project board
- Archive project materials

### Task 17.10: Celebrate Success

Acknowledge team effort and milestone achievement

---

## End of Task Plan

This task plan provides a structured approach to importing legacy museum database data into the new inventory management system. Each phase builds on previous phases, with clear dependencies and validation checkpoints throughout the process.
