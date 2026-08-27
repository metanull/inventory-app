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
 *
 * ## Layers
 *
 * Merging is not the last word: `splitLayers()` takes the merged catalogue back
 * apart into the common group and what a site actually owns, so the shared 450
 * messages are written once per run instead of once per site. `applyLayers()` is
 * the merge a scaffolded site performs to put them together again, and
 * `findLayerRoundTripFailures()` proves the two are inverses on real data.
 */

import { containsMarkup, convertHtmlToMarkdown } from './core/html-to-markdown.js'
import type {
  ExtractionStats,
  LayerSplit,
  MessageCatalogue,
  TranslationRow,
} from './core/types.js'

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
    overriddenNoOp: [],
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
  const commonValues = new Map<string, string | null>()

  for (const row of commonRows) {
    if (row.wordId === '') {
      stats.emptyKeyRows++
      continue
    }
    const key = pairKey(row.wordId, row.langId)
    commonValues.set(key, row.value)
    merged.set(key, row)
  }

  for (const row of siteRows) {
    if (row.wordId === '') {
      stats.emptyKeyRows++
      continue
    }
    const key = pairKey(row.wordId, row.langId)
    const label = pairLabel(row.wordId, row.langId)

    if (commonValues.has(key)) {
      stats.overridden.push(label)
      // Compare what a client would actually see, not the raw HTML: two
      // fragments that differ only in markup the converter normalises away are
      // the same message, and calling that an override would put it in a site's
      // own layer for no reason.
      const common = convertHtmlToMarkdown(commonValues.get(key) ?? '')
      if (common === convertHtmlToMarkdown(row.value ?? '')) {
        stats.overriddenNoOp.push(label)
      }
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
  stats.overriddenNoOp.sort()
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
export function buildLocaleIndex(
  stats: ExtractionStats,
  layers?: LayerSplit
): {
  locales: string[]
  defaultLocale: string
  fallbackLocale: string
  keysPerLocale: Record<string, number>
  ownKeysPerLocale?: Record<string, number>
} {
  const index = {
    locales: stats.locales,
    defaultLocale: FALLBACK_LOCALE,
    fallbackLocale: FALLBACK_LOCALE,
    keysPerLocale: stats.keysPerLocale,
  }

  // `locales` and `keysPerLocale` describe the *effective* catalogue — what the
  // site has once the shared layer is merged in — not the delta on disk. A
  // gallery whose only own message is English must not look English-only to the
  // runtime just because that is the only file in its directory.
  return layers === undefined ? index : { ...index, ownKeysPerLocale: layers.ownKeysPerLocale }
}

/**
 * The shared layer: the common group on its own, through the same conversion.
 *
 * Built by merging the common group over nothing rather than by a second code
 * path, so the shared layer and the merged catalogue can never disagree about
 * how a given row converts.
 */
export function buildSharedCatalogue(commonRows: TranslationRow[]): MessageCatalogue {
  return mergeTranslationGroups(commonRows, []).messages
}

/**
 * Split a merged catalogue into the shared layer and what the site owns.
 *
 * The base is **the common group**, not the intersection of the sites in the
 * run. That distinction decides whether layered output is reproducible:
 *
 * - The shared layer comes out byte-identical whether you extract one site or
 *   all 41. Diffing the extracted catalogues against each other instead would
 *   make the shared layer a function of the run's site selection — a single
 *   site would have no shared layer at all, and five sites would produce a
 *   different one from forty-one.
 * - The boundary survives editorial drift. Under a value diff, a curator
 *   editing one gallery's `galleryAbout` could push the key across the
 *   shared/own boundary and dirty every other site's diff.
 *
 * Values are still compared, but only to drop no-op overrides: a site group
 * that restates a common pair verbatim owns nothing.
 */
export function splitLayers(merged: MessageCatalogue, shared: MessageCatalogue): LayerSplit {
  const own: MessageCatalogue = {}
  const ownKeysPerLocale: Record<string, number> = {}
  const suppressed: Record<string, string[]> = {}

  for (const locale of Object.keys(merged).sort()) {
    const sharedMessages = shared[locale] ?? {}
    const ownMessages: Record<string, string> = {}

    for (const key of Object.keys(merged[locale]!).sort()) {
      const value = merged[locale]![key]!
      if (sharedMessages[key] !== value) {
        ownMessages[key] = value
      }
    }

    if (Object.keys(ownMessages).length > 0) {
      own[locale] = ownMessages
      ownKeysPerLocale[locale] = Object.keys(ownMessages).length
    }
  }

  for (const locale of Object.keys(shared).sort()) {
    const mergedMessages = merged[locale] ?? {}
    const gone = Object.keys(shared[locale]!)
      .filter((key) => !(key in mergedMessages))
      .sort()
    if (gone.length > 0) {
      suppressed[locale] = gone
    }
  }

  return { own, ownKeysPerLocale, suppressed }
}

/**
 * Merge a shared layer and a site's own layer back into one catalogue.
 *
 * This is the merge a scaffolded site performs at build or boot time — key by
 * key within a locale, own layer last. The extractor runs it too, to prove the
 * split it just wrote reproduces the flat catalogue exactly.
 */
export function applyLayers(shared: MessageCatalogue, own: MessageCatalogue): MessageCatalogue {
  const merged: MessageCatalogue = {}

  for (const locale of new Set([...Object.keys(shared), ...Object.keys(own)])) {
    const messages = { ...(shared[locale] ?? {}), ...(own[locale] ?? {}) }
    const sorted: Record<string, string> = {}
    for (const key of Object.keys(messages).sort()) {
      sorted[key] = messages[key]!
    }
    merged[locale] = sorted
  }

  return merged
}

/**
 * Names of the (locale, key) pairs where a round trip through the layers does
 * not reproduce the flat catalogue. Empty means the split is lossless.
 */
export function findLayerRoundTripFailures(
  merged: MessageCatalogue,
  layers: LayerSplit,
  shared: MessageCatalogue
): string[] {
  const rebuilt = applyLayers(shared, layers.own)
  const failures: string[] = []

  for (const locale of new Set([...Object.keys(merged), ...Object.keys(rebuilt)])) {
    const expected = merged[locale] ?? {}
    const actual = rebuilt[locale] ?? {}
    for (const key of new Set([...Object.keys(expected), ...Object.keys(actual)])) {
      if (expected[key] !== actual[key]) {
        failures.push(pairLabel(key, locale))
      }
    }
  }

  return failures.sort()
}
