/**
 * Site selection and registry sanity checks.
 *
 * `thg_gallery` is the registry of record: it is the only source that covers
 * exhibitions as well as galleries, and it is where the importer reads the
 * gallery anchor from (issue #1520). The legacy deployment scripts keep a second,
 * partial copy of the same mapping in `apps/<site>/api/environment/config.sh`;
 * the two disagree in places, and where they do the README says which to trust.
 */
import type { SiteRegistryEntry, TranslationRow } from './core/types.js'

/** A site is hidden — not publicly served — when thg_gallery.status is 'H'. */
export const isHidden = (site: SiteRegistryEntry): boolean => site.status === 'H'

/**
 * Resolve user-supplied selectors against the registry.
 *
 * A selector matches a gallery id, a slug, or an mwnf3 project code, so
 * `9`, `carpets` and `DCA` all name the same site. Matching is case-insensitive
 * because the project codes are upper-case and the slugs are not.
 */
export function selectSites(
  registry: SiteRegistryEntry[],
  selectors: string[]
): { selected: SiteRegistryEntry[]; unmatched: string[] } {
  const selected: SiteRegistryEntry[] = []
  const unmatched: string[] = []

  for (const selector of selectors) {
    const needle = selector.trim().toLowerCase()
    const match = registry.find(
      (site) =>
        String(site.galleryId) === needle ||
        site.slug?.toLowerCase() === needle ||
        site.mwnf3ProjectId?.toLowerCase() === needle
    )

    if (!match) {
      unmatched.push(selector)
      continue
    }
    if (!selected.some((site) => site.galleryId === match.galleryId)) {
      selected.push(match)
    }
  }

  return { selected, unmatched }
}

/**
 * Flag registry damage that a scaffolding run has to see rather than paper over.
 *
 * None of these is fatal — the extraction still runs and produces whatever the
 * legacy data supports — but each one means the resulting site is missing
 * strings for a reason a human has to decide about.
 */
export function collectWarnings(
  site: SiteRegistryEntry,
  commonRows: TranslationRow[],
  siteRows: TranslationRow[]
): string[] {
  const warnings: string[] = []

  if (site.i18nCommonGroupId === null) {
    warnings.push(
      'No common i18n group (`thg_gallery.i18n_common_group_id` is NULL). The legacy API binds ' +
        'NULL into its RIGHT JOIN and serves this site no messages at all; this extraction has ' +
        'only the site group to work from.'
    )
  } else if (commonRows.length === 0) {
    warnings.push(
      `Common i18n group ${site.i18nCommonGroupId} has no rows in \`mwnf3.translation\`.`
    )
  }

  if (site.i18nGroupId === null) {
    warnings.push('No site i18n group (`thg_gallery.i18n_group_id` is NULL).')
  } else if (siteRows.length === 0) {
    warnings.push(
      `Site i18n group ${site.i18nGroupId} has no rows in \`mwnf3.translation\`. Either the ` +
        'group id is stale or the strings were never authored — the site falls back entirely ' +
        'to the common group.'
    )
  }

  if (!site.slug) {
    warnings.push('No slug (`thg_gallery.link` is empty); output is keyed on the gallery id.')
  }
  if (!site.host) {
    warnings.push('No canonical host (`thg_gallery_url` has no row for this gallery).')
  }

  return warnings
}

/**
 * Directory name for a site's output.
 *
 * The slug is a URL path segment, not a filename, and one of them is not usable
 * as either — gallery 55's slug is
 * `lost_memories_along_the_hijaz_railway:_from_istanbul_to_mecca`, and a colon
 * cannot appear in a Windows path. Unsafe runs become a single separator; the
 * untouched slug is still written to `site.json`, which is what a scaffold reads.
 */
export function outputName(site: SiteRegistryEntry): string {
  const slug = (site.slug ?? '').trim()
  if (slug === '') {
    return `gallery-${site.galleryId}`
  }

  const safe = slug
    .replace(/[^A-Za-z0-9._-]+/g, '-')
    // The replacement often lands next to the separator the slug already uses
    // ("railway:_from" -> "railway-_from"); keep one separator, not two.
    .replace(/-+_/g, '_')
    .replace(/_-+/g, '_')
    .replace(/-{2,}/g, '-')
    .replace(/^[-_.]+|[-_.]+$/g, '')

  return safe === '' ? `gallery-${site.galleryId}` : safe
}
