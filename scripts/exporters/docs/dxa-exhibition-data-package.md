# DXA Exhibition data package — specification

Date: 2026-08-27 · Applies to every DXA Exhibition site (pilots:
**the-use-of-colours-in-art** gallery 47/EXHCOLOUR de+en, **water-in-islam**
gallery 56/GalEx6 en; generalizes to all 6 exhibitions). Package/folder names
are the kebab-cased legacy slugs (decision Q4); data values keep the
underscore slugs. Requirements source: `dxa-legacy-analysis.md`.

An Exhibition package is a **superset of the Gallery package**: everything in
`dxa-gallery-data-package.md` applies unchanged (items = membership union,
tags, partners, glossary, countries, languages, translations layout,
manifest), plus the curated theme layer and exhibition-specific chrome
described here, and two substitutions:

1. `gallery.json` → **`exhibition.json`** (richer identity).
2. The global country timeline is **replaced** by the exhibition's THG-local
   timeline — present only where legacy has one (Colours: 45 events; Water in
   Islam: none → no timeline files, nav hidden via `has_timeline: false`).

## Additional / changed files

```
exhibition.json            ← replaces gallery.json
themes.json                ← the curated tree (the heart of the product)
related_content.json       ← categorized external links
translations/
  themes.<lang>.json       ← theme titles/quotes/presentations + curated texts
```

## exhibition.json

```jsonc
{
  "id": "<collection uuid>",
  "backward_compatibility": "mwnf3_thematic_gallery:thg_gallery:47",
  "slug": "the_use_of_colours_in_art",   // load-bearing: legacy public URL path
  "legacy_host": "https://exhibitions.museumwnf.org",
  "mwnf3_project_id": "EXHCOLOUR",
  "kind": "exhibition",
  "languages_enabled": ["de", "en"],      // exhibition_i18n.enabled per language
  "titles":    { "de": "…", "en": "…" },  // exhibition_i18n.title
  "subtitles": { "de": "…", "en": "…" },
  "headlines": { "de": "…", "en": "…" },  // banner heading
  "abouts":    { "de": "…", "en": "…" },  // about page body (curated, data — unlike galleryAbout)
  "popup_logo": { "show": true, "image_url": "…" },
  "banner_image_url": "…",
  "has_timeline": true,
  "partners": [                            // exhibition_partner: bottom-banner strip
    { "partner_id": "<uuid>", "category": "…", "display_order": 1 }
  ],
  "logos": [                               // exhibition_logo: sponsor logos
    { "image_url": "…", "url": "…|null", "alt_texts": {"en": "…"}, "category": "…", "display_order": 1 }
  ],
  "hidden_partner_ids": ["<uuid>", …]      // E6: exclude everywhere (Water in Islam: 13 refs)
}
```

Note the split: `abouts` (exhibition_i18n, curated per-exhibition **data**)
lives in the package, while gallery About pages (i18n-group editorial) do not.
Both exist for exhibitions — the exhibition About page renders
`exhibition_i18n.about`; UI labels still come from site-i18n.

## themes.json

The ordered tree, one level of nesting (theme → sub-theme), with the curated
pictures inline:

```jsonc
[{
  "id": "<collection uuid>",              // BC mwnf3_thematic_gallery:theme:47:3
  "display_order": 3,
  "cover_picture_item_id": "<uuid|null>", // E4: theme_cover_image
  "sub_themes": [ /* same shape, no further nesting */ ],
  "pictures": [{
    "picture_item_id": "<uuid>",          // the `picture` child item (type='picture')
    "parent_item_id": "<uuid>",           // the object/monument the picture belongs to
    "display_order": 1,
    "image_url": "https://inventory.metanull.eu/pub/….jpg",
    "related": [{                          // theme_item_related (directional)
      "picture_item_id": "<uuid>",
      "theme_id": "<uuid>",               // may be another theme (E5 cross-theme rows)
      "descriptions": { "en": "…" }        // directional text, per language
    }]
  }]
}]
```

Per-language curated texts go to `translations/themes.<lang>.json`, keyed by
collection/picture ids:

```jsonc
{
  "<theme collection id>": { "title": "…", "quote": "…", "presentation": "…" },
  "<theme id>/<picture item id>": {
    "contextual_description": "…",         // theme_item_i18n (E2/E3 complete)
    "image_caption": "…"
  }
}
```

Why pictures reference **both** the picture item and its parent: the theme
page renders the selected picture + curated text, and links "see the full
record" to the parent's item sheet (`/database-item/<parent BC>`). Parent
items are guaranteed present in `items.json` (they are members of the
exhibition collection universe; verify during implementation for the 26
E2-recovered Explore/Travel pictures — their parents come from the Explore/
Travels item families).

## items.json note

Identical spec to the Gallery package. The exhibition's own pseudo-project
items (EXHCOLOUR/GalEx6) are ordinary members; `project_key` distinguishes
them from borrowed items so the sheet can suppress the "source database" link
for purpose-authored records exactly as legacy does
(`projectSourceIsExhibition` in the API response).

## partners.json note

Gallery spec plus:

- Institutions (monument owners) and museums both appear; the viewer routes
  museums to partner pages and institutions to institution pages by partner
  `type` (already in the shape).
- Entries listed in `exhibition.json.hidden_partner_ids` are exported but
  flagged — the viewer must exclude them from every list/profile page while
  their items still render (legacy behaviour: the museum is hidden, the item
  is not).

## related_content.json

`exhibition_related_content` (32 rows across the 6 exhibitions):

```jsonc
[{ "category": "…", "url": "…", "titles": { "en": "…" }, "display_order": 1 }]
```

## media

Exhibition/theme audio+video (`exhibition_audio/video`, `theme_audio/video` →
collection_media) ride on the existing `media` arrays of `exhibition.json` /
`themes.json` entries, same shape as item media in current packages.

## timelines.json / timeline_events.json

Only when the exhibition has a THG-local timeline
(BC `mwnf3_thematic_gallery:timeline:{gallery}`). Colours: 1 timeline,
45 events (de+en translations). Water in Islam: files omitted.

## Sizing expectation

Colours: 117+24 items, 26 themes, ~150 pictures. Water in Islam: ~424 items,
18 themes, ~432 pictures. Both far below existing packages.

## Per-language deployment (decision Q2)

The package always carries **all** enabled languages (`languages_enabled` +
per-language translation files). Per decision Q2 the websites ship as
**per-language builds** (strict legacy parity): Colours deploys separately for
de and en, Water in Islam for en. The split happens at viewer build/deploy
time — one package feeds all language builds of its exhibition.
