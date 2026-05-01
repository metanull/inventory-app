---
layout: default
title: Sharing History Import Validation Report
---

# Sharing History Import Validation Report

## Purpose

This report checks a small, broad sample of public content from `https://sharinghistory.museumwnf.org` against:

- the production legacy Sharing History database, checked read-only;
- the imported Inventory database on OVH, checked read-only;
- the import rules documented in [Legacy Import](../../../docs/understanding/legacy-import.md) and [Validation Guide](../../../docs/understanding/validation-guide.md).

The public Sharing History website is the primary source. The PHP codebase in `www.sharinghistory.org` was used only to understand how page URLs identify source records and how public pages assemble their content.

This is a sample-based validation. It does not claim a full corpus pass rate.

Discover Islamic Art is out of scope for this report.

## Overall Assessment

The sampled Sharing History import is broadly traceable and usable. The checked database objects, item images, partner identity, historical profile page, timeline events, timeline-to-item links, and exhibition structure are present in Inventory and can be traced back to their legacy source records.

The main gaps are concentrated around page media and page reconstruction rather than around missing core records:

- Partner profile gallery images shown on the public partner page were not found as `partner_images` in Inventory for the sampled partner. The partner logos are present.
- Exhibition cover/home/portal images for the sampled exhibition were not found as `collection_images` in Inventory, even though the exhibition text and item links are present.
- Timeline and historical-profile pages preserve item links, but page-level image records are not present in the checked Inventory image tables. A frontend can still show images by resolving the linked item image, but the page image itself is not represented as separate media.
- Sharing History `backward_compatibility` values are stored with lower-case project and partner codes, such as `awe` and `et_01`, while public URLs use `AWE` and `ET_01`. This is consistent within Inventory, but validators must account for it.
- Some imported display values preserve source formatting that differs from the website output. For example, one sampled title keeps italic markers in Inventory.

These findings are different from the earlier Discover Islamic Art findings. Sharing History partner visibility is good in the sampled case; the sampled partner is imported as visible.

## Sample Set

The sample covers several public page types instead of many records from one page type:

| Public page type | Sample URL or identifier | Reason for inclusion |
|---|---|---|
| Database object detail | `database_item.php?id=object;AWE;eg;79;en` | Appears on the Egypt historical profile page |
| Database object detail | `database_item.php?id=object;AWE;eg;99;en` | Appears on the Egypt timeline page |
| Database object detail | `database_item.php?id=object;AWE;eg;91;en` | Appears on the Egypt timeline page |
| Database object detail | `database_item.php?id=object;AWE;eg;3;en` | Appears on the Egypt timeline page and has a direct partner key |
| Partner profile | `pm_partner.php?id=ET_01;eg&shpro=AWE&` | Public partner page for Bibliotheca Alexandrina |
| Historical profile | `hb_result.php?country=eg&page=1` | Public country profile page with text and linked images |
| Timeline result | `hcr_result.php?nccountry=eg&theme=none&startPeriod=none&endPeriod=none&pageT=N` | Public Egypt timeline with object-linked events |
| Exhibition | `exhibitions/AWE/exh_items.php?eId=5&lan=en` and `exh_introduction.php?eId=5&lan=en` | Public virtual exhibition with introduction, themes, and items |

## Traceability Keys

The public website uses mixed-case identifiers in URLs. Inventory stores the imported Sharing History keys in lower case.

| Website identifier | Inventory `backward_compatibility` found |
|---|---|
| `object;AWE;eg;79;en` | `mwnf3_sharing_history:sh_objects:awe:eg:79` |
| `object;AWE;eg;99;en` | `mwnf3_sharing_history:sh_objects:awe:eg:99` |
| `object;AWE;eg;91;en` | `mwnf3_sharing_history:sh_objects:awe:eg:91` |
| `object;AWE;eg;3;en` | `mwnf3_sharing_history:sh_objects:awe:eg:3` |
| `ET_01` partner | `mwnf3_sharing_history:sh_partners:et_01` |
| Egypt historical profile page 1 | `mwnf3_sharing_history:sh_countries_historicalbackground_pages:40` |
| Egypt timeline event 1807 | `mwnf3_sharing_history:sh_hcr:869` |
| Exhibition `eId=5` | `mwnf3_sharing_history:sh_exhibitions:5` |

The lower-case Inventory keys do not indicate missing data. They are a normalization detail that validation queries must use.

## Sample Findings

| Website sample | Legacy source | Inventory result | Assessment |
|---|---|---|---|
| `object;AWE;eg;79;en` - `Colonel Ahmad ’Urabi Pasha` | `mwnf3_sharing_history.sh_objects`, `sh_objects_texts`, `sh_object_images`; source image `sharing_history/sh_objects/awe/eg/79/1.jpg` | Item found as `mwnf3_sharing_history:sh_objects:awe:eg:79`; English title, country `egy`, holder text, image original name, historical-profile collection link, and timeline link to `sh_hcr:935` found | Match |
| `object;AWE;eg;99;en` - `On the Mahmudiya Canal, Alexandria, Egypt` | `sh_objects`, `sh_objects_texts`, `sh_object_images`; timeline image pivot `sh_hcr_images:473` links it to event `sh_hcr:869` | Item found with one item image; timeline event link found through `timeline_event_item`; no historical-profile collection link found for this sample | Match for public timeline use |
| `object;AWE;eg;91;en` - `Shubra Palace, 1875` | `sh_objects`, `sh_objects_texts`, `sh_object_images`; date `1875`; timeline image pivot `sh_hcr_images:461` links it to event `sh_hcr:853` | Item found with date `1875`, one item image, and timeline event link | Match |
| `object;AWE;eg;3;en` - `Muhammad 'Ali Pasha` | `sh_objects`, `sh_objects_texts`, `sh_object_images`; partner `ET_01`; date text `19th century`, start `1800`, end `1900`; timeline pivots `sh_hcr_images:483` and `484` | Item found with partner `mwnf3_sharing_history:sh_partners:et_01`, date range, one item image, historical-profile links, exhibition/theme links, and timeline links | Match with display-format note |
| `pm_partner.php?id=ET_01;eg&shpro=AWE&` - Bibliotheca Alexandrina | `sh_partners`, `sh_partner_names`; public page shows profile gallery images and logo tab | Partner found as visible institution in Egypt, city Alexandria, website present, 48 linked Items, 2 partner logos found; `partner_images` count is 0 | Partial |
| `hb_result.php?country=eg&page=1` - `Modern Egyptian society` | `sh_countries_historicalbackground`, `sh_countries_historicalbackground_pages`, page text, and page image references including object `AWE;eg;79` | Collection found as `mwnf3_sharing_history:sh_countries_historicalbackground_pages:40`; title and description present; linked items include objects 79, 96, 108, and 17; no collection media/image rows found | Content match, page-media gap |
| Egypt timeline result page | `sh_hcr`, `sh_hcr_events`, `sh_hcr_images` | Events `852`, `885`, `931`, `869`, `853`, `909`, and `910` found. Object-linked events have `timeline_event_item` rows. `timeline_event_images` count is 0 for sampled events | Timeline content and links match; image representation is indirect |
| Exhibition `Fine and Applied Arts` (`eId=5`) | `sh_exhibitions`, `sh_exhibitionnames`, `sh_exhibition_images`, `sh_exhibition_themes`, `sh_exhibition_themenames` | Collection found as `mwnf3_sharing_history:sh_exhibitions:5`; title, Edward Said quote, introduction text, bibliography JSON, 257 direct item links, and five child themes found; no collection image rows found | Text and structure match, exhibition media gap |

## What Matches Well

### Core Database Objects

The four sampled object detail pages are present in Inventory with their expected identity, English title, country, holder text, and item image records. Their legacy image source paths are preserved as image `original_name` values while Inventory stores the files under generated UUID filenames.

The sample includes both very light source rows, where the public page mainly shows title, location, holder, and image, and a richer row with partner and date data. Both patterns are represented.

### Timeline Content And Item Links

The sampled Egypt timeline entries are imported as `timeline_events`. Public entries without item images, such as `Bayt al-Suhaymi` in 1796 and `Mehmed Hüsrev Pasha` in 1801, exist as timeline text events.

Public entries with database-entry links also preserve the relationship to the item. For example, the public 1807 entry links to object `AWE;eg;99`; Inventory contains a `timeline_event_item` link from event `mwnf3_sharing_history:sh_hcr:869` to item `mwnf3_sharing_history:sh_objects:awe:eg:99`.

This is important because it means the future read-only API can expose timeline entries together with their linked object records.

### Historical Profile Text And Linked Items

The Egypt historical profile page `Modern Egyptian society` is imported as a collection. The title and public text are present, and the checked item links include the page's visible object sample `Colonel Ahmad ’Urabi Pasha`.

The public page uses object images as part of the profile page experience. Inventory preserves the linked items and their item images, but does not expose separate collection image rows for this sampled page.

### Partner Identity And Visibility

The sampled public partner, Bibliotheca Alexandrina, is imported as a visible institution in Egypt. The name, city, website, and linked item count were found. This is a positive finding because it means the sampled Sharing History partner can be shown by an Inventory UI or read-only API that respects `visible`.

This differs from the previous Discover Islamic Art validation draft, where sampled partner visibility was a concern.

### Exhibition Text And Structure

The virtual exhibition `Fine and Applied Arts` is imported as an exhibition collection. The public introduction text beginning `The 19th century is characterised as an era of exchanges and rediscoveries...` is present in `collection_translations.description`.

The exhibition's five public themes are present as child collections:

- `Collecting`;
- `Encountering the East`;
- `Encountering the West`;
- `The concept of revivals`;
- `Photography`.

The exhibition collection also has direct item links, and the importer preserves bibliography data in `extra`.

## Main Gaps And Risks

### Partner Gallery Images Are Missing For The Sampled Partner

The public Bibliotheca Alexandrina partner page shows several profile/gallery images from paths such as:

```text
sharing_history/sh_partners/et_01/1.jpg
sharing_history/sh_partners/et_01/2.jpg
sharing_history/sh_partners/et_01/3.jpg
sharing_history/sh_partners/et_01/4.jpg
sharing_history/sh_partners/et_01/5.jpg
```

In Inventory, the sampled partner has two `partner_logos`, but no `partner_images` rows.

Business effect: the partner profile can show its name and text, but it cannot reproduce the visual gallery shown on the current website unless the read-only API or frontend obtains those images another way.

Legacy source area to inspect further:

- `mwnf3_sharing_history.sh_partners`;
- `mwnf3_sharing_history.sh_partner_names`;
- Sharing History partner image/logo tables or path conventions used by `class.partners.inc.php`.

Recommended decision:

- Treat partner logos and partner gallery images as separate content. Logos are already imported for the sample. Gallery images need a confirmed source-table or source-path rule, then importer coverage.

### Exhibition Images Are Not Imported As Collection Images

The public `Fine and Applied Arts` exhibition uses home, thumbnail, portal, cover, and item images. The legacy source includes fields such as:

```text
sharing_history/sh_exhibitions/exhibitions/AWE/5/homeImg5.jpg
sharing_history/sh_exhibitions/exhibitions/AWE/5/item_5.jpg
sharing_history/sh_exhibitions/exhibitions/awe/5/portal_image/1.jpg
```

Inventory contains the exhibition collection, text, themes, and item links, but no `collection_images` rows for the sampled exhibition.

Business effect: the exhibition can be reconstructed as text plus linked items, but the landing-page visual assets are not first-class Inventory media for the exhibition itself.

Legacy source tables involved:

- `mwnf3_sharing_history.sh_exhibitions`;
- `mwnf3_sharing_history.sh_exhibitionnames`;
- `mwnf3_sharing_history.sh_exhibition_images`;
- `mwnf3_sharing_history.sh_exhibition_themes`;
- `mwnf3_sharing_history.sh_exhibition_themenames`.

Recommended decision:

- Decide whether exhibition cover/home/portal images must be exposed as collection images in the future read-only API. If yes, add importer coverage for those exhibition-level image fields.

### Timeline Images Are Represented Through Linked Items, Not As Event Images

The public Egypt timeline displays object images beside some timeline entries and links them to database entries. The legacy table `sh_hcr_images` provides those links.

Inventory preserves the item links through `timeline_event_item`, but the sampled timeline events have no `timeline_event_images` rows.

Business effect: a frontend can still display the image by following the linked item and using its first image. However, a frontend that expects timeline events to carry their own image records will not reproduce the public page directly.

Recommended decision:

- For the read-only API, choose one rule and document it clearly: either timeline event images come from linked item images, or `sh_hcr_images` also creates explicit timeline event image records.
- Do not duplicate images blindly. If explicit timeline-event images are added, keep the link to the source item so the public `See Database Entry` behavior remains available.

### Historical Profile Page Images Are Also Indirect

The public Egypt historical profile page shows images linked to database items. Inventory imports the page as a collection and links the relevant items, but no collection images or media rows were found for the sampled historical-profile page.

Business effect: the page text and item relationships are usable, but page rendering must derive images from linked item records unless the importer adds collection-level image records.

Recommended decision:

- Use the same rule for historical-profile pages and timeline pages if possible. Either page images are derived from linked items, or page image rows become first-class collection/timeline media.

### Display Formatting Needs A Public Presentation Rule

The sampled item `Muhammad 'Ali Pasha` is stored in Inventory with italic markers in the title field:

```text
*Muhammad 'Ali Pasha*
```

The public website renders formatted text rather than showing literal Markdown-style markers. This is not a traceability failure, but it affects customer-facing display.

Business effect: reviewers may see imported content as less polished if the future UI displays raw formatting markers.

Recommended decision:

- Decide whether the importer should normalize these markers into plain text, or whether the read-only API/frontend should render them as formatting.
- Apply the decision consistently across item titles, descriptions, and exhibition text.

## Remediation Summary

| Area | Status in sample | Suggested action |
|---|---|---|
| Object records | Present and traceable | No importer change indicated by this sample |
| Item images | Present for sampled objects | No importer change indicated by this sample |
| Partner identity and visibility | Present; sampled partner is visible | No visibility change indicated by this sample |
| Partner logos | Present for sampled partner | No importer change indicated by this sample |
| Partner gallery images | Missing as `partner_images` | Confirm source mapping and import public partner gallery images |
| Historical profile text | Present | No text import change indicated by this sample |
| Historical profile page images | Indirect through linked items | Decide whether linked-item images are sufficient for the read-only API |
| Timeline events | Present | No event text import change indicated by this sample |
| Timeline item links | Present | No relationship import change indicated by this sample |
| Timeline event images | Indirect through linked items | Decide whether to import explicit timeline event image records |
| Exhibition text and introduction | Present | No text import change indicated by this sample |
| Exhibition themes and item links | Present | No relationship import change indicated by this sample |
| Exhibition cover/home/portal images | Missing as `collection_images` | Import if exhibition landing pages need first-class images |
| Display formatting | Some raw formatting markers preserved | Define public presentation rule in importer or read-only API |

## Scope Limits

- The report validates selected English public pages only.
- The checks verify database rows and stored image metadata. They do not prove that every physical image file is present in every final storage location.
- The report does not assess Discover Islamic Art, Explore, Travels, or Thematic Galleries.
- The report does not assess every Sharing History country, partner, exhibition, timeline event, or object.
- The report does not assign percentages or pass rates, because the sample is intentionally broad rather than statistically exhaustive.

## Recommended Customer Validation Decisions

Before customer sign-off on Sharing History import quality, decide these points explicitly:

1. Should public partner profile gallery images be imported as partner images, in addition to logos?
2. Should exhibition home, cover, thumbnail, and portal images be first-class collection images?
3. Should timeline and historical-profile page images be exposed as separate page/event media, or derived from linked item images?
4. Should public display remove or render source formatting markers such as `*...*` in item titles?
5. Should validation tooling treat Sharing History `backward_compatibility` lookups as lower-case normalized keys?

## Conclusion

The Sharing History sample supports a positive but qualified assessment. Core sampled content is imported: objects, object images, partner identity, historical profile text, timeline events, timeline-to-item links, exhibition text, exhibition themes, and exhibition item links are present and traceable.

The main open work is media representation for public pages. Partner gallery images and exhibition-level images are not imported as first-class media in the checked Inventory tables. Timeline and historical-profile images are available indirectly through linked item records, which may be sufficient if the read-only API is designed around that rule.

The import is therefore strong enough for content traceability review, but not yet complete for reproducing all public Sharing History page visuals without additional read-only API logic or importer media coverage.
