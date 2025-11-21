# Legacy Data Import - Glossary & HCR Supplemental Plan

## Overview

This supplemental plan addresses two additional dimensions that must be imported from the legacy system:

1. **Glossary System** - Specialized terminology with translations, spellings, and direct links to Items
2. **Historical Cross-Referencing (HCR) Timelines** - Historical events with date ranges, requiring a new model

Both systems exist in the current new model (Glossary) or need to be created (HCR). These entities integrate with the core data import but require specialized handling.

---

## Phase G1: Glossary Analysis - Legacy Structure

**Context:** The Glossary system in legacy databases contains specialized terms with multilingual support, alternative spellings, and direct relationships to Objects and Monuments. The new model already supports Glossary, GlossaryTranslation, GlossarySpelling, and GlossarySpelling-ItemTranslation relationships.

### Task G1.1: Analyze mwnf3 Glossary Tables Structure
Review legacy glossary tables:
- `mwnf3.glossary` - Main glossary entries (word_id, name)
- `mwnf3.gl_definitions` - Translations (word_id, lang_id, definition) - denormalized
- `mwnf3.gl_spellings` - Alternative spellings (spelling_id, word_id, lang_id, spelling)
- `mwnf3.glossary_index` - Item-glossary relationships (item_id, terms as semicolon list)
- Document PK structures and denormalization patterns

### Task G1.2: Analyze Legacy vs Current Versions
Review historical glossary tables:
- `mwnf3.old_glossary*` - Previous version (ignore for import)
- `mwnf3.final_glossary*` - Another version (determine if this is active)
- Determine which version is authoritative for import

### Task G1.3: Check Non-Empty Glossary Tables
Cross-reference with data files:
- Identify which glossary tables have actual data
- Count records in each table
- Determine import scope

### Task G1.4: Analyze Glossary-Item Relationships
Study `mwnf3.glossary_index`:
- Parse `item_id` format (e.g., "O;ISL;dz;Mus01;1;fr" or "M;ISL;es;Mon01;17;en")
- Parse `terms` format (semicolon-separated word_id list)
- Map item_id format to backward_compatibility patterns
- Understand how to resolve to Item UUIDs

### Task G1.5: Map Glossary to New Model Structure
Compare legacy schema with new model:
- Legacy `glossary.name` → New `Glossary.internal_name`
- Legacy `gl_definitions` (denormalized) → New `GlossaryTranslation` (normalized)
- Legacy `gl_spellings` → New `GlossarySpelling`
- Legacy `glossary_index.terms` → New `item_translation_spelling` pivot
- Document field mappings

### Task G1.6: Identify Glossary Synonyms
Check if legacy has synonym relationships:
- Review for any synonym tables or fields
- Map to new `glossary_synonyms` if applicable
- Document strategy

### Task G1.7: Analyze Language Codes in Glossary
Review language codes used in:
- `gl_definitions.lang_id`
- `gl_spellings.lang_id`
- `glossary_index.item_id` (includes language)
- Ensure mapping to new 3-character ISO codes

### Task G1.8: Document Glossary backward_compatibility Format
Define format for glossary entities:
- Glossary: `mwnf3:glossary:{word_id}`
- GlossarySpelling: `mwnf3:gl_spellings:{spelling_id}`
- Document deduplication strategy

### Task G1.9: Identify Glossary Import Dependencies
Establish import order:
- Must import after: Languages (reference data)
- Must import before: Glossary-Item relationships (requires Items to exist)
- Document dependencies

### Task G1.10: Document Glossary Data Quality Issues
Review legacy data for:
- Missing definitions for certain languages
- Orphaned spellings
- Invalid item_id references in glossary_index
- Document cleanup strategy

---

## Phase G2: HCR Model Design & Requirements

**Context:** Historical Cross-Referencing (HCR) timelines do not exist in the current new model and must be designed. Three legacy HCR systems exist with different structures that must be unified.

### Task G2.1: Analyze mwnf3 HCR Structure
Review `mwnf3.hcr` and `mwnf3.hcr_events`:
- `hcr` table: hcr_id, country_id, name, from_ad, to_ad, from_ah, to_ah
- `hcr_events` table: hcr_id, lang_id, name, description, datedesc_ah, datedesc_ad (denormalized translations)
- Document structure and relationships
- Note: Links to Country, has translations

### Task G2.2: Analyze Sharing History HCR Structure
Review `mwnf3_sharing_history.sh_hcr` and related tables:
- Structure: hcr_id, country, exhibition_id, name, date_from_year, date_to_year, months, dates
- More granular date fields (year, month, date)
- Links to Exhibition (sh_exhibitions)
- Check for translation tables (sh_hcr_* pattern)
- Document differences from mwnf3 HCR

### Task G2.3: Analyze Thematic Gallery HCR Structure
Review `mwnf3_thematic_gallery.hcr` and `hcr_events`:
- Structure: hcr_id, gallery_id, country_id, name, from_ad, to_ad, from_ah, to_ah
- Links to Gallery (thg_gallery)
- Check translation pattern
- Note: User mentioned this was "probably never used" - verify by checking data

### Task G2.4: Check Non-Empty HCR Tables
Cross-reference with data files:
- Count records in mwnf3.hcr (priority)
- Count records in sh_hcr (priority)
- Count records in thg.hcr (likely empty based on user note)
- Determine import scope and priorities

### Task G2.5: Design Unified HCR Model for New System
Create new model specification:
- **Timeline** model (main entity)
  - Fields: id (UUID), internal_name, country_id, from_year_ad, to_year_ad, from_year_ah, to_year_ah
  - Optional: from_month, to_month, from_day, to_day (to support sh_hcr granularity)
  - References: country_id → countries
  - Metadata: backward_compatibility
- **TimelineTranslation** model
  - Fields: id (UUID), timeline_id, language_id, name, description, date_description_ad, date_description_ah
  - References: timeline_id → timelines, language_id → languages
- **Timeline-Collection relationship** (pivot)
  - Links Timeline to Collection (for exhibition/gallery context)
  - Fields: timeline_id, collection_id
- **Timeline-Item relationship** (optional - if HCR links to specific items)
  - Fields: timeline_id, item_id

### Task G2.6: Identify HCR Relationships to Collections
Document how HCR relates to contexts:
- mwnf3.hcr: Country-level (no specific collection, general context)
- sh_hcr: Exhibition-specific (links to sh_exhibitions → Collection)
- thg.hcr: Gallery-specific (links to thg_gallery → Collection)
- Design pivot table strategy

### Task G2.7: Identify HCR-Item Relationships
Check if HCR events link to specific Items:
- Review for any item relationship tables
- Check if descriptions reference specific monuments/objects
- Design relationship strategy if needed

### Task G2.8: Document HCR backward_compatibility Format
Define format:
- mwnf3: `mwnf3:hcr:{hcr_id}`
- sh: `mwnf3_sharing_history:sh_hcr:{hcr_id}`
- thg: `mwnf3_thematic_gallery:hcr:{hcr_id}`
- TimelineTranslation: `{schema}:{table}:{hcr_id}:{lang_id}`

### Task G2.9: Analyze Calendar System Handling
Review calendar fields:
- AD (Anno Domini) vs AH (After Hijra) dates
- Ensure both calendar systems captured
- Plan for date range display and queries

### Task G2.10: Document HCR Import Dependencies
Establish import order:
- Must import after: Countries, Languages, Collections (for context)
- Can import independently of Items
- Document dependencies

---

## Phase G3: Model & Migration Development - HCR

**Context:** HCR models do not exist and must be created before import. Follow Laravel conventions and new system patterns.

### Task G3.1: Create Timeline Model
Generate model with:
- UUID primary key (HasUuids trait)
- Mass assignable fields
- Relationships to Country, Collections, Items (if applicable)
- Scopes if needed
- Documentation

### Task G3.2: Create TimelineTranslation Model
Generate model with:
- UUID primary key
- Mass assignable fields
- Relationships to Timeline, Language
- Scopes (defaultContext if applicable)
- Documentation

### Task G3.3: Create Timeline Migration
Create migration for `timelines` table:
- id (UUID, primary key)
- internal_name (string, unique)
- country_id (string, 3 chars, FK to countries, nullable)
- from_year_ad (integer, nullable)
- to_year_ad (integer, nullable)
- from_year_ah (integer, nullable)
- to_year_ah (integer, nullable)
- from_month (integer, nullable, 1-12)
- to_month (integer, nullable, 1-12)
- from_day (integer, nullable, 1-31)
- to_day (integer, nullable, 1-31)
- backward_compatibility (string, nullable)
- timestamps
- Indexes and foreign keys

### Task G3.4: Create TimelineTranslation Migration
Create migration for `timeline_translations` table:
- id (UUID, primary key)
- timeline_id (UUID, FK to timelines)
- language_id (string, 3 chars, FK to languages)
- name (string)
- description (text, nullable)
- date_description_ad (text, nullable)
- date_description_ah (text, nullable)
- backward_compatibility (string, nullable)
- timestamps
- Unique constraint on [timeline_id, language_id]
- Indexes

### Task G3.5: Create Timeline-Collection Pivot Migration
Create migration for `collection_timeline` pivot:
- collection_id (UUID, FK to collections)
- timeline_id (UUID, FK to timelines)
- timestamps
- Primary key on [collection_id, timeline_id]

### Task G3.6: Create Timeline-Item Pivot Migration (if needed)
If HCR links to specific items:
- Create `item_timeline` pivot table
- Fields: item_id, timeline_id, timestamps
- Primary key on [item_id, timeline_id]

### Task G3.7: Create Timeline Factory
Generate factory for testing:
- Realistic timeline data
- Date ranges in both calendars
- Country associations
- Follow existing factory patterns

### Task G3.8: Create TimelineTranslation Factory
Generate factory for testing:
- Translation data in multiple languages
- Date descriptions
- Relationship to Timeline and Language

### Task G3.9: Run Migrations and Test
Execute migrations:
- Run on test database
- Verify schema correctness
- Test factory creation
- Check relationships work

### Task G3.10: Update Model Documentation
Add Timeline models to documentation:
- Update docs/_model/index.md (if auto-generated)
- Document relationships
- Add to API documentation

---

## Phase G4: API & Controller Development - HCR

**Context:** Timeline models need API endpoints and controllers following existing patterns.

### Task G4.1: Create Timeline Resource
Generate API Resource:
- Format Timeline for API responses
- Include relationships (country, translations, collections)
- Follow existing resource patterns

### Task G4.2: Create TimelineTranslation Resource
Generate API Resource:
- Format TimelineTranslation for API responses
- Include relationships
- Follow patterns

### Task G4.3: Create Timeline Form Requests
Generate validation requests:
- StoreTimelineRequest
- UpdateTimelineRequest
- Validation rules aligned with model constraints
- Follow existing patterns

### Task G4.4: Create TimelineTranslation Form Requests
Generate validation requests:
- StoreTimelineTranslationRequest
- UpdateTimelineTranslationRequest
- Validation rules

### Task G4.5: Create Timeline Controller
Generate RESTful controller:
- index(), show(), store(), update(), destroy()
- Use Form Requests for validation
- Use Resources for responses
- Eager loading where appropriate
- Follow existing controller patterns

### Task G4.6: Create TimelineTranslation Controller
Generate RESTful controller:
- Standard CRUD operations
- Validation and resources
- Follow patterns

### Task G4.7: Add API Routes
Update routes/api.php:
- Timeline routes
- TimelineTranslation routes
- Proper grouping and middleware

### Task G4.8: Create Timeline Tests
Generate comprehensive tests:
- Factory tests
- API endpoint tests (CRUD operations)
- Validation tests
- Relationship tests
- Follow existing test patterns (see tests/README.md)

### Task G4.9: Create TimelineTranslation Tests
Generate comprehensive tests:
- All CRUD operations
- Validation
- Relationships
- Follow patterns

### Task G4.10: Run Tests and Fix Issues
Execute test suite:
- Run all Timeline-related tests
- Fix any failures
- Ensure 100% pass rate

---

## Phase G5: Glossary Import Script Development

**Context:** Implement import scripts for Glossary entities following established patterns from main import.

### Task G5.1: Implement Glossary Import (Main Entries)
Create import script section:
- Read `mwnf3.glossary` (or `final_glossary` if determined authoritative)
- Create Glossary records
- Set internal_name from legacy name
- Set backward_compatibility: `mwnf3:glossary:{word_id}`
- Handle duplicates
- Log results

### Task G5.2: Implement GlossaryTranslation Import
Create import script section:
- Read `mwnf3.gl_definitions` (denormalized with lang_id in PK)
- Group by word_id
- For each word_id + lang_id combination:
  - Resolve Glossary UUID via backward_compatibility
  - Map language code (2-char → 3-char)
  - Create GlossaryTranslation record
  - Set definition field
- Log results

### Task G5.3: Implement GlossarySpelling Import
Create import script section:
- Read `mwnf3.gl_spellings`
- For each spelling:
  - Resolve Glossary UUID via backward_compatibility
  - Map language code
  - Create GlossarySpelling record
  - Set spelling field
  - Set backward_compatibility: `mwnf3:gl_spellings:{spelling_id}`
- Handle unique constraint (glossary_id, language_id, spelling)
- Log results

### Task G5.4: Implement Glossary-Item Relationships Import
Create import script section:
- Read `mwnf3.glossary_index`
- For each glossary_index record:
  - Parse item_id to extract legacy item reference
  - Resolve Item UUID via backward_compatibility lookup
  - Parse terms (semicolon-separated word_id list)
  - For each word_id in terms:
    - Resolve Glossary UUID
    - Get GlossarySpelling records for that Glossary
    - Determine which ItemTranslation to link (match language from item_id)
    - Create `item_translation_spelling` pivot records
- Handle missing Items or Glossaries gracefully
- Log results and errors

### Task G5.5: Parse glossary_index item_id Format
Implement parser for legacy item_id format:
- Format examples: "O;ISL;dz;Mus01;1;fr" (Object) or "M;ISL;es;Mon01;17;en" (Monument)
- Parse components: Type, ProjectId, CountryId, MuseumId, SequenceNumber, Language
- Convert to backward_compatibility format for Item lookup: `mwnf3:objects:{proj}:{country}:{museum}:{seq}` (excluding language)
- Handle both Object and Monument types
- Return Item UUID or null

### Task G5.6: Handle Glossary Language Code Mapping
Implement language code mapper:
- Legacy uses 2-character codes
- New system uses 3-character ISO codes
- Use existing mapping table or create dedicated mapper
- Handle unmapped codes gracefully

### Task G5.7: Handle Glossary Import Errors
Implement error handling:
- Orphaned glossary_index references (item not found)
- Invalid word_id references
- Language code mapping failures
- Duplicate detection
- Log all errors with details for manual review

### Task G5.8: Test Glossary Import on Subset
Run import on test database with limited data:
- Import first 100 glossary entries
- Verify Glossary, GlossaryTranslation, GlossarySpelling creation
- Verify item_translation_spelling relationships
- Check backward_compatibility fields
- Review logs for errors

### Task G5.9: Create Glossary Import Validation Report
Generate report with:
- Glossary import statistics (counts by entity type)
- Translation and spelling counts per glossary
- Item relationship counts
- Error summary (orphaned refs, missing items, etc.)
- Sample data verification

### Task G5.10: Iterate and Fix Glossary Import Issues
Based on validation report:
- Fix identified bugs
- Adjust parsing logic
- Improve error handling
- Re-test

---

## Phase G6: HCR Import Script Development - mwnf3

**Context:** Implement import scripts for mwnf3 HCR timelines. This is the base HCR system.

### Task G6.1: Implement Timeline Import from mwnf3.hcr
Create import script section:
- Read `mwnf3.hcr`
- For each hcr record:
  - Create Timeline record
  - Set internal_name (generate or use legacy name)
  - Map country_id (2-char → 3-char if needed)
  - Set from_year_ad, to_year_ad, from_year_ah, to_year_ah
  - Set backward_compatibility: `mwnf3:hcr:{hcr_id}`
- Log results

### Task G6.2: Implement TimelineTranslation Import from mwnf3.hcr_events
Create import script section:
- Read `mwnf3.hcr_events` (denormalized with lang_id in PK)
- For each hcr_events record:
  - Resolve Timeline UUID via backward_compatibility lookup
  - Map language code (2-char → 3-char)
  - Create TimelineTranslation record
  - Set name, description, datedesc_ad, datedesc_ah
  - Set backward_compatibility: `mwnf3:hcr_events:{hcr_id}:{lang_id}`
- Log results

### Task G6.3: Handle mwnf3 HCR Context (Country-level)
Determine context strategy for mwnf3 HCR:
- These are country-level timelines (not collection-specific)
- Option 1: Link to Country only (no collection)
- Option 2: Create or link to default "Historical Context" collection per country
- Document decision and implement

### Task G6.4: Test mwnf3 HCR Import
Run import on test database:
- Verify Timeline creation
- Verify TimelineTranslation creation
- Check language distribution
- Verify country relationships
- Review logs for errors

### Task G6.5: Create mwnf3 HCR Import Validation Report
Generate report with:
- Timeline and translation counts
- Language distribution
- Country coverage
- Error summary
- Sample data checks

---

## Phase G7: HCR Import Script Development - Sharing History

**Context:** Implement import for Sharing History HCR, which links to exhibitions and has more granular date fields.

### Task G7.1: Implement Timeline Import from sh_hcr
Create import script section:
- Read `mwnf3_sharing_history.sh_hcr`
- For each sh_hcr record:
  - Create Timeline record
  - Set internal_name
  - Map country
  - Set date_from_year → from_year_ad
  - Set date_to_year → to_year_ad
  - Set date_from_month → from_month (if available)
  - Set date_to_month → to_month (if available)
  - Set date_from_date → from_day (if available)
  - Set date_to_date → to_day (if available)
  - Set backward_compatibility: `mwnf3_sharing_history:sh_hcr:{hcr_id}`
- Check for duplicates with mwnf3.hcr using backward_compatibility
- Log results

### Task G7.2: Link sh_hcr to Collections (Exhibitions)
Create import script section:
- For each sh_hcr record with exhibition_id:
  - Resolve Exhibition Collection UUID via backward_compatibility lookup
    - Format: `mwnf3_sharing_history:sh_exhibitions:{exhibition_id}`
  - Resolve Timeline UUID
  - Create collection_timeline pivot record
- Handle missing exhibition references
- Log results

### Task G7.3: Implement sh_hcr Translation Import
Check for translation tables:
- Search for `sh_hcr_events` or similar translation tables
- If found: Import translations following mwnf3 pattern
- If not found: Use sh_hcr.name field as default English translation
- Document approach

### Task G7.4: Test sh_hcr Import
Run import on test database:
- Verify Timeline creation with granular dates
- Verify collection_timeline relationships
- Check for duplicates
- Review logs

### Task G7.5: Create sh_hcr Import Validation Report
Generate report with statistics and issues

---

## Phase G8: HCR Import Script Development - Thematic Gallery

**Context:** Implement import for Thematic Gallery HCR if data exists. User noted it was "probably never used."

### Task G8.1: Verify thg HCR Data Exists
Check data files:
- Count records in `mwnf3_thematic_gallery.hcr`
- Count records in `mwnf3_thematic_gallery.hcr_events`
- If empty or minimal (< 10 records), skip remaining tasks in this phase
- Document decision

### Task G8.2: Implement Timeline Import from thg.hcr (if applicable)
If data exists:
- Read `mwnf3_thematic_gallery.hcr`
- Create Timeline records
- Follow mwnf3 pattern
- Set backward_compatibility: `mwnf3_thematic_gallery:hcr:{hcr_id}`

### Task G8.3: Link thg.hcr to Collections (Galleries) (if applicable)
If data exists:
- Resolve Gallery Collection UUID via backward_compatibility
- Create collection_timeline pivot records

### Task G8.4: Implement thg HCR Translation Import (if applicable)
If data exists:
- Read `mwnf3_thematic_gallery.hcr_events`
- Create TimelineTranslation records
- Follow mwnf3 pattern

### Task G8.5: Test thg HCR Import (if applicable)
If implemented:
- Run import on test database
- Verify creation and relationships
- Review logs

### Task G8.6: Create thg HCR Import Validation Report (if applicable)
If implemented:
- Generate report with statistics

---

## Phase G9: Glossary & HCR Full Import Execution

**Context:** Execute complete Glossary and HCR import on clean database and validate.

### Task G9.1: Determine Import Order in Main Script
Integrate Glossary and HCR into main import sequence:
- Glossary must import AFTER Items (needs Item UUIDs for relationships)
- HCR must import AFTER Collections (needs Collection UUIDs for context)
- Glossary and HCR can run in parallel (no interdependency)
- Document final import order

### Task G9.2: Prepare Clean Database for Full Import
Use database from main import Phase 13:
- Database should already have Items, Collections imported
- Ready to add Glossary and HCR

### Task G9.3: Execute Glossary Import
Run complete glossary import:
- Import all Glossary entries
- Import all GlossaryTranslations
- Import all GlossarySpellings
- Import all Glossary-Item relationships
- Monitor progress and log errors

### Task G9.4: Execute HCR Import
Run complete HCR import:
- Import mwnf3 HCR timelines
- Import sh HCR timelines
- Import thg HCR timelines (if applicable)
- Import all TimelineTranslations
- Create all Timeline-Collection relationships
- Monitor progress and log errors

### Task G9.5: Generate Comprehensive Import Report
Create detailed report for Glossary and HCR:
- Total counts by entity type (legacy vs imported)
- Success rates
- Error summaries
- Missing data reports (orphaned references)
- Processing time statistics

### Task G9.6: Validate Glossary Import
Check:
- All glossary entries have at least one translation
- Spelling counts match legacy
- Item-glossary relationship counts
- backward_compatibility fields populated correctly
- Sample glossary terms display correctly with items

### Task G9.7: Validate HCR Import
Check:
- Timeline counts by source (mwnf3, sh, thg)
- Translation completeness
- Collection relationships correct
- Country relationships correct
- Date ranges valid (from <= to)
- Calendar system data captured (AD and AH)

### Task G9.8: Spot Check Glossary Data
Manually review sample glossary entries:
- Select random terms
- Verify translations in multiple languages
- Verify spellings
- Verify linked items display correctly
- Check backward_compatibility resolution works

### Task G9.9: Spot Check HCR Data
Manually review sample timelines:
- Select random events from each source
- Verify translations
- Verify collection context correct
- Check date ranges display correctly
- Verify country associations

### Task G9.10: Document Validation Findings
Compile validation report:
- Issues found (Glossary and HCR)
- Data quality assessment
- Recommendations for fixes
- Known limitations

---

## Phase G10: Model Assessment & Enhancements

**Context:** Based on import experience, assess if models need enhancements.

### Task G10.1: Assess Glossary Model Completeness
Review Glossary model after import:
- Are all legacy fields captured?
- Are relationships working as expected?
- Any performance issues with item_translation_spelling queries?
- Document required changes

### Task G10.2: Assess Timeline Model Completeness
Review Timeline model after import:
- Are date fields sufficient?
- Are granular month/day fields used?
- Are relationships working correctly?
- Any missing fields identified?
- Document required changes

### Task G10.3: Assess Glossary API Endpoints
Review API functionality:
- Can glossary terms be searched effectively?
- Can item-glossary relationships be queried?
- Are responses formatted correctly?
- Document required enhancements

### Task G10.4: Assess Timeline API Endpoints
Review API functionality:
- Can timelines be queried by date range?
- Can timelines be filtered by collection or country?
- Are calendar systems displayed properly?
- Document required enhancements

### Task G10.5: Create Model Enhancement Plan (if needed)
For any required changes:
- Design migrations
- Plan backward compatibility
- Plan testing approach

### Task G10.6: Create API Enhancement Plan (if needed)
For any required API changes:
- Design endpoint changes
- Plan validation updates
- Plan testing approach

### Task G10.7: Implement Model Changes (if needed)
Execute model enhancements:
- Create and run migrations
- Update models
- Update factories
- Update tests

### Task G10.8: Implement API Changes (if needed)
Execute API enhancements:
- Update controllers and resources
- Update validation
- Update tests

### Task G10.9: Re-test After Enhancements
If changes made:
- Re-run import
- Verify improvements
- Run all tests

### Task G10.10: Update Documentation
Document final Glossary and Timeline models:
- Update model documentation
- Update API documentation
- Document import process

---

## Phase G11: Integration with Main Application

**Context:** Ensure Glossary and Timeline are integrated into frontend and backend interfaces.

### Task G11.1: Add Glossary to Web UI Navigation
Update web UI:
- Add Glossary menu item in navigation
- Add GlossaryTranslation menu item
- Follow existing navigation patterns

### Task G11.2: Add Timeline to Web UI Navigation
Update web UI:
- Add Timeline menu item
- Add TimelineTranslation menu item
- Follow patterns

### Task G11.3: Create Glossary List View (Web UI)
If not exists, create:
- Blade template for glossary listing
- Controller method
- Route
- Follow existing list view patterns

### Task G11.4: Create Glossary Detail View (Web UI)
If not exists, create:
- Blade template for glossary detail/edit
- Controller methods (show, edit, update)
- Display translations, spellings, linked items
- Follow existing detail view patterns

### Task G11.5: Create Timeline List View (Web UI)
Create:
- Blade template for timeline listing
- Controller method
- Route
- Filter by collection, country, date range

### Task G11.6: Create Timeline Detail View (Web UI)
Create:
- Blade template for timeline detail/edit
- Controller methods
- Display translations, collections, dates
- Show both AD and AH calendar dates

### Task G11.7: Add Glossary to SPA Demo (if applicable)
If SPA demo needs glossary:
- Add glossary views
- Use published API client
- Follow existing SPA patterns

### Task G11.8: Add Timeline to SPA Demo (if applicable)
If SPA demo needs timelines:
- Add timeline views
- Use published API client
- Follow patterns

### Task G11.9: Test UI Integration
Test web UI and SPA:
- Browse glossaries and view details
- Browse timelines and view details
- Verify linked items display correctly
- Verify translations work

### Task G11.10: Update User Documentation
Create user guides:
- How to use glossary features
- How to view timelines
- How to navigate historical context

---

## Phase G12: Production Deployment - Glossary & HCR

**Context:** Deploy Glossary and HCR imports to production.

### Task G12.1: Review Glossary & HCR Import Scripts
Final review before production:
- Code review
- Test coverage verification
- Error handling robustness
- Logging completeness

### Task G12.2: Update Production Import Runbook
Add Glossary and HCR to main import procedure:
- Document execution order
- Document expected duration
- Add validation steps

### Task G12.3: Execute HCR Model Migrations in Production
Run Timeline migrations:
- Backup database first
- Run migrations
- Verify schema

### Task G12.4: Conduct Dry Run in Staging (Glossary & HCR)
Execute import in staging:
- Import Glossary and HCR
- Measure duration
- Review logs
- Validate results

### Task G12.5: Review Dry Run Results
Assess staging import:
- Success rate
- Error types and frequencies
- Performance
- Data quality

### Task G12.6: Execute Production Import (Glossary & HCR)
Run import in production:
- Enable maintenance mode
- Execute glossary import
- Execute HCR import
- Monitor progress
- Log all operations

### Task G12.7: Validate Production Import
Run validation checks:
- Count verification
- Referential integrity
- Sample data checks
- UI functionality

### Task G12.8: Smoke Test Glossary & Timeline Features
Test critical functionality:
- Browse glossaries
- View glossary-item links
- Browse timelines
- View timeline details
- Verify API endpoints

### Task G12.9: Monitor Post-Deployment
Watch for:
- Errors in logs
- Performance issues
- User reports

### Task G12.10: Document Completion
Record:
- Import statistics
- Issues encountered
- Resolutions applied
- Lessons learned

---

## Summary

This supplemental plan adds **120 tasks** across **12 phases** to handle Glossary and HCR import:

### Glossary (Phases G1, G5, G9):
- Import from mwnf3.glossary, gl_definitions, gl_spellings, glossary_index
- Create Glossary, GlossaryTranslation, GlossarySpelling records
- Establish Glossary-Item relationships via item_translation_spelling pivot
- Handle complex item_id parsing and backward_compatibility resolution

### HCR Timelines (Phases G2-G4, G6-G9):
- Design and create new Timeline and TimelineTranslation models (not existing in current system)
- Import from three legacy HCR systems: mwnf3.hcr, sh_hcr, thg.hcr
- Support both AD and AH calendar systems
- Support granular dates (year, month, day) from sh_hcr
- Link timelines to Collections (exhibitions, galleries) for context
- Create comprehensive API and UI integration

### Integration (Phases G10-G12):
- Validate and enhance models based on import experience
- Integrate into web UI and SPA
- Deploy to production with full validation

These phases integrate seamlessly with the main import plan (Phases 1-17), executing after Items and Collections are imported to satisfy dependencies.
