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

Exhibitions are identified structurally, not by type: children of the marker
collection `mwnf3:exhibitions:root` (Islamic Art),
`mwnf3:exhibitions:root:<KEY>` (other mwnf3 projects — created by the
importer's `project-exhibition-root-keying` step), or
`mwnf3_sharing_history:sh_exhibitions:root:<key>` (Sharing History —
`sh-exhibition-root-keying` step, lowercase keys). Exhibitions whose legacy
`show` flag is `'n'` (preserved in
`collection_translations.extra.legacy_exhibition.show`) are excluded from the
package, as the legacy sites never listed them.

## Running

```bash
cd scripts/exporters/<dataset>
cp .env.example .env   # if present — DB_* (inventory DB), BASE_URL, PACKAGE_REPO_URL
npm install
npm run export -- --force            # export only
npm run export -- --force --publish  # export + version bump + npm publish
npm test                             # unit tests
```

The CLI also accepts `[subdirectory] [project-keys...]`, `--output-dir`,
`--base-url`, `--package-name`, `--package-version` and `--npm-registry`; run
`npm run export -- --help` for the authoritative list. The production
database is usually reached through an SSH tunnel (see the per-dataset
README); exports are **read-only** but always check what `.env` points at
before running.

## Publishing (GitHub Packages)

`--publish` auto-increments the patch version persisted in
`output/.version-<dataset>`, generates a `package.json` in the output
directory, and runs `npm publish` against `https://npm.pkg.github.com/`
(authentication via `~/.npmrc`). Details in each dataset's `NPM_PUBLISH.md`.

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
   `project-exhibition-root-keying` step already creates
   `mwnf3:exhibitions:root:<KEY>` for every non-ISL project — run it
   standalone with `--only` (see `../importer/README.md`), no full re-import
   required.
4. Validate counts against the legacy site/database before first publish
   (see `baroqueart/tools/legacy-validation.sql` for a worked example, and
   `.legacy-database/` for offline legacy dumps).
5. Publish, then create the matching viewer (see
   [`../viewers/README.md`](../viewers/README.md)).
