# Islamic Art Import Validation Report

Date: 2026-05-01

## Scope

This report checks a focused sample of Islamic Art content displayed on `https://explore.museumwnf.org` against:

- the legacy production databases used by the current Explore website;
- the imported Inventory database on OVH;
- the importer mapping documented in [Legacy Import](../../../docs/understanding/legacy-import.md) and [Validation Guide](../../../docs/understanding/validation-guide.md).

The sample is intentionally small and spread across several public page types. It is not a statistical audit. It is a business validation pass designed to identify whether the imported Inventory keeps the content needed to rebuild Islamic Art Explore pages.

## Sample Used

| Public page type | Public sample | Legacy source checked | Inventory records checked |
|---|---|---|---|
| Theme page | `Explore the Islamic Heritage of the Mediterranean` (`/themes/t-1`) | `mwnf3_explore.thematiccycle`, `thematiccycletranslated`, `thematiccyclecountries`, `thematiccycle_country_texts` | `mwnf3_explore:thematiccycle:1` |
| Country text inside the theme | Jordan (`/themes/t-1/c-jo`) | `mwnf3_explore.thematiccycle_country_texts` | theme translation `extra.country_texts` |
| Itinerary page | `The Umayyads. The Rise of Islamic Art` (`/itineraries/c-jo/i-43`) | `mwnf3_explore.explore_itineraries`, `explore_itineraries_langs` | `mwnf3_explore:itinerary:43` |
| Sub-itinerary page | `Amman, the Governor's Headquarters` (`/itineraries/c-jo/i-43/si-44`) | `explore_itineraries_langs`, `explore_itineraries_rel_locations`, `explore_itineraries_rel_monuments` | `mwnf3_explore:itinerary:44` |
| Location section | `Amman` | `mwnf3_explore.locations`, `locationtranslated` | `mwnf3_explore:location:10` |
| Monument cards | `Umayyad Palace`, `Jordan Archaeological Museum`, `Nymphaeum`, `Al-Husseini Mosque` | `mwnf3_explore.exploremonument`, `exploremonumentext`, relation tables | `mwnf3_explore:monument:773/774/776/777` |
| Monument detail source content | `Umayyad Palace` | `mwnf3_travels.tr_monuments`, `tr_monuments_pictures`; `mwnf3.monuments`, `monuments_pictures` | linked Travel and Virtual Museum Items |
| Institution content | `Jordan Archaeological Museum` | `mwnf3.museums`, `museums_pictures` | `mwnf3:museums:Mus01:jo` |

## Overall Assessment

The import is strong for traceability and structural placement. The tested records are present with stable `backward_compatibility` identifiers, the theme/location/itinerary hierarchy is recognizable, the sampled monument order in the Amman sub-itinerary is preserved, and the links from Explore monument shells to Travel and Virtual Museum records are present.

The main quality issue is that the current Explore website often displays text and images by combining a lightweight Explore record with richer linked records from Travel, Virtual Museum, or museum tables. The import preserves many of those richer records, but it does not yet assemble them into an Inventory shape that directly serves the same public page. A future read-only API can compensate for some of this by following Item Links, but several importer gaps still need attention because the expected display text or association is missing from the imported records.

## Confirmed Good Results

### Theme and country membership are traceable

Legacy theme `cycleId=1` is imported as collection `mwnf3_explore:thematiccycle:1`, type `theme`, under the Explore-by-theme root. Coordinates match the legacy value `25,10` with zoom `3`.

The legacy country list for the theme is:

```text
dz, eg, es, it, jo, ma, pa, pt, sy, tn, tr
```

The Inventory theme translation stores the same country membership in `extra.country_ids`, using the same two-letter legacy country codes. The order differs in the JSON output, but the set is preserved.

### Jordan country text is preserved

The Jordan text displayed on the theme page comes from `mwnf3_explore.thematiccycle_country_texts` for `cycleId=1`, `country_id='jo'`. In Inventory it is stored under the English theme translation in `extra.country_texts`. This is model reshaping, not data loss, as long as the read-only API knows to expose the country-specific text from that `extra` field.

### The Amman itinerary structure is preserved

The public sub-itinerary `Amman, the Governor's Headquarters` has one location, `Amman`, and four monuments in this order:

| Order | Legacy monumentId | Public name | Inventory Item |
|---:|---:|---|---|
| 1 | 774 | Umayyad Palace | `mwnf3_explore:monument:774` |
| 2 | 773 | Jordan Archaeological Museum | `mwnf3_explore:monument:773` |
| 3 | 776 | Nymphaeum | `mwnf3_explore:monument:776` |
| 4 | 777 | Al-Husseini Mosque | `mwnf3_explore:monument:777` |

Inventory keeps this order in `collection_item.display_order` for collection `mwnf3_explore:itinerary:44`. The `extra` field also preserves the legacy descriptor such as `tr_mn_desc="IAM;jo;I;1;a;en;1#1"`.

### Location and monument identities match

The location `Amman` is present as `mwnf3_explore:location:10` with coordinates `31.954411, 35.936328` and zoom `15`, matching the legacy location row.

The four sampled Explore monument records are present with matching public names and coordinates:

| Inventory Item | Name | Coordinates | Zoom |
|---|---|---|---:|
| `mwnf3_explore:monument:773` | Jordan Archaeological Museum | `31.954397, 35.934304` | 16 |
| `mwnf3_explore:monument:774` | Umayyad Palace | `31.955521, 35.934277` | 16 |
| `mwnf3_explore:monument:776` | Nymphaeum | `31.950610, 35.936058` | 16 |
| `mwnf3_explore:monument:777` | Al-Husseini Mosque | `31.949790, 35.934614` | 17 |

### Linked Travel and Virtual Museum content exists

The current Explore website gets the main `Umayyad Palace` description and seven images from Travel source rows. Inventory contains the corresponding linked Travel Item:

```text
mwnf3_travels:monument:IAM:jo:1:I:1:a
```

That Travel Item has multilingual names and the seven expected image paths, preserved in `original_name`:

```text
trails/iam/jo/1/i/1/a/1.jpg
trails/iam/jo/1/i/1/a/2.jpg
trails/iam/jo/1/i/1/a/3.jpg
trails/iam/jo/1/i/1/a/4.jpg
trails/iam/jo/1/i/1/a/5.jpg
trails/iam/jo/1/i/1/a/6.jpg
trails/iam/jo/1/i/1/a/7.jpg
```

Inventory also keeps the three Virtual Museum monument links for Explore monument `774`:

| Explore source | Linked Inventory Item |
|---|---|
| `mwnf3_explore:monument:774` | `mwnf3:monuments:ISL:jo:Mon01:8` |
| `mwnf3_explore:monument:774` | `mwnf3:monuments:ISL:jo:Mon01:33` |
| `mwnf3_explore:monument:774` | `mwnf3:monuments:ISL:jo:Mon01:34` |

The linked Virtual Museum Items contain the expected Islamic Art English names, dates, descriptions, authors, and primary images.

### Museum partner import works for the sampled institution

The legacy museum `ISL / Mus01 / jo` is imported as:

```text
mwnf3:museums:Mus01:jo
```

The Partner has the public name `Jordan Archaeological Museum`, country `jor`, coordinates close to the Explore monument coordinates, three partner images, and one logo. The three museum image paths are preserved as `original_name` values:

```text
museums/jo/1/1.jpg
museums/jo/1/2.jpg
museums/jo/1/3.jpg
```

## Confirmed Gaps

### 1. Main English theme description is not imported as the public description

On the public theme page, the English description starts:

> Did you know that Islam strongly influenced Christian art in medieval Spain and that this symbiosis is called Mudéjar?

In legacy data this comes from `mwnf3_explore.thematiccycletranslated` for `cycleId=1`, `langId='en'`.

In Inventory, the English translation for `mwnf3_explore:thematiccycle:1` has title `Explore the Islamic Heritage of the Mediterranean`, but its description is only `Explore the Islamic Heritage of the Mediterranean`. The richer public description is not present in the normal description field.

Business impact: a read-only API that uses the imported collection translation directly will show a title-like sentence instead of the public theme introduction.

Likely cause: the first thematic cycle importer creates the English translation from `thematiccycle.cycleDescription`; the later translation importer skips the English row because a translation already exists.

### 2. English itinerary 43 lost its public title and introduction

The public itinerary page is titled:

```text
The Umayyads. The Rise of Islamic Art
```

The legacy English row for `explore_itineraries_langs.itineraries_id=43` also contains the public introduction and local team.

In Inventory, collection `mwnf3_explore:itinerary:43` has an English translation titled:

```text
Explore the Islamic Heritage of the Mediterranean - JO
```

Its English description is empty. The French and Spanish imported translations exist, but the English public title, introduction, duration, and local team are not present in the English translation.

Business impact: the main itinerary page cannot be rebuilt from Inventory without special fallbacks or direct legacy knowledge.

Likely cause: the base itinerary importer creates an English placeholder translation first; the content importer then skips the real English translation because the translation already exists.

### 3. English sub-itinerary 44 lost its public title and Travel introduction pointer

The public sub-itinerary page is titled:

```text
Amman, the Governor's Headquarters
```

The live page displays a Travel-derived introduction beginning:

> The site of Amman, ancient Rabbath Bani 'Ammon, was occupied as early as the Pre-Pottery Neolithic B period...

In legacy data, the English `explore_itineraries_langs` row for `itineraries_id=44` has title `Amman, the Governor's Headquarters`, duration `One day`, `introd_type='ET-short#-'`, and `et_long_introduction='IAM;jo;I;en;1'`.

In Inventory, collection `mwnf3_explore:itinerary:44` has English title `Itinerary 44`, empty description, and `extra` only contains country and location IDs. The Spanish translation does preserve the title, duration, `introd_type`, and `et_title` pointer.

Business impact: the English public sub-itinerary page cannot be rebuilt correctly from Inventory as imported.

Likely cause: the same English-placeholder behavior as itinerary `43`.

### 4. Explore monument shell Items have no direct display images

On the public sub-itinerary page, the `Umayyad Palace` card shows seven Travel images. The `Jordan Archaeological Museum` card shows three museum images.

In Inventory, the Explore shell Items `mwnf3_explore:monument:774` and `mwnf3_explore:monument:773` have no direct `item_images` rows. Their images exist only on linked Travel Items or Partner records.

Business impact: a consumer that asks only for the Explore monument Item will see no images. A read-only API must either follow the item links and partner relationship, or the import must materialize display images onto the Explore shell Item.

This is not necessarily wrong as a normalized model decision, but it is a delivery gap for the future read-only API unless explicitly handled.

### 5. Explore monument 773 is not associated back to the museum Partner

The legacy relation `mwnf3_explore.exploremonument_museums` links Explore monument `773` to museum `ISL / Mus01 / jo`.

Inventory has the museum Partner `mwnf3:museums:Mus01:jo`, but:

- `mwnf3_explore:monument:773` has no `partner_id`;
- its English translation has no `extra.additional_explore_partners`;
- no item-item link to the Partner was reported.

Business impact: the public page can show the museum because the current code has custom relation logic. Inventory does not yet expose that association from the Explore monument record.

Likely cause: the cross-reference importer searches for the Partner using `mwnf3:museums:{country}:{museum_id}`. The actual imported Partner key is `mwnf3:museums:{museum_id}:{country}`.

### 6. Direct display captions are not on the displayed Explore shell Item

For `Umayyad Palace`, legacy Travel images include English captions such as:

```text
Umayyad Palace, monumental gate, Amman.
```

Inventory contains the Travel image rows, but the sampled query showed `alt_text` as `NULL` on those image rows. The original path and display order are preserved.

Business impact: image selection is recoverable, but public captions need additional handling. They may be stored elsewhere or omitted from the direct image rows; this needs a targeted image-caption check before the read-only API is designed.

## Business Interpretation

The import has successfully created a traceable Inventory graph for the sampled Islamic Art records. The customer can validate that the sampled public records did not disappear: the theme, Jordan itinerary, Amman location, four monuments, related Travel records, related Virtual Museum records, and museum Partner are all findable.

The import is not yet equivalent to the current public display. The current Explore website resolves public pages by applying presentation logic across several legacy tables. Inventory currently stores those sources as separate normalized records. That is acceptable only if the upcoming read-only API deliberately assembles the same page-level view.

The strongest importer-side gaps are the skipped English translations for themes and itineraries, and the broken museum Partner association for Explore monument `773`. These are not just API-shaping questions; they are missing or misplaced imported data in the sampled records.

## Recommended Validation Focus

Before broader customer validation, fix or explicitly document these points:

1. Ensure English translations created by base importers are updated by the later content importers instead of blocking the richer English source rows.
2. Correct the Explore monument to museum Partner lookup so the imported key order matches `mwnf3:museums:Mus01:jo`.
3. Decide whether Explore shell Items should expose display images directly, or whether the read-only API will always resolve display images through linked Travel, Virtual Museum, and Partner records.
4. Verify where legacy image captions land for Travel and museum images, because public pages need those captions.
5. Treat fields stored in `extra` as first-class read-only API inputs where they represent public content, especially country-specific theme text and itinerary ordering descriptors.

## Conclusion

For this Islamic Art sample, the import quality is good for identity, traceability, hierarchy, order, coordinates, and preservation of linked source records. It is incomplete for public-page readiness. The most visible issue is missing English public text on theme and itinerary records. The most concrete relationship issue is the missing museum Partner association for `Jordan Archaeological Museum` as an Explore monument.

The imported database is therefore suitable for continued validation of normalized source coverage, but not yet sufficient as a direct source for a light public frontend without either importer fixes or a read-only API layer that deliberately reconstructs the current Explore display model.
