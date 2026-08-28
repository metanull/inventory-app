# NPM Package Publishing Guide

How `--publish` turns an export into a released version of
`@metanull/the-use-of-colours-in-art-data` on GitHub Packages. What the package *contains* is
documented in [`README.md`](README.md#package-contents); this guide covers the
publishing mechanics only.

## Quick start

```bash
docker compose --profile jobs run --rm exporter the-use-of-colours-in-art --force --publish
```

That single run does everything:

1. Exports the gallery to `output/the-use-of-colours-in-art/`
2. Bumps the patch version persisted in `output/.version-the-use-of-colours-in-art`
   (1.0.0 → 1.0.1, …)
3. Generates `package.json` and a consumer `README.md` inside `output/the-use-of-colours-in-art/`
4. Runs `npm publish` from that directory against GitHub Packages

There is **no separate manual `npm publish` step** — running one after
`--publish` would fail as a duplicate version.

## Version management

The version counter lives in `output/.version-the-use-of-colours-in-art` — deliberately
*outside* the package directory, so `--force` (which deletes and recreates
`output/the-use-of-colours-in-art/`) does not reset it.

- Each `--publish` run auto-increments the patch component and persists the
  result.
- `--package-version <semver>` sets an explicit version instead (it is also
  persisted, so subsequent auto-increments continue from it).

⚠ The whole `output/` directory is **gitignored**, version file included. If
it is lost (fresh clone, deleted output directory), the next `--publish` would
restart at 1.0.0 and collide with already-published versions — recover by
passing `--package-version` with the next free version (check the published
versions on GitHub Packages first).

## Package structure

The published package is the output directory itself:

```
output/the-use-of-colours-in-art/
├── package.json          ← generated on every --publish run
├── README.md             ← generated consumer usage guide
├── manifest.json
├── gallery.json, items.json, tags.json, partners.json, …   (see README.md)
└── translations/
    └── <entity>.<lang>.json
```

The `files` allow-list in the generated `package.json` restricts the publish to
`*.json`, `translations/*.json` and `README.md` — the `.json.gz` companions
written by the export stay local and are never published.

## Package metadata

The generated `package.json` carries:

- **name** — `@metanull/the-use-of-colours-in-art-data` (hardcoded in the exporter CLI)
- **version** — from the version file (see above)
- **description** — names the exported gallery slug
- **exports** — `manifest.json` as the entry point, plus every top-level
  `*.json` and `translations/*`
- **author / license / repository** — from `PACKAGE_AUTHOR`, `PACKAGE_LICENSE`
  and `PACKAGE_REPO_URL` in `.env`. Keep `PACKAGE_REPO_URL` set: without a
  `repository` field, consumers authenticating with a GitHub Actions
  `GITHUB_TOKEN` cannot install the version, and already-published versions
  cannot be fixed retroactively.

## GitHub Packages authentication

Publishing goes to `https://npm.pkg.github.com` (override with
`--npm-registry` or the `NPM_REGISTRY` env var). Configure `~/.npmrc` with a
personal access token that has the `write:packages` scope:

```bash
echo "//npm.pkg.github.com/:_authToken=YOUR_GITHUB_TOKEN" >> ~/.npmrc
echo "@metanull:registry=https://npm.pkg.github.com" >> ~/.npmrc
```

See the [GitHub Packages npm documentation](https://docs.github.com/en/packages/working-with-a-npm-registry/working-with-the-npm-registry#authenticating-with-a-personal-access-token)
for details. The package is private: consumers need a token with
`read:packages` to install it.

## Consumer usage

```bash
npm install @metanull/the-use-of-colours-in-art-data
```

```javascript
import manifest from '@metanull/the-use-of-colours-in-art-data/manifest.json' assert { type: 'json' }
import gallery from '@metanull/the-use-of-colours-in-art-data/gallery.json' assert { type: 'json' }
import items from '@metanull/the-use-of-colours-in-art-data/items.json' assert { type: 'json' }

// Lazy-load translations for a language
const { default: t } = await import(`@metanull/the-use-of-colours-in-art-data/translations/items.${lang}.json`)
```

All data files sit at the package root (there is no `data/` directory);
per-language translation files live under `translations/`.

## Troubleshooting

**`npm publish` fails with "not authorized"** — `~/.npmrc` is missing or the
token lacks `write:packages`; see the authentication section above.

**"cannot publish over previously published version"** — that version already
exists on the registry (e.g. the version file was reset). Pass
`--package-version` with the next free version, or simply re-run `--publish`
to auto-increment past it.

**Version file lost** — do *not* just re-run `--publish` (it would restart at
1.0.0); see the version management section above.
