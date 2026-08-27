# Amulets Exporter

Reads the `inventory-app` database directly and writes a set of denormalized,
static JSON files for the rebuilt **Amulets and Talismans** website — no API
server, no auth, no runtime database dependency. Optionally packages and
publishes that output as a private npm package (`@metanull/amulets-data`) on
GitHub Packages.

This is the first of the DXA gallery exporters
([epic #1539](https://github.com/metanull/inventory-app/issues/1539),
[story #1542](https://github.com/metanull/inventory-app/issues/1542)). It
replaces one legacy deployment of `dxa-api` + `dxa-client`:
<https://amulets.museumwnf.org>. The package specification it implements is
[`../docs/dxa-gallery-data-package.md`](../docs/dxa-gallery-data-package.md);
the legacy behaviour it reproduces is analyzed in
[`../docs/dxa-legacy-analysis.md`](../docs/dxa-legacy-analysis.md).

## What makes a gallery exporter different

The three earlier exporters (islamicart, baroqueart, sharinghistory) each
export a **project**. This one exports a **collection**, and almost everything
else follows from that.

- **The item universe is a membership union, not a project.** Legacy computed
  visibility as an OR predicate — items of the gallery's own mwnf3 project OR
  items listed in the six `thg_gallery_*` link tables. The importer
  materializes that union in `collection_item` ([#1517](https://github.com/metanull/inventory-app/issues/1517),
  gap G1), so the exporter reads it as a plain join.
- **Amulets owns none of its content.** All 45 objects are borrowed: 24 from
  EPM, 13 from ISL, 5 from Sharing History and 3 from DCA. The source project
  shown on each sheet, and the context that selects a record's canonical
  translation, are therefore resolved **per item** rather than fixed per
  export.
- **EPM is the short description.** Legacy keeps the short text in
  `objects.description2`, which the importer files as a translation in the EPM
  context (`planTranslations` in `object-transformer.ts`). So the EPM-context
  row is the short description for every item — including EPM-native items,
  whose only row is that one and whose long description is legitimately empty.
- **Facet tags and sheet tags are different things.** `item_tag` carries both
  the THG tags that drive the five search facets (English-only, `thg:tags:*`)
  and the per-language `keyword`/`material` tags parsed out of the object
  records. `tags.json` holds the first; the second ship as `keywords` and
  `materials` inside the item translations. Mixing them would put 188
  unfaceted values into the Material dropdown on amulets alone.
- **Outbound links are references, never URLs** (decision Q3). Sibling
  galleries, "also on display in" galleries and related items outside the
  package are exported as identity plus whatever metadata the import carried
  (slug, legacy host, project key, `backward_compatibility`). Legacy resolved
  these from a manually-maintained table that has no counterpart in the new
  model, so an exporter cannot build the URLs — but it must not drop the
  links either. Related items carry `in_package` so the viewer knows which it
  can open locally.
- **The timeline is not gallery-specific.** Legacy serves it from `mwnf3.hcr`,
  keyed by country and independent of any project, which is why the live
  amulets site answers `/events/countries` with the worldwide list even though
  its `hasCountryBasedTimeline` flag is false. Every gallery package carries
  the same 18 per-country timelines (1,075 events).
- **`featured` is inverted, on purpose.** dxa-api computes
  `CASE WHEN featured = 'A' THEN 0 ELSE 1 END`, so `'A'` means *not* featured.
  Amulets stores `'H'` and the live API reports `featured: true`. See
  `isFeatured` in `src/exporters/gallery-exporter.ts` and its tests.

## Package contents

| File | Contents |
|---|---|
| `manifest.json` | Export metadata, the gallery's UI languages, item count |
| `gallery.json` | Site anchor: slug, legacy host, names, banner/homepage item, chrome flags, sibling galleries |
| `items.json` | The 45 member items — full sheets, facet tag ids, images, references |
| `tags.json` | 115 THG facet tags with their category (artist/dynasty/material/subject/type) |
| `partners.json` | The 26 museums holding member items, with `featured` and `item_count` |
| `countries.json` | The 19 countries the members and their holders reference |
| `languages.json` | The 9 languages the site can display, flagged `site_language` |
| `dynasties.json` | The 10 dynasties member items reference |
| `glossary.json` | The 80 terms reachable from member item texts, with spelling lists |
| `timelines.json` / `timeline_events.json` | The 18 global country timelines |
| `translations/<entity>.<lang>.json` | All human-readable text, one file per entity per language |

Entity files hold language-independent data; every human-readable string lives
under `translations/`. Image URLs are absolute, built from `BASE_URL`. A file
is absent when that entity has no translation in that language — viewers must
tolerate this.

Gallery chrome images (`image_path`, `banner_image_path`) are the exception to
the absolute-URL rule: they live on the legacy media server and were never
imported, so the package carries the legacy path only and the viewer supplies
the host, exactly as the legacy client did through `VUE_APP_IMAGES_URL`.

## Run

The exporters run inside Docker; there is no host-side Node tooling.

```bash
docker compose --profile jobs run --rm exporter amulets --force
```

Add `--publish` to bump the version, generate `package.json`/`README.md` and
push to GitHub Packages — see [`NPM_PUBLISH.md`](NPM_PUBLISH.md).

The compose service points at the **staging** database
(`staging-mysql`), which is where the exporter should be developed and
verified. `scripts/exporters/amulets/.env` is only consulted when running
outside compose, and by convention those files point at **production** — read
it before running anything that way.

## Naming

The folder and package are `amulets` / `@metanull/amulets-data` — the site's
public identity (`amulets.museumwnf.org`) and the name fixed by decision Q4.
The legacy slug is `amulets_and_talismans`, and that is what `gallery.json`
carries as `slug`: data values keep legacy identity verbatim, folder names do
not.

## Known gaps

Verified during implementation, none blocking:

- **EPM author attribution.** Legacy shows `preparedBy`/`copyEditedBy` on the
  English sheet; the importer files those names only on the Arabic row for EPM
  items, so `author`/`copy_editor` are missing from the English translations
  of EPM-native records. Importer-side, not exporter-side.
- **`notice` / `notice_b` / `notice_c`** (the copyedit notices on the legacy
  sheet) are not imported at all. Empty on every amulets item.
- **Museums with no items.** Legacy's partner list has a third branch
  (MWNF-384): museums created in the gallery's own project even when they hold
  nothing. It contributes nothing here — no `mwnf3.museums` row has
  `project_id = 'AMU'` — but a gallery whose native project owns museums will
  need it, and `partners.project_id` is null for every imported museum, so
  that fork needs an importer change first.
- **Legacy's two hardcoded partner exclusions** (`uk/Mus51`, `us/Mus51`, in
  `Partners.blade.php`) are not reproduced; neither holds an amulets member.

## Validation

[`tools/VALIDATION-2026-08-27.md`](tools/VALIDATION-2026-08-27.md) records the
export checked field by field against the live legacy API.
