# Islamic Art Import Validation Report

Date: 2026-05-02

## Scope

This report validates imported Inventory data against the public website `https://islamicart.museumwnf.org`, the supporting legacy codebase at `E:\mwnf-server\apps\islamicart.museumwnf.org`, the production legacy database, and the imported Inventory database.

The website is the primary source. Legacy code and databases are used only to understand where the website data comes from and how it maps into Inventory.

Relevant internal references:

- [Legacy Import](../../../docs/understanding/legacy-import.md)
- [Validation Guide](../../../docs/understanding/validation-guide.md)
- [Inventory Principles](../../../docs/understanding/inventory-principles.md)

## Summary

The Islamic Art import is strong for the main public content families sampled here: object identity, partner identity, historical chronology records, exhibition hierarchy, and exhibition-item links. The sampled records keep stable `backward_compatibility` keys and recognizable business fields.

The main gaps are not whole missing families. They are presentation and modeling issues that can affect public review: provenance can be missing, some objects have multiple same-language translations, and picture records can appear as standalone items if future screens do not filter them carefully.

## Samples Checked

| Website area | Sampled website page | Website facts checked | Inventory result |
|---|---|---|---|
| Object detail | `database_item.php?id=object;ISL;eg;Mus01;1;en` | `Perfume sprinkler`; Cairo, Egypt; Museum of Islamic Art; original owner Sultan al-Nasir Hasan; date AH 698-708 / AD 1299-1309 or AH 709-741 / AD 1309-40; inventory no. 15111; Mamluk; provenance `Egypt, probably Cairo`; full description | Item `mwnf3:objects:ISL:eg:Mus01:1` exists. Type, country, owner reference, MWNF reference, date range, title, holder, owner, dates, dimensions, and description match in substance. Provenance is missing in the sampled imported translation. |
| Partner and holdings | `pm_museum_items.php?id=Mus01%3Beg&type=museum&theme=ISL&link=ISL` | Museum of Islamic Art, Cairo, Egypt; holdings include `Wooden panel`, `Ewer`, `Fragment of a medical prescription` | Partner `mwnf3:museums:Mus01:eg` exists with matching name and country. Sampled holdings exist as items, including `mwnf3:objects:ISL:eg:Mus01:35`, `...:20`, and `...:45`. |
| Timeline | `hcr_result.php?country=eg&start_date=600&end_date=700&begin=0` | Egypt events for 619, 627, 639, 641, and 655 with matching descriptions | Timeline `mwnf3:hcr:country:eg` and events `mwnf3:hcr:123` through `mwnf3:hcr:127` exist. English date labels and descriptions match the sampled page. |
| Virtual exhibition | `exhibitions/ISL/the_mamluks/exhibition.php?theme=1&page=1` | Exhibition `The Mamluks`; page `The Mamluk System`; quote and linked items including `Coat of armour`, `Scaled armours (cuirass), and swords`, and `Sword` | Root collection `mwnf3:exhibitions:17` and page collection `mwnf3:exhibition_pages:80` exist. The sampled linked items exist and are connected to the page context. |

## Legacy Source Mapping

| Website content | Legacy source used by the site |
|---|---|
| Object pages | `mwnf3.objects`, including name, location, province, country, holding museum, original owner, date text, inventory number, materials, dimensions, dynasty, provenance, descriptions, working number, and date range. Loaded through the legacy item class used by `database_item.php`. |
| Partner pages | `mwnf3.museums`, museum translations, country names, and object filters by project, museum, and country. |
| Timeline pages | `mwnf3.hcr` and `mwnf3.hcr_events`. |
| Exhibition pages | `mwnf3.exhibitions`, exhibition fields, themes, pages, page fields, and page image/item references. |

## Import Quality

The sampled object, partner, timeline, and exhibition records are traceable and mostly complete. The import preserves the main public identity of objects and exhibitions, including legacy keys that reviewers can use to move between the website, legacy database, and Inventory.

Historical chronology import is particularly good in the sample: country grouping, event dates, and descriptions match the website.

Exhibition structure is also good in the sample. The root exhibition, page collection, page title, and linked objects are present.

## Gaps To Address

1. **Provenance loss on sampled object**

   The website displays provenance for `Perfume sprinkler`, but the sampled imported translation has `provenance = NULL`. This is visible museum content and should be preserved when the source row provides it.

2. **Multiple same-language translations need clear rules**

   Some sampled objects have more than one English translation. This may preserve different source text fields or contexts, but it can create duplicate rows in lists unless every query is context-aware.

3. **Picture rows can pollute object listings**

   The sampled museum partner has real objects and imported `type = picture` items. Pictures are valid Inventory content, but public object lists must filter them intentionally so images do not appear as holdings.

4. **Single-date timeline events use `year_to = 0`**

   The sampled timeline import is otherwise strong. Future presentation should treat `0` as no end year, or the importer should normalize it if the model permits.

## Business Assessment

The Islamic Art import is suitable for validating the core museum collection, exhibition hierarchy, and historical chronology content. Reviewers should focus next on provenance coverage, duplicate/contextual translations, and list filtering rules for picture items.
