# Travels Import Validation Report

Date: 2026-05-02

## Scope

This report validates imported Inventory data against the public website `https://travels.museumwnf.org`, the supporting legacy codebase at `E:\mwnf-server\apps\travels.museumwnf.org`, the production legacy database, and the imported Inventory database.

The Travels activity is currently on hold, but the data remains relevant for the importer. The website is the primary source.

Relevant internal references:

- [Legacy Import](../../../docs/understanding/legacy-import.md)
- [Validation Guide](../../../docs/understanding/validation-guide.md)
- [Inventory Principles](../../../docs/understanding/inventory-principles.md)

## Summary

The Travels import is solid for the core Exhibition Trail hierarchy where rows exist. The sampled Portugal trail, itinerary, locations, and monument titles are traceable and match the public website and legacy tables.

The business gaps are substantial: some visible countries are absent from the imported hierarchy, several countries have partial monument coverage, media coverage is much thinner than the website and legacy picture tables, and tour/agency/travel-service content is not represented as first-class Inventory data.

## Samples Checked

| Website area | Sampled website page | Website facts checked | Inventory result |
|---|---|---|---|
| Homepage/navigation | `https://travels.museumwnf.org/` | Countries include Egypt, Jordan, Italy, Morocco, Palestine, Portugal, Spain, Syria, Tunisia, and Turkiye; Exhibition Trails are prominent | Inventory contains a Travels context/root and imported trail/location/itinerary/monument data for some countries, but not all visible countries. |
| Theme trail list | `travel_et_trail.php?tid=IAM` | `Eleven Exhibition Trails`; includes Algeria trail `UNE ARCHITECTURE DE LUMIERE`; links include `IAM;pt;1;en` | Trail collections exist for 12 imported trails overall, but sampled country coverage shows Algeria and Syria absent from imported itinerary/monument rows. |
| Portugal trail list | `travel_et_trail.php?cid=pt` | `IN THE LANDS OF THE ENCHANTED MOORISH MAIDEN`; subtitle `Islamic Art in Portugal`; region `Central and Southern Portugal`; English, Spanish, French, Italian, Portuguese | Collection `mwnf3_travels:trail:IAM:pt:1` exists as an exhibition-trail collection with country `prt`. |
| Portugal trail detail | `travel_et_trailDetail.php?id=IAM;pt;1;en&fl=its` | Ten itineraries, from `Mudejar Art` through itinerary X; each has main locations and image-gallery links | Portugal has 10 imported itinerary collections for the sampled trail, including `mwnf3_travels:itinerary:IAM:pt:1:I` and `...:V`. |
| Itinerary detail | `travel_et_itenary.php?id=IAM;pt;I;en;1&fl=des&itrNo=10` | `ITINERARY I: Mudejar Art`; description begins with the Tagus estuary/fishing/vegetable garden text | Collection `mwnf3_travels:itinerary:IAM:pt:1:I` exists. Title and description match the sampled public/legacy text. |
| Itinerary gallery | `travel_et_itenary.php?id=IAM;pt;I;en;1&fl=vl&itrNo=10` | Image gallery starts with `City Museum, Lisbon`; many thumbnails under `images.museumwnf.org/thumb/trails/iam/pt/1/i/...` | The sampled itinerary has 1 imported collection image. The related Lisbon location has 0 images in the sampled Inventory query. Legacy rows show more itinerary, location, and monument images. |
| Tour detail | `tourdetails.php?id=47&flag=CON&cid=pt` | Paused-tour banner; `IN THE LAND OF THE ENCHANTED MOORISH MAIDEN. Islamic Art in Portugal.`; 7 days / 6 nights; anytime; price `Please contact`; link to Exhibition Trail | No sampled Inventory collection/item was found for tour 47. Tours are not modeled as first-class Inventory records in the sampled production import. |
| Partners | `tr_partners.php?country=all` | Travel agents include Manar Travel, Viajes Mundo Amigo, AlternativeSicily, Zaatarah & Co., and Batouta Voyages | No sampled `mwnf3_travels:*agency*` Inventory partners were found. |

## Legacy Source Mapping

| Website content | Legacy source used by the site |
|---|---|
| Trails | `mwnf3_travels.trails`. |
| Itineraries | `mwnf3_travels.tr_itineraries`. |
| Locations | `mwnf3_travels.tr_locations`. |
| Travel monuments | `mwnf3_travels.tr_monuments`. |
| Pictures | `tr_trails_pictures`, `tr_itineraries_pictures`, `tr_locations_pictures`, and `tr_monuments_pictures`. |
| Tours and agencies | `travels`, `travel_texts`, `travels_countries`, `travels_trails`, `travel_days`, `tr_agencies`, and `tr_agency_texts`. |

## Legacy Scale Observed

| Legacy family | Count observed |
|---|---:|
| Trails | 12 |
| Itineraries | 109 |
| Locations | 410 |
| Travel monuments | 1,049 |
| Tours | 24 |
| Agencies | 5 |
| Trail pictures | 123 |
| Itinerary pictures | 438 |
| Location pictures | 569 |
| Monument pictures | 7,807 |

## Inventory Scale Observed

| Inventory family | Count observed |
|---|---:|
| Travels context | 1 |
| Travels root collection | 1 |
| Trail collections | 12 |
| Itinerary collections | 82 |
| Location collections | 274 |
| Travel monument items | 676 |
| Travel collection images | 235 |
| Travel item images | 1,468 |
| Travel child picture items under monument parents | 0 |

## Country Coverage Sample

| Country | Legacy itineraries | Inventory itineraries | Legacy monuments | Inventory monuments |
|---|---:|---:|---:|---:|
| Algeria `dz` | 5 | 0 | 68 | 0 |
| Syria `sy` | 8 | 0 | 85 | 0 |
| Portugal `pt` | 24 | 10 | 258 | 77 |
| Jordan `jo` | 5 | 5 | 40 | 26 |
| Palestine `pa` | 9 | 9 | 67 | 60 |
| Tunisia `tn` | 11 | 11 | 109 | 99 |

## Import Quality

The core imported hierarchy is credible for the sampled Portugal data. Trail, itinerary, location, and monument keys are consistent and traceable:

- `mwnf3_travels:trail:IAM:pt:1`
- `mwnf3_travels:itinerary:IAM:pt:1:I`
- `mwnf3_travels:itinerary:IAM:pt:1:V`
- `mwnf3_travels:location:IAM:pt:1:I:1`
- `mwnf3_travels:location:IAM:pt:1:V:5`
- `mwnf3_travels:monument:IAM:pt:1:I:1:a`
- `mwnf3_travels:monument:IAM:pt:1:I:1:b`
- `mwnf3_travels:monument:IAM:pt:1:V:5:d`

The sampled Portugal itinerary title and description match the public website. Sampled Portugal locations and monuments also preserve recognizable titles, including `LISBON`, `MERTOLA`, `City Museum`, `National Archaeology Museum`, and `Parish Church of Nossa Senhora da Anunciacao`.

The import is weaker for coverage and media. Unlike the `mwnf3` object/monument importer, the sampled Travels monument picture data does not appear as child `picture` items. The observed image count is much lower than the legacy picture-table count.

## Gaps To Address

1. **Country coverage is incomplete**

   Algeria and Syria are visible/source-backed on the Travels website but have zero sampled imported itinerary and monument rows. Portugal is also partial in the sampled count comparison.

2. **Monument and location coverage is partial**

   Several countries have fewer imported monuments than the legacy tables expose. This affects the ability to rebuild full Trails pages from Inventory.

3. **Media coverage is much thinner than the website**

   The website exposes large itinerary/location/monument galleries. Legacy picture tables contain 8,937 rows across sampled Travels media families, while Inventory has 1,703 travel image rows across collection and item images and no sampled child picture items under travel monument parents.

4. **Tours are not imported as first-class Inventory data**

   The sampled tour 47 page has title, duration, period, price text, day rows, and a trail link. No corresponding Inventory collection or item was found.

5. **Travel agencies are not imported as first-class partners**

   The live partner page lists five travel agencies. No sampled `mwnf3_travels:*agency*` Inventory partners were found.

6. **Travel-service side content is not represented**

   The legacy itinerary code loads cultural events, related walks, accommodation, traditional food, and useful contacts. The sampled Inventory import covers trail/itinerary/location/monument hierarchy, not these travel-service content families.

## Business Assessment

The Travels import is a useful base for Exhibition Trail hierarchy and some country content, especially the sampled Portugal IAM trail. It is not complete enough for a faithful Travels website replacement. The next importer review should prioritize missing countries, partial monument/location coverage, media coverage, and a clear business decision on whether tours, agencies, and travel-service sections belong in Inventory or a separate content system.