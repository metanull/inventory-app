# Carpets Exporter

Reads the `inventory-app` database directly and writes a set of denormalized,
static JSON files for the rebuilt **Carpets** website — no API server, no auth,
no runtime database dependency. Optionally packages and publishes that output as
a private npm package (`@metanull/carpets-data`) on GitHub Packages.

This is the second of the DXA gallery exporters
([epic #1539](https://github.com/metanull/inventory-app/issues/1539),
[story #1544](https://github.com/metanull/inventory-app/issues/1544)), forked
from [`../amulets`](../amulets/README.md) ([#1542](https://github.com/metanull/inventory-app/issues/1542)).
It replaces one legacy deployment of `dxa-api` + `dxa-client`:
<https://carpets.museumwnf.org>. The package specification it implements is
[`../docs/dxa-gallery-data-package.md`](../docs/dxa-gallery-data-package.md);
the legacy behaviour it reproduces is analyzed in
[`../docs/dxa-legacy-analysis.md`](../docs/dxa-legacy-analysis.md).

## Carpets is the hybrid gallery

Amulets was the purely-curated case — 45 objects, every one of them borrowed.
Carpets is where the collection-scoped design gets its real test.

- **486 members, 398 of them native.** The universe is still the membership
  union legacy expressed as an OR predicate (items of the gallery's own mwnf3
  project OR items listed in the six `thg_gallery_*` link tables), materialized
  by the importer in `collection_item` ([#1517](https://github.com/metanull/inventory-app/issues/1517),
  gap G1). But here the native branch dominates: 398 DCA objects, plus 59 EPM,
  21 ISL, 4 Sharing History, 2 BAR, 1 EXTHE and 1 GALLERIES.
- **Seven source projects, so `project_key` and context selection are per
  item.** A DCA carpet shows its DCA texts, a borrowed EPM object shows its EPM
  texts, and each sheet names its own source database. Nothing here can be a
  per-export constant.
- **EPM is still the short description** — for native records as much as
  borrowed ones. Legacy keeps the short text in `objects.description2`, which
  the importer files as an EPM-context translation (`planTranslations` in
  `object-transformer.ts`). For an EPM-native item that row is the only one, and
  its long description is legitimately empty.
- **Arabic is a site language** (ar/en/es/fr), so everything downstream has to
  render RTL. The package carries the language code on `languages.json` and
  every translation file; direction is the viewer's job.
- **`featured` disagrees with the live API, in the opposite direction from
  amulets.** `featured` and `status` are two independent flags sharing
  `enum('A','H')`: `status` is site-wide visibility, `featured` is membership of
  the portal's highlight strip. dxa-api builds its `featured` output by copying
  the `hidden` projection without flipping the polarity, so its JSON is the
  inverse of the record. Gallery 9 is `featured = 'A'` — one of the ten
  hand-picked galleries — and the live `/thg/galleries/self` answers
  `featured: false`. The package ships the documented meaning,
  `featured: true`. See `isFeatured` in `src/exporters/gallery-exporter.ts`.
- **Slug and site name coincide here, and that is a coincidence.** Gallery 9's
  `thg_gallery.link` is `carpets`, which happens to equal the public subdomain
  and therefore the folder and package name. Gallery 4's is
  `amulets_and_talismans` against a folder called `amulets`. `gallery.json`
  always carries the legacy value; never infer one from the other.

Everything else about a gallery exporter is unchanged from the amulets fork:
facet tags and sheet tags are different things, outbound links are references
rather than URLs (decision Q3), and the timeline is not gallery-specific.

## Two fixes this fork does not inherit from amulets

Both were found while building the amulets viewer
([#1566](https://github.com/metanull/inventory-app/issues/1566)) and are
corrected here. The amulets exporter still has them.

### The global timeline is a merge of two chronologies, not one

`/v2/events` is served by `App\MWNF\DAO\v2\Events`, which queries **two**
sources and merges them sorted by year:

| Source | Legacy SQL | Importer keyspace | Timelines | Events |
|---|---|---|---|---|
| Discover Islamic Art country chronologies | `app/MWNF/SQL/mwnf3/Events.blade.php` | `mwnf3:hcr:country:<cc>` | 18 | 1,075 |
| Sharing History, **exhibition 2 only** | `app/MWNF/SQL/sh/Events.blade.php` | `mwnf3_sharing_history:sh_hcr:country:<cc>:exhibition:2` | 19 | 315 |

The SH half is pinned by a `where h.exhibition_id = 2` that the legacy source
labels a "HARDCODED BUSINESS DECISION" — exhibition 2 is *Political Context*.
Match only the first family (the amulets rule) and 8 countries and 315 events
vanish; widen to a bare `mwnf3:hcr:%` and the Baroque Art chronology
(`mwnf3:hcr:bar:country:*`) walks in. Two exact families, no wildcard in the
middle — see `GLOBAL_TIMELINE_LIKE_PATTERNS` in
`src/exporters/timeline-exporter.ts` and `tests/unit/timeline-scope.test.ts`.

The corrected set is **37 timelines over 26 countries, 1,390 events**, matching
the live `/events/count` exactly, including on countries served by only one
source (dz 60, at 22) and on merged ones (eg 60+8=68, tr 60+38=98, ma 60+18=78).
North Macedonia is the case that proves the rule: it has SH exhibitions 4/5/8/9
and no exhibition 2, and the live `/events/count?ic[]=mc` answers 0.

Each row in `timelines.json` carries a `source` (`mwnf3` | `sharing_history`)
so a viewer can present one merged list per country the way legacy did.

### `countries.json` must cover the timeline too

Three sets of countries need names on a gallery site: the member items' own
countries, their holding museums' countries, and the countries of the global
timeline. The amulets fork ships only the first two, so its viewer falls back
to `Intl.DisplayNames` for every timeline-only country. On carpets the three
sets are 26 / 26 / 26 and their **union is 34** — the timeline alone contributes
fr, lb, ma, pa, sa, sy, tn and ua.

## Package contents

| File | Contents |
|---|---|
| `manifest.json` | Export metadata, the gallery's UI languages, item count |
| `gallery.json` | Site anchor: slug, legacy host, names, banner item, chrome flags, sibling galleries |
| `items.json` | The 486 member items — full sheets, facet tag ids, images, references |
| `tags.json` | 248 THG facet tags with their category (artist 11, dynasty 22, material 103, subject 30, type 82) |
| `partners.json` | The 70 museums holding member items, with `featured` and `item_count` |
| `countries.json` | The 34 countries the members, their holders and the timeline reference |
| `languages.json` | The 10 languages the site can display (ar/en/es/fr as `site_language`, plus cs/de/el/it/pt/tr carried by borrowed records and partners) |
| `dynasties.json` | The 14 dynasties member items reference |
| `glossary.json` | The 137 terms reachable from member item texts, with spelling lists |
| `timelines.json` / `timeline_events.json` | 37 country timelines over 26 countries, 1,390 events |
| `translations/<entity>.<lang>.json` | All human-readable text, one file per entity per language |

Entity files hold language-independent data; every human-readable string lives
under `translations/`. Image URLs are absolute, built from `BASE_URL`. A file is
absent when that entity has no translation in that language — viewers must
tolerate this.

Gallery chrome images (`image_path`, `banner_image_path`) are the exception to
the absolute-URL rule: they live on the legacy media server and were never
imported, so the package carries the legacy path only
(`thematic_gallery/thg_galleries/9/{1,banner}.jpg`) and the viewer supplies the
host, exactly as the legacy client did through `VUE_APP_IMAGES_URL`.

## Run

The exporters run inside Docker; there is no host-side Node tooling.

```bash
docker compose --profile jobs run --rm exporter carpets --force
```

Add `--publish` to bump the version, generate `package.json`/`README.md` and
push to GitHub Packages — see [`NPM_PUBLISH.md`](NPM_PUBLISH.md).

The compose service points at the **staging** database (`staging-mysql`), which
is where the exporter should be developed and verified.
`scripts/exporters/carpets/.env` is only consulted when running outside compose,
and by convention those files point at **production** — read it before running
anything that way.

## Naming

The folder and package are `carpets` / `@metanull/carpets-data` — the site's
public identity (`carpets.museumwnf.org`) and the name fixed by decision Q4.
Gallery 9 is the one gallery whose legacy slug is the same string; that is not a
rule, and `gallery.json` still carries the slug as data rather than deriving it.

## Known gaps

Verified during implementation, none blocking:

- **Museums with no items (legacy MWNF-384).** Legacy's partner list has a
  third branch: museums created in the gallery's *own* project appear even when
  they hold nothing. Carpets is the first gallery where it fires — legacy lists
  72 partners, and `jo/Mus31` (Greater Amman Municipality) and `pt/Mus31`
  (Centro de História d'Aquém e d'Além-Mar) are there with `hasObjects: 0`
  because they were created under DCA. This exporter ships the other 70 and
  cannot do better yet: the importer never populates `partners.project_id` for
  museums (it is set on ten ISL schools and nothing else) and
  `partner_translations.extra` records only `source: "mwnf3"`. Reproducing the
  branch needs `mwnf3.museums.project_id` carried through the import first —
  **importer-side, not exporter-side**. Both partner records themselves are
  present in the inventory database, so the fix is a scoping query, not a
  re-import of content.
- **EPM author attribution.** Legacy shows `preparedBy`/`copyEditedBy` on the
  English sheet; the importer files those names only on the Arabic row for EPM
  items, so `author`/`copy_editor` are missing from the English translations of
  EPM-native records. Importer-side; affects EPM items generally.
- **`notice` / `notice_b` / `notice_c`** (the copyedit notices on the legacy
  sheet) have no counterpart in the inventory schema and are not imported.
- **Legacy's two hardcoded partner exclusions** (`uk/Mus51`, `us/Mus51`, in
  `Partners.blade.php`) are not reproduced; neither holds a carpets member.
- **MWNF-371 (partners of a not-yet-live project)** is not reproduced either;
  it excludes nothing here — 70 exported plus the 2 MWNF-384 rows accounts for
  the whole legacy list of 72.

## Validation

[`tools/VALIDATION-2026-08-27.md`](tools/VALIDATION-2026-08-27.md) records the
export checked metric by metric against the live legacy API.
