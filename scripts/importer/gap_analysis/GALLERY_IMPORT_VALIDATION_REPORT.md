# Amulets Gallery Import Validation Report

Date: 2026-05-02

## Scope

This report validates imported Inventory data against the public gallery website `https://amulets.museumwnf.org`, the supporting legacy codebase at `E:\mwnf-server\apps\amulets.museumwnf.org`, the production legacy database, and the imported Inventory database.

The Amulets site is one thematic gallery using the shared gallery codebase. The website is the primary source.

Relevant internal references:

- [Legacy Import](../../../docs/understanding/legacy-import.md)
- [Validation Guide](../../../docs/understanding/validation-guide.md)
- [Inventory Principles](../../../docs/understanding/inventory-principles.md)

## Summary

The Amulets gallery import is strong for the core object and partner records sampled from the public site. Dates, holders, locations, object descriptions, partner descriptions, coordinates, and related object links are mostly preserved.

The gallery-level import is weaker: the collection exists and membership is present, but the sampled gallery collection has no English title/description. Multi-image object coverage and image rights/caption preservation also need attention.

## Samples Checked

| Website area | Sampled website page or API | Website facts checked | Inventory result |
|---|---|---|---|
| Gallery landing | `/`; `/api/v2/thg/galleries/self?hash=2e356d8` | Gallery id 4; name `Amulets and Talismans`; banner object `/mwnf3/objects/EPM/at/Mus22/51`; banner image `thematic_gallery/thg_galleries/4/banner.jpg` | Collection `mwnf3_thematic_gallery:thg_gallery:4` exists as `gallery_amulets_and_talismans`. No English collection translation/title was found in the sampled query. |
| Collection results | `/collection-results`; `/api/v2/items?hash=2e356d8` | 46 total objects across 3 pages; first samples include `ISL/jo/Mus01/4`, `EPM/gr/Mus21/25`, `ISL/tr/Mus01/18` | Sampled object items exist. They are linked to the gallery or related contexts. |
| Search | `/search?q=kufic`; `/api/v2/items?hash=2e356d8&q=kufic` | 7 results including `Inscription stone`, `Bronze mirror with sphinxes and kufic inscription`, `Amulet`, and `Amulet case` | Sampled objects exist with matching names and fields. Search behavior itself is not directly validated by Inventory rows. |
| Item detail | `/database-item/mwnf3/objects/ISL/jo/Mus01/4/en` | `Inscription stone`; date 700-750; holder Jordan Archaeological Museum; long and short descriptions; one image; 5 related objects; source URL to Islamic Art | Item `mwnf3:objects:ISL:jo:Mus01:4` exists. Core fields, image, and related object links match. It has multiple English translations across contexts. |
| Item detail | `/database-item/mwnf3/objects/ISL/tr/Mus01/18/en` | `Talismanic shirt`; date 1375-1425; two live/legacy images; one related object; exhibition link to `Echoes of Paradise` | Item `mwnf3:objects:ISL:tr:Mus01:18` exists with matching core fields and related link. Only one imported image was found, while the website has two. |
| Partner profiles | `/partner/ISL/jo/Mus01/en`; `/partner/EPM/gr/Mus21/en` | Jordan Archaeological Museum and Benaki Museum include descriptions, contact/map data, logos, and three partner photos each | Partners `mwnf3:museums:Mus01:jo` and `mwnf3:museums:Mus21:gr` exist with names, cities, phone/website where available, map coordinates, descriptions, contact data, and three images. |
| Timeline | `/timeline-results?c=jo&start=700&end=750` | Four Jordan events: HCR 673-676, years 728, 743-744, 747, and 749 | Events `mwnf3:hcr:673` through `mwnf3:hcr:676` exist with matching years, country, and English descriptions. |

## Legacy Source Mapping

| Website content | Legacy source used by the site |
|---|---|
| Objects | `mwnf3.objects`, including project, country, museum, number, language, name, dates, location, type, holder, and descriptions. |
| Object images | `mwnf3.objects_pictures`, including image number, path, copyright, and captions. |
| Gallery membership | `mwnf3_thematic_gallery.thg_gallery_mwnf3_objects` and thematic-gallery membership tables. |
| Partners | `mwnf3.museums`, `mwnf3.museumnames`, partner pictures, and logos. |
| Timeline | `mwnf3.hcr` and `mwnf3.hcr_events`. |
| Related objects | Legacy object-object and object-monument links, imported as Inventory item links. |

## Import Quality

The sampled gallery import is reliable for the underlying cultural records. Objects and partners are traceable, and important object relationships are preserved.

The imported collection shell is present, but the public gallery name and description are not available as sampled English collection translation data. This is a visible gap because the Amulets site is a gallery-first experience.

## Gaps To Address

1. **Gallery title/description translation is missing in the sample**

   Collection `mwnf3_thematic_gallery:thg_gallery:4` exists, but the English title `Amulets and Talismans` was not found as a collection translation.

2. **Multi-image object coverage is incomplete**

   The `Talismanic shirt` page has two live/legacy images, while Inventory has one image in the sampled query.

3. **Context-scoped translations need clear display rules**

   Sampled items have multiple English translations from project and gallery contexts. This can be correct, but future screens must select the intended context.

4. **Image credits and captions need broader review**

   Some live/legacy image records include copyright or credit data. The sampled imported image fields did not clearly preserve all such values.

5. **Source action links are not fully first-class**

   The live gallery exposes source-site actions such as remote object, collection, book, and travel links. Inventory preserves some relationships and extra data, but these actions are not clearly structured as user-facing links.

## Business Assessment

The Amulets import is good for object and partner validation, but gallery-level metadata and complete media coverage need work before the public gallery experience can be reproduced from Inventory alone.
