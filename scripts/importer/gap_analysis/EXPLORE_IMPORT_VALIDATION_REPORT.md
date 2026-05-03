# Explore Import Validation Report

Date: 2026-05-02

## Scope

This report validates imported Inventory data against the public website `https://explore.museumwnf.org`, the supporting legacy codebase at `E:\mwnf-server\apps\explore.museumwnf.org`, the production legacy database, and the imported Inventory database.

The website is the primary source. The sample covers themes, countries, locations, monuments, filters, itineraries, and sub-itineraries.

Relevant internal references:

- [Legacy Import](../../../docs/understanding/legacy-import.md)
- [Validation Guide](../../../docs/understanding/validation-guide.md)
- [Inventory Principles](../../../docs/understanding/inventory-principles.md)

## Summary

The Explore import contains a broad foundation: themes, countries, locations, monument shells, filters, tags, and many collection-item links exist. Locations and Explore-native monuments are the strongest area in the sample.

The largest quality issue is that the live Explore website often builds monument pages from other MWNF sources when Explore-native descriptions are empty. The imported Inventory records keep the monument identity, but cross-source descriptions and images are often missing. Itinerary import is also weak in the sample: public titles, descriptions, duration, and local-team text are missing or generic.

## Samples Checked

| Website area | Sampled website page | Website facts checked | Inventory result |
|---|---|---|---|
| Theme | `/themes/t-1` | `Explore the Islamic Heritage of the Mediterranean`; order 2; map lat 25, lon 10, zoom 3 | Collection `mwnf3_explore:thematiccycle:1` exists. Title and map values match. Sampled display order was null. |
| Theme | `/themes/t-3` | `Explore Baroque`; map lat 47.162494, lon 19.503304, zoom 4 | Collection `mwnf3_explore:thematiccycle:3` exists with matching title and map values. |
| Country | `/countries/c-eg` | Egypt page; country image; map coordinates; locations Alexandria, Cairo, Fuwa, Rosetta; filters such as `Islamic | Mamluk`, `Waterfront`, `Islamic | Ottoman Baroque` | Collection `mwnf3_explore:country:eg` exists with title `Egypt` and country `egy`. Sampled collection lacks visible country image/map metadata found on the website. |
| Location | `/countries/c-eg/l-2` | Cairo; lat 30.044774, lon 31.235601, zoom 15; rich filter set | Collection `mwnf3_explore:location:2` exists. Title and coordinates match. It has 65 linked items in the sampled Inventory query. |
| Monument from Virtual Museum | `/countries/c-eg/l-2/m-300` | `Aqmar Mosque`; Cairo/Egypt; source `Virtual Museum`; 4 images; long Discover Islamic Art description | Item `mwnf3_explore:monument:300` exists with name and coordinates. Sampled description is empty and image count is 0. |
| Monument from Exhibition Trails | `/countries/c-eg/l-2/m-299` | `Amir Bashtak Palace`; source `Exhibition Trails`; 2 images; Travel-derived description | Item `mwnf3_explore:monument:299` exists with name and coordinates. Sampled description is empty and image count is 0. |
| Explore-native monument | `/countries/c-eg/l-2/m-1780` | `Abdeen Palace`; source `Explore`; 1 image; Explore-native description | Item `mwnf3_explore:monument:1780` exists with name, coordinates, description, and 1 image. |
| Itinerary | `/itineraries/c-eg/i-6` | `Mamluk Art. Splendour and Magic of the Sultans`; Egypt; 8 sub-itineraries | Collection `mwnf3_explore:itinerary:6` exists, but sampled title is `Explore the Islamic Heritage of the Mediterranean - EG` and only 1 linked item was found. |
| Sub-itinerary | `/itineraries/c-eg/i-6/si-7` | `The Seat of the Sultanate`; duration `One day`; description and local-team content | Collection `mwnf3_explore:itinerary:7` exists, but sampled title is generic (`Itinerary 7`), and visible duration/description are missing. |
| Filters | Cairo/Egypt pages | Filters include `Islamic | Mamluk`, `Islamic | Ottoman Baroque`, and `Waterfront` | Tags `mwnf3_explore:filter:filters_13`, `filters_19`, and `filters_11` exist with matching labels and linked item counts. |

## Legacy Source Mapping

| Website content | Legacy source used by the site |
|---|---|
| Themes | `mwnf3_explore.thematiccycle` and `thematiccycletranslated`. |
| Countries | `countries`, `countrynames`, and `countrytranslated`. |
| Locations | `locations`, `locationtranslated`, and `locations_pictures`. |
| Monuments | `exploremonument`, `exploremonumentext`, `exploremonument_pictures`, and fallback source joins to Virtual Museum, Exhibition Trails, Sharing History, and related image tables. |
| Itineraries | `explore_itineraries`, `explore_itineraries_langs`, and itinerary relation tables. |
| Filters | `filters` and `filters_explore_monuments`. |

## Import Scale Observed

| Imported Explore area | Count observed |
|---|---:|
| Collections | 867 |
| Collection translations | 1,283 |
| Items | 1,692 |
| Item translations | 2,070 |
| Tags | 30 |

These counts show that Explore was imported at meaningful scale. The issue is quality and completeness for specific website behaviors, not absence of the whole import.

## Import Quality

The import captures Explore structure well enough for many navigation and search purposes. Themes, countries, locations, filters, tags, coordinates, and many item links are present.

Explore-native monuments are stronger than cross-source monuments. `Abdeen Palace` has description and image data, while `Aqmar Mosque` and `Amir Bashtak Palace` keep only shell information in the sample.

Itineraries are not business-ready in the sample. The imported records do not preserve public titles and visible text for the sampled itinerary and sub-itinerary.

## Gaps To Address

1. **Cross-source monument content is missing**

   The live website intentionally pulls descriptions and images from Virtual Museum, Exhibition Trails, Sharing History, and other sources. Sampled Inventory records for those monuments do not carry that visible content.

2. **Itinerary pages are incomplete**

   Public itinerary titles, sub-itinerary titles, duration, descriptions, and local-team content are missing or generic in sampled imported records.

3. **Country and theme landing metadata is incomplete**

   Names and some coordinates exist, but sampled country image/map metadata and sampled theme ordering are not fully represented.

4. **Filter keys need clear documentation for validators**

   Imported filter keys use the live-style `filters_13` shape, not a bare numeric key. This is acceptable if documented and used consistently.

## Business Assessment

Explore has a useful imported foundation for records, places, coordinates, and filters. It is not yet sufficient to reproduce the public Explore website experience, especially for monument pages assembled from other legacy sources and itinerary pages.
