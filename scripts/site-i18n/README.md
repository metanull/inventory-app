# Site i18n extractor

Extracts the UI strings and editorial page content of a legacy DXA gallery or
exhibition website into [vue-i18n](https://vue-i18n.intlify.dev/) message files,
so a rebuilt site can be scaffolded with its text already in place.

Implements **THG G3** ([#1521](https://github.com/metanull/inventory-app/issues/1521)),
a sub-story of [#1517](https://github.com/metanull/inventory-app/issues/1517).

## Why this is not an importer

Legacy DXA sites keep per-site editorial pages (`galleryAbout`, `galleryCredits`,
`galleryPartners`, `searchHowTo`) and UI labels in `mwnf3.translation`, grouped by
`group_id`. The decision recorded on #1517 is that **these do not belong in the
inventory model**: they are website copy, not inventory data — they describe a
particular website's chrome, not an object, a collection or a partner.

So they travel legacy → website repo and stop there. This tool has no connection
to the inventory database and no write path of any kind; every statement it
issues against the legacy database is a `SELECT`.

## Setup

```bash
cd scripts/site-i18n
npm install
cp .env.example .env    # then fill in the legacy credentials
```

The variables are the same names the importer uses, so `scripts/importer/.env`
can be copied verbatim. Reaching the live legacy server normally requires the
VPN to be up.

## Usage

List the registry — which sites exist, and which i18n groups each is registered
against:

```bash
npm run list
```

```bash
npm run list -- --hidden
```

Extract one or more sites. A selector is a gallery id, a slug, or an mwnf3
project code, so `9`, `carpets` and `DCA` all name the same site:

```bash
npm run extract -- carpets
```

```bash
npm run extract -- 9 amulets EXHCOLOUR --force
```

```bash
npm run extract -- --all --force
```

## Output

```
output/
  extraction-report.md         what the run did, across every site
  <slug>/
    site.json                  gallery id, project, slug, host, i18n groups
    i18n/
      index.json               locales, default and fallback locale, key counts
      en.json                  flat vue-i18n messages
      fr.json
      …
```

A locale file holds exactly the messages legacy has in that language. It is not
padded with English: `index.json` names `en` as the `fallbackLocale`, and
vue-i18n resolves the rest — the same arrangement the legacy client used. Padding
would turn "this string was never translated" into "this string is English on
purpose", which is not a distinction worth destroying.

Keys are sorted, so re-running against unchanged legacy data produces a
byte-identical file and a diff shows only real edits.

## Values are Markdown, not HTML

The legacy strings are HTML fragments — the legacy client renders them straight
into the page with `v-html`. Markup is not an accepted content format anywhere in
this project: the importer converts every legacy string to Markdown before it
reaches the database, and these strings follow the same rule. The Turndown
configuration is identical to
[`scripts/importer/src/utils/html-to-markdown.ts`](../importer/src/utils/html-to-markdown.ts),
so the same fragment yields the same Markdown whichever pipeline it travels
through.

In practice the legacy markup is narrow — `<br>`, `<b>`, `<i>`, `<a>` and a
handful of `<span>`s — and converts cleanly. A scaffolded site renders these
values through a Markdown component rather than `v-html`.

## How the merge works

Each site is registered against two groups: its own, and a common group shared
across sites (59 for every gallery and every exhibition but one). The legacy DXA
API merges them in `Translations.blade.php`:

```sql
from (select * from translation where group_id = :groupId) t_specific
right join (select * from translation where group_id = :commonGroupId) t_common
  on (t_common.word_id = t_specific.word_id and t_common.lang_id = t_specific.lang_id)
```

The `RIGHT JOIN` makes the **common group** define the whole key × language
universe: a site-specific string whose (key, language) pair has no counterpart in
the common group is silently discarded before it ever reaches a browser.

**This extractor merges as a union instead** — common group as the base, site
group overriding pair by pair and free to add pairs of its own. That is a
deliberate deviation, and it recovers **120 messages across the 48 sites**:

| Key | Messages recovered | Why legacy drops them |
| --- | --- | --- |
| `goToFullSearch` | 108 | Every gallery has it in Arabic, Spanish and French; the common group carries that key in English only. |
| `Search Related Database` | 4 | A capitalised, spaced variant of `searchRelatedDatabase` used by gallery 45. |
| `Footer_logo_section_1`, `Footer_logo_section_2` | 6 | Exhibitions 52, 55 and 56 capitalise the `F`; the common group does not. |
| `galleryAbout`, `galleryNewPartners` | 2 | Gallery 45 only. |

Every message recovered this way is listed in `extraction-report.md`, per site,
so the deviation is auditable rather than invisible. Nothing is lost relative to
legacy: the union is a superset.

Rows with an empty `word_id` (legacy junk that no client can address) and values
that are empty after conversion are dropped, and both are counted in the report.

## The registry

`mwnf3_thematic_gallery.thg_gallery` is the registry of record. It carries
`i18n_group_id`, `i18n_common_group_id`, `link` (the slug) and
`mwnf3_project_id` for all 48 galleries and exhibitions, and
`thg_gallery_url.link` carries the canonical host. This is the same anchor the
importer writes to `collections.extra.thg_gallery`
([#1520](https://github.com/metanull/inventory-app/issues/1520)).

The legacy deployment scripts keep a second, partial copy of the mapping in
`E:\mwnf-server\apps\<site>\api\environment\config.sh` (`sites`/`projects`/
`themes`/`i18ns` parallel arrays). Prefer the database: `config.sh` lists 38
gallery instances and knows nothing about the six exhibitions, Precious Stones,
Historical Cars or the hidden galleries. Run `npm run list` rather than reading
either by hand.

The two sources agree everywhere except one entry, and there `config.sh` is
right:

| Site | `thg_gallery` | `config.sh` | Reality |
| --- | --- | --- | --- |
| Portraits (gallery 31, POT) | group 63 | group 45 | Group 63 has no rows at all; group 45 has the expected 5 keys × 4 languages. Extract it with `--group 45`. |

### Known registry damage

`npm run extract` warns about each of these as it runs, and `--all` completes
regardless — a damaged entry costs that site its own strings, not the run.

As of 2026-08-25, five of the 48 sites warn:

| Site | Problem |
| --- | --- |
| 31 Portraits (**active**) | Registered against group 63, which has no rows. `config.sh` says 45, and 45 has the expected content: extract it with `--group 45`, which restores a gallery-specific `galleryAbout` and `galleryCredits`. |
| 15 Curiosities (H), 42 Unclear (H), 43 Doubts (H), 44 Excluded (H) | Registered against groups 69 and 56, which have no rows, so each falls back entirely to the common group. All four are hidden, so this is likely deliberate. Three also have no `thg_gallery_url` row. |

A site whose `i18n_common_group_id` is `NULL` warns too — the legacy API binds
NULL into its join and serves that site nothing at all. Gallery 56 was in that
state when this tool was written and has since been repaired in the legacy data;
the check stays because the shape can recur.

Gallery 55's slug contains a colon
(`lost_memories_along_the_hijaz_railway:_from_istanbul_to_mecca`), which is legal
in a URL and illegal in a Windows path, so its output directory drops the colon.
`site.json` always carries the true slug.

## Scaffolding a site

1. `npm run list` — find the site's gallery id and slug.
2. `npm run extract -- <slug> --force` — check the warnings it prints.
3. Read `output/extraction-report.md`: confirm the locale coverage is what you
   expect and look over what the legacy RIGHT JOIN was dropping.
4. Copy `output/<slug>/i18n/` into the new site repo's message directory, and
   `site.json` into whatever the scaffold uses for per-site configuration.
5. Wire vue-i18n with `fallbackLocale: 'en'` and render the page-content keys
   (`galleryAbout`, `galleryCredits`, `galleryPartners`, `searchHowTo`,
   `thg_about_text`, `txt*`) through a Markdown component.

The palette for a site lives separately, in
`dxa-client/src/sites/<key>/_variables.scss` — see #1510 for how that fits the
viewer-core / viewer-layout platform.

## Tests

```bash
npm test
```

The merge and registry logic are pure functions and are covered directly; the
cases are drawn from the shapes the legacy data actually contains, not invented
ones. Nothing in the test suite touches a database.
