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
  "slug": "amulets",                      // thg_gallery.link
  "legacy_host": "https://amulets.museumwnf.org",
  "mwnf3_project_id": "AMU",
  "names": { "en": "Amulets and Talismans", "ar": "…", "es": "…", "fr": "…" },
  "banner_item_id": "<item uuid|null>",   // resolved from banner_item composite key
  "banner_image_url": "…|null",
  "homepage_item_id": "<item uuid|null>",
  "has_timeline": false,                   // THG-local timeline (galleries: false)
  "featured": true,
  "sibling_galleries": [                   // the featured-galleries strip
    { "name": "…", "slug": "…",            // thg_gallery.link
      "backward_compatibility": "mwnf3_thematic_gallery:thg_gallery:18",
      "legacy_host": "https://…|null",     // thg_gallery_url — metadata, not a resolved link
      "image_url": "…" }
  ]
}
```

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
- Translations (`translations/items.<lang>.json`): name, description,
  short_description, object_date, location, provenance, holder line fields —
  context selection = the item's own project context first (the rule
  established for islamicart/baroqueart), so a borrowed EPM item shows its
  EPM texts exactly as legacy does.

## tags.json (new)

```jsonc
[{ "id": "<uuid>", "backward_compatibility": "thg:tags:material_22c4",
   "category": "material", "label": "Wool" }]
```

Categories: `artist`, `dynasty`, `material`, `subject`, `type`. English-only
(G5). The collection-search facets and their dependent narrowing are computed
client-side from `items[].tag_ids` × `tags.json`.

## partners.json

Existing partner shape (contacts, images, logos, lat/long/zoom, level,
parent). Scope: partners holding ≥1 member item. Additions:

- **`featured`**: boolean from the legacy `showOnPortal` flag — drives the
  home-page featured strip (verify flag fidelity during implementation).
- **`item_count`**: member items held (partners list shows counts; cheap to
  precompute, keeps the viewer trivial).

## glossary.json

As islamicart: entry + per-language spellings + definitions, scoped to terms
referenced by member items (`glossary_ids`). Spelling lists are required —
the item sheet regex-links terms inside descriptions (incl. the Arabic
word-boundary variant).

## timelines.json / timeline_events.json

The 18 global country timelines (`mwnf3:hcr:*`) with their events and
translations — identical extraction to the islamicart exporter. Every gallery
package carries the same set (legacy parity: the timeline is global; ~1,075
events is small). The timeline-gallery page joins events to member items by
country + year range client-side.

## countries.json / languages.json / dynasties.json

As in existing packages, scoped to what member items reference. Dynasties only
where members actually link them (AMU/DCA members include ISL/EPM items with
dynasties).

## Sizing expectation

AMU ≈ 45 items → trivial. DCA ≈ 486 items → well under the islamicart package
(~1,800 items). No new size risks; media excluded as always.

## What is deliberately NOT in the package

- UI strings and editorial pages (About/Credits/How-to/labels) → site-i18n.
- Palette/theming → website scaffold (`src/sites/<key>/_variables.scss` is the
  legacy source to port).
- Search indexes → computed client-side.
- Other sites' data (sibling galleries appear as name+URL+image only).
