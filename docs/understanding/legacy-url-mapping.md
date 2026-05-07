---
layout: default
title: Legacy URL Mapping
parent: Understanding the Inventory
nav_order: 5
---

# Legacy URL Mapping

Use this guide when you turn an imported record's `backward_compatibility` value into links back to legacy MWNF websites and back-office tools.

The Legacy Link Resolver supports import validation in `/admin`. It resolves links for Items, Collections, and Partners, and it reports whether each result is exact, inferred, needs lookup data, or is unsupported.

## Result types

| Result type | Meaning |
|---|---|
| `exact` | The `backward_compatibility` value contains all parts needed to build the legacy URL. |
| `inferred` | The resolver combines `backward_compatibility` with Inventory context such as project, parent collection, or country. |
| `requires_lookup` | The resolver needs legacy data such as a slug, hash, parent country, location, or partner code before it can build the URL. |
| `unsupported` | The source family is known, but no reliable URL rule exists yet. |

Display the result type with the URL. Do not hide uncertainty from reviewers.

## Link types

| Link type | Use |
|---|---|
| `public` | Opens the user-facing legacy website. |
| `backoffice` | Opens the legacy editing or administration surface. |
| `source` | Opens a related legacy source page when the visible page is not native to the current source family. |
| `diagnostic` | Explains why a URL cannot be built yet. |

Some records have more than one useful public link. For example, an Islamic Art object can have its canonical Islamic Art page and one or more thematic-gallery pages.

Store back-office route patterns separately from public hosts and mark unknown rules as `requires_lookup` or `unsupported` until you verify them against legacy back-office routes.

The back-office host is always `https://virtual-office.museumwnf.org`. It is a private domain and requires VPN access. Resolver rules build back-office links with `section` and `edit` query parameters that match the Virtual Office router.

## Language handling

Inventory stores normalized language identifiers such as `eng`, `fra`, and `spa`. Legacy public URLs usually use two-letter codes such as `en`, `fr`, and `es`.

Use `languages.backward_compatibility` when you build a legacy URL. Default to `en` when the caller does not request a language.

## Public host map

| Source family | Public host |
|---|---|
| Islamic Art projects `ISL` and `EPM` | `https://islamicart.museumwnf.org` |
| Baroque Art projects `BAR` and `AMA` | `https://baroqueart.museumwnf.org` |
| Sharing History | `https://sharinghistory.museumwnf.org` |
| Explore | `https://explore.museumwnf.org` |
| Travels | `https://travels.museumwnf.org` |
| MWNF Portal | `https://museumwnf.org` |
| Thematic-gallery exhibition pages | `https://exhibitions.museumwnf.org` |
| Thematic-gallery standalone gallery pages | Gallery-specific hosts such as `https://amulets.museumwnf.org` |

Keep this map in configuration. Do not hard-code production hosts inside rule classes.

## Test fixture examples

These examples come from `scripts/importer/gap_analysis` reports and form the first resolver fixture set.

| Record type | `backward_compatibility` | Expected public result |
|---|---|---|
| Item | `mwnf3:objects:ISL:eg:Mus01:1` | `https://islamicart.museumwnf.org/database_item.php?id=object;ISL;eg;Mus01;1;en` |
| Item | `mwnf3:monuments:BAR:pt:Mon11:23` | `https://baroqueart.museumwnf.org/database_item.php?id=monument;BAR;pt;Mon11;23;en` |
| Item | `mwnf3_sharing_history:sh_objects:awe:at:26` | `https://sharinghistory.museumwnf.org/database_item.php?id=object;awe;at;26;en` |
| Collection | `mwnf3_travels:trail:IAM:pt:1` | `https://travels.museumwnf.org/travel_et_trailDetail.php?id=IAM;pt;1;en&fl=its` |
| Collection | `mwnf3_travels:itinerary:IAM:pt:1:I` | `https://travels.museumwnf.org/travel_et_itenary.php?id=IAM;pt;I;en;1&fl=des` |
| Collection | `mwnf3_explore:country:eg` | `https://explore.museumwnf.org/countries/c-eg` |
| Collection | `mwnf3_thematic_gallery:thg_gallery:47` | `https://exhibitions.museumwnf.org/the_use_of_colours_in_art/en` |
| Partner | `mwnf3:museums:Mus01:eg` with project `ISL` | `https://islamicart.museumwnf.org/pm_partner.php?id=Mus01;eg&type=museum&theme=ISL` |
| Partner | `mwnf3_sharing_history:sh_partners:at_01` with country `at` | `https://sharinghistory.museumwnf.org/pm_partner.php?id=AT_01;at&shpro=AWE&` |

Representative back-office links:

| Record type | `backward_compatibility` | Expected back-office result |
|---|---|---|
| Item | `mwnf3:objects:ISL:eg:Mus01:1` | `https://virtual-office.museumwnf.org/?section=database/dba_objects&edit=1;ISL;eg;Mus01;1&` |
| Item | `mwnf3:monuments:BAR:pt:Mon11:23` | `https://virtual-office.museumwnf.org/?section=database/dba_monuments&edit=1;BAR;pt;Mon11;23&` |
| Item | `mwnf3_sharing_history:sh_objects:awe:at:26` | `https://virtual-office.museumwnf.org/?section=sh/sh_objects&edit=1;AWE;at;26&` |
| Collection | `mwnf3_travels:trail:IAM:pt:1` | `https://virtual-office.museumwnf.org/?section=travel/trails&edit=1;IAM;pt;1&` |
| Collection | `mwnf3_thematic_gallery:thg_gallery:47` | `https://virtual-office.museumwnf.org/?section=thg/thg_galleries&edit=1;47&` |
| Partner | `mwnf3:museums:Mus01:eg` | `https://virtual-office.museumwnf.org/?section=database/museum&edit=1;Mus01;eg&` |

## Initial rule matrix

| Inventory type | Source pattern | Public URL confidence |
|---|---|---|
| Item | `mwnf3:objects:{project}:{country}:{museum}:{number}` | Exact for configured project hosts. |
| Item | `mwnf3:monuments:{project}:{country}:{institution}:{number}` | Exact for configured project hosts. |
| Item | `mwnf3:monument_details:...` | Unsupported until detail URL rules are confirmed. |
| Item | `mwnf3_sharing_history:sh_objects:{project}:{country}:{number}` | Exact Sharing History object URL. |
| Item | `mwnf3_explore:monument:{id}` | Requires parent country and location lookup unless Inventory context provides them. |
| Collection | `mwnf3_sharing_history:sh_exhibitions:{id}` | Inferred exhibition item-list URL. |
| Collection | `mwnf3_thematic_gallery:thg_gallery:{id}` | Exact only when the configured gallery slug or host is known. |
| Collection | `mwnf3_thematic_gallery:theme:{gallery}:{theme}` | Exact only when the configured gallery slug or host is known. |
| Collection | `mwnf3_travels:trail:{theme}:{country}:{trail}` | Exact Travels trail URL. |
| Collection | `mwnf3_travels:itinerary:{theme}:{country}:{trail}:{itinerary}` | Exact Travels itinerary URL without optional `itrNo`. |
| Collection | `mwnf3_explore:country:{country}` | Exact Explore country URL. |
| Collection | `mwnf3_explore:location:{id}` | Inferred when Inventory has country context. |
| Partner | `mwnf3:museums:{museum}:{country}` | Inferred when Inventory has project context. |
| Partner | `mwnf3:institutions:{institution}:{country}` | Inferred when Inventory has project context. |
| Partner | `mwnf3_sharing_history:sh_partners:{id}` | Inferred when Inventory has country context. |

## Resolver behavior

The resolver must:

1. Parse `backward_compatibility` into source family, table, and key parts.
2. Choose a rule by source family, table, and Inventory model type.
3. Use the requested Inventory language to find the legacy language code.
4. Use project, parent Collection, country, or context only when the rule declares that it needs model context.
5. Return all matching links with result type and notes.
6. Return visible diagnostics for unsupported and unresolved mappings.
7. Fail explicitly for ambiguous rules instead of choosing a legacy URL by guesswork.

## Known gaps

- Thematic-gallery collection URLs need a durable gallery id to slug or host lookup for every gallery.
- Explore monument URLs need parent country and location lookup.
- Sharing History partner URLs need partner country and public partner code lookup.
- `mwnf3` exhibition URLs need legacy exhibition slug, theme, and page lookup.
- Monument detail and picture Item URLs need a decision: link to parent pages or verify separate legacy detail/image routes.

Keep this page current as new rules are verified. Treat it as the input ledger for the Legacy Link Resolver.