# Islamic Art Import Validation Report

Date: 2026-05-02

## Scope

This report validates a small set of Islamic Art records displayed on the live exhibition website against:

- the legacy production databases used by the website;
- the imported Inventory database on OVH;
- the importer mapping rules documented in [Legacy Import](../../../docs/understanding/legacy-import.md) and [Core Model](../../../docs/understanding/core-model.md).

The primary source is the live website and its API. Existing reports in this folder were not used for this analysis.

The live exhibition checked is:

- `https://exhibitions.museumwnf.org/the_use_of_colours_in_art/en/`
- gallery id `47`
- title: `The Use of Colours in Art`
- subtitle: `About Techniques, Symbolism and Meanings`

Although the exhibition includes material from several MWNF projects, the detailed checks below focus on Islamic Art data and Islamic-content timeline entries.

## Method

The validation uses a targeted sample across several page types instead of many records from one page:

| Website area | Sample checked | Why it matters |
|---|---|---|
| Home / exhibition identity | Gallery `47` | Confirms the exhibition-level record imports as an Inventory collection/context. |
| Themes | Theme `5`, `Primary Colours in Architectural Monuments` | Checks a THG subtheme, parent hierarchy, item membership, and contextual text. |
| Themes | Theme `10`, `The Divine and Colour` | Checks a second Islamic-content theme path and another project source. |
| Item detail | `ISL/de/Mus01/19`, `Prayer niche (mihrab)` | Checks a Discover Islamic Art object, object text, date, dynasty, materials, image. |
| Item detail | `EPM/uk/Mus21/37`, Qur'an manuscript | Checks an Explore Islamic Art Collections object with sparse base description but rich contextual text. |
| Partner page | `ISL/eg/Mus01`, Museum of Islamic Art | Checks a partner/museum, description, logo, images, country and contact surface. |
| Timeline | HCR events `39` and `42` | Checks Islamic-content timeline entries displayed by the exhibition. |

All database checks were read-only.

## Overall Assessment

The import quality is good for the sampled exhibition structure, item membership, contextual descriptions, base object facts, partner facts, and image file records. The sampled Islamic Art objects can be traced from the website to the legacy source tables and then to Inventory records by their `backward_compatibility` values.

One significant functional gap is confirmed: the exhibition-specific THG timeline is not imported or linked to gallery `47` in Inventory. The live website displays 45 timeline events for the exhibition, including Islamic-content events `39` and `42`, but Inventory has no gallery-47 timeline rows for those events.

A smaller data-quality point is also visible: object and partner image rows exist in Inventory, but sampled image `alt_text` values are empty. One legacy contextual image caption for the mihrab sample is a placeholder-like value, `Test Caption`; this is source data, not an importer-created value.

## Findings

### 1. Exhibition Identity Imports Correctly

Website/API sample:

| Field | Website value |
|---|---|
| Gallery id | `47` |
| Gallery key | `the_use_of_colours_in_art` |
| Title | `The Use of Colours in Art` |
| Subtitle | `About Techniques, Symbolism and Meanings` |
| Has timeline | `true` |

Legacy source:

| Source table | Key | Relevant values |
|---|---|---|
| `mwnf3_thematic_gallery.thg_gallery` | `gallery_id=47` | `project_id=EXH`, `mwnf3_project_id=EXHCOLOUR`, `status=A`, `link=the_use_of_colours_in_art`, `has_timeline=1`, `has_country_timeline=0` |
| `mwnf3_thematic_gallery.thg_gallery_lang` | `(47, en)` | `The Use of Colours in Art` |
| `mwnf3_thematic_gallery.exhibition_i18n` | `(47, en)` | title and subtitle match the website; headline begins with the same demo-exhibition text |

Inventory result:

| Inventory record | Value |
|---|---|
| Collection | `mwnf3_thematic_gallery:thg_gallery:47` |
| Collection type | `exhibition` |
| Internal name | `exhibition_the_use_of_colours_in_art` |
| Language | `eng` |
| Context | `mwnf3_thematic_gallery:thg_gallery:47` |
| English translation | `mwnf3_thematic_gallery:exhibition_i18n:47:en` |
| Title | `The Use of Colours in Art` |
| Description | Starts with `About Techniques, Symbolism and Meanings` and the website headline text converted from HTML to Inventory text format |

Assessment: imported correctly. The subtitle/headline are not stored as separate Inventory fields in the sample; they are combined into the collection description. That matches the documented model approach where legacy text can be reshaped into normalized Inventory fields.

### 2. Theme 5 and the Mihrab Object Import Correctly

Website/API sample:

| Field | Website value |
|---|---|
| Theme | `5`, `Primary Colours in Architectural Monuments` |
| Parent theme | `1`, `Primary Colours and Emotions` |
| Theme item | `1` |
| Object | `Prayer niche (<i>mihrab</i>)` |
| Database UID | `/mwnf3/objects/ISL/de/Mus01/19` |
| Contextual description | Begins `The colour blue (al-azraq) in Islamic tradition often signifies...` |
| Date | `About Hegira 669 / AD 1270.` |
| Dynasty | `Seljuqs of Rum (Anatolian Seljuq)` |
| Materials | `Glazed ceramic, faience mosaic.` |
| Holding institution | `Museum of Islamic Art at the Pergamon Museum` |
| Image | `objects/isl/de/1/19/1.jpg` |

Legacy source:

| Source table | Key | Relevant values |
|---|---|---|
| `mwnf3_thematic_gallery.theme` | `(47, 5)` | parent theme `1`, display order `2` |
| `mwnf3_thematic_gallery.theme_i18n` | `(47, 5, en)` | title `Primary Colours in Architectural Monuments` |
| `mwnf3_thematic_gallery.theme_item` | `(47, 5, 1)` | object reference `ISL/de/Mus01/19`, image `1` |
| `mwnf3_thematic_gallery.theme_item_i18n` | `(47, 5, 1, en)` | contextual description matches the website; `image_caption=Test Caption` |
| `mwnf3.objects` | `(ISL, de, Mus01, 19, en)` | object name, date, dynasty, dimensions, materials, location and base description match the website sample |

Inventory result:

| Inventory record | Value |
|---|---|
| Theme collection | `mwnf3_thematic_gallery:theme:47:5` |
| Parent collection | `mwnf3_thematic_gallery:theme:47:1` |
| Grandparent collection | `mwnf3_thematic_gallery:thg_gallery:47` |
| Theme translation | `mwnf3_thematic_gallery:theme_i18n:47:5:en` |
| Theme title | `Primary Colours in Architectural Monuments` |
| Item | `mwnf3:objects:ISL:de:Mus01:19` |
| Collection-item link | Present |
| Contextual item translation | `mwnf3_thematic_gallery:theme_item_i18n:47:5:1:en` |
| Contextual description | Present and starts with the same text as the website |
| Item image | Present, original name `objects/isl/de/1/19/1.jpg`, size `291189` |

Assessment: imported correctly for the checked business facts. The object, theme membership and exhibition-specific description are all present and traceable.

Data-quality note: the legacy contextual image caption is `Test Caption`. This appears to be legacy editorial content that needs validation, not an importer transformation problem.

### 3. Theme 10 and the Qur'an Object Import Correctly

Website/API sample:

| Field | Website value |
|---|---|
| Theme | `10`, `The Divine and Colour` |
| Parent theme | `3`, `Colours in Religious Art` |
| Theme item | `1` |
| Object | `Single-volume Qur'an with lacquer-painted binding signed by Lutf 'Ali Shirazi` |
| Database UID | `/mwnf3/objects/EPM/uk/Mus21/37` |
| Contextual description | Begins `Calligraphy is an essential aspect of Islamic art...` |
| Date | `Main text: c. 1844; Persian translation dated AH 25 Sha'ban 1272 / AD 2 May 1856; binding dated Hejira 1269 / AD 1852-3` |
| Dynasty | `Qajar` |
| Materials | Ink, gold and opaque watercolour on paper; lacquer-painted binding; scripts in `naskhi` and `nasta'liq` |
| Holding institution | `Khalili Family Trust - Nasser D. Khalili Collection of Islamic Art` |
| Image | `objects/epm/uk/21/37/1.jpg` |

Legacy source:

| Source table | Key | Relevant values |
|---|---|---|
| `mwnf3_thematic_gallery.theme` | `(47, 10)` | parent theme `3`, display order `1` |
| `mwnf3_thematic_gallery.theme_i18n` | `(47, 10, en)` | title `The Divine and Colour` |
| `mwnf3_thematic_gallery.theme_item` | `(47, 10, 1)` | object reference `EPM/uk/Mus21/37`, image `1` |
| `mwnf3_thematic_gallery.theme_item_i18n` | `(47, 10, 1, en)` | contextual description matches the website |
| `mwnf3.objects` | `(EPM, uk, Mus21, 37, en)` | object name, date, dynasty, dimensions and materials match the website sample; base description is blank in legacy |

Inventory result:

| Inventory record | Value |
|---|---|
| Theme collection | `mwnf3_thematic_gallery:theme:47:10` |
| Parent collection | `mwnf3_thematic_gallery:theme:47:3` |
| Grandparent collection | `mwnf3_thematic_gallery:thg_gallery:47` |
| Theme translation | `mwnf3_thematic_gallery:theme_i18n:47:10:en` |
| Theme title | `The Divine and Colour` |
| Item | `mwnf3:objects:EPM:uk:Mus21:37` |
| Collection-item link | Present |
| Contextual item translation | `mwnf3_thematic_gallery:theme_item_i18n:47:10:1:en` |
| Contextual description | Present and starts with the same text as the website |
| Item image | Present, original name `objects/epm/uk/21/37/1.jpg`, size `419976` |

Assessment: imported correctly for the checked business facts. The blank base object description is already blank in the legacy object row; the website-facing explanatory text comes from the THG contextual description, and that is present in Inventory.

### 4. Partner Sample Imports Correctly, With One Visibility Point to Review

Website/API sample:

| Field | Website value |
|---|---|
| Project | `Discover Islamic Art` |
| Partner | `Museum of Islamic Art` |
| Country | Egypt |
| City | Cairo |
| Logo | `museums/eg/01/logos/1.jpg` |
| Description | Begins `At the order of Khedive Ismail Pasha in 1880...` |

Legacy source:

| Source table | Key | Relevant values |
|---|---|---|
| `mwnf3.museums` | `(Mus01, eg)` | internal name `Islamic Art Museum`, city `Cairo`, URL `https://www.miaegypt.org/`, logo `museums/eg/01/logos/1.jpg`, coordinates `30.044644,31.252735`, project `ISL`, `portal_display=y` |
| `mwnf3.museumnames` | `(Mus01, eg, en)` | public name `Museum of Islamic Art`, description starts with the website text |
| `mwnf3.projectnames` | `(ISL, en)` | `Discover Islamic Art` |

Inventory result:

| Inventory record | Value |
|---|---|
| Partner | `mwnf3:museums:Mus01:eg` |
| Partner type | `museum` |
| Internal name | `Islamic Art Museum` |
| Country | `egy`, Egypt |
| Coordinates | `30.04464400`, `31.25273500`, zoom `17` |
| English translation | name `Museum of Islamic Art`, city `Cairo`, website `https://www.miaegypt.org/`, description starts with the same text as the website |
| Logo | Present, original name `museums/eg/01/logos/1.jpg`, size `38566` |
| Additional partner images | Three image rows present |

Assessment: partner name, description, country, location, website, logo and images import correctly for this sample.

Point to review: legacy `portal_display` is `y`, and the partner appears on the website. Inventory has this partner with `visible=0`. This report does not assume that `visible=0` is wrong, because visibility rules may have changed during model rationalization. It does need a business decision before a read-only public API depends on this flag.

### 5. Exhibition Timeline Is Not Imported for Gallery 47

Website/API sample:

| Field | Website value |
|---|---|
| Timeline count | `45` events |
| Event `39` | `7th century`, range `600` to `700`, description begins `The religious significance of colours was openly discussed in the Quran...` |
| Event `42` | `9th-10th century`, range `800` to `1000`, description begins `The so-called Blue Qur'an...` |

Legacy source:

| Source table | Key | Relevant values |
|---|---|---|
| `mwnf3_thematic_gallery.hcr` | `hcr_id=39`, `gallery_id=47` | `7th century`, `from_ad=600`, `to_ad=700` |
| `mwnf3_thematic_gallery.hcr_events` | `(39, en)` | description matches the website sample |
| `mwnf3_thematic_gallery.hcr` | `hcr_id=42`, `gallery_id=47` | `9th-10th century`, `from_ad=800`, `to_ad=1000` |
| `mwnf3_thematic_gallery.hcr_events` | `(42, en)` | description matches the website sample |

Inventory result:

| Check | Result |
|---|---|
| Timeline linked to gallery 47 collection | No rows |
| Timeline linked to gallery 47 child theme collections | No rows |
| Thematic-gallery HCR event patterns for `39` or `42` | No rows |
| Timeline labels matching `7th century` or `9th-10th century` for gallery 47 | No rows |

Inventory does contain unrelated timeline events with ids `39` and `42` from other sources, such as `mwnf3:hcr:39` and `mwnf3_sharing_history:sh_hcr:39`. Those are different legacy records and do not represent the Use of Colours exhibition timeline.

Assessment: this is a confirmed import gap. The exhibition timeline is visible and sourced in legacy data, but it is absent from the imported Inventory model for gallery `47`.

Business impact: any future read-only API or public client using Inventory cannot reproduce the exhibition Timeline page for this exhibition until THG timeline data is imported and linked to the exhibition collection.

### 6. Images Exist, but Captions and Alt Text Need Review

Sample image rows in Inventory:

| Object | Original image path | Inventory image present | Alt text |
|---|---|---:|---|
| `mwnf3:objects:ISL:de:Mus01:19` | `objects/isl/de/1/19/1.jpg` | Yes | `NULL` |
| `mwnf3:objects:EPM:uk:Mus21:37` | `objects/epm/uk/21/37/1.jpg` | Yes | `NULL` |
| `mwnf3:museums:Mus01:eg` logo | `museums/eg/01/logos/1.jpg` | Yes | `NULL` |

Assessment: the sampled binaries are represented as imported image records with original legacy paths preserved. Accessibility text is not populated for these samples. The current `item_images` table does not expose a caption field for direct validation of the THG contextual image caption.

## What Works Well

- The exhibition itself imports as an Inventory collection and context.
- Theme hierarchy imports correctly for the sampled subthemes.
- Theme titles and quotes import correctly for the sampled subthemes.
- The sampled Islamic Art object records are present with their key public facts.
- Exhibition-specific contextual descriptions are present as context-specific item translations.
- Theme-to-item membership exists through `collection_item`.
- Partner name, description, country, city, website, coordinates, logo and images are present for the sampled Egyptian partner.
- Sample object images exist in Inventory and retain traceable original legacy paths.
- Language code normalization is working in the checked records: website/legacy `en` appears as Inventory `eng`, while backward compatibility keeps the legacy source reference.

## Gaps and Review Items

| Priority | Finding | Evidence | Business effect |
|---|---|---|---|
| High | THG exhibition timeline is missing from Inventory. | Website has 45 gallery-47 timeline events; legacy has `mwnf3_thematic_gallery.hcr` / `hcr_events`; Inventory has no gallery-47 timeline rows. | The Inventory-backed read-only API cannot reproduce the exhibition Timeline page. |
| Medium | Partner visibility needs a business rule check. | Legacy `portal_display=y` and website displays the partner; Inventory partner has `visible=0`. | A future public API may hide a partner that the current site displays, depending on how `visible` is used. |
| Medium | Image alt text is empty in sampled records. | Two object images and the partner logo have `alt_text=NULL`. | Public clients may need extra editorial data for accessibility and image presentation. |
| Low | One sampled legacy contextual image caption looks like placeholder data. | `theme_item_i18n.image_caption=Test Caption` for gallery `47`, theme `5`, item `1`. | This may surface as poor editorial content if imported or displayed later. |

## Conclusion

For the sampled Islamic Art content, the importer preserves the main business value of the legacy data: exhibition structure, object identity, museum partner data, object facts, image records, and exhibition-specific contextual text are all traceable and present.

The main missing area is not the sampled objects or partners; it is the exhibition-specific THG timeline. That gap is important because the Timeline page is a visible part of the current website and contains Islamic-content entries that are not represented in Inventory as part of gallery `47`.

Before customer validation continues at scale, the next checks should focus on the same categories across more Islamic Art samples: theme coverage, partner visibility behavior, image metadata quality, and the missing THG timeline import path.
