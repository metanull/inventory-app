-- Sharing History data-package validation queries
-- ================================================
-- Run these against a MySQL/MariaDB instance loaded from the offline legacy
-- dumps in `.legacy-database/` (ddl/creation + data, one file per table).
-- Loading recipe (same gotchas as the baroqueart validation):
--   * strip NO_AUTO_CREATE_USER from any SET sql_mode line if the target
--     server rejects it,
--   * the dump files disable FOREIGN_KEY_CHECKS themselves,
--   * load ddl/creation/mwnf3_global_entities.sql FIRST — the sh_objects /
--     sh_objects_texts / sh_monuments / sh_monuments_texts DDL embeds
--     triggers that reference mwnf3.global_entities (empty table = no-op),
--   * load sh_objects_texts / sh_monuments_texts DDL even if you skip their
--     data — the triggers join them.
--
-- Each query is paired with the exported JSON figure it should match.
-- Everything is scoped to project AWE — the only public SH project
-- (sh_projects.show='Y' AND category='SP'). project_id casing is mixed in
-- legacy ('AWE' vs 'awe'); the *_general_ci collation makes = comparisons
-- case-insensitive.

USE mwnf3_sharing_history;

-- 1. Objects  →  items.json entries with type='object'         (expect 2420)
--    display_status split →  items.json display_status         (A 1967 / N 453)
SELECT display_status, COUNT(*) FROM sh_objects GROUP BY display_status;
SELECT COUNT(*) AS objects FROM sh_objects;

-- 2. Monuments  →  items.json type='monument'                  (expect 216; A 207 / N 9)
SELECT display_status, COUNT(*) FROM sh_monuments GROUP BY display_status;
SELECT COUNT(*) AS monuments FROM sh_monuments;

-- 3. Exhibitions  →  collections.json type='exhibition'
--    AWE published (show='y') = ids 1,3..11                    (expect 10 exported)
--    id 2 "Political Context" is show='n' → excluded, its timelines become
--    the Permanent Collection timelines (collection_id null).
--    ids 12-18 belong to USA/RUS (other contexts, never exported).
SELECT exhibition_id, project_id, `show`, name FROM sh_exhibitions ORDER BY project_id, exhibition_id;

-- 4. Themes  →  collections.json type='theme'                  (expect 42 exported)
--    47 total; the 5 themes of hidden exhibition 2 are excluded with it.
SELECT COUNT(*) AS themes_total FROM sh_exhibition_themes;
SELECT COUNT(*) AS themes_hidden FROM sh_exhibition_themes WHERE exhibition_id = 2;

-- 5. Subthemes ("Chapters")  →  collections.json type='subtheme' (expect 140)
--    None belong to exhibition 2's themes, so all 140 export.
SELECT COUNT(*) AS subthemes FROM sh_exhibition_subthemes;
SELECT COUNT(*) AS subthemes_under_hidden FROM sh_exhibition_subthemes st
  JOIN sh_exhibition_themes t ON t.theme_id = st.theme_id WHERE t.exhibition_id = 2;

-- 6. Partners  →  partners.json                                (expect 120)
--    Tier split → level: associated_partner (88) vs partner (32).
--    The RENDERED viewer list must equal the live-page ground truth:
--    114 partners (27 main + 87 associated) — see
--    tmp/sharinghistory-partner-list-legacy-2026-08-21.md; the ~6 extras
--    have no English sh_partner_names row / unlisted country and are hidden
--    by the name join, exactly like legacy.
SELECT COUNT(*) AS partners FROM sh_partners;
SELECT COUNT(DISTINCT partners_id) AS associated FROM sh_partner_associated WHERE project_id = 'AWE';
SELECT COUNT(DISTINCT partners_id) AS further_associated FROM sh_partner_further_associated WHERE project_id = 'AWE';

-- 7. Timelines  →  timelines.json                              (expect 161 = distinct country×exhibition groups)
--    Events → timeline_events.json                             (expect 1509)
--    Exhibition-2 groups → collection_id null ("PC timelines") (expect 19)
SELECT COUNT(DISTINCT country, exhibition_id) AS timeline_groups FROM sh_hcr;
SELECT COUNT(*) AS hcr_rows FROM sh_hcr;
SELECT COUNT(DISTINCT country) AS pc_timelines FROM sh_hcr WHERE exhibition_id = 2;
-- Event translations per language → translations/timeline_events.*.json
SELECT lang, COUNT(*) FROM sh_hcr_events GROUP BY lang ORDER BY COUNT(*) DESC;
-- Timeline illustrations: item-linked vs standalone images
SELECT (ref_item IS NULL OR ref_item = '') AS standalone, COUNT(*) FROM sh_hcr_images GROUP BY standalone;

-- 8. Historical Background  →  'collection'-type entries with bc prefix
--    mwnf3_sharing_history:sh_countries_historicalbackground
--    (expect 20 records + 63 pages = 83; NOTE hb_id=20 is the USA project's
--    "Historical Profile / Germany" — leaked into awe by the importer's
--    hard-coded parent, tracked in #1494)
SELECT COUNT(*) AS hb_records FROM sh_countries_historicalbackground;
SELECT COUNT(*) AS hb_pages FROM sh_countries_historicalbackground_pages;
SELECT COUNT(*) AS hb_maps FROM sh_countries_historicalbackground_maps;

-- 9. Justifications  →  collections.json item entries carrying
--    justifications/curator_status                             (2310 theme/subtheme rows + 98+16 exhibition-level)
SELECT 'obj_exh' src, COUNT(*) FROM rel_objects_exhibitions_justification
UNION ALL SELECT 'obj_theme', COUNT(*) FROM rel_objects_themes_justification
UNION ALL SELECT 'obj_subtheme', COUNT(*) FROM rel_objects_subthemes_justification
UNION ALL SELECT 'mon_exh', COUNT(*) FROM rel_monuments_exhibitions_justification
UNION ALL SELECT 'mon_theme', COUNT(*) FROM rel_monuments_themes_justification
UNION ALL SELECT 'mon_subtheme', COUNT(*) FROM rel_monuments_subthemes_justification;

-- 10. Authors — exported per item-translation as author/copy_editor/
--     translator/translation_copy_editor name fields
SELECT COUNT(*) AS authors FROM sh_authors;
SELECT COUNT(*) AS author_object_links FROM sh_authors_objects;
SELECT COUNT(*) AS author_monument_links FROM sh_authors_monuments;
SELECT COUNT(*) AS author_cvs FROM sh_authors_cv;  -- 5 — omitted from v1 (disposition)

-- 11. Media / documents → items.json media[]
SELECT COUNT(*) AS object_documents FROM sh_objects_document;      -- 23
SELECT COUNT(*) AS object_media FROM sh_objects_video_audio;      -- 2

-- 12. Item text languages → translations/items.*.json coverage
SELECT lang, COUNT(*) FROM sh_objects_texts GROUP BY lang;         -- en 2420, fr 389
SELECT lang, COUNT(*) FROM sh_monuments_texts GROUP BY lang;       -- en 216, fr 44

-- 13. Known data quirks (must not break the export)
--     Exhibition 18: show='y', empty name, NO sh_exhibitionnames row — USA
--     project, excluded by scoping anyway.
SELECT exhibition_id, project_id, `show` FROM sh_exhibitions
  WHERE exhibition_id NOT IN (SELECT exhibition_id FROM sh_exhibitionnames);
--     Theme 26: no sh_exhibition_themenames row — exports with its fallback
--     internal name; viewers must tolerate a title-less theme.
SELECT theme_id, exhibition_id FROM sh_exhibition_themes
  WHERE theme_id NOT IN (SELECT theme_id FROM sh_exhibition_themenames);
--     45 items use country 'pd' ("public domain", a non-ISO MWNF code).
SELECT country, COUNT(*) FROM sh_objects WHERE country = 'pd' GROUP BY country
UNION ALL SELECT country, COUNT(*) FROM sh_monuments WHERE country = 'pd' GROUP BY country;
