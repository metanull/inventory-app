# Dataset Viewers

One viewer per public website ("dataset"): a lightweight, standalone Vue 3 +
Vite single-page application that consumes its dataset's static JSON
data-package (`@metanull/<dataset>-data`, produced by the matching exporter —
see [`../exporters/`](../exporters/README.md)) at **build time**. The
deployed site is pure static files: no database, no Laravel API, no server
runtime.

```
@metanull/<dataset>-data ──(npm install + vite build)──▶ dist/ ──(GitHub Actions)──▶ https://inventory.metanull.eu/<dataset>/
```

## Datasets

| Directory | Package consumed | Live URL | Deploy workflow |
|---|---|---|---|
| [`islamicart/`](islamicart/README.md) | `@metanull/islamicart-data` | https://inventory.metanull.eu/islamicart/ | `.github/workflows/deploy-viewer-islamicart-ovh.yml` |
| [`baroqueart/`](baroqueart/README.md) | `@metanull/baroqueart-data` | https://inventory.metanull.eu/baroqueart/ | `.github/workflows/deploy-viewer-baroqueart-ovh.yml` |
| [`sharinghistory/`](sharinghistory/README.md) | `@metanull/sharinghistory-data` | https://inventory.metanull.eu/sharinghistory/ | `.github/workflows/deploy-viewer-sharinghistory-ovh.yml` |
| [`amulets/`](amulets/README.md) | `@metanull/amulets-data` *(not published yet — see below)* | https://inventory.metanull.eu/amulets/ *(not deployed yet)* | `.github/workflows/deploy-viewer-amulets-ovh.yml` |

The first three viewers are **verification tools** for their packages. The
fourth, `amulets`, is the first of the DXA gallery rebuilds
([epic #1539](https://github.com/metanull/inventory-app/issues/1539)): a
faithful reproduction of a public legacy website
(<https://amulets.museumwnf.org>), reproducing its routes, page structure,
facet behaviour and palette rather than reinterpreting them. Its UI strings
come from [`../site-i18n`](../site-i18n/README.md), never from the data
package.

Its data package is **not published**, so it resolves the exporter's local
output instead — see
[`amulets/README.md`](amulets/README.md#where-the-data-comes-from) for how,
and for the two edits publishing will require.

Each directory is a self-contained Node project. Viewers are **forked per
dataset by design** (same decision as the exporters): each website has its
own feature set (Islamic Art has dynasties and artistic introductions;
Baroque Art doesn't), its own branding and color scheme (Islamic Art golds,
Baroque Art blues — palettes taken from the legacy sites' CSS in
`.legacy-code/`), and is expected to diverge. Cross-cutting fixes must be
ported between forks explicitly.

## How a viewer finds its data

- All data is imported from `@inventory-data/…` (a Vite alias for the
  installed data-package). Entity files are imported statically; per-language
  translation files are loaded lazily.
- English translation files are loaded through `import.meta.glob` rather than
  literal imports: which `translations/<entity>.en.json` files exist varies
  by dataset/export, and a literal import of an absent file fails the build.
- Section anchors are resolved from `collections.json` via the `purpose`
  field (#1505): exhibitions are the children of the collection with
  `purpose: "exhibitions-root"`, the Islamic Art artistic introduction hangs
  off `purpose: "artistic-introduction-root"`, Sharing History additionally
  uses `historical-background-root` / `topics-root` /
  `historical-profiles-root` / `national-context`. Themes are an
  exhibition's children, pages a theme's children. The
  `backward_compatibility` keys in the data are informational only — no
  viewer parses them.
- **The gallery viewers are the exception on that last point.** DXA gallery
  sites keep the legacy dbUid *in the public URL*
  (`/database-item/mwnf3/objects/EPM/uk/Mus21/41/en`), so `amulets` does read
  `backward_compatibility` — it is the item's public identity there, not an
  implementation detail. It also anchors its site metadata on `gallery.json`
  rather than a `purpose` marker, since a gallery package has no
  `collections.json`.

## Development

```bash
cd scripts/viewers/<dataset>
npm install
npm run dev       # Vite dev server (Claude Code: .claude/launch.json has
                  #   per-viewer entries, e.g. baroqueart-viewer on port 4174)
npm run build     # production build into dist/
```

To pick up a new data-package version locally:

```bash
npm install @metanull/<dataset>-data@latest
```

then **restart the dev server** — Vite's dependency pre-bundle cache serves
the old package contents otherwise. Installing from GitHub Packages requires
a `~/.npmrc` with a token that can read the `@metanull` scope.

`amulets` is wired differently while its package is unpublished: it falls back
to `scripts/exporters/amulets/output/amulets`, so "picking up new data" there
means re-running the exporter. See its README.

## Deployment

Each viewer has a GitHub Actions workflow that builds and ships it to the
OVH VPS. It triggers on **any push to `main` touching
`scripts/viewers/<dataset>/**`**, and on **manual dispatch**
(`gh workflow run deploy-viewer-<dataset>-ovh.yml`, or the Actions UI). The
workflow runs `npm ci`, then **always installs
`@metanull/<dataset>-data@latest`** — deliberately overriding whatever the
lockfile pins, so a deploy always reflects the newest published data —
builds with `--base=/<dataset>/`, and copies `dist/` to `/opt/<dataset>/`
over SSH. Nginx serves it from an alias block in
`/etc/nginx/sites-available/inventory`:

```nginx
location /<dataset> {
    alias /opt/<dataset>;
    index index.html;
    try_files $uri $uri/ /<dataset>/index.html;
}
```

The workflow's `GITHUB_TOKEN` can only install the package if the package is
linked to this repo and granted Actions access — see the publishing gotchas
in [`../exporters/README.md`](../exporters/README.md).

### Shipping a content (data-only) update

1. Re-export and publish the data-package — one `npm run export -- --force
   --publish` command per dataset; see
   [`../exporters/README.md`](../exporters/README.md#build--publish-a-data-package-update-end-to-end).
2. Dispatch the viewer's deploy workflow:
   `gh workflow run deploy-viewer-<dataset>-ovh.yml`. Nothing needs to be
   committed — the workflow installs `@latest` regardless of the lockfile.
3. Verify the live URL (hard-refresh: the built JS bundle hash changes).

The viewers' `package.json` pins (`^x.y.z`) only govern **local
development**; keep them bumped to the latest published version so a fresh
`npm install` + `npm run dev` matches what production shows.

## Adding a new dataset

1. Copy the closest existing viewer to `scripts/viewers/<name>`; swap the
   data-package dependency and the exhibitions marker key.
2. Prune views/routes the dataset doesn't have; rebrand (title, nav, color
   scheme — extract the palette from the legacy site's CSS under
   `.legacy-code/<dataset>/`, and keep it centralized in the `:root` CSS
   variables in `App.vue`).
3. Add a dev-server entry to `.claude/launch.json` (unique port).
4. Copy and adapt the deploy workflow; add the Nginx alias block on the
   server; dispatch and verify the live URL.
5. Add an `npm` entry for the new directory to
   [`.github/dependabot.yml`](../../.github/dependabot.yml) — **this one is
   maintained by hand**. Dependabot config is static YAML with no scripting,
   so unlike the `Dependency Audit` matrix it cannot enumerate
   `scripts/viewers/`. A viewer entry needs `registries: [npm-github]`,
   because it installs `@metanull/<dataset>-data` from GitHub Packages.
   Nothing fails if you forget; the viewer simply never receives dependency
   updates or security alerts.
