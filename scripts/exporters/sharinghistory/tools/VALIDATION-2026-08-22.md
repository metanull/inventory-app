# Sharing History data-package validation — 2026-08-22

Validation of the first `sharinghistory` export (production inventory DB,
read-only) against the legacy ground truth: the offline dumps in
`.legacy-database/` and the live site (sharinghistory.museumwnf.org).
Queries: [`legacy-validation.sql`](legacy-validation.sql). Method follows the
baroqueart validation (2026-08-21).

## Counts: legacy expected vs exported

| Metric | Legacy (dumps) | Exported | Verdict |
|---|---|---|---|
| Objects | 2,420 (100% AWE) | 2,420 `type='object'` | ✅ exact |
| Monuments | 216 | 216 `type='monument'` | ✅ exact |
| `display_status` N (HB/HCR-only) | 453 obj + 9 mon = 462 | 462 `display_status:'N'` | ✅ exact |
| Published AWE exhibitions | 10 (ids 1, 3–11; id 2 `show='n'`) | 10 `type='exhibition'` | ✅ exact (live site nav lists exactly ids 1,3–11) |
| Themes | 47 (5 under hidden exh. 2) | 42 | ✅ (5 excluded with their hidden exhibition — legacy never showed them) |
| Subthemes ("Chapters") | 140 (none under exh. 2) | 140 `type='subtheme'` | ✅ exact |
| Partners | 120 | 120 (`level`: 88 `associated_partner` / 32 `partner`) | ✅ exact; tier split = `sh_partner_associated` 88 |
| Timelines (country × exhibition groups) | 161 distinct groups over 1,509 `sh_hcr` rows | 161 | ✅ exact |
| — of which Permanent Collection (exh. 2) | 19 countries | 19 with `collection_id: null` | ✅ exact |
| Timeline events | 1,509 | 1,509 | ✅ exact |
| Historical Background | 19 AWE records (1 general + 18 profiles) + 63 pages | 83 collections (**20** records + 63 pages) | ⚠️ +1 — see finding 1 |
| Justification-bearing collection items | 2,310 theme/subtheme + 114 exhibition-level rows | 2,319 entries with `justifications`, 2,909 with `curator_status` | ✅ (per-relation rows collapse per (collection,item) pivot) |
| Glossary | no SH tables; usage via mwnf3 glossary | 184 entries (usage-scoped: 2,425 item + 105 collection + 267 timeline-event spelling links) | ✅ |
| Item text languages | en 2,420/216 + fr 389/44 | translations in 15 languages, en complete, fr partial | ✅ |
| Documents / media | 23 PDFs + 2 video/audio | in `items.json` `media[]` | ✅ |

## Live-site spot checks

1. **Exhibition list** — `exhibitions/AWE/index.php` links exactly
   `eId=1,3,4,5,6,7,8,9,10,11` ↔ the 10 exported exhibitions. ✅
2. **Object `object;AWE;at;4`** — live: "Ratification document of the
   Congress of Vienna (29 September 1814 – 9 June 1815)", Vienna, Austrian
   State Archives ↔ package: identical name, `location: Vienna`,
   `display_status: A`, 1 image. ✅
3. **Monument `monument;AWE;lb;1`** — live: "Traditional house", Ashrafiyye,
   Lebanon, 19th century ↔ package: identical name/location. ✅
4. **Partner list** — live page ground truth = **114 partners (27 main +
   87 associated)**, captured with ids in
   `tmp/sharinghistory-partner-list-legacy-2026-08-21.md`. The package
   carries all 120 DB partners; the viewer reproduces the 114 by requiring a
   name translation (same INNER-JOIN semantics as legacy — the ~6 extras
   have no English name / unlisted country). ✅ (viewer assertion re-checked
   in the viewer story)

## Findings & dispositions

1. **USA Historical Background record leaks into awe** —
   `sh_countries_historicalbackground` hb_id=20 ("Historical Profile /
   Germany") belongs to the placeholder USA project, but the importer
   hard-codes the awe parent (`sh-bibliography-hb-importer.ts:505`), so all
   20 records sit in the awe context (verified on production). Legacy AWE
   showed 18 country profiles + 1 general text. **Disposition: importer bug,
   low harm (one extra real profile) — shipped in v1, tracked in #1494.**
2. **Partner categories collapsed** — legacy `sh_partners.partner_category`
   (museums/archives/universities/libraries/heritage-authorities/others) was
   mapped to the inventory's binary `museum`/`institution` partner type
   (67/53). **Disposition: intentional — the legacy Partners page groups by
   country, never by category; nothing user-visible is lost.**
3. **Author CVs omitted** — author names + roles are exported per item
   translation (`author`, `copy_editor`, `translator`,
   `translation_copy_editor`); the 5 `sh_authors_cv` biographies are not.
   **Disposition: intentional v1 scope — negligible content; revisit if the
   CV tooltip is wanted.**
4. **Orphans tolerated** — legacy exhibition 18 (USA, `show='y'`, empty
   name, no names row) is excluded by project scoping; theme 26 (no name
   row) exports with its fallback internal name and no title translation —
   viewers must tolerate a title-less theme.
5. **Deferred scope (epic decision)** — About/Team/Chronology module,
   sponsors, `myexhibitions` (dormant), per-exhibition DB theming, DOC/PDF
   exports are not in the package; recorded in Epic #1482, not silent.
6. **Data source note** — the five importer enrichment steps were run
   against production using the committed `.legacy-database` dumps as the
   legacy side (the live legacy DB was unreachable); the dumps' SH counts
   match the SHARING_HISTORY_IMPORT_VALIDATION_REPORT figures exactly, and
   SH legacy content is frozen.

## Outcome

Counts and spot checks match legacy exactly except finding 1 (+1 HB record,
tracked). **Validation passed — `@metanull/sharinghistory-data` v1 cleared
for publish.** (A placeholder 0.0.1 was published 2026-08-22 to allow the
GitHub Packages repo-link/Actions-access grant ahead of time; v1.0.0 is the
first real release.)

## Addendum — 1.1.0 (2026-08-22, #1498)

The parity follow-up added the general Historical Background module (new
importer step `--only sh-hb-general`, run on production the same way as the
five steps above — local dump-loaded MariaDB as the legacy side):

| Check | Legacy | Package 1.1.0 | Status |
|---|---|---|---|
| Perspective pages (`sh_project_about_historical_background`, AWE) | 3 (Arab/Ottoman/European Perspective, en) | 3, under `…:sh_project_about_historical_background:root:awe` | ✅ exact |
| "Read more" topics (`sh_project_about_topics`, AWE) | 10 (en, **titles only** — legacy `about_hb_topics.php` popup renders the bare title; `desc` is empty for every row) | 10, under `…:sh_project_about_topics:root:awe`, titles only | ✅ exact |
| USA test project's topic row (topic 1, USA) | present in legacy table | excluded (`show='Y' AND category='SP'` scoping) | ✅ intended |
| Total collections | — | 340 (325 in 1.0.x + 15 new) | ✅ |

Timeline re-verification (user report, #1498): Austria × Cities and Urban
Spaces = exactly the legacy 8 events (1858–1910); Austria Political Context
= exactly the legacy 22 events (1797–1920) — package and deployed bundle
both match `hcr_result.php` row-for-row. The reported mismatch was traced
to a crashed local dev server, not to data.
