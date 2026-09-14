/**
 * Project URL Map for Legacy Data Import
 *
 * Legacy "project" keys (`ISL`, `EPM`, `BAR`, `AWE`, ...) each correspond to
 * one of a handful of published MWNF sites. Those sites' base URLs, public
 * "related database" search pages, and (for the Islamic Art family only) the
 * artistic-introduction page are legacy knowledge that used to be hardcoded
 * in the frontend (`viewer-core`'s `conventions.js`). Per epic #1727 this
 * moves into the data package: `projects.site_url`, `projects.related_database_url`
 * and `projects.artistic_introduction_url` are populated by the importer so a
 * re-import never wipes hand-entered Filament data.
 *
 * Keyed on the RAW, UPPERCASE legacy `project_id` as read from the source
 * database (`mwnf3.projects.project_id` / `mwnf3_sharing_history.sh_projects.project_id`),
 * before any lower-casing done for `backward_compatibility` formatting.
 *
 * SOURCE: viewer-core/src/conventions.js (`mwnfLinks`, `RELATED_DATABASE_PROJECTS`)
 * and the DXA `ItemSheet.vue` views (carpets/amulets/the-use-of-colours-in-art/water-in-islam),
 * cross-checked against `.legacy-database/data/mwnf3_projects.sql` for which
 * keys are real legacy project ids (`DBA` is a viewer-core-only alias, not a
 * real key, and is intentionally absent from this map).
 *
 * Entries with all-null values are placeholders for legacy keys that do not
 * (yet) have a confirmed public site/database/introduction URL. They need
 * manual review — see scripts/importer/README.md.
 *
 * MAINTENANCE: this map must be updated by hand whenever a new legacy
 * project/site is added or an existing site's URLs change; nothing derives
 * it automatically.
 */

export interface ProjectUrls {
  site_url: string | null;
  related_database_url: string | null;
  artistic_introduction_url: string | null;
}

const NO_URLS: ProjectUrls = {
  site_url: null,
  related_database_url: null,
  artistic_introduction_url: null,
};

export const PROJECT_URL_MAP: Record<string, ProjectUrls> = {
  // Islamic Art family - EPM shares ISL's site and artistic introduction
  ISL: {
    site_url: 'https://islamicart.museumwnf.org',
    related_database_url: 'https://islamicart.museumwnf.org/database.php',
    artistic_introduction_url: 'https://islamicart.museumwnf.org/gai/ISL/',
  },
  EPM: {
    site_url: 'https://islamicart.museumwnf.org',
    related_database_url: 'https://islamicart.museumwnf.org/database.php',
    // Legacy always points EPM's artistic introduction at the ISL path - not a typo, don't "fix".
    artistic_introduction_url: 'https://islamicart.museumwnf.org/gai/ISL/',
  },

  // Baroque Art
  BAR: {
    site_url: 'https://baroqueart.museumwnf.org',
    related_database_url: 'https://baroqueart.museumwnf.org/database.php',
    artistic_introduction_url: null,
  },

  // Sharing History
  AWE: {
    site_url: 'https://sharinghistory.museumwnf.org',
    related_database_url: 'https://sharinghistory.museumwnf.org/database.php',
    artistic_introduction_url: null,
  },

  // Discover Islamic Art (DCA) - legacy has no public database search to point at
  // (confirmed absence, per ItemSheet.vue's RELATED_DATABASE_PROJECTS exclusion comment).
  DCA: { ...NO_URLS },

  // Placeholders below: no confirmed source found this session for these keys'
  // site/database/introduction URLs. Left null deliberately - needs Pascal's input.
  DGA: { ...NO_URLS },
  EXTHE: { ...NO_URLS },
  GALLERIES: { ...NO_URLS },
};

/**
 * Look up the site/related-database/artistic-introduction URLs for a legacy
 * project key. Unknown keys resolve to all-null rather than throwing, since
 * most legacy projects (the long tail of one-off exhibitions) never had a
 * dedicated public site and that is an expected, not exceptional, outcome.
 */
export function lookupProjectUrls(legacyProjectId: string): ProjectUrls {
  return PROJECT_URL_MAP[legacyProjectId] ?? { ...NO_URLS };
}
