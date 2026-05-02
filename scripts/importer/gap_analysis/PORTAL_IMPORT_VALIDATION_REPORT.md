# Portal Import Validation Report

Date: 2026-05-02

## Scope

This report validates imported Inventory data against the public portal `https://museumwnf.org`, the supporting legacy codebases at `E:\mwnf-server\apps\portal.museumwnf.org` and `E:\mwnf-server\apps\museumwnf.org`, the production legacy database, and the imported Inventory database.

The portal is different from the other sampled websites: it mixes cultural content with portal-owned CMS, navigation, institutional pages, videos, and curated homepage panels. The website is the primary source.

Relevant internal references:

- [Legacy Import](../../../docs/understanding/legacy-import.md)
- [Validation Guide](../../../docs/understanding/validation-guide.md)
- [Inventory Principles](../../../docs/understanding/inventory-principles.md)

## Summary

The imported Inventory is strong for cultural content surfaced through the portal: sampled virtual museum items, Baroque project count, Islamic Art plus EPM aggregate count, partner detail, Sharing History items, and thematic-gallery exhibition collections all match recognizable source records.

The portal-owned CMS is not imported. Homepage panels, menus, newsletter/news items, videos, learning resources, legal/language/credits/about pages, and curation metadata are visible public content on `museumwnf.org`, but no matching Inventory records were found in the sampled checks.

This is not automatically a defect if Inventory is intended to store cultural content rather than every legacy portal application concern. It is a business decision point for any future portal replacement.

## Samples Checked

| Portal area | Sampled portal source | Website facts checked | Inventory result |
|---|---|---|---|
| Virtual museum card | `/api/virtual-museums` | `Discover Islamic Art`; 2,443 artefacts; 29 countries; 18 exhibitions; sample image item `Kırkçeşme water-supply system`, `ISL;tr;Mon01;34;en`, date AH 971 / AD 1564 | Contexts `mwnf3:projects:ISL` and `mwnf3:projects:EPM` exist. Combined ISL+EPM distinct items = 2,443. Item `mwnf3:monuments:ISL:tr:Mon01:34` exists with English name, date, location, and image. |
| Baroque portal card | `/api/virtual-museums` | `Discover Baroque Art`; 597 artefacts; sample item `View of the Monastery and Riverbank in Belem`, `BAR;pt;Mus11_A;12;en`; holder National Museum of Ancient Art | Project collection `mwnf3:projects:BAR` has 597 item links. Item `mwnf3:objects:BAR:pt:Mus11_A:12` exists with holder, date 1657, location, partner, and image. |
| MWNF gallery card | `/api/mwnf-gallery` | Gallery `Scientific objects`, id 34; 37 galleries; 43 countries; 5,762 artefacts; sample item `Astrolabe`, `AWE;uk;66`, holder The British Museum | Context `mwnf3_thematic_gallery:thg_gallery:34` exists. Item `mwnf3_sharing_history:sh_objects:awe:uk:66` exists with name, holder, date, and image. Collection 34 has 60 item links but no sampled English collection translation. THG aggregate count differs from the portal card count. |
| Homepage panels | `/api/panel` | Cards include `Database`, `Partners`, `My Collection`; URLs point to legacy search, partner list, and collection functions | No sampled Inventory records with portal-style `backward_compatibility` were found. These are portal CMS/navigation records. |
| Partner list/detail | `partner_list.php`; `partner.php?id=Mus11_A;pt&theme=BAR&tye=museum` | Country-grouped partners; sampled detail shows `Further Associated Museums`, Portugal, and a long list of Portuguese museums | Partner `mwnf3:museums:Mus11_A:pt` exists with type museum, country Portugal, English name, description beginning with the visible Lisbon museum list, and one image. |
| New exhibitions | `/api/new-exhibitions` | Featured entries include `Water in Islam` (`56;en`), `The Table Is Set` (`52;en`), and video exhibition `Dining with the Sultan` | Collections `mwnf3_thematic_gallery:thg_gallery:56` and `:52` exist with English titles/descriptions and large item links. Sampled collection image counts are 0. The video exhibition has no sampled Inventory counterpart. |
| Legacy virtual exhibition card | `/api/exhibitions` | `Rediscovering the Past`, project `AWE`, exhibition id 1, 29 item links | Collection `mwnf3_sharing_history:sh_exhibitions:1` exists with title, description, and 29 item links. Sampled collection image count is 0. |
| News, learning, institutional pages | `/api/newsletter-info`, `/api/learn`, `/api/atrium-about`, `/api/language-policy`, `/api/legal_notice`, `/api/credits` | Newsletter items for 2026/2025; learning page; About MWNF text; legal, language, and credit content | No sampled Inventory counterpart was found for these portal-specific CMS and institutional records. |

## Legacy Source Mapping

| Portal content | Legacy source used by the site |
|---|---|
| Virtual museum cards | `mwnf3.sites`, `sites_translation`, portal image tables, and linked `mwnf3` objects/monuments. |
| Thematic-gallery cards | `mwnf3_thematic_gallery.thg_gallery`, gallery URL/translation tables, and linked gallery items. |
| Homepage panels | `mwnf3_portal.portal_panels` and `portal_panels_translation`. |
| Partner pages | `mwnf3.partner_museums`, `associated_museums`, `mwnf3_sharing_history.sh_partners`, `mwnf3.all_partners`, and partner loaders. |
| New exhibitions | `mwnf3_portal.portal_exhibitions`, `mwnf3_thematic_gallery.exhibition_i18n`, and portal video-exhibition tables. |
| News and institutional pages | `mwnf3.newsletter`, `mwnf3_portal.portal_learning`, `portal_youtube_video`, `mwnf3.translation`, `mwnf3.act_activities`, board tables, and related portal resources. |

## Import Quality

The imported Inventory does well when the portal points to cultural content that is already part of imported domains: virtual museum records, Baroque records, Sharing History records, partners, and thematic-gallery collections.

The imported Inventory does not represent much of the portal's own content-management layer. This matters because the public portal is not only a gateway to cultural records; it also contains current institutional and editorial content.

## Gaps To Address

1. **Portal homepage/navigation CMS is not imported**

   Homepage panels, menus, footer/top links, learning resources, videos, and portal exhibition curation have no sampled Inventory equivalent.

2. **Institutional content is not imported**

   About MWNF, newsletters/news, chronology, legal notice, credits, language policy, support, and volunteer pages remain legacy portal/API content.

3. **Some thematic-gallery collection translations are incomplete**

   Sampled gallery `mwnf3_thematic_gallery:thg_gallery:34` exists and has item links, but no English collection title/description was found.

4. **Collection media is missing for sampled portal collections**

   `Rediscovering the Past`, `Water in Islam`, `The Table Is Set`, `Scientific objects`, and project collections reported zero sampled collection images, while the portal relies on curated images.

5. **Aggregate counts need explicit reproduction rules**

   DIA matches when ISL and EPM are combined. DBA matches directly. THG and Sharing History portal aggregate semantics do not always equal simple Inventory counts.

6. **Video exhibitions are not Inventory records in the sample**

   Portal video exhibitions and external feature cards are business objects of the portal, not imported cultural collections/items.

## Business Assessment

Inventory is a strong source for cultural records surfaced by the portal. It is not currently a replacement for the portal CMS. A future portal rebuild needs an explicit decision: either import/model portal CMS content, keep it in a separate CMS, or reduce the replacement scope to cultural-data surfaces only.
