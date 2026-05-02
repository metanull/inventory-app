# Sharing History Import Validation Report

Date: 2026-05-02

## Scope

This report validates imported Inventory data against the public website `https://sharinghistory.museumwnf.org`, the supporting legacy codebase at `E:\mwnf-server\apps\sharinghistory.museumwnf.org`, the production legacy database, and the imported Inventory database.

The website is the primary source. The sample covers project pages, exhibitions, objects, partners, timelines, historical profiles, media, and documents.

Relevant internal references:

- [Legacy Import](../../../docs/understanding/legacy-import.md)
- [Validation Guide](../../../docs/understanding/validation-guide.md)
- [Inventory Principles](../../../docs/understanding/inventory-principles.md)

## Summary

The Sharing History import is strong for the central public content: project identity, exhibition hierarchy, object records, partners, timelines, item-linked timeline pivots, media, and documents. Sampled records have stable `mwnf3_sharing_history:*` keys and recognizable fields.

The main gaps are page-level presentation and profile richness: project/about copy is not carried into the sampled project collection, partner gallery photos are missing, some objects lose secondary images, and national-context child collections need further content review.

## Samples Checked

| Website area | Sampled website page | Website facts checked | Inventory result |
|---|---|---|---|
| Project homepage | `index.php` | `Sharing History Arab World - Europe | 1815 - 1918`; ten virtual exhibitions | Project collection `mwnf3_sharing_history:sh_projects:awe` exists. Title is imported as `Sharing History - Arab-Ottoman-European relations in the 19th century.` Sampled project collection description is null. |
| Exhibition list and themes | `exhibitions/AWE/index.php`; `exh_items.php?eId=3&lan=en` | Exhibition `Cities and Urban Spaces`; subtopics include `The image of the city`, `Urban Planning and the Instruments of Planning`, `Architecture and Construction` | Collection `mwnf3_sharing_history:sh_exhibitions:3` exists with title and intro. Child theme collections `...:sh_exhibition_themes:6`, `7`, and `8` exist with matching titles. |
| Object detail | `database_item.php?id=object;AWE;at;26;en&pageT=N` | `Mummy board and inner coffin for Nes-pauti-taui`; KHM Vienna; date `21st Dyn, c. 1000 BC`; image path under `sharing_history/sh_objects/awe/at/26/1.jpg` | Item `mwnf3_sharing_history:sh_objects:awe:at:26` exists with matching name, holder, date, and image. |
| Object from timeline | `database_item.php?id=object;awe;tn;46;en` | `Portrait of Hammouda Pacha Bey`; Tunisia; 19th century; linked from timeline | Item `mwnf3_sharing_history:sh_objects:awe:tn:46` exists. Timeline link from sampled HCR record exists. |
| Partners | `pm_partner.php?id=AT_01;at&shpro=AWE&`; `pm_partner.php?id=DZ_01;dz&shpro=AWE&` | Kunsthistorisches Museum, Vienna; Ministry of Culture, Algiers; rich descriptions, address/contact, websites, logos, partner photos | Partners `mwnf3_sharing_history:sh_partners:at_01` and `...:dz_01` exist with text, contact fields, country/type, and logos. Sampled partner profile photos were not imported as `partner_images`. |
| Timeline | `hcr_result.php?country=eg&start_date=1815&end_date=1918` | Rows include First Saudi State, Hammouda Pacha Bey, Campo Formio; object links to `awe;tn;46` and `awe;at;2` | Events such as `mwnf3_sharing_history:sh_hcr:780`, `...:71`, and `...:420` exist. Item pivots such as `...:sh_hcr_images:55` and `...:258` exist. |
| Historical profiles | `hb_result.php?country=eg&page=1` | Egypt page shows object-backed profiles including Colonel Ahmad Urabi Pasha, Opera House 1869, Alexandria Sporting Club, Women in the Revolution of 1919 | Sampled profile objects exist as imported items, including `eg:79`, `eg:96`, `eg:108`, and `eg:17`. |
| Media and documents | `database_item.php?id=object;AWE;gr;50;en`; `database_item.php?id=object;awe;es;28;en` | Video links for `Mr. Byzantoine et ses amis`; PDF link for Spanish sample object; two visible images on `es;28` page | Video/audio rows for `gr:50` and `gr:51` are imported as item media. The PDF document for `es:28` is imported. The sampled `es:28` item has one imported image while the website shows two. |

## Legacy Source Mapping

| Website content | Legacy source used by the site |
|---|---|
| Project | `mwnf3_sharing_history.sh_projects` and `sh_project_names`. |
| Exhibitions and themes | `sh_exhibitions`, `sh_exhibitionnames`, `sh_exhibition_themes`, and `sh_exhibition_themenames`. |
| Objects | `sh_objects` and `sh_objects_texts`. |
| Partners | `sh_partners`, `sh_partner_names`, and partner link tables. |
| Timeline | `sh_hcr`, `sh_hcr_events`, and `sh_hcr_images`. |
| Media and documents | `sh_objects_video_audio` and `sh_objects_document`. |

## Import Quality

Sharing History is one of the stronger sampled imports. Core objects, exhibitions, partners, timeline events, item-timeline pivots, videos, and documents are present and traceable.

The sampled import count for AWE objects was confirmed at 2,420 rows in both checked inventory environments. This supports the conclusion that the object family imported broadly, not only for isolated examples.

Some presentation content remains outside the sampled Inventory rows, especially richer project landing text and partner photo galleries.

## Gaps To Address

1. **Project/about narrative gap**

   The website exposes project introduction and about text from project-name tables. The sampled Inventory project collection exists, but its description is null.

2. **Partner profile image gap**

   Sampled partner pages show profile/gallery photos in addition to logos. Inventory has logos, but sampled partner image counts are zero.

3. **Multi-image object gap**

   The sampled Spanish object page shows two images, while Inventory has one imported item image.

4. **National-context child collections need review**

   Sampled national-context child collections under exhibition 3 had null English title/description. If these overlays are needed by the future read-only API or front end, they need focused validation.

5. **Encoding should be checked through application responses**

   Terminal SQL output can distort curly quotes, dashes, and accents. The website renders these correctly. Encoding should be verified through Laravel/UI/API responses before treating it as an import defect.

## Business Assessment

The Sharing History import is suitable for validating the main public collection, exhibition, object, timeline, media, and document content. The next validation round should focus on richer page-level presentation: project copy, partner galleries, multi-image objects, and national-context collections.
