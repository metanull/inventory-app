# The Use Of Colours In Art Import Validation Report

Date: 2026-05-02

## Scope

This report validates imported Inventory data against the public exhibition website `https://exhibitions.museumwnf.org/the_use_of_colours_in_art/en`, the supporting legacy codebases at `E:\mwnf-server\dynapps\api\the_use_of_colours_in_art\en` and `E:\mwnf-server\dynapps\cli\the_use_of_colours_in_art\en`, the production legacy database, and the imported Inventory database.

The website is the primary source. The sample covers the exhibition shell, theme hierarchy, theme artwork, object detail, partners, related content, media, and timeline.

Relevant internal references:

- [Legacy Import](../../../docs/understanding/legacy-import.md)
- [Validation Guide](../../../docs/understanding/validation-guide.md)
- [Inventory Principles](../../../docs/understanding/inventory-principles.md)

## Summary

The exhibition shell and theme hierarchy are imported well. The sampled gallery collection, title, subtitle, theme title, theme parent, quote, item membership, and contextual artwork text are present.

The major gaps are the rest of the exhibition experience: the live timeline is not imported, exhibition-level media/contributor/related-content records are missing in the sampled collection, and sampled EXHCOLOUR base object metadata is incomplete in item translations.

## Samples Checked

| Website area | Sampled website page | Website facts checked | Inventory result |
|---|---|---|---|
| Landing | `/the_use_of_colours_in_art/en` | Gallery 47; key `the_use_of_colours_in_art`; title `The Use of Colours in Art`; subtitle `About Techniques, Symbolism and Meanings` | Collection `mwnf3_thematic_gallery:thg_gallery:47` exists. Translation `mwnf3_thematic_gallery:exhibition_i18n:47:en` contains matching title and description. |
| Theme navigation | `/the_use_of_colours_in_art/en/themes` | Themes include `Primary Colours and Emotions` and `The Three Dimensions of Colours` | Imported theme collections exist under gallery 47. |
| Theme page | `/the_use_of_colours_in_art/en/theme/8` | Theme 8 is `Beyond Natural Colours, Technical Achievement in Colours`; parent theme 2; quote matches live API | Collection `mwnf3_thematic_gallery:theme:47:8` exists with parent `mwnf3_thematic_gallery:theme:47:2`. Translation `mwnf3_thematic_gallery:theme_i18n:47:8:en` matches title and quote. |
| Theme artwork | `/the_use_of_colours_in_art/en/theme-gallery/8/1` | Artwork `Brush Washer`; date `Late 1000s-1127`; holder Cleveland Museum of Art; contextual Ru-ware description; image copyright Cleveland Museum of Art; link to `/mwnf3/objects/EXHCOLOUR/us/Mus51/15/en` | Item `mwnf3:objects:EXHCOLOUR:us:Mus51:15` exists. Theme membership and contextual translation `mwnf3_thematic_gallery:theme_item_i18n:47:8:1:en` exist. Image path exists. Base item translation is incomplete. |
| Object detail | `/database-item/mwnf3/objects/EXHCOLOUR/us/Mus51/15/en` | Public item page shows `Brush Washer`, date, holder, location, and dimensions | Inventory item has `internal_name = Brush Washer`, but sampled English `item_translations.name` is blank and dates, holder, location, and dimensions are null. Description contains contextual THG text. |
| Partners | `/the_use_of_colours_in_art/en/partners` | Live partners endpoint returns 75 rows; self payload includes exhibition partner/contributor data | Sampled partner `mwnf3:museums:Mus51:us` exists, but `visible = 0`. Collection 47 has no sampled `collection_partner` links. |
| Related content and logos | Landing/self payload | Footer logo, exhibition partner, related PDFs/articles, and authors such as Claizza Regalado and Jana Zarkovic | Sampled collection 47 has 0 collection images, 0 collection media, and 0 collection partner links. |
| Timeline | `/the_use_of_colours_in_art/en/timeline` | Live timeline has 45 events, range -50000 to 2029; sampled events 39 and 42 are gallery 47 colour-history entries | No timeline linked to `mwnf3_thematic_gallery:thg_gallery:47`; sampled `mwnf3_thematic_gallery:hcr:%` timeline event count is 0. |

## Legacy Source Mapping

| Website content | Legacy source used by the site |
|---|---|
| Exhibition shell | `mwnf3_thematic_gallery.thg_gallery`, `thg_gallery_lang`, and exhibition internationalization tables. |
| Themes | `mwnf3_thematic_gallery.theme` and `theme_i18n`. |
| Theme artwork | `theme_item` and `theme_item_i18n`, linked to base `mwnf3.objects`. |
| Object detail | `mwnf3.objects` and object picture tables. |
| Partners and contributors | Mixed MWNF partner sources and thematic-gallery exhibition partner structures. |
| Related content, logos, media | Thematic-gallery logo, partner, related-content, PDF/article, and media tables exposed by the legacy API. |
| Timeline | Thematic-gallery HCR tables for gallery 47. |

## Import Quality

The imported collection and theme structure is good. This is enough to validate the exhibition outline and some contextual item descriptions.

The sampled base object import is not good enough for public object/detail pages. The item exists, but the public fields shown by the website are missing from the sampled English translation.

Timeline and exhibition-level related content are major missing families for this exhibition.

## Gaps To Address

1. **The exhibition timeline is not imported**

   The live timeline has 45 events. No sampled Inventory timeline or event rows were found for gallery 47.

2. **Base object metadata is incomplete**

   The `Brush Washer` item exists, but sampled translation fields for name, dates, holder, location, and dimensions are blank or null.

3. **Exhibition partners and contributors are not linked to the collection**

   The website exposes partners and contributors. Sampled `collection_partner` links for collection 47 are absent.

4. **Related content, PDFs, authors, logos, and collection media are missing**

   Sampled collection media/image counts are zero, despite visible landing/footer/related-content features.

5. **Image captions and alt text need review**

   The image path is imported, but sampled caption/credit coverage is weak compared with the website payload.

## Business Assessment

The import captures the exhibition skeleton and theme editorial structure. It does not yet capture enough data to reproduce the public exhibition experience, especially timeline, related content, contributors, collection media, and complete artwork detail fields.
