# Islamic Art Exporter

Reads the `inventory-app` database directly and writes a set of denormalized,
static JSON files for public-facing frontends to consume — no API server, no
auth, no runtime database dependency. Optionally packages and publishes that
output as a private npm package via GitHub Packages, which is how
[`scripts/viewers/islamicart`](../../viewers/islamicart/README.md) and the
deployed Discover Islamic Art site actually consume it.

Exporters are forked per dataset (`scripts/exporters/<dataset>`): this one
produces the Discover Islamic Art data-package.

## Run (TL;DR)

```powershell
cd scripts/exporters/islamicart
npm install                # first run only
npm run export -- islamicart ISL `
  --force `
  --base-url https://inventory.metanull.eu `
  --publish `
  --package-name @metanull/islamicart-data `
  --package-version 1.0.15
cd output/islamicart && npm publish
```

(`--package-version` is optional — omit it to auto-increment instead; see
[`NPM_PUBLISH.md`](NPM_PUBLISH.md).)

## What it exports

One JSON file per entity type, plus a manifest and per-language translation
files, written to `output/<subdirectory>/data/`:

| File | Exporter | Contents |
|---|---|---|
| `manifest.json` | `ManifestExporter` | Metadata about the export itself (project keys, generated-at timestamp, available languages) |
| `languages.json` | `LanguageExporter` | Language reference data |
| `countries.json` | `CountryExporter` | Country reference data + translations |
| `dynasties.json` | `DynastyExporter` | Dynasty reference data + translations |
| `timelines.json` | `TimelineExporter` | Timelines and their events |
| `partners.json` | `PartnerExporter` | Museums/institutions + translations + images |
| `items.json` | `ItemExporter` | Items (objects/monuments/details), with images, dynasty/tag links, related-item links |
| `collections.json` | `CollectionExporter` | Collections (exhibitions/themes/galleries), with images and item membership |
| `glossary.json` | `GlossaryExporter` | Glossary terms + translations |
| `translations/items.<lang>.json` | `ItemExporter` | Per-language item translation fields, lazy-loadable separately from `items.json` |
| `translations/collections.<lang>.json` | `CollectionExporter` | Per-language collection translation fields |

Every JSON file is also written gzip-compressed (`.json.gz`) alongside the
plain version — the compressed copies aren't part of the npm package (see
below) but are there for CDN/static-hosting use.

### Scoping to specific projects

Export is always scoped to one or more legacy project keys (e.g. `ISL`,
`WHS`), resolved against `projects.backward_compatibility` (`mwnf3:projects:
{KEY}`) — not every project in the database gets exported by default. The
same keys resolve a matching set of context IDs, used internally to exclude
explore-context translations from overwriting the canonical project
translations for items/collections.

## Configure

```bash
cp .env.example .env
# edit .env
```

- `DB_*` — the inventory database to read from (same variables/meaning as
  the [importer](../importer/README.md)'s target-DB connection — point this
  at a tunnel, a local dev DB, or [import-tool](../import-tool/README.md)'s
  `local-mysql` interchangeably, since the exporter only ever reads)
- `BASE_URL` — prepended to image paths in the exported JSON (defaults to
  `./images`, relative to wherever the JSON is served from)
- `PACKAGE_AUTHOR` / `PACKAGE_LICENSE` / `PACKAGE_REPO_URL` / `NPM_REGISTRY`
  — npm package metadata, only used with `--publish`

## Usage

```bash
npm run export -- <subdirectory> <project-key> [more-project-keys...] [options]
```

```bash
# Export a single project
npm run export -- islamicart ISL

# Export multiple projects into one output
npm run export -- combined ISL WHS

# Custom output location and image base URL
npm run export -- islamicart ISL --output-dir /tmp/export --base-url https://cdn.example.com/storage

# Export, bump the package version, generate package.json/README.md, and publish
npm run export -- islamicart ISL --publish
```

| Option | Description |
|---|---|
| `--force` | Overwrite the output directory if it already exists (refuses to run otherwise) |
| `--output-dir <path>` | Base output directory (default: `output`, relative to cwd) |
| `--base-url <url>` | Base URL prepended to image paths (default: `BASE_URL` env var, then `./images`) |
| `--publish` | Bump version, generate `package.json`/`README.md`, and `npm publish` the output as an npm package |
| `--package-name <name>` | Override the package name (default: `@mwnf/{subdirectory}-data`) |
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
scripts/exporters/islamicart  ──npm publish──▶  @metanull/islamicart-data (GitHub Packages)
                                                       │  npm install
                                                       ▼
                                          scripts/viewers/islamicart (or any consumer)
```

The exporter and the viewer are decoupled entirely through the published
package — the viewer never talks to the exporter or the database directly,
it just depends on whatever version of the data package is installed.
Routine content updates mean: re-export with `--publish --force`, `npm
publish`, then either let the viewer's deploy workflow pick up `@latest`
automatically (see [`scripts/viewers/islamicart/README.md`](../../viewers/islamicart/README.md)) or
bump it manually for other consumers. `@metanull/islamicart-data` is the
package actually deployed today; the code's own default naming
(`@mwnf/{subdirectory}-data`, see the option table above) is there for
anyone exporting a differently-scoped or differently-named package.

## Troubleshooting

**`Output directory already exists`** — pass `--force` to overwrite it, or
pick a different `--output-dir`/subdirectory.

**Export completes with `EXPORT COMPLETED WITH ERRORS`** — one or more
exporters failed independently (the CLI continues through all exporters
regardless, then reports every failure at the end); `--publish` is skipped
entirely if any exporter errored. Check the per-exporter error line in the
final summary for which one failed and why.

**`npm publish` fails with "not authorized"** — GitHub Packages
authentication isn't configured; see [`NPM_PUBLISH.md`](NPM_PUBLISH.md#github-packages-authentication).

**Database connection fails** — same `DB_*` variables and troubleshooting as
the [importer](../importer/README.md#troubleshooting); this exporter has no
`validate` command of its own, so a quick `--force` export against a small
single project is the fastest way to confirm connectivity.
