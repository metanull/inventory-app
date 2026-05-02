# Import Gap Root-Cause Analysis

Date: 2026-05-03

## Scope

This document correlates the import validation reports with:

- the full import log at `scripts/importer/logs/import-2026-04-20T11-14-12-177Z.log`;
- importer source under `scripts/importer/src`;
- read-only production legacy database checks on the Windows server;
- read-only imported Inventory checks on production Inventory databases;
- confirmed importer conventions for timelines, monument details, Explore references, Sharing History national context, and THG galleries/exhibitions.

The purpose is to distinguish importer logic bugs from legacy data gaps and to capture fixable importer issues as implementation stories.

## Executive Summary

The reassessment keeps the provenance and Travels findings, but changes several conclusions:

- `item_translations.provenance` is dropped by the SQL writer even when `object-transformer.ts` computes it.
- Baroque timelines must be materialized by the importer. No explicit BAR item-HCR pivot was found, so the importer needs to implement the legacy dynamic logic using `mwnf3.hcr` and `mwnf3.hcr_events` and bind the resulting timeline to the Baroque Art collection.
- Monument details are valid `Item` records of type `detail`. Missing parent monuments must not block importing the detail; the importer must set `parent_id = NULL` and raise a warning.
- Sharing History partner profile photos are not imported because only SH partner logos have an importer.
- Sharing History national-context import must ignore `sh_national_context_exhibition_texts`. The relevant legacy tables are `sh_national_context_exhibitions` and `sh_national_context_exhibition_images`.
- Explore references must resolve to already-imported source items instead of creating duplicate Explore monument items. Explore-native monuments stay as native Explore items.
- THG galleries and exhibitions share the same base collection model. `exhibition_i18n` is exhibition-only data; absence of `exhibition_i18n` means the row is a gallery, not missing gallery translation data.
- THG theme item contextual descriptions are currently written as `item_translations`; that is the wrong target. Theme contextual text belongs to the theme collection or collection-item relationship, not to the item name or base item metadata.
- THG collection partners, exhibition logos, collection images, and related documents fit the current model and need importer support. `CollectionMedia` also needs document support for related PDFs.
- Travels country/child coverage is broken by global `collections.internal_name` collisions, which cascade into skipped monuments and media errors.

## Classification Matrix

| Area | Reported issue | Revised classification | Evidence summary | Story |
|---|---|---|---|---|
| Islamic Art / `mwnf3` objects | Provenance missing for `mwnf3:objects:ISL:eg:Mus01:1` | Importer logic bug | Legacy row contains `Egypt, probably Cairo.`; `object-transformer.ts` computes `provenanceMarkdown`; `sql-strategy.ts` omits `provenance` from the `INSERT INTO item_translations` columns. | Story 1 |
| Baroque | Timeline links absent for sampled BAR object/monument | Importer logic gap | No explicit BAR item-HCR relation table was found. Core `mwnf3.hcr` and `mwnf3.hcr_events` contain chronology events by country/date. The importer must materialize the dynamic Baroque Art timeline and bind it to the parent collection. | Story 2 |
| Baroque | Monument special features/detail grouping not represented | Importer logic bug | `MonumentDetailImporter` throws when parent monument is missing. Project convention requires detail items to import anyway with nullable `parent_id` and a warning. | Story 3 |
| Sharing History | Partner profile/gallery images missing for AT_01/DZ_01 | Importer logic gap | Phase 03 has `sh-partner-logo-importer.ts`; no SH partner picture importer is exported or run. | Story 4 |
| Sharing History | National-context child collections missing content | Importer logic bug | The current importer uses `sh_national_context_exhibition_texts`, but that table has only 3 dummy rows and must be ignored. Collections come from `sh_national_context_exhibitions`; ordered item images come from `sh_national_context_exhibition_images`. | Story 5 |
| Explore | Referenced monuments create shells instead of using source items | Importer logic bug | Explore has reference tables/columns for VM, Travels, and SH monuments. Referenced monuments must resolve to source items and use them in Explore collections. Explore-native monuments are already properly imported and must remain native. | Story 6 |
| Explore | Itinerary title/description/duration generic/missing | Importer logic gap | Generic `Itinerary {id}` titles must not be used when source titles exist. Explore itineraries contain both Explore-native monuments and resolved source monuments. | Story 7 |
| THG | Galleries 4/34 treated as missing `exhibition_i18n` translations | Analysis correction / importer scope clarification | `thg_gallery_lang` supplies gallery titles. `exhibition_i18n` exists only for exhibition collections. Galleries 4 and 34 are galleries; gallery 47 is an exhibition. | Story 8 |
| Colours / THG | EXHCOLOUR contextual text masks item metadata | Importer logic bug | `ThgThemeItemTranslationImporter` writes `theme_item_i18n.contextual_description` as `item_translations` with `name: ''`. Contextual theme text belongs to the theme collection or collection-item association, not to the item. | Story 9 |
| Colours / THG | Gallery 47 timeline not imported | Importer logic gap | THG timeline/HCR data is not imported as timeline rows. THG exhibition timelines must be materialized by the importer. | Story 10 |
| THG | Contributor translation crashes | Importer logic bug | Log contains `translation (undefined) failed: Cannot read properties of undefined (reading 'toLowerCase')`. The importer must validate expected language values before lookup and raise explicit warnings/errors. | Story 11 |
| THG | Theme media parent skips | Importer logic bug or orphan data | Collection media importer skips theme media when theme collections are missing. Valid media must import; orphan references must raise explicit warning/error. No fallback behavior is allowed. | Story 12 |
| THG | Collection partners, logos, images, related PDFs incomplete | Importer logic gap | Legacy `exhibition_partner`, `exhibition_logo`, `exhibition_related_content`, and `exhibition_related_content_i18n` fit `CollectionPartner`, `CollectionImage`, and `CollectionMedia` after document support is added. Portal CMS remains out of scope. | Story 13 |
| Travels | Algeria/Syria missing child hierarchy; Portugal partial | Importer logic bug | Legacy rows exist and no status/filter columns exclude them. Log shows 351 location errors from duplicate internal names, then 946 monument skips due to missing parent locations. | Story 14 |
| Travels | Media much lower than legacy | Importer logic bug, mostly cascading from missing parents | Travel picture importers exist. Log shows `Travels Location Pictures: 18 imported, 204 errors` and `Travels Monument Pictures: 231 imported, 2163 errors`, mostly parent not found. | Story 14 |
| Travels / Portal CMS | Tours, travel agencies, portal panels, menus, newsletter/legal/CMS pages | Out of scope | These are not currently retained as first-class Inventory model concepts. | No importer story unless scope changes |

## Detailed Findings

### 1. `mwnf3` Object Provenance Is Dropped By The SQL Writer

Validation found provenance missing for `mwnf3:objects:ISL:eg:Mus01:1`. A read-only legacy check confirmed that the English source row contains `Egypt, probably Cairo.`. The imported Inventory translation has `provenance = NULL`.

The transformer already maps provenance:

- `scripts/importer/src/domain/transformers/object-transformer.ts` computes `const provenanceMarkdown = obj.provenance ? convertHtmlToMarkdown(obj.provenance) : null;` and assigns `provenance: provenanceMarkdown`.

The SQL writer omits the destination column:

- `scripts/importer/src/strategies/sql-strategy.ts` inserts `item_translations` columns through `method_for_provenance`, then `obtention`, but not `provenance`.

This explains why all mwnf3 object provenance can be lost even when the source row is valid.

#### Story 1: Persist Item Translation Provenance

**Goal:** Preserve legacy object provenance in `item_translations.provenance`.

**Implementation notes:**

- Update `writeItemTranslation()` in `scripts/importer/src/strategies/sql-strategy.ts` to include the `provenance` column and `safeNull(sanitized.provenance)` value.
- Verify `ItemTranslationData` includes `provenance`; the transformer path does.
- Add or update a self-contained importer test around a transformed object row with provenance.
- Re-run the object import path or a focused import fixture.

**Acceptance criteria:**

- Given a legacy object row with `provenance = 'Egypt, probably Cairo.'`, the imported `item_translations.provenance` contains the Markdown-converted value.
- The sampled key `mwnf3:objects:ISL:eg:Mus01:1` imports English provenance.
- Existing item translation fields still map in the same order and no column/value mismatch is introduced.

### 2. Baroque Timeline Must Be Materialized By The Importer

The BAR report found no sampled `timeline_event_item` links for `mwnf3:objects:BAR:pt:Mus11_A:13` and `mwnf3:monuments:BAR:pt:Mon11:23`.

Read-only legacy checks found:

- `mwnf3.hcr` has 1,075 chronology rows with `hcr_id`, `country_id`, `name`, `from_ad`, `to_ad`, `from_ah`, and `to_ah`.
- `mwnf3.hcr_events` has 4,415 translated event rows with `hcr_id`, `lang_id`, `name`, `description`, `datedesc_ah`, and `datedesc_ad`.
- No core `mwnf3` table was found that explicitly links BAR objects or monuments to HCR events.
- The sampled BAR object and monument carry date ranges and country data, and Portugal HCR rows exist in the relevant date windows.

The dedicated Baroque Art website calculates this dynamically because its UI knows the legacy rules. Inventory must not rely on that future UI behavior. The importer must create a timeline bound to the Baroque Art parent collection and feed it with the events selected by the legacy dynamic logic.

#### Story 2: Materialize Baroque Art Timeline From Legacy HCR

**Goal:** Create a Baroque Art collection-bound timeline and import the actual events used by the legacy Baroque Art website.

**Implementation notes:**

- Inspect the legacy Baroque Art timeline code and document the event-selection rules.
- Implement those rules in an importer over `mwnf3.hcr` and `mwnf3.hcr_events`.
- Create or reuse a `timelines` row bound to the Baroque Art collection.
- Import `timeline_events` and `timeline_event_translations` for the selected HCR rows.
- Materialize item-to-event associations in `timeline_event_item` when the legacy item-page behavior requires item-specific event links. Do not leave that responsibility to rendering layers.

**Acceptance criteria:**

- The Baroque Art collection has a timeline after import.
- The timeline contains the HCR events selected by the legacy Baroque Art dynamic rules.
- Sampled events around the BAR Portugal object/monument date ranges are imported with translated text.
- Any item-specific timeline pivots required by the legacy behavior are imported into `timeline_event_item`.
- The importer logs the applied rule and counts of imported/skipped events.

### 3. Monument Details Must Import Even When Parent Is Missing

Project convention defines monument details as `Item` records of type `detail` with a parent `Item` of type `monument`. The parent foreign key is nullable. The first image of a detail becomes the detail item image; all detail images also become child `Item` records of type `picture` under the detail item.

Current importer behavior does not follow this convention:

- `MonumentDetailImporter` resolves the parent monument and throws when it is missing.
- The log shows many BAR Czech detail failures such as `Parent monument not found: mwnf3:monuments:BAR:cz:Mon11:23`.
- `MonumentDetailPictureImporter` creates child `picture` items and attaches the first image to the parent detail item, but this phase cannot run for details that fail earlier.

Missing parent monuments must still be visible as import warnings/errors, but they must not prevent detail item import.

#### Story 3: Import Orphan Monument Details With Nullable Parent

**Goal:** Import valid monument detail records as `detail` items even when the parent monument is missing.

**Implementation notes:**

- Update `MonumentDetailImporter` to treat missing parent monument as a warning and set `parent_id = NULL`.
- Keep hard failures for missing project context, collection, partner, or invalid required detail data.
- Preserve all detail translations and artist/tag handling.
- Ensure `MonumentDetailPictureImporter` still attaches the first image to the detail item and creates child `picture` items for all images.
- Review SH monument detail importers for the same parent-hard-fail pattern and apply the same convention where applicable.

**Acceptance criteria:**

- BAR Czech detail rows import as `Item` records with `type = detail` and `parent_id = NULL` when the parent monument is missing.
- The importer emits explicit warnings naming the missing parent BC key.
- Details with present parent monuments import with `parent_id` set.
- Detail images import after their detail item exists, including first-image attachment and child picture items.

### 4. Sharing History Partner Profile Photos Have No Importer

The SH report found partner logos but no partner profile/gallery photos for sampled partners AT_01 and DZ_01.

Importer evidence:

- `scripts/importer/src/importers/phase-03/sh-partner-logo-importer.ts` imports SH partner logos.
- `scripts/importer/src/importers/phase-03/index.ts` exports `ShPartnerLogoImporter` but no SH partner picture importer.
- The CLI imports SH partner logos, but no SH partner pictures step was found.

This is a logic gap: the source content is public and image-bearing, but only logos are imported.

#### Story 4: Add Sharing History Partner Picture Importer

**Goal:** Import SH partner profile/gallery photos into `partner_images`.

**Implementation notes:**

- Create a `ShPartnerPictureImporter` in `scripts/importer/src/importers/phase-03/`.
- Follow the pattern of `scripts/importer/src/importers/phase-02/partner-picture-importer.ts` for normal MWNF partner pictures.
- Query the SH partner picture table or source used by the legacy partner pages.
- Resolve partner BC keys such as `mwnf3_sharing_history:sh_partners:at_01` and `mwnf3_sharing_history:sh_partners:dz_01`.
- Write `partner_images` with caption/copyright/alt text where available.
- Export the importer and add it to the phase order after SH partner import and before validation.

**Acceptance criteria:**

- Sampled partners AT_01 and DZ_01 have imported `partner_images` matching the visible profile photos.
- SH partner logos continue to import into the logo table.
- Missing parent partner rows are reported with explicit warnings or errors.

### 5. SH National Context Must Ignore The Dummy Text Table

The previous analysis was wrong: `sh_national_context_exhibition_texts` is not a valid source for national context import. Read-only legacy checks found only 3 dummy rows in that table.

Relevant tables:

- `sh_national_context_exhibitions` defines the national-context exhibition/country pivot. For exhibition 3, countries are `fr`, `jo`, `pt`, `rm`, and `tn`.
- `sh_national_context_exhibition_images` attaches ordered representative item references using `item_type`, `image_item`, and `sort_order`.

Mapping rule:

- `item_type = 'obj'` plus `image_item = 'AWE;tn;90'` maps to the imported SH object BC for project/country/number.
- `item_type = 'mon'` maps to the imported SH monument BC for project/country/number.
- `sort_order` maps to the `collection_item.display_order`.

Current `ShNationalContextImporter` imports collections, then reads the dummy text table, then links images/items. It must stop treating missing rows in `sh_national_context_exhibition_texts` as missing translation data.

#### Story 5: Rework SH National Context Import Around Exhibition And Image Tables

**Goal:** Import national-context collections and their ordered representative items from the two real source tables.

**Implementation notes:**

- Keep `importNCCollections()` based on `sh_national_context_exhibitions`.
- Remove or disable `importNCTexts()` against `sh_national_context_exhibition_texts`.
- Import `sh_national_context_exhibition_images` as ordered `collection_item` links.
- Parse `image_item` into project/country/number and resolve item BC by `item_type`.
- Do not synthesize fake descriptions from the dummy text table.
- Add explicit warning/error behavior for invalid `image_item`, unknown `item_type`, missing item, and duplicate collection-item links.

**Acceptance criteria:**

- Exhibition 3 national-context child collections are created for the five source countries.
- The 13 source rows in `sh_national_context_exhibition_images` for exhibition 3 resolve to imported items when those items exist.
- `sort_order` is preserved on `collection_item`.
- The importer ignores `sh_national_context_exhibition_texts` and does not report absent rows from that table as content loss.

### 6. Explore References Must Reuse Source Items, Not Create Duplicates

Explore monuments fall into two categories:

- Explore-native monuments, which are real Explore items and are already properly imported.
- References to already-imported VM/mwnf3, Travels, or SH monument items, which must resolve to the source item and use that item in Explore collections.

The importer currently creates Explore monument shell items for all rows and then creates item-item cross-reference links through `ExploreMonumentCrossRefImporter`. That preserves relationships, but it still leaves unwanted duplicate Explore items for referenced monuments.

Read-only checks found that sampled IDs 299, 300, and 1780 are standalone Explore rows with no reference fields populated. ID 1780 has native description/image data; IDs 299 and 300 have sparse source text/image data. These sample facts do not change the rule: referenced Explore monuments must not become duplicate items, and Explore-native monuments must continue to import as native items.

When a referenced Explore monument has text data in `mwnf3_explore.exploremonumentext`, the importer must add that text to the looked-up source item under the Explore master context.

#### Story 6: Resolve Explore Monument References To Existing Items

**Goal:** Prevent duplicate Explore item shells for monuments that reference already-imported source items, while preserving Explore-native monuments.

**Implementation notes:**

- Identify reference fields/tables in `mwnf3_explore.exploremonument`, `exploremonument_vm`, `exploremonument_tr`, and `exploremonument_sh` before creating an Explore item.
- For referenced monuments, resolve the target item BC and use that target item in Explore location/thematic-cycle/itinerary collection links.
- Do not create a duplicate `mwnf3_explore:monument:{id}` item for referenced monuments.
- Store mapping metadata so later Explore import phases can resolve the Explore monument ID to the target item ID.
- If `exploremonumentext` has text for a referenced monument, write an `item_translation` for the target item using the Explore context.
- Keep Explore-native monument import unchanged for rows without source references.

**Acceptance criteria:**

- Referenced VM/Travels/SH Explore monuments link the existing source item into Explore collections.
- Explore-native monuments such as the verified native sample 1780 continue to import as `mwnf3_explore:monument:{id}` items.
- No duplicate item exists solely because Explore references an existing monument.
- Explore-context text for referenced monuments is imported onto the looked-up source item.
- Missing target references raise explicit warnings/errors; no fallback item is created.

### 7. Explore Itinerary Presentation And Membership Are Under-Modeled

The Explore report found generic or wrong itinerary titles and missing duration/description for sampled itinerary 6 and sub-itinerary 7. Generic `Itinerary {id}` titles must never be used when source titles exist.

Explore itineraries also contain a mixed set of Explore-native monuments and monuments from other sources. The importer must resolve each member using the same rule as Story 6: native rows use native Explore items; referenced rows use already-imported source items.

#### Story 7: Complete Explore Itinerary Translation And Member Resolution

**Goal:** Import visible Explore itinerary content and include every itinerary monument through the correct item identity.

**Implementation notes:**

- Inspect the legacy itinerary tables and API response used by `/itineraries/c-eg/i-6` and `/itineraries/c-eg/i-6/si-7`.
- Import public title, description, duration, route/local-team content, and ordering into `collection_translations`, `extra`, and collection hierarchy fields.
- Replace generic title behavior with warning/error behavior when a required title is missing.
- Resolve itinerary monument membership through the native/reference mapping from Story 6.

**Acceptance criteria:**

- `mwnf3_explore:itinerary:6` imports the real source title rather than `Itinerary 6`.
- `mwnf3_explore:itinerary:7` imports `The Seat of the Sultanate`, duration `One day`, and visible descriptive text when those values exist in source.
- Explore itinerary collections include both Explore-native monument items and resolved source monument items.
- Missing required title/member data produces explicit warnings/errors.

### 8. THG Gallery And Exhibition Processing Must Share Common Ground And Add Exhibition Extras

The previous analysis incorrectly treated missing `exhibition_i18n` rows for galleries 4 and 34 as missing gallery translations.

Verified source model:

- `thg_gallery` contains both galleries and exhibitions.
- `thg_gallery_lang` supplies base gallery titles and descriptive text.
- `exhibition_i18n` exists only for exhibitions and supplies extra exhibition-specific data.
- Absence of `exhibition_i18n` means the collection is a gallery.
- Presence of `exhibition_i18n` means the collection is an exhibition with all gallery features plus additional exhibition features.

Current importer issues:

- `ThgGalleryImporter` classifies exhibitions through `project_id = 'EXH'` rather than directly using the presence of `exhibition_i18n`.
- `ThgGalleryTranslationImporter` imports only `exhibition_i18n`, so normal gallery translations from `thg_gallery_lang` are not represented as collection translations.
- Gallery and exhibition common processing is split incorrectly.

#### Story 8: Rework THG Gallery And Exhibition Collection Import

**Goal:** Import all THG collections with shared gallery processing and exhibition-only enrichment.

**Implementation notes:**

- Import every `thg_gallery` row as a collection.
- Determine `type = exhibition` from the presence of `exhibition_i18n` rows; otherwise use `type = gallery`.
- Import common collection translations from `thg_gallery_lang` for both galleries and exhibitions.
- Import exhibition-specific translations and extra fields from `exhibition_i18n` only for exhibitions.
- Preserve gallery/exhibition URLs from `thg_gallery_url` where the model supports them.
- Keep themes, theme translations, item associations, tags, and media behavior common to both galleries and exhibitions unless a table is explicitly exhibition-only.

**Acceptance criteria:**

- Galleries 4 and 34 import titles from `thg_gallery_lang`, not from `exhibition_i18n`.
- Gallery 47 imports common gallery data plus exhibition-specific data from `exhibition_i18n`.
- Collection type reflects the presence or absence of exhibition-specific rows.
- Validators no longer report galleries without `exhibition_i18n` as missing exhibition translations.

### 9. THG Theme Item Contextual Descriptions Are Imported To The Wrong Target

The Colours report found `mwnf3:objects:EXHCOLOUR:us:Mus51:15` with `internal_name = Brush Washer`, but a sampled English contextual translation row had blank `name` and missing object metadata.

The current importer explains the problem:

- `ThgThemeItemTranslationImporter` reads `theme_item_i18n.contextual_description`.
- It writes that value as an `item_translations` row for the resolved item.
- It writes `name: ''`, which is invalid importer behavior.

This is a target-model bug, not a UI/API issue. Theme item contextual text is about the item in a theme collection. The theme itself is a child collection of the gallery/exhibition, and its translation is handled by `ThgThemeTranslationImporter`. The item should keep its base object translation. The contextual text must be stored where the importer models the item-in-theme relation, not as a replacement item translation.

#### Story 9: Move THG Item Contextual Text Out Of Item Translations

**Goal:** Preserve THG contextual descriptions without overwriting or masquerading as base item metadata.

**Implementation notes:**

- Stop writing `theme_item_i18n.contextual_description` as `item_translations` with an empty name.
- If contextual text describes the theme collection, merge it into the relevant `collection_translations` for the theme only when the legacy semantics confirm that target.
- If contextual text describes the specific item-in-theme occurrence, store it on the `collection_item` pivot `extra` or a dedicated relation translation structure if one exists.
- When the target field cannot accept the data without loss, raise a clear importer error and document the missing model support.
- Never write empty string as an item translation `name`. Use `NULL` only if the schema allows it; otherwise raise a warning/error.

**Acceptance criteria:**

- No `item_translations` row is created with `name = ''` from `theme_item_i18n`.
- Base item translations for EXHCOLOUR objects remain the source of object name/date/holder/location/dimensions.
- Contextual descriptions remain imported in the correct collection/relation target.
- If a model change is needed to retain contextual item-in-theme text, the importer fails loudly instead of storing it in the wrong table.

### 10. THG Exhibition Timelines Must Be Imported

The live Colours timeline for gallery 47 has timeline events. The importer has generic timeline support and a Sharing History HCR image/link importer, but no importer for THG exhibition timeline rows was found.

Like the Baroque timeline, this must be importer work. Rendering layers must receive imported timeline data; they must not reimplement legacy timeline selection logic.

#### Story 10: Import THG Exhibition Timelines

**Goal:** Import thematic-gallery exhibition timeline events and translations for gallery/exhibition timelines.

**Implementation notes:**

- Identify the THG legacy timeline/HCR tables or query rules used by the Colours timeline endpoint.
- Create a THG timeline importer that writes `timelines`, `timeline_events`, and `timeline_event_translations` with BC keys under `mwnf3_thematic_gallery:*`.
- Bind each imported timeline to the relevant THG collection.
- Import event images or event-item relations if the legacy timeline exposes them.

**Acceptance criteria:**

- Gallery/exhibition 47 has an imported timeline with the expected legacy event set.
- Sampled live timeline events import with date and English text.
- Existing mwnf3 and SH timeline import behavior remains unchanged.

### 11. THG Contributor Import Must Validate Required Language Values

The log shows contributor translation warnings such as:

- `Contributor 14: translation (undefined) failed: Cannot read properties of undefined (reading 'toLowerCase')`
- Similar warnings for other contributors and exhibition partners.

This is a real importer bug. The code must verify expected values before calling `toLowerCase()` or language lookup helpers. Missing expected values must produce explicit warnings or errors consistently across all similar cases.

#### Story 11: Harden THG Contributor And Partner Translation Validation

**Goal:** Replace undefined language crashes with explicit validation and consistent warning/error reporting.

**Implementation notes:**

- Review `thg-contributor-importer.ts` and helper calls used by contributor and exhibition-partner translations.
- Add explicit checks for missing/empty language values before any `.toLowerCase()` or language lookup.
- Search the import log for similar `undefined` and `toLowerCase` failures and handle those code paths consistently.
- Do not invent fallback languages or placeholder translations.
- Import the contributor shell only when required contributor data is valid; otherwise report a clear error.

**Acceptance criteria:**

- The THG contributor phase produces no `Cannot read properties of undefined` warnings.
- Missing translation language values are logged with source table, primary key, and reason.
- Valid contributor and exhibition-partner translations still import.
- No fallback language or degraded translation behavior is introduced.

### 12. THG Theme Media Must Import Valid Rows And Report Orphans

The log shows collection-media skips for galleries 52 and 54 because theme collections are not found:

- `Skipping theme audio: theme collection not found for BC=mwnf3_thematic_gallery:thg_theme:54:6`
- Similar skips for gallery 52/54 theme media.

No fallback is allowed. When the source media references a valid theme, the theme collection import must be fixed. When the source media references an orphan theme, the importer must raise a warning or error that names the orphan source row.

#### Story 12: Resolve THG Theme Media Parent Collections

**Goal:** Import all valid THG audio/video media and explicitly report orphaned theme references.

**Implementation notes:**

- Query legacy theme rows for galleries 52 and 54 and compare them with collections imported by `ThgThemeImporter`.
- Fix backward-compatibility naming mismatches between `ThgThemeImporter` and `CollectionMediaImporter` if present.
- Import valid `exhibition_audio`/`theme_audio` and `exhibition_video`/`theme_video` rows into `collection_media`.
- Report orphan media as warnings/errors with media ID, gallery ID, and theme ID.

**Acceptance criteria:**

- Valid theme media imports into `collection_media`.
- Orphan media does not silently skip and does not attach to a fallback collection.
- The importer logs exact source IDs for every missing parent collection.

### 13. THG Collection Partners, Logos, Images, And Related Documents Are In Scope

The previous analysis was too vague. Several THG exhibition presentation entities fit current model concepts and must be imported:

- `exhibition_partner` fits `CollectionPartner` when it represents an organization associated with a collection.
- `exhibition_logo` and gallery/exhibition image/banner fields fit `CollectionImage`.
- `exhibition_audio` and `exhibition_video` fit `CollectionMedia`.
- `exhibition_related_content` and `exhibition_related_content_i18n` fit `CollectionMedia` when they are URL-like related media.
- Uploaded related PDFs need document support. `MediaType` and the `collection_media.type` enum currently only support `audio` and `video`; add `document` before importing PDF URLs/paths into `CollectionMedia`.

Portal CMS content remains out of scope for the importer.

#### Story 13: Import THG Collection Partners, Logos, Images, And Related Content

**Goal:** Import THG exhibition-level partner, logo, image, audio/video, and related document data into existing Inventory models.

**Implementation notes:**

- Add `DOCUMENT` to `App\Enums\MediaType` and create a database-agnostic migration to allow `collection_media.type = 'document'`.
- Extend importer `CollectionMediaData` and strategy typing to accept `document`.
- Import `exhibition_logo` and gallery/exhibition image fields into `collection_images` with explicit source BC keys.
- Import partner associations into `collection_partner` when the legacy row represents a partner organization. Contributor/person entries remain contributors.
- Import URL-like related content rows as `CollectionMedia` records with appropriate type.
- Import uploaded related PDFs as `document` media when they are collection-level resources.
- Keep Portal CMS menus/panels/newsletter/legal/video/learning pages out of importer scope.

**Acceptance criteria:**

- Gallery/exhibition 47 imports its exhibition partner association as `CollectionPartner` where the source represents a partner organization.
- Gallery/exhibition 47 imports logo/image assets as `CollectionImage`.
- Audio/video URLs import as existing collection media types.
- Related PDF URLs or uploaded document paths import as `document` collection media after enum and database support exist.
- Portal CMS-only data remains excluded and documented as out of scope.

### 14. Travels Coverage And Media Failures Cascade From Non-Unique Collection Internal Names

The Travels report found missing Algeria/Syria child hierarchy, partial Portugal coverage, and much lower media counts. Read-only production checks confirmed the legacy data exists and is not filtered by status columns. The log reveals the root cause pattern.

Key log evidence:

- `Travels Itineraries: 108 imported, 1 errors`, with duplicate `Mudejar Art` for `IAM:pt:1:I`.
- `Travels Locations: 54 imported, 5 skipped, 351 errors`, with many duplicate `collections_internal_name_unique` errors such as `SINTRA`, `SANTAREM`, `COIMBRA`, etc.
- `Travels Monuments: 103 imported, 946 skipped`, mostly `Parent location not found`.
- `Travels Location Pictures: 18 imported, 204 errors`, mostly `Parent location collection not found`.
- `Travels Monument Pictures: 231 imported, 99 skipped, 2163 errors`, mostly `Parent monument item not found`.

The importer reads the full source tables:

- `travels-itinerary-importer.ts` queries all `mwnf3_travels.tr_itineraries` and groups by `(project_id, country, trail_id, number)`.
- `travels-location-importer.ts` queries all `tr_locations` and groups by `(project_id, country, trail_id, itinerary_id, number)`.
- `travels-monument-importer.ts` queries all `tr_monuments` and groups by `(project_id, country, trail_id, itinerary_id, location_id, number)`.
- Travel picture importers exist and import from `tr_trails_pictures`, `tr_itineraries_pictures`, `tr_locations_pictures`, and `tr_monuments_pictures`.

The failure is that collection `internal_name` is globally unique, while Travels location and itinerary titles repeat naturally across trails/countries. The importers select display titles such as `SINTRA` as `internal_name`, which collides with existing collections. Once parent locations fail, monuments and pictures cascade into missing-parent skips/errors.

The current production Inventory contains at least one corrected namespaced itinerary internal name for `mwnf3_travels:itinerary:IAM:pt:1:I`, so the code or data has changed since the logged duplicate error. The log under analysis still documents the bug pattern that explains the missing pieces in the validation reports.

#### Story 14: Namespace Travels Collection Internal Names And Re-import Child Hierarchy

**Goal:** Import all valid Travels trails, itineraries, locations, monuments, and media without global `internal_name` collisions.

**Implementation notes:**

- Update Travels collection importers to generate stable unique internal names from BC components, not only display titles.
- Keep public titles in `collection_translations.title`; do not use title uniqueness as identity.
- Apply the same principle to travel monument item internal names if needed.
- Re-run Travels phases from itinerary onward after clearing or idempotently updating failed rows.
- Ensure case normalization for BC keys is consistent between parent and picture importers. Current log contains both uppercase and lowercase variants in parent-not-found messages.

**Acceptance criteria:**

- Travels Locations imports close to the 410 unique legacy groups without duplicate `collections_internal_name_unique` errors.
- Travels Monuments imports close to the 1,049 unique legacy groups, except for explicitly documented orphan source rows.
- Algeria and Syria have imported itineraries, locations, and monuments where legacy rows exist.
- Travels Location Pictures and Monument Pictures no longer fail due to parent-not-found cascades for valid rows.
- Public display titles remain unchanged in translations.

## Out-Of-Scope Or Retained-Model Decisions

The gap reports mention some public website entities that are not currently retained as Inventory model concepts. These are not importer bugs unless the product scope changes.

| Entity family | Current recommendation |
|---|---|
| Travels tours | Keep out of importer bug list unless Inventory is explicitly expanded to travel packages/tours. |
| Travels travel agencies | Keep out of importer bug list unless partner scope expands to travel agencies. |
| Portal panels, menus, newsletters, legal pages, videos, learning pages | Treat as Portal CMS scope, not Inventory content. |
| Display-time punctuation differences | Do not track as import bugs when the legacy column value is preserved. |
| Parent item plus child picture model | Do not track as missing multi-image data when child `type = picture` items or item images exist according to the chosen Inventory model. |

## Recommended Next Work Order

1. Fix `item_translations.provenance` persistence. This is small, high-confidence, and affects all imported object provenance.
2. Fix Travels internal-name collisions and re-run Travels child phases. This explains the largest missing-data surface.
3. Rework SH national context to ignore the dummy text table and import ordered item images from the real source tables.
4. Add SH partner picture importer.
5. Materialize Baroque timelines from HCR dynamic logic and bind them to the Baroque Art collection.
6. Fix monument detail orphan handling so details import with nullable parent and warnings.
7. Rework Explore referenced monuments to reuse source items while preserving Explore-native monuments.
8. Rework THG gallery/exhibition common processing and move contextual item text to the correct collection/relation target.
9. Import THG timelines, partners, logos, collection images, and document media after adding `document` collection media support.
