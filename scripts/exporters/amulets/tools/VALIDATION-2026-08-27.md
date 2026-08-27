# Amulets data-package validation — 2026-08-27

Validation of the first `amulets` export against the legacy ground truth. The
method differs from the baroqueart/sharinghistory validations, which compared
against the offline dumps in `.legacy-database/`: the DXA API is still live and
public, so the comparison here is against the **running legacy instance**
(<https://amulets.museumwnf.org/api/v2/…>), which is a stricter target — it
reflects every server-side filter and quirk the dumps do not show.

Export run against the **staging** inventory database (post-[#1523](https://github.com/metanull/inventory-app/issues/1523)
import), `BASE_URL=https://inventory.metanull.eu`.

## Counts: legacy API vs exported

| Metric | Legacy endpoint | Legacy | Exported | Verdict |
|---|---|---|---|---|
| Items (membership union) | `/items` `page.total` | 45 | 45 | ✅ exact |
| Partners | `/partners` | 26 | 26 | ✅ exact |
| Countries | `/items/countries` | 19 | 19 | ✅ exact |
| Year range | `/items/years` | 700 – 2000 | 700 – 2000 | ✅ exact |
| Facet tags — artist | `/items/tags` | 1 | 1 | ✅ exact |
| Facet tags — dynasty | `/items/tags` | 13 | 13 | ✅ exact |
| Facet tags — material | `/items/tags` | 53 | 53 | ✅ exact |
| Facet tags — subject | `/items/tags` | 4 | 4 | ✅ exact |
| Facet tags — type | `/items/tags` | 44 | 44 | ✅ exact |
| Facet tags — total | `/items/tags` | 115 | 115 | ✅ exact |

The tag totals are the sharpest check in the table. They only come out right if
both scoping rules hold at once: items limited to the collection's membership
union, and tags limited to the THG families — the same `item_tag` pivot also
carries 441 `keyword` and 188 non-THG `material` links for these items, and
including either would show up here immediately.

## Gallery anchor

`/thg/galleries/self` vs `gallery.json`:

| Legacy field | Legacy value | Exported |
|---|---|---|
| `galleryKey` | `amulets_and_talismans` | `slug` ✅ |
| `galleryName` | Amulets and Talismans | `names.en` ✅ (plus ar/es/fr) |
| `url` | `https://amulets.museumwnf.org` | `legacy_host` ✅ |
| `featured` | `true` | `featured: true` ✅ (stored flag is `'H'` — the legacy inversion) |
| `hidden` | `false` | `hidden: false` ✅ |
| `hasTimeline` | `false` | `has_timeline: false` ✅ |
| `hasCountryBasedTimeline` | `false` | `has_country_timeline: false` ✅ |
| `liveDate` | `2022-12-01` | `live_date` ✅ |
| `banner-object` link | `/mwnf3/objects/EPM/at/Mus22/51` | `banner_item_id` resolves to that item ✅ |
| `homepage-object` / `homepage-image` | `null` | `null` ✅ |
| `logos` | `[]` | — (legacy `thg_gallery_logos` holds 1 row across all sites) ✅ |

## Item sheet, field by field

`/mwnf3/objects/EPM/at/Mus22/51/en` (the gallery's banner object) against
`items.json` + `translations/items.en.json`:

`name`, `objectDate`, `startDate`/`endDate` (1600–1800), `typeOfObject`,
`holdingMuseum`, `location`, `dimensions`, `provenance`,
`methodOfDatation`, `methodOfProvenance`, `methodOfObtention`,
`bibliography`, `inventoryId` (`AE_SEM_982` → `owner_reference`),
`workingNumber` (`AT2 51` → `mwnf_reference`), `shortDescription`,
`i18n` (`ar,de,en` → `languages`), 2 pictures with copyright, 8 glossary
terms, 9 keywords, 1 material, 4 facet tags
(`dynasty_1b2c`, `type_1b81`, `type_1ce5`, `material_1ce4`) — **all identical**.

Two deliberate differences:

- **Markdown, not HTML.** Legacy returns `<i>kufic</i>`; the package carries
  `*kufic*`. The importer converts legacy HTML to Markdown repo-wide and all
  three existing packages do the same.
- **`galleries` excludes self.** Legacy lists 3 (Amulets, Calligraphy,
  Precious Stones); `gallery_references` lists the 2 others, since a viewer
  never links a gallery to itself.

Across the 20 items reachable from `/items?page=1`, English `name`,
`startDate` and `endDate` matched on all 20. (Legacy's `page`/`pagesize`
parameters do not paginate this endpoint reliably, so pages 2–3 could not be
retrieved; the count and facet checks above cover the full 45.)

## Partner spot checks

`/mwnf3/partners/at/Mus22/en` vs `partners.json`:
`showOnPortal: 1` → `featured: true`, `mapZoom: 17` → `map_zoom: 17`,
`mapCoordinates: 48.203897,16.361539` → identical lat/long, 3 images, 1 logo.
✅

Per-partner item counts against `/items?ic=…&ip=…`:

| Partner | Legacy total | `item_count` |
|---|---|---|
| at / Mus22 | 4 | 4 ✅ |
| dz / Mus01 | 2 | 2 ✅ |
| uk / Mus04 | 4 | 4 ✅ |

10 of the 26 partners carry `portal_display = 'y'` and are exported as
`featured`. Legacy's `/partners/featured` returns a **random subset** sized by
`config('dxa.API_PAGESIZE')` — on the sampled call, one entry, and an empty one
at that. The package ships the flag and leaves the random pick to the viewer,
which is the only way a static site can reproduce a per-request random subset.

## Timeline

The amulets deployment reports `hasCountryBasedTimeline: false` yet still
answers `/events/countries` with the full worldwide list and `/events/years`
with 400–1968 — the gallery timeline is served from `mwnf3.hcr`, keyed by
country and independent of any project. The package therefore carries all 18
per-country timelines and their 1,075 events, and passes the two flags through
on `gallery.json` for the viewer to interpret.

## Findings

1. **EPM author attribution is missing in English.** Legacy's English sheet
   shows `preparedBy: "Mohamed ABBAS, CAIRO"` and
   `copyEditedBy: "Liz COOPER"`; in the inventory database those names sit on
   the **Arabic** `item_translations` row only, so the exported English
   translation has no `author`/`copy_editor`. Importer-side, affects EPM items
   generally rather than this gallery. Not fixed here.
2. **Copyedit notices are not imported.** `notice`, `notice_b` and `notice_c`
   have no counterpart in the inventory schema. Empty on every amulets item, so
   nothing is visibly lost on this site.
3. **`has_timeline` bit fields can still be raw Buffers.** Gallery 47
   (Colours) stores `{"type":"Buffer","data":[1]}` in
   `collection_translations.extra` where gallery 9 stores `false` — some rows
   predate the importer's `bitToBoolean` normalization. `bitToBoolean` in the
   exporter accepts both forms; the exhibition exporter ([#1546](https://github.com/metanull/inventory-app/issues/1546))
   will need the same, or an importer fix.
4. **Gallery chrome images are not in inventory storage.**
   `thematic_gallery/thg_galleries/4/{1,banner}.jpg` were never imported, so
   the package carries legacy paths and the viewer must supply the media host
   ([#1543](https://github.com/metanull/inventory-app/issues/1543) decides how).

## Reproducing

```bash
docker compose --profile jobs run --rm exporter amulets --force
```

The legacy endpoints used above are public and need no credentials.
