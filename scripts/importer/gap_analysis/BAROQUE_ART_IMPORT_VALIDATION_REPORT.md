# Baroque Art Import Validation Report

Date: 2026-05-02

## Scope

This report validates imported Inventory data against the public website `https://baroqueart.museumwnf.org`, the supporting legacy codebase at `E:\mwnf-server\apps\baroqueart.museumwnf.org`, the production legacy database, and the imported Inventory database.

The website is the primary source. The sample deliberately covers several visible website areas instead of limiting validation to one record family.

Relevant internal references:

- [Legacy Import](../../../docs/understanding/legacy-import.md)
- [Validation Guide](../../../docs/understanding/validation-guide.md)
- [Inventory Principles](../../../docs/understanding/inventory-principles.md)

## Summary

The Baroque Art import is credible for the main text of sampled objects, monuments, project context, and exhibition titles. The imported records keep consistent legacy identities such as `mwnf3:objects:BAR:pt:Mus11_A:13` and `mwnf3:monuments:BAR:pt:Mon11:23`.

The most important gaps are supporting website experience data: item-to-timeline links are missing in the sample, partner detail is thinner than the live page, and monument special-feature content is not clearly represented. The sampled multi-image object and monument pages are not image-import gaps: their extra images exist as child `picture` items.

## Samples Checked

| Website area | Sampled website page | Website facts checked | Inventory result |
|---|---|---|---|
| Homepage and highlight | `https://baroqueart.museumwnf.org/` | Website title; navigation to Permanent Collection, Database, Exhibitions, Timeline; highlighted object `object;BAR;pt;Mus11_A;13;en` | BAR project context exists as `mwnf3:projects:BAR`. Highlighted object exists. |
| Object detail | `database_item.php?id=object;BAR;pt;Mus11_A;13;en` | `Portrait of the Marquis of Pombal`; Oeiras, Lisbon, Portugal; holder Oeiras Town Hall; date 1766; oil on canvas; 6 visible object images; timeline link; portrait category | Item `mwnf3:objects:BAR:pt:Mus11_A:13` exists. Type, country, partner, English title, date, location, holder, and description match. The parent has 1 image and 6 child `picture` items (`mwnf3:objects_pictures:bar:pt:Mus11_A:13:1..6`). |
| Partner list/detail | `pm_partner_list.php?type=museum&`; `pm_partner.php?id=Mus11_A;pt&type=museum&theme=BAR` | Country-grouped partner list; Portugal includes associated museums; detail page displays `Further Associated Museums`, Portugal, image, logo, and a visible list of Portuguese institutions | Partner `mwnf3:museums:Mus11_A:pt` exists with name and country. The sampled detail is flatter than the website: city and website are null, and one image is imported. |
| Permanent collection | `pclist_all.php?country=pt&lang=en` | Portugal has 50 objects and 35 monuments; includes monument `Church of St. Francis, Oporto` and object links | Sampled object and monument records exist. Project collection `mwnf3:projects:BAR` has 597 item links, matching the portal Baroque count sampled separately. |
| Monument detail | `database_item.php?id=monument;BAR;pt;Mon11;23;en` | `Church of St. Francis, Oporto`; 13th-14th and 17th-18th century date text; Parish of St. Nicolau, Oporto, Portugal; 5 main images plus special features; timeline link | Item `mwnf3:monuments:BAR:pt:Mon11:23` exists. Title, date, location, country, and description match. The parent has 1 image and 5 child `picture` items (`mwnf3:monuments_pictures:bar:pt:Mon11:23:1..5`). No sampled timeline links were found. |
| Exhibition index | `exhibitions/BAR/index.php` | Exhibitions include `Absolutism`, `Devotion and Pilgrimage`, `The Age of Enlightenment`, and others | Imported collection titles match sampled BAR exhibition roots such as `mwnf3:exhibitions:43`, `44`, `45`, `46`, `47`, `50`, and `51`. |

## Legacy Source Mapping

| Website content | Legacy source used by the site |
|---|---|
| Objects | `mwnf3.objects`, `objects_pictures`, object links, dynasty and author tables, loaded through the legacy item class. |
| Monuments | `mwnf3.monuments`, `monuments_pictures`, `monument_details`, monument links, trail links, and author links. |
| Partners | `mwnf3.museums`, museum names, museum pictures, and associated partner tables. |
| Exhibitions | `mwnf3.exhibitions`, exhibition fields, themes, pages, page images, and page image details. |
| Timelines | `mwnf3.hcr` and item-country/project filters surfaced by item pages. |

## Import Quality

Core text import is good for the sampled object and monument. The public identity of both records is preserved: names, dates, places, holders, and descriptions are recognizable.

The exhibition root import also looks strong in the sample. The public exhibition titles are present as Inventory collections.

The import is weaker for the material that turns a database record into the full website page: partner profile richness, timeline relations, and the structured special-feature content shown on monument pages.

## Gaps To Address

1. **Timeline relationship gap**

   The sampled object and monument pages expose `Timeline for this item`, but no `timeline_event_item` links were found for those records.

2. **Partner detail gap**

   The sampled associated-museum partner exists, but the imported row is sparse compared with the live page. City, website, richer associated-museum structure, and some image/logo presentation data need review.

3. **Monument special-feature gap**

   The live monument page exposes structured special features with detail-level images and text. The main monument images are present as child `picture` items, but the sampled imported rows did not show the structured special-feature text/detail grouping.

4. **Context duplication requires deliberate filtering**

   Sampled BAR item translations also appear under an EPM context. That may be legitimate reuse, but validation and future screens must filter by the intended context.

## Business Assessment

The Baroque Art import is good enough for validating the main object, monument, exhibition text, and sampled multi-image records through the parent plus child-picture model. It is not yet good enough to reproduce the public website experience for timeline navigation, partner profiles, and structured monument special features.
