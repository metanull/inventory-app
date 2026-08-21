-- Legacy-vs-package validation queries for the Baroque Art data-package.
--
-- Run against a MySQL instance loaded with the `.legacy-database` dumps
-- (DDL/creation + Data for: projects, exhibitions, objects, monuments,
-- monument_details, partner_museums, partner_institutions).
-- Loading notes (MySQL 8): strip NO_AUTO_CREATE_USER from SET sql_mode
-- lines, SET FOREIGN_KEY_CHECKS=0, and DROP the m3obj_*/m3mon_* triggers
-- after loading the DDL (they sync to global_entities, absent here).
--
-- Compare each result against the exported JSON (see
-- VALIDATION-2026-08-21.md for the recorded comparison).

-- Distinct BAR objects (PK minus lang) — expect items.json type='object'
SELECT COUNT(DISTINCT project_id, country, museum_id, number) AS objects
FROM objects WHERE project_id = 'BAR';

-- Distinct BAR monuments — expect items.json type='monument'
SELECT COUNT(DISTINCT project_id, country, institution_id, number) AS monuments
FROM monuments WHERE project_id = 'BAR';

-- Distinct BAR monument details WITH a non-empty name in at least one
-- language — expect items.json type='detail'. (The importer skips
-- name-less rows by policy: no synthesized titles. The raw distinct count
-- including nameless stubs is higher.)
SELECT COUNT(*) AS details_named FROM (
  SELECT DISTINCT country_id, institution_id, monument_id, detail_id
  FROM monument_details
  WHERE project_id = 'BAR' AND TRIM(COALESCE(name, '')) <> ''
) d;
SELECT COUNT(*) AS details_raw FROM (
  SELECT DISTINCT country_id, institution_id, monument_id, detail_id
  FROM monument_details WHERE project_id = 'BAR'
) d;

-- BAR exhibitions — expect the 9 collections under mwnf3:exhibitions:root:BAR
SELECT exhibition_id, name FROM exhibitions
WHERE project_id = 'BAR' ORDER BY exhibition_id;

-- Timeline countries: legacy hcr_home.php derives the DBA timeline country
-- list from OBJECTS only (EXISTS ... objects.project_id='BAR'), so the
-- expected timelines.json count is the object-country count (monument-only
-- countries, e.g. 'at', never showed a timeline in legacy either).
SELECT COUNT(DISTINCT country) AS timeline_countries,
       GROUP_CONCAT(DISTINCT country ORDER BY country) AS countries
FROM objects WHERE project_id = 'BAR';

-- Tier-1 partners — expect partners.json rows with level='partner'
-- (associated_partner / minor_contributor come from the associated_* tables)
SELECT 'museums' AS what, COUNT(*) AS n FROM partner_museums WHERE LOWER(project_id) = 'bar'
UNION ALL
SELECT 'institutions', COUNT(*) FROM partner_institutions WHERE LOWER(project_id) = 'bar';

-- Languages present in BAR object rows — expect at least these among the
-- package's translations/*.{lang}.json files
SELECT GROUP_CONCAT(DISTINCT lang ORDER BY lang) AS object_langs
FROM objects WHERE project_id = 'BAR';
