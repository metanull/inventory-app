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
- Exhibitions are resolved structurally from `collections.json`: children of
  the marker collection with `backward_compatibility`
  `mwnf3:exhibitions:root` (Islamic Art) or `mwnf3:exhibitions:root:<KEY>`
  (other projects; created by the importer's
  `project-exhibition-root-keying` step). Themes are an exhibition's
  children, pages a theme's children.

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

## Deployment

Each viewer has a GitHub Actions workflow (`workflow_dispatch`) that installs
`@metanull/<dataset>-data@latest`, builds with `--base=/<dataset>/`, and
copies `dist/` to `/opt/<dataset>/` on the OVH VPS over SSH. Nginx serves it
from an alias block in `/etc/nginx/sites-available/inventory`:

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
