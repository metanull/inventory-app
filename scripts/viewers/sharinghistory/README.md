# Sharing History Viewer

A Vue 3 single-page application rendering the Sharing History data-package
(`@metanull/sharinghistory-data`). It serves two purposes:

1. A visual verification tool for the owner to assert that the data-package
   produced by the exporter is correct.
2. A model/blueprint for frontend developers building products on top of a
   data-package.

It is a full multi-view application (Vue Router: Home, Database
search/results, Item detail, Permanent Collection, Timeline, Historical
Background, Partners, Exhibitions with themes and chapters, …), not a
single-component sample.

## Differences from the baroqueart viewer

This viewer was forked from `scripts/viewers/baroqueart/` and adapted for
Sharing History — Arab World – Europe (legacy SH project key `awe`,
lowercase keyspace):

- **Chapters** — SH exhibitions are 3-level (exhibition → theme → subtheme,
  "Chapter" in the legacy UI). Theme pages list their chapters; the new
  `ExhibitionChapter` view renders each chapter's introduction, quotation,
  see-also / further-reading blocks and item grid with the
  **curator-vs-partner justification** panel.
- **Historical Background** — SH-only feature: a general introduction plus
  per-country multi-page illustrated historical profiles (with related
  items and bibliography), resolved structurally from the
  `sh_countries_historicalbackground` collections.
- **Timeline** — SH timelines are per (country × exhibition); the results
  view filters by both and offers the legacy
  thematic-vs-**Permanent-Collection** toggle (PC = the timelines the
  exporter ships with `collection_id: null`, legacy hidden sentinel
  exhibition 2). Event images and linked items are rendered.
- **`display_status`** — the ~462 items legacy kept only to illustrate
  HB/timeline pages (`display_status: 'N'`) are excluded from Database
  search and Permanent Collection browsing (`publicItems`), mirroring
  legacy.
- **Partners** — a single country-grouped list (no museum/institution
  split) with the Associated-partners accordion (from `level`). Requiring a
  name translation and a country reproduces the live legacy list of 114
  partners exactly.
- **Exhibitions** are anchored at the
  `mwnf3_sharing_history:sh_exhibitions:root:awe` marker collection.
- **Palette** — the legacy oxblood red + gold scheme (header `#750101`,
  nav/footer `#990000`, deep `#500101`, rules `#AF2626`, golds
  `#FFC000`/`#E0B700`/`#FEBE40`, warm ivory `#F0EDE4`), centralized in the
  `:root` CSS variables in `App.vue`; Roboto typography.

## Structure

Same layout as the sibling viewers: `src/views/*` one component per route,
`src/composables/useInventoryData.js` as the single data access layer
(module-level singletons over the statically imported package JSON, lazy
per-language translation loading), `src/router/index.js` with hash-based
routing.

## Development

```bash
npm install          # requires ~/.npmrc auth for the @metanull scope
npm run dev          # Claude Code: launch.json entry "sharinghistory-viewer", port 4175
npm run build        # production build into dist/
```

To pick up a new data-package version: `npm install
@metanull/sharinghistory-data@latest`, then restart the dev server (Vite
pre-bundle cache).

## Deployment

`.github/workflows/deploy-viewer-sharinghistory-ovh.yml` builds with
`--base=/sharinghistory/` and deploys `dist/` to `/opt/sharinghistory/` on
the OVH VPS; Nginx serves it at https://inventory.metanull.eu/sharinghistory/
via an alias block. See [`../README.md`](../README.md) for the shared
deployment mechanics and GitHub Packages access prerequisites.
