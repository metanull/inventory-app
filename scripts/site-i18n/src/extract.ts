/**
 * Merge a site's i18n group over the common group and produce vue-i18n catalogues.
 *
 * ## The legacy merge, and where we deviate
 *
 * The legacy DXA API builds a site's messages in `Translations.blade.php`:
 *
 *     from (select * from translation where group_id = :groupId) t_specific
 *     right join (select * from translation where group_id = :commonGroupId) t_common
 *       on (t_common.word_id = t_specific.word_id and t_common.lang_id = t_specific.lang_id)
 *
 * A RIGHT JOIN on the common group means the common group defines the whole
 * key × language universe: a site-specific string whose (key, language) pair has
 * no counterpart in the common group is silently discarded. That is not a rule,
 * it is an accident of the query, and it costs real content — every gallery has
 * Arabic, Spanish and French strings that never reach a browser because the
 * common group happens to carry that key in English only.
 *
 * This extractor merges the two groups as a union instead: the common group is
 * the base, the site group overrides it pair by pair and may add pairs of its
 * own. Everything legacy would have dropped is recorded in
 * `stats.droppedByLegacyRightJoin` so a scaffolding run can show exactly what it
 * recovered.
 *
 * ## Markdown
 *
 * Values arrive as HTML fragments. They leave as Markdown — see
 * `core/html-to-markdown.ts` for why.
 */

import { containsMarkup, convertHtmlToMarkdown } from './core/html-to-markdown.js'
import type { ExtractionStats, MessageCatalogue, TranslationRow } from './core/types.js'

/**
 * Separator for the (key, language) composite.
 *
 * NUL rather than something readable: legacy keys contain spaces, hyphens and
 * underscores — `Search Related Database` is a real one — so any printable
 * separator risks colliding with a key.
 */
const PAIR = '\u0000'

const pairKey = (wordId: string, langId: string): string => `${wordId}${PAIR}${langId}`
const pairLabel = (wordId: string, langId: string): string => `${wordId}@${langId}`

export interface MergeResult {
  messages: MessageCatalogue
  stats: ExtractionStats
}

/**
 * Merge the common group and the site group into per-locale message catalogues.
 *
 * @param commonRows rows of the common i18n group (empty when the site has none)
 * @param siteRows   rows of the site's own i18n group (empty when the site has none)
 */
export function mergeTranslationGroups(
  commonRows: TranslationRow[],
  siteRows: TranslationRow[]
): MergeResult {
  const stats: ExtractionStats = {
    commonRows: commonRows.length,
    siteRows: siteRows.length,
    overridden: [],
    added: [],
    droppedByLegacyRightJoin: [],
    emptyKeyRows: 0,
    markdownConverted: 0,
    emptyValueRows: 0,
    keysWithDots: [],
    locales: [],
    keysPerLocale: {},
  }

  // Index the common group first; it defines what "already present" means.
  const merged = new Map<string, TranslationRow>()
  const commonPairs = new Set<string>()

  for (const row of commonRows) {
    if (row.wordId === '') {
      stats.emptyKeyRows++
      continue
    }
    const key = pairKey(row.wordId, row.langId)
    commonPairs.add(key)
    merged.set(key, row)
  }

  for (const row of siteRows) {
    if (row.wordId === '') {
      stats.emptyKeyRows++
      continue
    }
    const key = pairKey(row.wordId, row.langId)
    const label = pairLabel(row.wordId, row.langId)

    if (commonPairs.has(key)) {
      stats.overridden.push(label)
    } else {
      stats.added.push(label)
      // No counterpart in the common group, so the legacy RIGHT JOIN drops it.
      stats.droppedByLegacyRightJoin.push(label)
    }

    merged.set(key, row)
  }

  // Convert to Markdown and bucket by locale.
  const messages: MessageCatalogue = {}
  const keysWithDots = new Set<string>()

  for (const [key, row] of merged) {
    const [wordId, langId] = key.split(PAIR) as [string, string]

    const raw = row.value ?? ''
    if (containsMarkup(raw)) {
      stats.markdownConverted++
    }
    const value = convertHtmlToMarkdown(raw)

    // An empty message shadows vue-i18n's fallback chain with a blank string,
    // which is worse than not defining the key at all.
    if (value === '') {
      stats.emptyValueRows++
      continue
    }

    if (wordId.includes('.')) {
      keysWithDots.add(wordId)
    }

    ;(messages[langId] ??= {})[wordId] = value
  }

  // Sort keys so re-running the extractor produces a byte-identical file when
  // the legacy data has not changed, and diffs stay readable.
  for (const langId of Object.keys(messages)) {
    const sorted: Record<string, string> = {}
    for (const wordId of Object.keys(messages[langId]!).sort()) {
      sorted[wordId] = messages[langId]![wordId]!
    }
    messages[langId] = sorted
  }

  stats.overridden.sort()
  stats.added.sort()
  stats.droppedByLegacyRightJoin.sort()
  stats.keysWithDots = [...keysWithDots].sort()
  stats.locales = Object.keys(messages).sort()
  for (const langId of stats.locales) {
    stats.keysPerLocale[langId] = Object.keys(messages[langId]!).length
  }

  return { messages, stats }
}

/**
 * The locale a scaffolded site falls back to.
 *
 * English is the only language every group covers in full, and it is what the
 * legacy client already used as its vue-i18n `fallbackLocale`.
 */
export const FALLBACK_LOCALE = 'en'

/**
 * Build the `index.json` that sits next to the per-locale files.
 *
 * Sites are expected to keep vue-i18n's own fallback behaviour rather than have
 * the extractor pad every locale with English: a locale file holds exactly the
 * messages legacy actually has in that language, and vue-i18n resolves the rest.
 */
export function buildLocaleIndex(stats: ExtractionStats): {
  locales: string[]
  defaultLocale: string
  fallbackLocale: string
  keysPerLocale: Record<string, number>
} {
  return {
    locales: stats.locales,
    defaultLocale: FALLBACK_LOCALE,
    fallbackLocale: FALLBACK_LOCALE,
    keysPerLocale: stats.keysPerLocale,
  }
}
