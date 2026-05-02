# Import Gap Root-Cause Analysis

Date: 2026-05-02

## Scope

This document correlates the import validation reports with:

- the full import log at `scripts/importer/logs/import-2026-04-20T11-14-12-177Z.log`;
- importer source under `scripts/importer/src`;
- read-only production legacy database checks on the Windows server;
- read-only imported Inventory checks on production Inventory databases.

The purpose is to distinguish legacy data quality issues from importer logic bugs, and to capture fixable importer issues as implementation stories.

## Executive Summary

Several reported gaps are real importer issues, not legacy data problems:

- `item_translations.provenance` is dropped by the SQL writer even when `object-transformer.ts` computes it.
- Travels country/child coverage is broken by global `collections.internal_name` collisions, which cause parent locations to be missing and cascade into skipped monuments and image errors.
- Sharing History partner profile photos are not imported because only SH partner logos have an importer.
- Explore public monument pages rely on cross-source fallback descriptions/images that the Explore importer does not import or resolve.
- THG/Colours timeline and several exhibition-level media/contributor/link families are either missing importers or incomplete importers.

Some gaps are legacy data issues or scope decisions:

- THG gallery English translations for galleries 4 and 34 are absent from `mwnf3_thematic_gallery.exhibition_i18n`.
- Sharing History national-context text for exhibition 3 is absent from `sh_national_context_exhibition_texts`.
- Portal CMS content, Travels tours, and travel agencies are not currently retained as first-class Inventory model concepts.

## Classification Matrix

| Area | Reported issue | Current classification | Evidence summary | Story |
|---|---|---|---|---|
| Islamic Art / `mwnf3` objects | Provenance missing for `mwnf3:objects:ISL:eg:Mus01:1` | Importer logic bug | Legacy row contains `Egypt, probably Cairo.`; `object-transformer.ts` computes `provenanceMarkdown`; `sql-strategy.ts` omits `provenance` from the `INSERT INTO item_translations` columns. | Story 1 |
| Baroque | Item timeline links absent for sampled BAR object/monument | Importer gap or presentation-scope gap | `writeTimelineEventItem()` exists, but importer usage is for Sharing History HCR images. No importer was found for BAR/mwnf3 item-to-timeline pivots. The legacy site needs a source-code check to determine whether item timelines are explicit pivots or dynamic country/date results. | Story 2 |
| Baroque | Monument special features/detail grouping not represented | Mixed: importer has detail importer, but failures are missing-parent cascades for Czech; UI grouping remains model/design work | Log lines around 692-814 show many `BAR:cz:Mon11:*` monument detail failures due to parent monument not found. The sampled Portuguese parent has child picture items, but structured special-feature grouping is not represented as such. | Story 3 |
| Sharing History | Partner profile/gallery images missing for AT_01/DZ_01 | Importer logic gap | Phase 03 has `sh-partner-logo-importer.ts`; no SH partner picture importer is exported or run. | Story 4 |
| Sharing History | National-context child translations missing for exhibition 3 | Legacy data issue | Production legacy check found no `sh_national_context_exhibition_texts` rows for exhibition 3. Importer can only synthesize translations when `context` rows exist. | No importer story; document data gap |
| Explore | Monuments 300/299 missing visible fallback descriptions/images | Importer logic gap | `ExploreMonumentImporter` reads only `exploremonument` fields and `exploremonumentext.name`; transformer stores only identity and coordinates. The live site uses fallback sources for VM/Travels/SH-backed content. | Story 5 |
| Explore | Itinerary title/description/duration generic/missing | Importer gap / needs targeted verification | Existing itinerary importers do not import the full live itinerary presentation model. Relation pivots need inspection for extra data, and visible titles/duration/text need first-class collection translations or extra fields. | Story 6 |
| Thematic Gallery | Galleries 4 and 34 missing English title/description | Legacy data issue, with optional fallback story | Production legacy check found no `exhibition_i18n` rows for galleries 4 and 34. The importer only imports `exhibition_i18n`. | Optional Story 7 |
| Colours / THG | EXHCOLOUR base item translation has blank name/date/holder/location/dimensions | Importer logic gap | `thg-theme-item-translation-importer.ts` creates contextual item translations with `name: ''` and description only. Base EXHCOLOUR object metadata needs to come from the normal object import or contextual translation must not masquerade as the base object translation. | Story 8 |
| Colours / THG | Gallery 47 timeline not imported | Importer gap | THG HCR/timeline data is not imported as timeline rows. Existing timeline importer focuses on mwnf3/SH chronology. | Story 9 |
| THG / Portal | Collection partners, contributors, related PDFs/authors/logos/media incomplete | Mixed importer gaps and scope decisions | Contributor importer has undefined-language warnings; collection media importer skips theme media when theme collections are missing. No collection-partner importer was found. Portal CMS data is outside current Inventory scope. | Stories 10-12 |
| Travels | Algeria/Syria missing child hierarchy; Portugal partial | Importer logic bug | Legacy rows exist and no status/filter columns exclude them. Log shows 351 location errors from duplicate internal names, then 946 monument skips due to missing parent locations. | Story 13 |
| Travels | Media much lower than legacy | Importer logic bug, mostly cascading from missing parents | Travel picture importers exist. Log shows `Travels Location Pictures: 18 imported, 204 errors` and `Travels Monument Pictures: 231 imported, 2163 errors`, mostly parent not found. | Story 13 |
| Travels | Tours and travel agencies absent | Intentionally out of current model unless scope changes | Legacy data exists, but these entities are not retained as first-class Inventory records. | No importer story unless scope changes |

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
- Verify `ItemTranslationData` already includes `provenance`; the transformer path does.
- Add or update a unit/integration test around a transformed object row with provenance.
- Re-run the import path for objects or a focused import fixture.

**Acceptance criteria:**

- Given a legacy object row with `provenance = 'Egypt, probably Cairo.'`, the imported `item_translations.provenance` contains the Markdown-converted value.
- The sampled key `mwnf3:objects:ISL:eg:Mus01:1` imports English provenance.
- Existing item translation fields still map in the same order and no column/value mismatch is introduced.

### 2. Baroque Item Timeline Links Are Not Imported For `mwnf3` Items

The BAR report found no sampled `timeline_event_item` links for `mwnf3:objects:BAR:pt:Mus11_A:13` and `mwnf3:monuments:BAR:pt:Mon11:23`.

Importer evidence:

- `writeTimelineEventItem()` exists in `scripts/importer/src/strategies/sql-strategy.ts`.
- `scripts/importer/src/importers/phase-05/timeline-importer.ts` uses it for Sharing History `sh_hcr_images` item-linked chronology rows.
- No equivalent importer was found for `mwnf3` object/monument timeline links.

This is an importer gap if the legacy BAR website stores explicit item-event relationships. It is a presentation-scope gap if the legacy page derives the item timeline dynamically from item country/date and a country HCR search.

#### Story 2: Decide And Implement `mwnf3` Item Timeline Relations

**Goal:** Make item timeline behavior explicit for `mwnf3` object and monument pages.

**Implementation notes:**

- Inspect the BAR/Islamic legacy item timeline code and identify whether explicit relation tables exist or whether the site calculates timeline events by country/project/date range.
- If explicit relation tables exist, add a focused importer that resolves:
  - event BC, likely `mwnf3:hcr:{event_id}`;
  - item BC, e.g. `mwnf3:objects:{project}:{country}:{museum}:{number}` or `mwnf3:monuments:{project}:{country}:{institution}:{number}`;
  - `timeline_event_item` rows using `writeTimelineEventItem()`.
- If the legacy site calculates timeline dynamically, document this as a future API/UI query requirement rather than an import defect.

**Acceptance criteria:**

- The implementation decision is documented with legacy code/table evidence.
- If importer-based, sampled BAR object/monument records have expected `timeline_event_item` rows after import.
- If query-based, no report labels this as missing imported data; public API/UI requirements describe the dynamic timeline behavior.

### 3. Baroque Monument Details Fail When Parent Monuments Are Missing

The BAR special-feature/detail issue is not simply a missing image problem. The import log shows many detail rows failing because parent monuments are missing, especially Czech BAR rows:

- `Monument detail mwnf3:monument_details:BAR:cz:Mon11:23:1: Parent monument not found: mwnf3:monuments:BAR:cz:Mon11:23`
- Similar errors cover `BAR:cz:Mon11:*` in the log around the Monument Details phase.

The sampled Portuguese monument parent exists and has child picture items. What remains is structured detail/special-feature representation and missing-parent cascades for language/country variants.

#### Story 3: Stabilize Monument Detail Parent Resolution And Representation

**Goal:** Import valid monument details whenever their source parent exists, and define how special-feature groupings appear in Inventory.

**Implementation notes:**

- Audit `MonumentImporter` and `MonumentDetailImporter` for case normalization and language handling in backward-compatibility keys.
- Verify whether `mwnf3.monuments` contains parent Czech BAR rows. If not, classify those Czech errors as legacy orphaned details.
- If parent rows exist but use different key casing or language grouping, normalize parent lookup to the canonical parent item key.
- Decide whether monument details are child `Item` records, child `picture` records, or collection/pivot metadata in the current model.

**Acceptance criteria:**

- Detail rows with valid source parents import without `Parent monument not found` errors.
- Orphaned detail rows are logged as data-quality skips with a concise reason.
- The sampled `BAR:pt:Mon11:23` special-feature behavior is documented and test-covered according to the chosen representation.

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
- Query the SH partner picture table(s) used by the legacy partner pages.
- Resolve partner BC keys such as `mwnf3_sharing_history:sh_partners:at_01` and `mwnf3_sharing_history:sh_partners:dz_01`.
- Write `partner_images` with caption/copyright/alt text where available.
- Export the importer and add it to the phase order after SH partner import and before image sync validation.

**Acceptance criteria:**

- Sampled partners AT_01 and DZ_01 have imported `partner_images` matching the visible profile photos.
- SH partner logos continue to import into the logo table.
- Missing parent partner rows are skipped with explicit data-quality warnings.

### 5. SH National Context Exhibition 3 Has No Source Text Rows

The report noted missing title/description for national-context child collections under SH exhibition 3. Production legacy checks found link rows for countries, but no `sh_national_context_exhibition_texts` rows for exhibition 3, including English.

`ShNationalContextImporter` imports text only from rows where `context IS NOT NULL AND context != ''`. It synthesizes the title and maps `context` to the translation description. With no source text rows, there is nothing to import.

Classification: legacy data gap. No importer fix is recommended unless the business wants generated placeholder translations for empty national-context overlays.

### 6. Explore Monuments Missing Cross-Source Public Content

The Explore report found that monuments 300 and 299 exist as shells with names/coordinates but lack the public descriptions/images shown by the website.

Importer evidence:

- `ExploreMonumentImporter` queries `mwnf3_explore.exploremonument` and `mwnf3_explore.exploremonumentext` name only.
- `explore-monument-transformer.ts` writes item identity, coordinates, zoom, and type only.
- It does not import descriptions, fallback source references, or images.

The live Explore site resolves content from multiple sources when Explore-native fields are sparse: Virtual Museum, Exhibition Trails/Travels, Sharing History, and Explore-native tables. Because those fallback joins are not modeled in the importer, valid website content is lost.

#### Story 5: Import Explore Monument Public Detail Content

**Goal:** Preserve the public description and image content used by Explore monument pages.

**Implementation notes:**

- Inspect the legacy Explore page/API resource that resolves monuments and document its fallback order.
- Extend Explore monument import to either:
  - import fallback description/image data directly into Explore item translations/images; or
  - link the Explore monument item to the source item and expose fallback resolution in the future API/UI.
- Cover at least these samples:
  - `mwnf3_explore:monument:300` Aqmar Mosque, VM-backed content;
  - `mwnf3_explore:monument:299` Amir Bashtak Palace, Travels/Exhibition Trails-backed content;
  - `mwnf3_explore:monument:1780` Explore-native content, which already imports better.

**Acceptance criteria:**

- Monuments 300 and 299 have the public description available through the chosen model path.
- Their visible images are available either as item images or resolvable source-linked images.
- Explore-native monument 1780 continues to import successfully.
- The fallback strategy is documented so validators know whether content is duplicated or linked.

### 7. Explore Itinerary Presentation Is Under-Modeled

The Explore report found generic or wrong itinerary titles and missing duration/description for sampled itinerary 6 and sub-itinerary 7. The current import captures structural collections and item links, but not all website presentation fields.

#### Story 6: Complete Explore Itinerary Translation And Duration Import

**Goal:** Import the visible itinerary and sub-itinerary content needed for public Explore pages.

**Implementation notes:**

- Inspect the legacy itinerary tables and API response used by `/itineraries/c-eg/i-6` and `/itineraries/c-eg/i-6/si-7`.
- Map public title, description, duration, route/local-team content, and ordering into `collection_translations`, `extra`, and/or related collections.
- Avoid using generic `Itinerary {id}` titles when a legacy title exists.

**Acceptance criteria:**

- `mwnf3_explore:itinerary:6` imports the public title `Mamluk Art. Splendour and Magic of the Sultans` or a documented canonical title from the source row.
- `mwnf3_explore:itinerary:7` imports `The Seat of the Sultanate`, duration `One day`, and visible descriptive text.
- Parent/child itinerary hierarchy and linked monuments remain intact.

### 8. THG Gallery 4 And 34 English Translations Are Absent In Source

Production legacy checks found `exhibition_i18n` rows for gallery 47, but no English rows for galleries 4 or 34. The importer correctly imports from `mwnf3_thematic_gallery.exhibition_i18n`; it cannot create translations that do not exist there.

Classification: legacy translation gap. If the live gallery API has display names from another table, an optional fallback importer can be added, but that is a product decision because it changes the source-of-truth rule.

#### Optional Story 7: Add THG Gallery Title Fallbacks For Sparse `exhibition_i18n`

**Goal:** Provide usable collection titles for high-value galleries when `exhibition_i18n` lacks rows.

**Implementation notes:**

- Identify the table/API source that supplies `Amulets and Talismans` and `Scientific objects` on the live sites.
- If approved, update `ThgGalleryTranslationImporter` to create fallback translations from that source only when no `exhibition_i18n` translation exists.
- Mark fallback translations in `extra` so editors know they are not full curated exhibition translations.

**Acceptance criteria:**

- Galleries 4 and 34 have English collection titles after import.
- Fallback translations are distinguishable from curated `exhibition_i18n` rows.
- Gallery 47 continues to use its real `exhibition_i18n` translation.

### 9. EXHCOLOUR Contextual Item Translation Masks Base Metadata

The Colours report found `mwnf3:objects:EXHCOLOUR:us:Mus51:15` with `internal_name = Brush Washer`, but a sampled English `item_translation` row had blank `name` and missing object metadata. `ThgThemeItemTranslationImporter` intentionally writes contextual descriptions with `name: ''` because `theme_item_i18n` only supplies contextual text.

This is not a legacy data issue. It is a modeling/query/import issue: contextual THG translations should complement, not replace or masquerade as, base object translations.

#### Story 8: Preserve Base EXHCOLOUR Object Metadata Beside THG Contextual Text

**Goal:** Ensure EXHCOLOUR item detail pages can show base object metadata and THG contextual descriptions without field loss.

**Implementation notes:**

- Verify whether the normal object importer creates a base translation for `mwnf3:objects:EXHCOLOUR:us:Mus51:15` in the project/default context.
- If base translation is missing, fix object import coverage for `EXHCOLOUR` project rows.
- If base translation exists but validation/UI selects the THG contextual row, update querying rules to prefer base metadata for object fields and contextual translation for gallery narrative.
- Consider changing `ThgThemeItemTranslationImporter` to avoid writing empty-string `name`; use `NULL` if allowed, or preserve base name in contextual row.

**Acceptance criteria:**

- The sampled `Brush Washer` item has a base English translation with name, date, holder, location, dimensions, and description where source data provides them.
- THG contextual description remains available under gallery 47/theme 8 context.
- UI/API selection rules are documented and tested for base-versus-contextual item translations.

### 10. Colours / THG Timeline Is Not Imported

The live Colours timeline for gallery 47 has 45 events. The importer has generic timeline support and a Sharing History HCR image/link importer, but no importer for THG HCR/timeline rows was found.

#### Story 9: Import THG Gallery Timelines

**Goal:** Import thematic-gallery timeline events and translations for gallery-based public timelines.

**Implementation notes:**

- Identify the THG legacy HCR/timeline tables used by the Colours timeline endpoint.
- Create a THG timeline importer that writes `timelines`, `timeline_events`, and `timeline_event_translations` with BC keys under `mwnf3_thematic_gallery:*`.
- Link events to the gallery collection where appropriate.
- Import event images if the legacy timeline exposes them.

**Acceptance criteria:**

- Gallery 47 has a timeline with the expected 45 events or a documented filtered count.
- Sampled live events 39 and 42 are imported with date and English text.
- Existing mwnf3 and SH timelines still import unchanged.

### 11. THG Contributors And Media Importers Have Fixable Gaps

The log shows contributor translation warnings around the THG contributor phase:

- `Contributor 14: translation (undefined) failed: Cannot read properties of undefined (reading 'toLowerCase')`
- Similar warnings for other contributors and exhibition partners.

The log also shows collection-media skips for galleries 52 and 54 because theme collections are not found:

- `Skipping theme audio: theme collection not found for BC=mwnf3_thematic_gallery:thg_theme:54:6`
- Similar skips for gallery 52/54 theme media.

#### Story 10: Harden THG Contributor Translation Import

**Goal:** Import contributors and exhibition partners without crashing on undefined translation language values.

**Implementation notes:**

- Add guards before `.toLowerCase()` in `thg-contributor-importer.ts` translation handling.
- Log contributor ID, source table, and missing language field when a translation is skipped.
- Import the contributor shell even if optional translations are missing.

**Acceptance criteria:**

- The THG contributor phase produces no `Cannot read properties of undefined` warnings.
- Contributors with valid translations still import translated names/descriptions.
- Contributors with missing translation language are either skipped with clear data-quality logs or imported with documented fallback text.

#### Story 11: Resolve THG Theme Media Parent Collections

**Goal:** Attach THG theme audio/video records when the referenced theme collection exists, and clearly classify orphaned media when it does not.

**Implementation notes:**

- Query legacy theme rows for galleries 52 and 54 and compare them with collections imported by `thg-theme-importer.ts`.
- If theme rows exist, fix theme collection import so BC keys like `mwnf3_thematic_gallery:thg_theme:54:6` resolve.
- If media references orphan themes, log them as legacy data quality skips with source IDs.

**Acceptance criteria:**

- Collection media import for galleries 52/54 either imports all valid media or emits explicit orphan-media warnings.
- No valid media is skipped because a theme collection failed to import due to importer logic.

### 12. THG Collection Partners / Related PDFs / Logos / Portal CMS Need Scope Decisions

No importer was found for `collection_partner` links for THG exhibitions. Related PDFs, author/article metadata, logos, and portal CMS features are also not consistently imported into Inventory.

Classification: mixed feature gap and scope decision. These are public website concerns, and the team needs to mark each family as Inventory scope or external CMS scope.

#### Story 12: Decide Scope For Exhibition-Level Partner And Related Content Imports

**Goal:** Decide which exhibition-level presentation entities belong in Inventory, then implement the chosen importers.

**Implementation notes:**

- Inventory model already has collection relationships such as partners/contributors/media. Confirm which legacy THG tables should populate them.
- List legacy sources for exhibition partners, related PDFs/articles/authors, footer logos, and portal curation cards.
- For in-scope entities, implement importers with stable BC keys and tests.
- For out-of-scope entities, record the decision so future portal/exhibition rebuilds do not treat absence as accidental data loss.

**Acceptance criteria:**

- A scope matrix lists each legacy entity family as Inventory, external CMS, or intentionally retired.
- In-scope collection partner/media/contributor records import for gallery 47.
- Out-of-scope portal CMS records are removed from importer-gap tracking.

### 13. Travels Coverage And Media Failures Cascade From Non-Unique Collection Internal Names

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

#### Story 13: Namespace Travels Collection Internal Names And Re-import Child Hierarchy

**Goal:** Import all valid Travels trails, itineraries, locations, monuments, and media without global `internal_name` collisions.

**Implementation notes:**

- Update Travels collection importers to generate stable unique internal names from BC components, not only display titles.
  - Example itinerary internal name: `itin_{project}_{country}_{trail}_{number}_{slug}`.
  - Example location internal name: `loc_{project}_{country}_{trail}_{itinerary}_{number}_{slug}`.
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

The gap reports mention some public website entities that are not currently retained as Inventory model concepts. These should not be treated as importer bugs unless the product scope changes.

| Entity family | Current recommendation |
|---|---|
| Travels tours | Keep out of importer bug list unless Inventory is explicitly expanded to travel packages/tours. |
| Travels travel agencies | Keep out of importer bug list unless partner scope expands to travel agencies. |
| Portal panels, menus, newsletters, legal pages, videos, learning pages | Treat as portal CMS scope, not Inventory content, unless a portal-rebuild epic chooses to model them. |
| Display-time punctuation differences | Do not track as import bugs when the legacy column value is preserved. |
| Parent item plus child picture model | Do not track as missing multi-image data when child `type = picture` items or item images exist according to the chosen Inventory model. |

## Recommended Next Work Order

1. Fix `item_translations.provenance` persistence. This is small, high-confidence, and affects all imported object provenance.
2. Fix Travels internal-name collisions and re-run Travels child phases. This explains the largest missing-data surface.
3. Add SH partner picture importer. This is targeted and follows an existing normal partner-picture pattern.
4. Decide BAR/mwnf3 timeline behavior: explicit import versus dynamic query.
5. Implement Explore fallback detail strategy for VM/Travels/SH-backed monuments.
6. Decide THG exhibition-level scope, then implement timeline/partner/media/contributor fixes in smaller stories.
