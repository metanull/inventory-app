# Baroque Art Import Validation Report

Date: 2026-05-02

## Scope Note

The corrected public website for this validation is `https://baroqueart.museumwnf.org`. It identifies itself as **Discover Baroque Art** and its item detail code accepts only records whose legacy project is `BAR`.

An Islamic Art test URL on the corrected host, `database_item.php?id=object;ISL;dz;Mus01;8;en`, returns the website message that the object reference is invalid. The supporting PHP code confirms this rule: after loading a record, `modules/database_item.php` rejects it when the loaded `project_id` is not `BAR`.

For that reason, this report validates the data that the corrected website actually serves: Baroque Art website records. It does not make a quality assessment of Islamic Art imports.

## Sources Used

This validation uses read-only checks against:

- the live website pages on `https://baroqueart.museumwnf.org`;
- the Baroque Art PHP codebase at `E:\mwnf-server\apps\baroqueart.museumwnf.org`;
- the production legacy database used by that PHP site;
- the imported Inventory database on OVH;
- the importer mapping documentation in [Legacy Import](../../../docs/understanding/legacy-import.md).

Existing reports in this folder were not used as evidence for the findings below.

## Method

The validation uses a small number of website-led samples across different page types instead of many records from one list:

| Website area | Sample | Why it matters |
|---|---|---|
| Home page and object detail | `object;BAR;pt;Mus11_A;13;en`, **Portrait of the Marquis of Pombal** | Checks a record displayed as the home page highlight and as a full object detail page. |
| Object images | Six public images under `objects/bar/pt/11_a/13/` | Checks whether the visible image set survives the import. |
| Partner page | `pm_partner.php?id=Mus11_A;pt&type=museum`, **Further Associated Museums** | Checks the holding-partner surface linked from the object. |
| Exhibition relation | **The Age of Enlightenment** > **Signs of social responsibility: enlightened absolutism** | Checks whether an object shown as part of a website exhibition is linked to the matching Inventory collection path. |
| Timeline link | Timeline for the Pombal object | Checks whether the website's timeline behaviour is represented in Inventory. |

## Overall Assessment

The sampled object, its main descriptive fields, its partner record, its image records, and its exhibition placement are traceable from the website to the legacy database and then to Inventory.

The strongest import result is the object detail page: the Inventory item keeps the same legacy identity, title, owner reference, MWNF reference, country, project, partner, date, location, owner fields, materials, dimensions, and descriptive text. The exhibition path shown on the website is also present in Inventory as nested collections, with the object linked to the page-level collection.

The main gap is timeline behaviour. The website shows a timeline link for the object based on Portuguese country events within the object's date range. Inventory contains Baroque-relevant HCR text, but no direct BAR compatibility keys and no `timeline_event_item` links to BAR project items were found in the imported database.

A second review point is context duplication. The sampled Baroque object has English and Portuguese translations both in the `Discover Baroque Art` context and in an `Explore Islamic Art Collections` context. This is not a duplicate item, but it can confuse validation unless reviewers filter translations by context.

## Sample Findings

### 1. Object Detail: Portrait of the Marquis of Pombal

Website page:

- `https://baroqueart.museumwnf.org/database_item.php?id=object;BAR;pt;Mus11_A;13;en`
- Display title: `Portrait of the Marquis of Pombal`
- Location: `Oeiras, Lisbon, Portugal`
- Holding museum: `Oeiras Town Hall`
- Date: `1766`
- Museum inventory number: `002605`
- Material: `Oil on canvas`
- Dimensions: `H: 290 cm; w: 354 cm`
- Type of object: `Painting`
- MWNF working number: `PT 16`

Legacy source:

| Source | Legacy key | Result |
|---|---|---|
| `objects` | `project_id=BAR`, `country=pt`, `museum_id=Mus11_A`, `number=13`, `lang=en` | The website fields above come from this row. |
| `objects_pictures` | same key, `type=''` | Six image rows exist, ordered by `image_number`. |

Important legacy values:

| Field | Legacy value |
|---|---|
| `name` | `Portrait of the Marquis of Pombal` |
| `location` / `province` | `Oeiras` / `Lisbon` |
| `holding_museum` | `Oeiras Town Hall` |
| `original_owner` | `Sebastião José de Carvalho e Melo, Marquis de Pombal` |
| `current_owner` | `Oeiras Town Hall` |
| `date_description` | `1766` |
| `inventory_id` | `002605` |
| `materials` | `Oil on canvas` |
| `dimensions` | `H: 290 cm; w: 354 cm.` |
| `workshop` | `Portraiture European and Baroque` |
| `provenance` | `Palace of the Marquis de Pombal, Oeiras` |
| `typeof` | `Painting` |
| `period_activity` | `Mid/2nd half 18th century` |
| `production_place` | `Lisbon and Paris` |
| `working_number` | `PT 16` |
| `start_date` / `end_date` | `1717` / `1807` |
| `log_dateupd` | `2021-08-22 18:10:19` |

Inventory result:

| Inventory record | Value |
|---|---|
| Item legacy key | `mwnf3:objects:BAR:pt:Mus11_A:13` |
| Type | `object` |
| Internal name | `Portrait of the Marquis of Pombal` |
| Country | `prt` |
| Project | `mwnf3:projects:BAR` / `Discover Baroque Art` |
| Direct collection | `mwnf3:projects:BAR` / `Discover Baroque Art` |
| Partner | `mwnf3:museums:Mus11_A:pt` |
| English title in BAR context | `Portrait of the Marquis of Pombal` |
| Holder / owner / initial owner | `Oeiras Town Hall` / `Oeiras Town Hall` / `Sebastião José de Carvalho e Melo, Marquis of Pombal` |

Assessment: **Good match.** The sampled business-facing object facts are present and traceable. Minor formatting differences are expected because the importer converts legacy text into the Inventory text format.

### 2. Object Images

Website image links for the object:

- `objects/bar/pt/11_a/13/1.jpg`
- `objects/bar/pt/11_a/13/2.jpg`
- `objects/bar/pt/11_a/13/3.jpg`
- `objects/bar/pt/11_a/13/4.jpg`
- `objects/bar/pt/11_a/13/5.jpg`
- `objects/bar/pt/11_a/13/6.jpg`

Legacy source:

| Source | Count | Notes |
|---|---:|---|
| `objects_pictures` | 6 | All six rows have photographer `Carlos Santos`; all six have copyright `Câmara Municipal de Oeiras`; only image 6 has caption `Detail`. |

Inventory result:

| Inventory representation | Result |
|---|---|
| Direct `item_images` on the object | One row, original `objects/bar/pt/11_a/13/1.jpg`, display order `1`. |
| Child picture items | Six child items exist: `mwnf3:objects_pictures:bar:pt:Mus11_A:13:1` through `:6`. |
| Child picture image rows | Each child picture item has one image row with the corresponding original image name. |

Assessment: **Good match, with a model difference.** The website shows six images directly on the object. Inventory preserves the full six-image set, but represents images 1-6 primarily as child picture items. Reviewers should include child picture items when validating image completeness.

### 3. Partner Page: Further Associated Museums

Website page:

- `https://baroqueart.museumwnf.org/pm_partner.php?id=Mus11_A;pt&type=museum`
- Displayed heading: `Further Associated Museums`
- Country: `Portugal`
- The visible description is a list of associated Portuguese museums and places, including `Oeiras Town Hall`.
- The page has a `View Objects` link to `pm_museum_items.php?id=Mus11_A;pt`.
- The visible image uses `museums/pt/11_a/1.jpg` and has alt text `Army Museum Lisbon`.

Legacy source:

| Source | Legacy key | Result |
|---|---|---|
| `museums` | `museum_id=Mus11_A`, `country=pt` | `project_id=BAR`, name `Further Associated Museums`, logo `museums/pt/11_A/logos/1.jpg`. |
| `museumnames` | same museum/country, `lang=en` | Display name and description used by the page. |
| `museums_pictures` | same museum/country | One image row: `museums/pt/11_a/1.jpg`, caption `Army Museum Lisbon`. |

Inventory result:

| Inventory record | Value |
|---|---|
| Partner legacy key | `mwnf3:museums:Mus11_A:pt` |
| Type | `museum` |
| Country | `prt` |
| Internal name | `Further Associated Museums` |
| Visible | `0` |
| Partner image | One row, original `museums/pt/11_a/1.jpg`, display order `1`. |

Assessment: **Good match for the legacy partner record.** The partner is not a single institution named `Oeiras Town Hall`; it is a grouped legacy partner called `Further Associated Museums`. That grouping is source data, not an importer error.

### 4. Exhibition Placement

The object page displays the item under:

- `Discover Baroque Art`
- `The Age of Enlightenment`
- `Signs of social responsibility: enlightened absolutism`

Legacy source:

| Source | Key/value | Result |
|---|---|---|
| `exhibition_page_images` | `image_id=1857`, `page_id=342`, `ref_item=O;BAR;pt;11_A;13` | Links the object to the exhibition page. |
| `exhibitions` and EAV fields | `exhibition_id=45` | English title `The Age of Enlightenment`. |
| `exhibition_themes` and EAV fields | `theme_id=146` | English title `Signs of social responsibility: enlightened absolutism`. |
| `exhibition_pages` and EAV fields | `page_id=342` | Page title `Signs of social responsibility: enlightened absolutism`; page quote begins `The rebuilding of Lisbon after the disastrous earthquake...`. |

Inventory result:

| Inventory link | Result |
|---|---|
| Collection path | `Discover Baroque Art` > `The Age of Enlightenment` > `Signs of social responsibility: enlightened absolutism` > `Signs of social responsibility: enlightened absolutism`. |
| Page collection key | `mwnf3:exhibition_pages:342` |
| Object link | The Pombal object is linked to this page collection with display order `1`. |
| Extra picture | `objects/bar/pt/11_a/13/2.jpg` |

Assessment: **Good match.** The website exhibition placement is preserved in Inventory as a collection hierarchy and item membership.

### 5. Additional Collection Membership

Inventory also links the sampled object to:

| Collection | Title / meaning |
|---|---|
| `mwnf3:exhibitions:46` | `Absolutism` |
| `mwnf3_thematic_gallery:thg_gallery:31` | `gallery_portraits` / `Portraits` gallery |
| `mwnf3:projects:BAR` | `Discover Baroque Art` main project collection |

Assessment: **Useful enrichment, not a mismatch.** These memberships explain why the same object can appear through several public contexts.

### 6. Timeline Link

The object detail page shows a `Timeline for this item` link.

Legacy website behaviour:

- The website does not use a direct object-to-event relation for this sample.
- It checks whether HCR rows exist for the object's country, `pt`.
- It opens the timeline popup using the object's country and date range: `start_date=1717`, `end_date=1807`.

Legacy events returned for this date window:

| HCR id | Country | Date | Description summary |
|---:|---|---|---|
| 831 | `pt` | `1755` | Lisbon earthquake. Reconstruction begins under the Marquis of Pombal. |
| 832 | `pt` | `1769` | Mazagán, the last Portuguese town in Morocco, is abandoned. |

Inventory result:

- No timeline rows with BAR compatibility keys were found.
- No timeline event rows with BAR compatibility keys were found.
- `timeline_event_item` has no links to BAR project items.
- A Baroque-relevant HCR event exists independently, but it is not linked to BAR items.

Assessment: **Confirmed gap.** The website timeline behaviour for the sampled item is not represented as item-linked timeline data in Inventory.

## Data Quality Points For Reviewers

### Context Duplication

The sampled Baroque item has four Inventory translation rows:

| Language | Context | Notes |
|---|---|---|
| English | `mwnf3:projects:BAR` / `Discover Baroque Art` | Matches the sampled website page. |
| Portuguese | `mwnf3:projects:BAR` / `Discover Baroque Art` | Matches the alternate website language. |
| English | `mwnf3:projects:EPM` / `Explore Islamic Art Collections` | Same item, different context and description text. |
| Portuguese | `mwnf3:projects:EPM` / `Explore Islamic Art Collections` | Same item, different context and description text. |

This is not a duplicate object row. It is one object with translations in more than one context. For website validation, reviewers should filter by the context that corresponds to the website being checked.

### Partner Grouping

The sampled partner page is a grouped source record named `Further Associated Museums`. The object's visible holding museum, `Oeiras Town Hall`, is part of that grouped description rather than a standalone partner record in this sample.

This is important for customer review: a search for a specific institution name can miss the imported partner if the legacy site grouped several institutions under one partner record.

### Image Completeness

Inventory preserves the visible image set, but object images are split between:

- one direct image on the object;
- six child picture items, each with its own image row.

Validation screens and queries should include child picture items when checking whether all website images are present.

## Findings Summary

| Area | Assessment |
|---|---|
| Website object identity | Good match. |
| Main object descriptive fields | Good match. |
| Object images | Good match when child picture items are included. |
| Partner page | Good match to the legacy grouped partner. |
| Exhibition placement | Good match. |
| Timeline behaviour | Gap: website country/date HCR timeline is not linked to BAR Inventory items. |
| Translation context clarity | Review point: the same BAR item also has translations in the EPM context. |

## Practical Validation Guidance

When customers validate imported Baroque records, they should:

- compare item facts by `backward_compatibility`, not only by title;
- filter translations by the website context, for example `mwnf3:projects:BAR` for Discover Baroque Art;
- include child picture items when checking image completeness;
- treat grouped legacy partner pages as source truth when the website groups several institutions under one partner;
- review timelines separately, because the sampled website timeline is driven by country and date range rather than direct item-event links.