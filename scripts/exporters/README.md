# Dataset Exporters

One exporter per public website ("dataset"). Each exporter reads the
inventory-app MySQL database (read-only) and produces a static, denormalized
JSON **data-package**, published as a private npm package on GitHub Packages.
The matching viewer (see [`../viewers/`](../viewers/README.md)) consumes that
package at build time — the public websites never talk to the database or the
Laravel API.

```
legacy DBs ──(importer, run once)──▶ inventory-app DB ──(exporter, per dataset)──▶ @metanull/<dataset>-data ──▶ viewer
```

## Datasets

| Directory | Legacy project | Package | Consumed by |
|---|---|---|---|
| [`islamicart/`](islamicart/README.md) | `ISL` (+ `EPM` extras) | `@metanull/islamicart-data` | `scripts/viewers/islamicart` |
| [`baroqueart/`](baroqueart/README.md) | `BAR` | `@metanull/baroqueart-data` | `scripts/viewers/baroqueart` |
| [`sharinghistory/`](sharinghistory/README.md) | `awe` (SH keyspace, lowercase) | `@metanull/sharinghistory-data` | `scripts/viewers/sharinghistory` |

Each directory is a **self-contained Node/TypeScript project** (own
`package.json`, `tsconfig.json`, `vitest.config.ts`, `.env`). See the README
inside each one for dataset specifics.

## Why forked per dataset (deliberate decision)

Exporters are **forked, not shared** (decision from the Baroque Art epic,
2026-08-21). Each website's legacy feature set differs — e.g. dynasties,
artintro and the glossary browse exist only for Islamic Art — and a shared
profile-driven exporter would accumulate per-dataset conditionals. Forks are
pruned instead: the baroqueart fork simply has no dynasty exporter. The cost
is that cross-cutting fixes must be ported to every fork (e.g. the
unpublished-exhibition filter, added to both in #1477/#1478); when you fix a
`src/exporters/*` file in one fork, check whether the siblings need the same
change.

## Package layout

```
output/<dataset>/
├── manifest.json            # projectIds/projectKeys (parallel arrays), languages, export metadata
├── items.json               # objects, monuments, monument details
├── collections.json         # projects, exhibitions, themes, pages, galleries…
├── partners.json / countries.json / languages.json
├── timelines.json / timeline_events.json
├── glossary.json
├── translations/
│   └── <entity>.<lang>.json # one file per entity per language; a file is
│                            #   absent when that entity has no translations
│                            #   in that language — viewers must tolerate this
├── *.json.gz                # precompressed copies
└── .version… (state lives in output/.version-<dataset>)
```

Entity files hold language-independent data (ids, relations, image URLs,
display order); all human-readable text lives in `translations/`. Image URLs
are absolute, built from `BASE_URL` (the inventory app's public storage).

Section anchors (exhibitions root, artistic-introduction root, Historical
Background/Profiles roots, National Context overlays, …) are identified by
the `purpose` field on collections (#1505) — a controlled vocabulary set by
the importer (`exhibitions-root`, `artistic-introduction-root`,
`historical-profiles-root`, `national-context`, …), unique per context for
`*-root` values. Viewers resolve sections **only** via `purpose`; the
`backward_compatibility` keys still shipped alongside are informational.
Exhibitions whose legacy `show` flag is `'n'` (preserved in
`collection_translations.extra.legacy_exhibition.show`) are excluded from the
package, as the legacy sites never listed them.

## Build + publish a data-package update (end to end)

Prerequisites, one-time:

- `.env` in the exporter directory (`cp .env.example .env` if present):
  `DB_*` (the inventory DB — production is reached through the SSH tunnel,
  `ssh -L 3307:localhost:3306 deploy@<vps>`, so `DB_HOST=127.0.0.1`,
  `DB_PORT=3307`), `BASE_URL` (the public base URL of the inventory app's
  storage, prepended to image paths in the exported JSON), and
  `PACKAGE_REPO_URL=https://github.com/metanull/inventory-app` (see gotcha 1
  below).
- npm authentication for the `@metanull` scope on
  `https://npm.pkg.github.com` (a PAT with `write:packages` in `~/.npmrc` or
  the repo-root `.npmrc`).

Then, per dataset (exports are **read-only**, but always check what `.env`
points at first):

```bash
cd scripts/exporters/islamicart
npm run export -- islamicart ISL EPM --force --publish --package-name @metanull/islamicart-data
```

```bash
cd scripts/exporters/baroqueart
npm run export -- --force --publish
```

```bash
cd scripts/exporters/sharinghistory
npm run export -- --force --publish
```

(The islamicart fork is the oldest: its subdirectory/project-key arguments
are required — the deployed package covers **both** `ISL` and `EPM` — and
its default package name is `@mwnf/…`, so `--package-name` is required too.
The baroqueart/sharinghistory forks default to the right values.)

A single `--publish` run does everything: auto-increments the patch version
persisted in `output/.version-<dataset>` (or use `--package-version` for an
explicit semver), generates `package.json`/`README.md` in the output
directory, and runs `npm publish` against GitHub Packages. There is **no**
separate manual `npm publish` step. Details in each dataset's
`NPM_PUBLISH.md`.

Publishing is where the exporter's job ends — consumers install the package
on their own schedule. (For updating the viewers in this repo after a
publish, see [`../viewers/README.md`](../viewers/README.md#deployment).)

Two gotchas, learned the hard way:

1. The published package must carry a `repository` field pointing at this
   repo (`PACKAGE_REPO_URL` in `.env`) **and** the repo must be granted read
   access in the package's *Manage Actions access* settings on GitHub
   (UI-only, one-time) — otherwise the deploy workflow's `GITHUB_TOKEN` gets
   403 on install. Versions published *before* the repo link stay
   inaccessible forever; publish a new version instead.
2. Version state lives in `output/.version-<dataset>`, which is not committed
   — deleting the output directory and republishing would restart at 1.0.0
   and collide with existing versions.

## Adding a new dataset

1. Copy the closest existing exporter directory to `scripts/exporters/<name>`.
2. Prune exporters the dataset doesn't need; adjust the CLI defaults
   (`[subdirectory]`, default project keys, `--package-name`).
3. If the project's exhibitions need a root marker, the importer's
   `project-exhibition-root-keying` step already creates one (with
   `purpose: exhibitions-root`) for every non-ISL project — run it
   standalone with `--only` (see `../importer/README.md`), no full re-import
   required. On a database populated before #1505, run
   `--only collection-purpose-backfill` once so all markers carry their
   `purpose`.
4. Validate counts against the legacy site/database before first publish
   (see `baroqueart/tools/legacy-validation.sql` for a worked example, and
   `.legacy-database/` for offline legacy dumps).
5. Publish, then create the matching viewer (see
   [`../viewers/README.md`](../viewers/README.md)).
