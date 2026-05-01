****---
layout: default
title: Islamic Art Import Validation Report
---

# Islamic Art Import Validation Report

## Purpose

This report checks a small, broad sample of public content from `https://islamicart.museumwnf.org` against:

- the legacy production `mwnf3` database that feeds the current Islamic Art website;
- the imported Inventory database hosted on OVH;
- the importer rules documented in [docs/understanding/legacy-import.md](docs/understanding/legacy-import.md) and [docs/understanding/validation-guide.md](docs/understanding/validation-guide.md).

The website is the primary source. The PHP codebase was used only to trace how public pages identify their source records. The database checks were read-only.

This is a sample-based validation. It does not claim a full corpus pass rate.

## Overall Assessment

The sampled Islamic Art import is usable for many core content records. The checked object, monument, partner text, image references, glossary entry, HCR country timeline, museum object membership, and related item links are present in Inventory and can be traced back to their legacy records.

The most important gaps are not random import failures. They are concentrated around presentation and publication readiness:

- Public museum and institution partners are imported with `visible = 0`, so an Inventory API or UI that respects visibility may hide partners that are public on the Islamic Art website.
- The virtual exhibition landing record is present, but the public introduction text for `The Umayyads` was not found in the imported exhibition collection translation.
- Some virtual exhibition image references are present only as collection-item metadata, not as collection image records.
- HCR country timelines are imported, but the sampled Islamic Art object has no item-specific timeline-event links in Inventory.
- Several display fields preserve source punctuation or normalized naming rather than matching the website exactly. Examples include trailing periods in titles and dates, plural dynasty names, and shorter location fields.

These issues are manageable, but they matter for customer validation because they affect what reviewers see, not only whether source rows exist.

## Gap Source Tables At A Glance

| Gap or partial area | Legacy schema and tables involved | Importer area to adjust |
|---|---|---|
| Public partner visibility | `mwnf3.partner_museums`, `mwnf3.museums`, `mwnf3.museumnames`; institution records are imported from `mwnf3.institutions` and `mwnf3.institutionnames` | Phase 01 partner import and partner transformers |
| Virtual exhibition introduction text | `mwnf3.exhibitions`, `mwnf3.exhibition_fields`, especially the introduction field used by `exhibitions/ISL/the_umayyads/introduction.php` | Phase 01 `Mwnf3ExhibitionTranslationImporter` |
| Virtual exhibition images and page narrative images | `mwnf3.exhibition_images`; for page-level images also `mwnf3.exhibition_page_images`, `mwnf3.exhibition_page_images_fields`, `mwnf3.exhibition_page_image_details`, and `mwnf3.exhibition_page_image_details_fields` | Phase 01 `Mwnf3ExhibitionItemImporter` and image synchronization |
| HCR item-specific timeline links | `mwnf3.hcr` and `mwnf3.hcr_events` provide the country timeline. No direct mwnf3 item-to-HCR link table was identified in the sampled PHP flow. | Phase 05 `TimelineImporter`, only if the desired product behavior requires explicit item-event pivots |
| Display differences for object and monument fields | `mwnf3.objects`, `mwnf3.monuments`, `mwnf3.objects_pictures`, `mwnf3.monuments_pictures`, `mwnf3.dynasties`, `mwnf3.dynasty_texts`, `mwnf3.objects_dynasties`, and `mwnf3.monuments_dynasties` | Phase 01 object/monument transformers, Phase 02 picture importers, or the future read-only API presentation layer |
| Museum object list filtering | `mwnf3.objects` for public object cards; `mwnf3.objects_pictures` creates child picture Items in Inventory | Future read-only API query or Inventory list projection |

These table names are included so the gaps can be addressed without re-discovering the legacy source structure.

## Method

The sample covered several page types instead of many records from one page type:

- object detail;
- monument detail;
- related objects and monuments;
- museum partner profile;
- museum object list;
- HCR timeline popup and HCR result page;
- glossary popup;
- virtual exhibition landing and introduction pages.

Legacy source records were traced through public page identifiers such as:

```text
object;ISL;dz;Mus01;8;en
monument;ISL;tn;Mon01;2;en
```

Imported records were checked through `backward_compatibility` values such as:

```text
mwnf3:objects:ISL:dz:Mus01:8
mwnf3:monuments:ISL:tn:Mon01:2
mwnf3:museums:Mus01:eg
mwnf3:exhibitions:10
```

## Sample Findings

| Website sample | Legacy source | Inventory result | Assessment |
|---|---|---|---|
| `database_item.php?id=object;ISL;dz;Mus01;8;en` - object detail for `Sitting lion` | `mwnf3.objects`, key `ISL / dz / Mus01 / 8 / en`; image `objects/isl/dz/1/8/1.jpg` | Item found as `mwnf3:objects:ISL:dz:Mus01:8`, type `object`, country `dza`, English title `Sitting lion.`, inventory reference `II.S.27.`, date `Hegira 406-547 / AD 1015-1152.`, holder `National Museum of Antiquities and Islamic Arts`, image original name preserved | Match with display differences |
| `database_item.php?id=monument;ISL;tn;Mon01;2;en` - monument detail for `Great Mosque of Kairouan` | `mwnf3.monuments`, key `ISL / tn / Mon01 / 2 / en`; image `monuments/isl/tn/1/2/1.jpg` | Item found as `mwnf3:monuments:ISL:tn:Mon01:2`, type `monument`, country `tun`, English title `Great Mosque of Kairouan.`, date `Hegira 221 / AD 836.`, patron in `extra`, image original name preserved | Match with display differences |
| Related content from `Sitting lion` | `objects_objects` and `objects_monuments` for `ISL / dz / Mus01 / 8` | Expected links found to objects 3, 4, 5, 9, 11 and monument `ISL / dz / Mon01 / 4` | Match |
| Related content from `Great Mosque of Kairouan` | `monuments_objects` for `ISL / tn / Mon01 / 2` | Expected links found to `ISL / eg / Mus01 / 27`, `ISL / eg / Mus01 / 28`, and `ISL / es / Mus01 / 1` | Match |
| `pm_partner.php?id=Mus01;eg&type=museum&theme=ISL&museumlng=en` - Museum of Islamic Art profile | `mwnf3.partner_museums`, `mwnf3.museums`, `mwnf3.museumnames`, key `ISL / eg / Mus01 / en` | Partner found as `mwnf3:museums:Mus01:eg`, type `museum`, country `egy`, English name and description present, logo/profile image records present, but `visible = 0` | Content match, publication gap |
| `pm_museum_items.php?id=Mus01;eg&link=ISL` - Museum of Islamic Art object list | `objects` where project is `ISL` or `EPM`, museum `Mus01`, country `eg` | Expected examples found as Inventory object items, including `Ewer.` and `Wooden panel.` | Match, with filtering caveat |
| `hcr.php?id=object;ISL;dz;Mus01;8;en` - object timeline context | Object key above plus Algeria HCR country timeline | Object found; Algeria timeline found as `mwnf3:hcr:country:dz`; no `timeline_event_item` rows found for this object | Partial |
| `hcr_result.php?start_date=500&end_date=700&country=eg` - Egypt HCR result | `hcr` and `hcr_events`, country `eg`, English events | Egypt timeline found as `mwnf3:hcr:country:eg`; events `mwnf3:hcr:121` and `mwnf3:hcr:122` found with English descriptions | Match |
| `show_glossary_def.php?alng=en`, POST `id=22` - glossary definition | `glossary.word_id = 22`, English definition | Glossary found as `mwnf3:glossary:22`, internal name `Ablaq`, English definition present | Match |
| `exhibitions/ISL/the_umayyads/index.php` - virtual exhibition landing | `mwnf3.exhibitions.exhibition_id = 10`, `mwnf3.exhibition_fields`, `mwnf3.exhibition_themes` | Collection found as `mwnf3:exhibitions:10`, title `The Umayyads`, description present, child themes present | Partial |
| `exhibitions/ISL/the_umayyads/introduction.php` - virtual exhibition introduction | `mwnf3.exhibition_fields` for introduction text; `mwnf3.exhibition_images` for sampled introduction images | Expected intro text beginning `The Umayyad Dynasty was founded by the caliph Mu'awiya I...` was not found on the imported exhibition collection translation; sampled ref-item images are represented as collection-item metadata | Gap |

## What Matches Well

### Core Object Content

The `Sitting lion` object is traceable from the public website to the legacy row and to the Inventory item. The main business fields are present:

- title;
- country;
- location;
- holding museum;
- inventory reference;
- MWNF reference;
- date;
- dynasty;
- image source path.

The Inventory record stores the image under a new UUID filename, while preserving the legacy path as the image `original_name`. This is expected because the importer does not keep public legacy image paths as storage paths.

### Core Monument Content

The `Great Mosque of Kairouan` monument is also traceable and present. The title, date, patron, related institution, country, dynasties, and image source path are represented.

The Inventory location is shorter than the public website location. The website shows `In the Medina, Kairouan, Tunisia`; Inventory stores `Kairouan` and the country separately. This is a presentation difference unless the future read-only API needs the full public display string.

### Related Content

The sampled related links from object to objects, object to monument, and monument to objects were found in `item_item_links`. This is a strong sign that direct Islamic Art relationships are being imported and not only the standalone items.

Some query outputs showed repeated rows because target items can have more than one English translation/context row. That does not by itself prove duplicate relationship records. It means validation views must account for translation context when checking links.

### Glossary

The glossary entry `Ablaq` is present with its English definition. The public glossary endpoint is POST-driven, but the imported record itself is traceable and usable.

### HCR Country Timeline

The Egypt HCR result sample is present in Inventory. The country timeline and the sampled events for years 527 and 571 were found with English descriptions.

For these events, the useful public text is in the description field. The event name field is empty, which is acceptable if consumers use descriptions for timeline display.

## Main Gaps And Risks

### Public Partners May Be Hidden

The sampled `Museum of Islamic Art` partner has the expected name, city, country, description, logo, and profile images. However, it is imported with `visible = 0`.

This is important because the partner is public on the Islamic Art website. If the Inventory UI or future read-only API hides records where `visible = 0`, the museum profile and related partner data may disappear from public-facing results even though the content was imported.

The importer currently sets legacy museums and institutions to not visible by default. This should be treated as a business rule to validate, not as a data-loss symptom.

Legacy source tables for this gap:

- `mwnf3.partner_museums` identifies museums that participate in the Islamic Art project;
- `mwnf3.museums` stores the base museum record;
- `mwnf3.museumnames` stores translated museum names and descriptions;
- `mwnf3.institutions` and `mwnf3.institutionnames` are the equivalent base and translation sources for institution partners.

Importer fix direction:

- In Phase 01, change the partner import so public mwnf3 partners are not always created with `visible = false`.
- For museums, derive visibility from `mwnf3.partner_museums` project membership, at minimum for `project_id = 'ISL'` records that are public on the Islamic Art site.
- Keep the base data transformation in the museum/institution transformers simple, and add the project-publication decision in `PartnerImporter` or a dedicated post-pass where `mwnf3.partner_museums` is available.
- Do not blindly mark every imported institution visible until the public-source rule for Islamic Art institution pages is confirmed; the sampled institution profile page did not expose useful body content.

### Virtual Exhibition Introduction Text Is Missing From The Imported Exhibition

The virtual exhibition `The Umayyads` is imported as a collection, and its title, short description, and child themes are present.

The public introduction page contains text beginning `The Umayyad Dynasty was founded by the caliph Mu'awiya I...`. This text was not found in the imported exhibition collection translation during the OVH check.

The importer maps exhibition-level fields such as title, description, and credits. It does not import the sampled exhibition introduction text into the same exhibition collection translation. This creates a visible content gap for the public exhibition experience.

Legacy source tables for this gap:

- `mwnf3.exhibitions` identifies the exhibition, including `exhibition_id = 10` for `The Umayyads`;
- `mwnf3.exhibition_fields` stores exhibition-level translated fields used by the landing and introduction pages;
- `mwnf3.exhibition_themes` and `mwnf3.exhibition_theme_fields` store child theme structure and translated theme labels;
- `mwnf3.exhibition_pages` and `mwnf3.exhibition_page_fields` store page-level virtual exhibition text.

Importer fix direction:

- Extend `Mwnf3ExhibitionTranslationImporter` so exhibition-level introduction fields from `mwnf3.exhibition_fields` are imported, not only title, description, and credits.
- Preserve the distinction between landing description and introduction text. A practical importer-safe approach is to keep the landing description in `collection_translations.description` and store introduction text in `collection_translations.extra` under a clear key such as `intro_text`, unless the read-only API design chooses a dedicated page collection for introductions.
- Add a focused importer test using a self-contained `mwnf3.exhibition_fields` fixture for exhibition 10-style rows, verifying that the introduction text lands in the chosen Inventory field.

### Virtual Exhibition Images Are Not First-Class Collection Images In The Sample

For `The Umayyads`, sampled introduction image references were found as collection-item metadata, not as collection image records.

This preserves some traceability, but it may not be enough for a read-only API or frontend that expects a collection to expose its page images directly. The business question is whether virtual exhibition pages should be rebuilt from collection items plus metadata, or whether their narrative images should become explicit media/image records.

Legacy source tables for this gap:

- `mwnf3.exhibition_images` stores exhibition-level images and their `ref_item` values;
- `mwnf3.exhibition_page_images` stores page-level virtual exhibition images;
- `mwnf3.exhibition_page_images_fields` stores editorial fields such as detail names and justifications;
- `mwnf3.exhibition_page_image_details` and `mwnf3.exhibition_page_image_details_fields` store image detail annotations.

Importer fix direction:

- In `Mwnf3ExhibitionItemImporter`, keep the existing collection-item link when `ref_item` points to an object or monument, because it preserves the relationship.
- Add an explicit representation for the narrative image itself when a public exhibition page needs to display that image independently of the linked item. The likely target is a `collection_images` row with the source `picture` path and an `extra` payload that also records the linked item backward compatibility.
- Ensure the image synchronization tool includes these collection image rows so the binary file lands in Inventory storage, not only the source path stored in `collection_item.extra.picture`.
- Cover both cases in importer tests: `mwnf3.exhibition_images` with a `ref_item`, and `mwnf3.exhibition_page_images` with image field/detail metadata.

### HCR Item-Specific Links Are Absent For The Sampled Object

The `Sitting lion` object and the Algeria HCR timeline both exist. No `timeline_event_item` rows were found for this object.

If the Islamic Art timeline popup only needs the object summary plus a country timeline, this is acceptable. If the future read-only API must express direct object-to-event links, this is a gap.

The importer code imports mwnf3 HCR timelines and events by country. Item-linked timeline pivots are imported for Sharing History HCR images, not for the sampled mwnf3 Islamic Art object.

Legacy source tables for this partial area:

- `mwnf3.hcr` stores the HCR event date and country rows;
- `mwnf3.hcr_events` stores translated HCR event descriptions;
- no direct mwnf3 Islamic Art item-to-HCR pivot table was identified in the sampled PHP route. The public popup is driven by the item country and the country timeline.

Importer fix direction:

- If the target behavior is country timeline context only, do not add synthetic `timeline_event_item` rows; instead document that the read-only API should resolve the relevant timeline from the item's country.
- If explicit item-event links are required, add a new mwnf3-specific importer step only after identifying a real legacy source relation. Do not infer links from country alone, because that would create misleading relationships between every item and every country event.
- Keep the existing Sharing History `sh_hcr_images` pivot import separate; it uses `mwnf3_sharing_history.sh_hcr_images`, not `mwnf3.hcr`.

### Public Display Text Is Not Always Identical

Several values are semantically correct but not display-identical:

- `Sitting lion` is stored as `Sitting lion.`;
- `Great Mosque of Kairouan` is stored as `Great Mosque of Kairouan.`;
- dates preserve trailing punctuation in Inventory;
- public dynasty labels such as `Hammadid` and `Umayyad, Abbasid` appear in Inventory as normalized names such as `Hammadids` and `Abbasids; Umayyads`;
- locations may be split between a short location field and country data.

These differences are not necessarily import defects. They should be reviewed as presentation rules for the future read-only API and frontend.

Legacy source tables for this partial area:

- `mwnf3.objects` and `mwnf3.monuments` contain the sampled title, date, location, holder, patron, and descriptive source fields;
- `mwnf3.objects_pictures` and `mwnf3.monuments_pictures` contain source image paths and translated captions;
- dynasty labels and item-dynasty links come from `mwnf3.dynasties`, `mwnf3.dynasty_texts`, `mwnf3.objects_dynasties`, and `mwnf3.monuments_dynasties`.

Importer fix direction:

- Do not change importer data just to mimic the old PHP output if the Inventory value is intentionally normalized.
- If customer-facing display must match the Islamic Art site, implement formatting in the future read-only API: strip terminal punctuation where the public page does, combine location plus country for display, and choose singular/plural dynasty wording consistently.
- Only change Phase 01 object/monument transformers if reviewers decide that the stored canonical Inventory text itself should be punctuation-normalized.

### Museum Object Lists Need Type Filtering

The Museum of Islamic Art object list examples were found. The Inventory also contains picture child items that can share object titles.

Any public list equivalent to the website's museum object list should filter to content item types such as `object`, not include child `picture` items unless the design explicitly needs them.

Legacy source tables for this partial area:

- `mwnf3.objects` is the source for public museum object list cards;
- `mwnf3.objects_pictures` creates child picture Items during import.

Importer fix direction:

- No importer change is needed if child picture Items are an intentional Inventory feature.
- The read-only API or list projection should filter museum object lists to `items.type = 'object'` for Islamic Art object-list pages.
- If a future importer creates separate public-list collection membership, make it point only to the object Items from `mwnf3.objects`, not to picture child Items from `mwnf3.objects_pictures`.

## Importer Remediation Summary

| Problem | Where to solve | Brief solution |
|---|---|---|
| Public Islamic Art museum partner imported as hidden | Phase 01 partner import; source tables `mwnf3.partner_museums`, `mwnf3.museums`, `mwnf3.museumnames` | Set `visible` from confirmed Islamic Art publication membership instead of defaulting all mwnf3 museums to hidden. Keep institution visibility conservative until a public institution rule is confirmed. |
| Missing virtual exhibition introduction text | Phase 01 `Mwnf3ExhibitionTranslationImporter`; source table `mwnf3.exhibition_fields` | Import the introduction field used by the public introduction page into a stable Inventory location, preferably `collection_translations.extra.intro_text` unless a dedicated introduction page collection is chosen. |
| Virtual exhibition images represented only as collection-item metadata | Phase 01 `Mwnf3ExhibitionItemImporter`; source tables `mwnf3.exhibition_images`, `mwnf3.exhibition_page_images`, `mwnf3.exhibition_page_images_fields`, `mwnf3.exhibition_page_image_details`, and `mwnf3.exhibition_page_image_details_fields` | Preserve the item link and also create explicit `collection_images` for narrative images that public pages need to render directly; include the linked item reference in `extra`. |
| No item-specific HCR links for sampled Islamic Art object | Phase 05 `TimelineImporter`; source tables `mwnf3.hcr`, `mwnf3.hcr_events` | Do not synthesize item-event links from country alone. Either expose country timeline context through the read-only API, or add pivots only if a real legacy mwnf3 item-HCR relation is identified. |
| Display differences in punctuation, dynasty labels, and location strings | Prefer future read-only API presentation layer; source tables `mwnf3.objects`, `mwnf3.monuments`, `mwnf3.objects_pictures`, `mwnf3.monuments_pictures`, `mwnf3.dynasties`, `mwnf3.dynasty_texts`, `mwnf3.objects_dynasties`, and `mwnf3.monuments_dynasties` | Treat as presentation unless business reviewers require stored canonical text changes. Format public display strings outside the importer where possible. |
| Museum object lists may include picture child Items if queried naively | Future read-only API query; source tables `mwnf3.objects`, `mwnf3.objects_pictures` | Filter public museum object lists to `items.type = 'object'`; keep picture child Items available for image identity use cases. |

## Scope Limits Observed During Sampling

- No Islamic Art `monument_details` rows were found in the sampled `mwnf3` source inspection. Monument component validation should not be assumed without a confirmed Islamic Art source page and source table rows.
- Sampled institution partner pages returned page chrome but no useful public body content, so the report does not assess institution profile quality beyond the monument's linked institution record.
- The glossary page requires a POST body. A direct GET with the word id does not reproduce the public popup content.
- Image validation here checks database records and preserved source paths. It does not prove that every physical image file has been synchronized to the final storage location.

## Recommended Validation Decisions

Before customer sign-off, decide these points explicitly:

1. Partner visibility: confirm whether public Islamic Art **museums** and institutions should be visible in Inventory after import.
2. Virtual exhibitions: decide where introduction text and page-level narrative images belong in the Inventory model.
3. Timeline behavior: decide whether Islamic Art item pages need direct item-to-event links, or only country timeline context.
4. Display normalization: decide whether the read-only API should remove trailing punctuation, singularize dynasty labels, and reconstruct public location strings.
5. Public lists: make object-list endpoints filter out child picture items unless those picture records are intentionally part of the public result.

## Conclusion

The sample supports a positive but qualified assessment. Core Islamic Art object and monument records, many relationships, glossary content, HCR country timelines, images, and partner text are present and traceable in Inventory.

The main validation concerns are around publication behavior and page reconstruction: partner visibility, virtual exhibition introduction text, virtual exhibition image representation, and exact public display formatting. These should be resolved before using the imported Inventory as the source for customer-facing Islamic Art pages or the future read-only API.
