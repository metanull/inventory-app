# DXA Gallery data package — specification

Date: 2026-08-27 · Applies to every DXA Gallery site (pilots: **amulets**
gallery 4/AMU, **carpets** gallery 9/DCA; generalizes to the ~38 gallery
instances). Requirements source: `dxa-legacy-analysis.md`.

One package per gallery website: `@metanull/<site>-data` with `<site>` the
kebab-cased legacy slug (`amulets`, `carpets` — decision Q4). Produced by a
discrete exporter per site
(`scripts/exporters/<site>`), reading the production inventory DB. Exporters
are read-only; media stays out of the package (absolute URLs to
`https://inventory.metanull.eu/pub/…`).

## Design principles

1. **The package is the pre-scoped API.** Legacy scoped everything server-side
   per instance; the package ships only the gallery's own universe — the
   member items of the gallery collection (native ∪ linked, G1) and
   everything derivable from them.
2. **Same skeleton as the existing three packages** (manifest / entity files /
   `translations/<entity>.<lang>.json`) so tooling and viewers stay familiar —
   plus a `gallery.json` that carries what legacy kept in per-instance `.env` +
   `thg_gallery`.
3. **UI strings are not data.** About/Credits/How-to-search/labels come from
   `scripts/site-i18n` at scaffold time, not from the package.

## File layout

```
manifest.json
gallery.json               ← site anchor (new, DXA-specific)
items.json                 ← the membership union, full sheets
tags.json                  ← THG facet tags with category (new)
partners.json
countries.json
languages.json
glossary.json
dynasties.json             ← only if member items reference dynasties
timelines.json             ← the 18 global country timelines
timeline_events.json
translations/
  items.<lang>.json
  partners.<lang>.json
  countries.<lang>.json
  glossary.<lang>.json
  timeline_events.<lang>.json
```

## manifest.json

As in existing packages: `generatedAt`, `version`, `languages` (union of
languages present in translation files), plus `site` = the package's site key
and `kind: "gallery"`. The website derives its offered languages from the
translation files actually present (baroqueart decision), not the manifest.

## gallery.json (new)

The per-site identity legacy spread across `.env`, `thg_gallery`,
`thg_gallery_lang`, `thg_gallery_url` — now read from the gallery collection +
`extra.thg_gallery`:

```jsonc
{
  "id": "<collection uuid>",
  "backward_compatibility": "mwnf3_thematic_gallery:thg_gallery:4",
  "kind": "gallery",
  "slug": "amulets_and_talismans",        // thg_gallery.link — NOT the folder name
  "legacy_host": "https://amulets.museumwnf.org",
  "mwnf3_project_id": "AMU",
  "languages": ["ar", "en", "es", "fr"],  // thg_gallery_lang
  "names": { "en": "Amulets and Talismans", "ar": "…", "es": "…", "fr": "…" },
  "banner_item_id": "<item uuid|null>",   // resolved from the banner_item composite key
  "homepage_item_id": "<item uuid|null>",
  "image_path": "thematic_gallery/thg_galleries/4/1.jpg",       // legacy media path
  "banner_image_path": "thematic_gallery/thg_galleries/4/banner.jpg",
  "homepage_image_path": null,
  "has_timeline": false,                   // THG-local timeline (galleries: false)
  "has_country_timeline": false,
  "featured": false,                       // thg_gallery.featured = 'H'; see below
  "hidden": false,
  "live_date": "2022-12-01T00:00:00Z",
  "sibling_galleries": [                   // the featured-galleries strip
    { "id": "<collection uuid>",
      "backward_compatibility": "mwnf3_thematic_gallery:thg_gallery:18",
      "slug": "…",                         // thg_gallery.link
      "legacy_host": "https://…|null",     // thg_gallery_url — metadata, not a resolved link
      "names": { "en": "…" },
      "image_path": "…",
      "featured": true, "hidden": false, "live_date": "…" }
  ]
}
```

Three things a fork must not get wrong, all verified against the live legacy
API while building the amulets exporter:

- **`slug` is the legacy value, folder names are not.** Gallery 4's
  `thg_gallery.link` is `amulets_and_talismans`, while the site, its package
  and its exporter directory are all `amulets` (decision Q4, the public
  subdomain). Only gallery 9 happens to have both the same (`carpets`).
- **`featured` and `status` are two independent flags sharing one enum.** Both
  are `enum('A','H')`, which invites conflating them, but the `thg_gallery`
  column comments are explicit: `status` is *A: Active; H: Hidden* — visibility
  of the gallery on every site — while `featured` is *A: should appear in
  "featured Galleries"; H: hidden from the featured galleries*, defaulting to
  `H`. The data agrees: `featured = 'A'` is a hand-picked set of ten
  (`carpets`, `glass`, `textiles`, `toys`, `precious_stones`,
  `the_use_of_colours_in_art`, …), and gallery 54 is `status = 'H'` with
  `featured = 'A'`, which only makes sense if the two are orthogonal.

  **dxa-api gets this wrong**: `WithTHGTemporaryTables.php` builds `featured`
  by copying the `hidden` projection — `CASE WHEN featured = 'A' THEN 0 ELSE 1
  END` — without flipping the polarity, so its JSON is the inverse of the
  record. Amulets is `featured = 'H'` (not featured) yet `/thg/galleries/self`
  reports `featured: true`. The defect never surfaced on the legacy sites:
  `/thg/galleries/featured` returns random non-exhibition galleries and ignores
  the flag, the `bf` filter compares the derived integer against `'A'` (MySQL
  casts that to `0`, inverting a second time and accidentally working), and
  dxa-client never reads the field. **Packages ship the documented meaning**
  (`featured = 'A'`), so this is the one place a live-API parity check is
  expected to disagree. `hidden` is the ordinary direction (`status = 'A'`
  shows).
- **Chrome images are legacy-hosted.** `image` / `banner_image` /
  `homepage_image` point into the legacy media server
  (`https://images.museumwnf.org/{size}/…`, a per-deployment env constant) and
  were never imported into inventory storage. The package therefore ships the
  **path** and the viewer supplies the host — the one exception to the
  absolute-image-URL convention.

`sibling_galleries` reproduces `/thg/galleries/featured` (4 random siblings in
legacy — the exporter ships the full active-gallery roster; the viewer picks).
Per decision Q3 these are **reference objects**: the exporter records identity
and available metadata (slug, imported host) but does not construct target
URLs; link resolution is a later, separate mechanism and the viewer degrades
gracefully where a reference cannot be resolved yet.

## items.json

Same field set as the islamicart package (id, type, internal_name,
backward_compatibility, partner_id, country_id, project_id, owner_reference,
mwnf_reference, start_date, end_date, lat/long, images[], dynasty_ids,
related_items, glossary_ids, artist_names, media, languages), with these
gallery deltas:

- **`tag_ids`** replaces the flat `tags` string list: references into
  `tags.json` so the facet **category** survives (legacy infers
  type/dynasty/subject/material from the tag id — the flat list cannot).
- **`project_key`** (e.g. `EPM`, `ISL`, `DCA`, `AWE`): member items span many
  projects; the sheet shows the source database. Per decision Q3 the package
  does NOT construct the legacy `remote-object`/`remote-monument` URL — the
  reference is the pair (`project_key`, `backward_compatibility`), which is
  exactly the legacy dbUid; a future resolver turns it into a link, and the
  viewer omits the link until then.
- Monument members carry embedded `details[]` (baroqueart 2.0.0 convention)
  — AMU/DCA have no monument members today, but the shape is specified for
  the other galleries.
- **`related_items`** keeps every outgoing link, including targets outside the
  gallery, each with `in_package` saying whether the viewer can open it
  locally, plus `backward_compatibility` and `project_key` for the ones it
  cannot (decision Q3 — the links must not be dropped). Amulets' 45 members
  carry 56 outgoing links, none of them to another member.
- **`gallery_references`** replaces the legacy `galleries` array of
  constructed URLs: the other thematic galleries and exhibitions featuring
  this item, as `{id, backward_compatibility, kind, slug, legacy_host, name}`,
  with the exporting gallery itself omitted.
- Translations (`translations/items.<lang>.json`): name, description,
  short_description, object_date, location, provenance, holder line fields —
  context selection = the item's own project context first (the rule
  established for islamicart/baroqueart), so a borrowed EPM item shows its
  EPM texts exactly as legacy does. **EPM is a special case**: legacy keeps the
  short text in `objects.description2`, which the importer files as an
  EPM-context translation, so the EPM row is the short description for every
  item — including EPM-native items, whose long description is then correctly
  empty (`planTranslations` in the importer's `object-transformer.ts`).
- Translations also carry **`keywords[]` and `materials[]`**, the per-language
  free-text lines on the legacy sheet. They come from the same `item_tag`
  pivot as the facets but from the mwnf3 tag families
  (`mwnf3:tags:{keyword,material}:{lang}:…`), are language-scoped, and must
  never be mixed into `tags.json` — on amulets alone that would add 441
  keyword and 188 unfaceted material links to the search dropdowns.

## tags.json (new)

```jsonc
[{ "id": "<uuid>", "backward_compatibility": "thg:tags:material_22c4",
   "legacy_tag_id": "material_22c4",     // what legacy search URLs carry
   "category": "material", "label": "Wool" }]
```

Categories: `artist`, `dynasty`, `material`, `subject`, `type`. English-only
(G5). The collection-search facets and their dependent narrowing are computed
client-side from `items[].tag_ids` × `tags.json`.

Two scoping rules make this file correct, and getting either wrong shows up
immediately in the facet counts (amulets: artist 1, dynasty 13, material 53,
subject 4, type 44 — 115 total, matching `/items/tags` exactly):

- **THG tags only.** Filter on `backward_compatibility LIKE '%thg:tags:%'`, and
  note the `LIKE`: a THG tag that matched an existing mwnf3 tag was
  deduplicated by the importer, which appends the THG key to the one already
  there (`mwnf3:tags:material:eng:carnelian;thg:tags:material_1ce4`).
- **Members only.** Tags reached through `item_tag` from the gallery's member
  items, never the whole `thg_tags` table.

## partners.json

Existing partner shape (contacts, images, logos, lat/long/zoom). Scope:
partners holding ≥1 member item. Additions:

- **`featured`**: from `partner_translations.extra.portal_display` — legacy's
  `showOnPortal` (`museums.portal_display = 'y'`), verified against
  `/mwnf3/partners/at/Mus22/en` on the live amulets instance. Legacy's
  `/partners/featured` returns a *random* subset of these, sized by
  `config('dxa.API_PAGESIZE')`; the package ships the flag and the viewer does
  the picking, which is the only way a static site can reproduce that.
- **`item_count`**: member items held — the count the partners list prints,
  and the reason a partner appears at all.

Legacy's list (`app/MWNF/SQL/mwnf3/Partners.blade.php`) is a three-branch
union, and only the first two reduce to "holds a member item". The third
(MWNF-384) adds museums created in the gallery's own project even when they
hold nothing; it is empty for amulets — no `mwnf3.museums` row has
`project_id = 'AMU'` — but a gallery whose native project owns museums needs
it, and `partners.project_id` is null for every imported museum, so that fork
needs an importer change first. The same query also carries two hardcoded
exclusions (`uk/Mus51`, `us/Mus51`) worth re-checking per gallery.

## glossary.json

As islamicart: entry + per-language spellings + definitions, scoped to terms
referenced by member items (`glossary_ids`). Spelling lists are required —
the item sheet regex-links terms inside descriptions (incl. the Arabic
word-boundary variant).

## timelines.json / timeline_events.json

The 18 global country timelines with their events and translations — 1,075
events, all of it small. Match on `mwnf3:hcr:country:%`, not `mwnf3:hcr:%`:
the latter also catches the separate Baroque Art chronology
(`mwnf3:hcr:bar:country:*`).

Every gallery package carries the same set regardless of the gallery's
`has_timeline` / `has_country_timeline` flags, because legacy does: the live
amulets instance reports both false and still answers `/events/countries` with
the worldwide list. The flags ride along on `gallery.json` for the viewer to
interpret. `timeline_event_item` links are filtered to member items — an event
may name items from any project, but the site can only open the ones it ships.
The timeline-gallery page joins events to member items by country + year range
client-side.

## countries.json / dynasties.json

Scoped to what the gallery references. Countries = the union of member item
countries and their holding partners' countries (the two differ, and the
partners page groups by the latter). Dynasties only where members actually link
them — unlike the islamicart package, which ships the whole table because the
Discover Islamic Art site has a dynasty browser; a gallery has no such page.

## languages.json

Two sets meet here, and both are needed: the gallery's own UI languages
(`thg_gallery_lang` — amulets: ar/en/es/fr) and every language a member record
carries, since the item sheet's language switcher is built from the record, not
the site (amulets' borrowed items add de/el/tr, and its partners add it/pt).
Entries are flagged `site_language` to tell the two apart. Exporting the whole
`languages` table instead would ship 180 rows of names the site can never show.

## Sizing expectation

AMU ≈ 45 items → trivial. DCA ≈ 486 items → well under the islamicart package
(~1,800 items). No new size risks; media excluded as always.

## What is deliberately NOT in the package

- UI strings and editorial pages (About/Credits/How-to/labels) → site-i18n.
- Palette/theming → website scaffold (`src/sites/<key>/_variables.scss` is the
  legacy source to port).
- Search indexes → computed client-side.
- Other sites' data (sibling galleries appear as references only).

## Reference implementation

`scripts/exporters/amulets` implements this specification
([story #1542](https://github.com/metanull/inventory-app/issues/1542)), verified
field by field against the live legacy API in
[`../amulets/tools/VALIDATION-2026-08-27.md`](../amulets/tools/VALIDATION-2026-08-27.md).
Fork it for the remaining galleries rather than re-deriving the scoping rules.
