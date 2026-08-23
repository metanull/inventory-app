# Sharing History Exporter

Reads the `inventory-app` database directly and writes a set of denormalized,
static JSON files for public-facing frontends to consume — no API server, no
auth, no runtime database dependency. Optionally packages and publishes that
output as a private npm package (`@metanull/sharinghistory-data`) on GitHub
Packages, for any consumer to install.

This exporter produces the Sharing History data-package (one exporter per
dataset lives under `scripts/exporters/<dataset>`).

## The SH data-package

The package is shaped by what the Sharing History dataset actually contains:

- **Keyspace** — Sharing History has its own legacy database;
  project keys resolve through `mwnf3_sharing_history:sh_projects:{key}`
  with **lowercase** keys. Default project key: `awe` ("Arab World – Europe",
  the single real SH project — `rus`/`usa` are legacy test placeholders).
- **3-level exhibitions** — `collections.json` carries
  exhibition → theme → **subtheme** ("Chapter" in the legacy UI). The
  exhibitions root marker carries `purpose: "exhibitions-root"` (importer
  step `sh-exhibition-root-keying`; legacy key
  `mwnf3_sharing_history:sh_exhibitions:root:awe` kept as informational
  `backward_compatibility`).
- **`display_status` on items** — `'N'` marks the ~462 items legacy kept
  only to illustrate Historical Background / timeline pages; consumers must
  exclude them from database search and Permanent Collection browse (`'A'`
  is the default).
- **Dual justifications on collection items** — collection item entries can
  carry `justifications: {lang: {partner, curator}}` and `curator_status`,
  at exhibition, theme AND subtheme level (legacy's curator-vs-partner hover
  cards).
- **Permanent Collection timelines** — SH timelines are per
  (country × exhibition). Timelines bound to an unpublished exhibition
  (legacy sentinel: exhibition 2 "Political Context", `show='n'`) are
  exported with `collection_id: null` — they are the project-level
  "Permanent Collection timeline" of the legacy site. `timelines.json`
  entries also carry `extra` (per-language bibliography), and
  `timeline_events.json` entries can carry `item_extras` (legacy per-item
  caption texts from `sh_hcr_image_texts`).
- **Historical Background / National Context** — exported as regular
  collections (children of the project collection); consumers resolve them
  via the `purpose` field (`historical-background-root` / `topics-root` /
  `historical-profiles-root` / `national-context`, #1505), never by parsing
  `backward_compatibility`.
- **Item translations spread their `extra`** — the SH importer stores a
  wider, item-type-dependent field set in `item_translations.extra`
  (archival, materials, artist + artist_* details, notices,
  monument_contact, external_sources, structured_bibliography, …); the
  translation files include all of them (dedicated columns win on
  collision).
- **No dynasty exporter** — SH has no dynasty entity (free-text field only).
- **Glossary** — SH content carries ~2,800 usage-scoped glossary spelling
  links.
- **Multilingual** — every translation present for the SH context is
  exported. In practice `en` is complete and `fr` a substantial partial
  (~16% of item texts); other languages are scraps.

## Run (TL;DR)

```powershell
cd scripts/exporters/sharinghistory
npm install                # first run only
npm run export -- `
  --force `
  --publish
```

Defaults: subdirectory `sharinghistory`, project key `awe`, package name
`@metanull/sharinghistory-data`. Image URLs in the exported JSON are built
from `BASE_URL` in `.env` (or `--base-url`). `--publish` does everything in
one go: version bump, `package.json`/`README.md` generation, and the actual
`npm publish` — no separate manual publish step. (`--package-version` is
optional — omit it to auto-increment instead; see
[`NPM_PUBLISH.md`](NPM_PUBLISH.md).)

## What it exports

One JSON file per entity type, plus a manifest and per-language translation
files, written to `output/<subdirectory>/`:

| File | Exporter | Contents |
|---|---|---|
| `manifest.json` | `ManifestExporter` | Metadata about the export itself (project keys, generated-at timestamp, available languages) |
| `languages.json` | `LanguageExporter` | Language reference data |
| `countries.json` | `CountryExporter` | Country reference data + translations |
| `timelines.json` / `timeline_events.json` | `TimelineExporter` | Per-(country × exhibition) SH timelines (+ Permanent Collection timelines with `collection_id: null`), events with images, item links and legacy captions |
| `partners.json` | `PartnerExporter` | SH partners (flat tiers via `level`: `partner` / `associated_partner`) + translations + logos + images |
| `items.json` | `ItemExporter` | Items (objects/monuments), with `display_status`, images, media/documents, related-item links |
| `collections.json` | `CollectionExporter` | Project, exhibitions root, exhibitions → themes → subthemes, Historical Background and National Context collections — with images, item membership, justifications |
| `glossary.json` | `GlossaryExporter` | Glossary terms used by SH content + translations |
| `translations/<entity>.<lang>.json` | (several) | Per-language translation fields, lazy-loadable separately |

There is deliberately **no `dynasties.json`** (see above).

Every JSON file is also written gzip-compressed (`.json.gz`) alongside the
plain version — the compressed copies aren't part of the npm package (see
below) but are there for CDN/static-hosting use.

### Scoping to specific projects

Export is always scoped to one or more legacy SH project keys (default
`awe`), resolved against `projects.backward_compatibility`
(`mwnf3_sharing_history:sh_projects:{key}`, lowercase). The same keys resolve
a matching set of context IDs, used internally to filter item translations to
the project's own context.

## Configure

```bash
cp .env.example .env
# edit .env
```

- `DB_*` — the inventory database to read from (same variables/meaning as
  the [importer](../../importer/README.md)'s target-DB connection — point this
  at a tunnel or a local dev DB interchangeably, since the exporter only ever
  reads)
- `BASE_URL` — prepended to image paths in the exported JSON (defaults to
  `./images`, relative to wherever the JSON is served from)
- `PACKAGE_AUTHOR` / `PACKAGE_LICENSE` / `PACKAGE_REPO_URL` / `NPM_REGISTRY`
  — npm package metadata, only used with `--publish`

## Usage

```bash
npm run export -- [subdirectory] [project-keys...] [options]
```

```bash
# Standard export (defaults: sharinghistory awe)
npm run export --

# Custom output location and image base URL
npm run export -- --output-dir /tmp/export --base-url https://cdn.example.com/storage

# Export, bump the package version, generate package.json/README.md, and publish
npm run export -- --publish
```

| Option | Description |
|---|---|
| `--force` | Overwrite the output directory if it already exists (refuses to run otherwise) |
| `--output-dir <path>` | Base output directory (default: `output`, relative to cwd) |
| `--base-url <url>` | Base URL prepended to image paths (default: `BASE_URL` env var, then `./images`) |
| `--publish` | Bump version, generate `package.json`/`README.md`, and `npm publish` the output as an npm package |
| `--package-name <name>` | Override the package name (default: `@metanull/sharinghistory-data`) |
| `--package-version <semver>` | Set an explicit version instead of auto-incrementing |
| `--npm-registry <url>` | Override the publish registry (default: `NPM_REGISTRY` env var, then GitHub Packages) |

See [`NPM_PUBLISH.md`](NPM_PUBLISH.md) for the full publishing workflow —
version-file mechanics, package structure, GitHub Packages authentication,
and the consumer-side install/import story.

## How this fits together

```
inventory-app DB
      │  (exporter reads directly — no API involved)
      ▼
scripts/exporters/sharinghistory  ──npm publish──▶  @metanull/sharinghistory-data (GitHub Packages)
                                                       │  npm install
                                                       ▼
                                                 any consumer
```

The exporter and its consumers are decoupled entirely through the published
package — a consumer never talks to the exporter or the database, it just
depends on whatever version of the data package it installs. A routine
content update is one re-export with `--publish --force` (which publishes by
itself); consumers pick the new version up on their own schedule.

## Troubleshooting

**`Output directory already exists`** — pass `--force` to overwrite it, or
pick a different `--output-dir`/subdirectory.

**Export completes with `EXPORT COMPLETED WITH ERRORS`** — one or more
exporters failed independently (the CLI continues through all exporters
regardless, then reports every failure at the end); `--publish` is skipped
entirely if any exporter errored. Check the per-exporter error line in the
final summary for which one failed and why.

**`collections.json` is missing the exhibitions root** — the importer's
`sh-exhibition-root-keying` step has not been run against this database
yet; run `npm run import -- --only sh-exhibition-root-keying` from
`scripts/importer` (after verifying the `.env` target) and re-export. The
same applies to `sh-exhibition-show-flag` (unpublished-exhibition filter),
`sh-item-display-status`, `sh-exhibition-item-justifications` and
`sh-partner-project-linker` (without the latter, `partners.json` is empty).

**`npm publish` fails with "not authorized"** — GitHub Packages
authentication isn't configured; see [`NPM_PUBLISH.md`](NPM_PUBLISH.md#github-packages-authentication).

**Database connection fails** — same `DB_*` variables and troubleshooting as
the [importer](../../importer/README.md#troubleshooting).
