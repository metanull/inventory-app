# Baroque Art data-package validation — 2026-08-21

First validation of the `@metanull/baroqueart-data` export against the legacy
Discover Baroque Art data (`.legacy-database` dumps + legacy site behavior),
per the epic's validation story. Queries: [`legacy-validation.sql`](legacy-validation.sql).

Export run: `npm run export -- --force` against the production inventory DB
(read-only, via the OVH tunnel), after the `project-exhibition-root-keying`
importer step had been executed and DB-verified in production.

## Counts

| Entity | Legacy expected | Exported | Verdict |
|---|---|---|---|
| Objects | 304 (distinct PK minus lang) | 304 | **exact** |
| Monuments | 293 | 293 | **exact** |
| Monument details | 1362 raw; **1207 with a non-empty name** | 1207 | **intentional** — the importer skips name-less rows by policy (no synthesized titles); the 155 skipped legacy rows are empty-name stubs with no displayable content |
| Exhibitions | **8 published** (ids 43–51 minus 49 "Academia", which is `show='n'` and was never listed on the legacy site) | initially 9 (finding 4); 8 after the exporter fix, all under `mwnf3:exhibitions:root:BAR` (`e4318b5c-d592-53f9-a9fd-7f05d87c09e6`) | **exact after fix** |
| Timelines | 6 — legacy `hcr_home.php` lists timeline countries from **objects only** (`cz,de,hr,hu,it,pt`); Austria (`at`) has only monuments and never had a legacy timeline | 6 timelines, 356 events | **exact legacy behavior** |
| Partners, tier-1 (`level='partner'`) | 12 museums + 10 institutions | 12 museums + 10 institutions | **exact** |
| Partners, other tiers | associated/minor tables | 24 `associated_partner` + 1 `minor_contributor` (total 47) | consistent |
| Glossary | usage-scoped (tooltips on item pages) | 30 entries | plausible; usage-scoping means no direct legacy count |
| Languages | BAR object rows exist in 4 languages; other entities add more | 14 translation languages (`ar,cs,de,el,en,es,fr,hr,hu,it,pt,se,si,tr`); manifest lists 18 | multilingual as decided |
| Dynasty files | n/a (all legacy dynasties are ISL) | **absent** | by design |

## Spot checks

- Exhibition display titles (translations/collections.en.json) match the
  legacy site's exhibition names, e.g. legacy internal name `Ephemera` →
  exported title "Ephemera, Festivals and Theatrical Representation",
  `Bourgeoisie` → "The Ascension of the Bourgeoisie", `Enlightenment` →
  "The Age of Enlightenment" — all 9 titled and parented correctly.
- ISL data unaffected: `mwnf3:exhibitions:root` still has 18 children in
  production (verified both via the tunnel and directly on the OVH server).

## Findings and dispositions

1. **BAR timeline events have no translations** (importer gap — follow-up
   story filed). `timeline_event_translations` has 0 rows for the 6 BAR
   timelines' 356 events, so the package ships no
   `translations/timeline_events.*.json` and the viewer's timeline results
   show dates without period names. Legacy shows per-language period
   names/descriptions from `mwnf3.hcr_events`; the phase-05 BAR timeline
   path imports events + item pivots but not the event translations.
   Re-running that importer step and re-exporting will fix the labels — no
   exporter change needed.
2. **Viewer hard-imported `translations/timeline_events.en.json`** — a
   literal import of an absent file fails the build. Fixed in the viewer:
   English translation files are now loaded through `import.meta.glob`, so
   any legitimately-absent translation file resolves to an empty map. This
   also future-proofs other datasets whose translation coverage varies.
3. The two count deltas (details, timeline countries) are explained above
   as intentional importer policy and exact legacy behavior respectively —
   no follow-up needed.
4. **Unpublished exhibition leaked into the package** (found post-launch,
   fixed same day). Legacy `mwnf3.exhibitions` row 49 "Academia,
   Universities, Sciences" has `show='n'` — the legacy site filtered on
   `show='y'` so it was never listed (it has 3 themes but zero object
   links). The importer rightly preserves it (with the flag in
   `collection_translations.extra.legacy_exhibition.show`); the collection
   exporter now excludes `show='n'` exhibitions and their theme/page
   subtrees, bringing the package to the 8 exhibitions the legacy site
   actually displayed.

## Viewer verification (real published data, dev server)

Home (random BAR item), Database results (1804 items), Item detail (full
record incl. images/credits/metadata), Exhibitions (all 9), Timeline
entrance (the 6 legacy countries) and results (56 events for cz, correct
dates; names pending finding 1), Partners (28 museums + hierarchy, 19
institutions) — all render with zero console errors.

## Outcome

Validation passed → `@metanull/baroqueart-data@1.0.1` published to GitHub
Packages (first publish, 2026-08-21). Timeline event labels arrive with the
follow-up importer fix + re-export.
