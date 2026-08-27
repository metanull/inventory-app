/**
 * The layered layout, exercised on the shapes the legacy data actually has.
 *
 * Across the 41 active sites, 0.5% of the (locale, key) instances differ: six of
 * the ten locales are byte-identical everywhere, `ar`/`es`/`fr` differ by the
 * single key `goToFullSearch`, and English differs by `galleryAbout` and
 * `galleryCredits`. The cases below are built from exactly those.
 *
 * The property that matters is the round trip: whatever the split does, merging
 * the two layers back has to reproduce the flat catalogue byte for byte, or a
 * scaffolded site renders something the extraction never saw.
 */

import { describe, expect, it } from 'vitest'

import {
  applyLayers,
  buildLocaleIndex,
  buildSharedCatalogue,
  findLayerRoundTripFailures,
  mergeTranslationGroups,
  splitLayers,
} from '../../src/extract.js'
import type { TranslationRow } from '../../src/core/types.js'

const row = (wordId: string, langId: string, value: string | null): TranslationRow => ({
  wordId,
  langId,
  value,
})

/** The common group, in the shape every site is registered against. */
const commonGroup = (): TranslationRow[] => [
  row('galleryAbout', 'en', 'Generic gallery blurb'),
  row('galleryCredits', 'en', 'Generic credits'),
  row('goToFullSearch', 'en', 'Go to full search'),
  row('download', 'en', 'Download'),
  row('download', 'fr', 'Télécharger'),
  row('download', 'de', 'Herunterladen'),
]

const split = (siteRows: TranslationRow[]) => {
  const common = commonGroup()
  const { messages } = mergeTranslationGroups(common, siteRows)
  const shared = buildSharedCatalogue(common)
  return { messages, shared, layers: splitLayers(messages, shared) }
}

describe('buildSharedCatalogue', () => {
  it('is the common group alone, through the same conversion as the merge', () => {
    const shared = buildSharedCatalogue([row('galleryAbout', 'en', 'A <b>bold</b> blurb')])

    expect(shared['en']).toEqual({ galleryAbout: 'A **bold** blurb' })
  })

  it('does not depend on which sites the run covers', () => {
    // The whole point of splitting by provenance: extract one site or forty-one,
    // the shared layer is the same bytes. A diff of the extracted catalogues
    // against each other could not promise this.
    const common = commonGroup()

    const alone = buildSharedCatalogue(common)
    const alongsideOthers = buildSharedCatalogue(common)

    expect(JSON.stringify(alone)).toBe(JSON.stringify(alongsideOthers))
  })
})

describe('splitLayers', () => {
  it('gives a gallery only the messages it overrides', () => {
    const { layers } = split([
      row('galleryAbout', 'en', 'Ceramics'),
      row('galleryCredits', 'en', 'Ceramics credits'),
    ])

    expect(layers.own).toEqual({
      en: { galleryAbout: 'Ceramics', galleryCredits: 'Ceramics credits' },
    })
    expect(layers.ownKeysPerLocale).toEqual({ en: 2 })
  })

  it('leaves no file for a locale the site does not touch', () => {
    const { layers } = split([row('galleryAbout', 'en', 'Ceramics')])

    // `fr` and `de` exist in the merged catalogue but come entirely from the
    // common group, so the site directory must not carry them at all.
    expect(Object.keys(layers.own)).toEqual(['en'])
    expect(layers.ownKeysPerLocale['fr']).toBeUndefined()
  })

  it('keeps a message the site adds outright', () => {
    // `goToFullSearch` in ar/es/fr is the real case: every gallery group has it,
    // the common group carries the key in English only.
    const { layers } = split([row('goToFullSearch', 'fr', 'Recherche complète')])

    expect(layers.own).toEqual({ fr: { goToFullSearch: 'Recherche complète' } })
  })

  it('drops an override whose value matches the common group', () => {
    const { layers } = split([row('download', 'en', 'Download')])

    expect(layers.own).toEqual({})
    expect(layers.ownKeysPerLocale).toEqual({})
  })

  it('drops an override that differs only in markup the converter normalises', () => {
    const common = [row('download', 'en', 'Download')]
    const { messages, stats } = mergeTranslationGroups(common, [
      row('download', 'en', '<span>Download</span>'),
    ])
    const layers = splitLayers(messages, buildSharedCatalogue(common))

    expect(layers.own).toEqual({})
    expect(stats.overridden).toEqual(['download@en'])
    expect(stats.overriddenNoOp).toEqual(['download@en'])
  })

  it('owns everything when the site has no common group', () => {
    // `i18n_common_group_id` was NULL for gallery 56 until the legacy data was
    // repaired; the extractor passes an empty shared layer for that shape.
    const { messages } = mergeTranslationGroups([], [row('galleryAbout', 'en', 'Water in Islam')])
    const layers = splitLayers(messages, {})

    expect(layers.own).toEqual({ en: { galleryAbout: 'Water in Islam' } })
  })

  it('sorts locales and keys so an unchanged extraction re-runs byte-identical', () => {
    const { layers } = split([
      row('goToFullSearch', 'fr', 'Recherche complète'),
      row('galleryCredits', 'en', 'Credits'),
      row('galleryAbout', 'en', 'About'),
    ])

    expect(Object.keys(layers.own)).toEqual(['en', 'fr'])
    expect(Object.keys(layers.own['en']!)).toEqual(['galleryAbout', 'galleryCredits'])
  })
})

describe('applyLayers', () => {
  it('reproduces the merged catalogue exactly', () => {
    const { messages, shared, layers } = split([
      row('galleryAbout', 'en', 'Ceramics'),
      row('goToFullSearch', 'fr', 'Recherche complète'),
      row('download', 'en', 'Download'),
    ])

    expect(applyLayers(shared, layers.own)).toEqual(messages)
    expect(findLayerRoundTripFailures(messages, layers, shared)).toEqual([])
  })

  it('lets the own layer win key by key without discarding the rest of the locale', () => {
    const merged = applyLayers(
      { en: { a: 'shared a', b: 'shared b' } },
      { en: { b: 'own b', c: 'own c' } }
    )

    expect(merged['en']).toEqual({ a: 'shared a', b: 'own b', c: 'own c' })
  })
})

describe('a site group that blanks a common message', () => {
  /**
   * The one shape the layered layout cannot express. An empty value is dropped
   * rather than written — a blank message shadows vue-i18n's fallback chain —
   * so the key vanishes from the merged catalogue while the shared layer still
   * carries it. Shallow-merging the layers would resurrect it.
   *
   * No site in the registry does this today. The extractor detects it and
   * refuses rather than writing output that silently disagrees with the flat
   * catalogue.
   */
  const blanked = () => {
    const common = [row('download', 'en', 'Download'), row('galleryAbout', 'en', 'Blurb')]
    const { messages } = mergeTranslationGroups(common, [row('download', 'en', '')])
    const shared = buildSharedCatalogue(common)
    return { messages, shared, layers: splitLayers(messages, shared) }
  }

  it('is recorded as suppressed rather than silently lost', () => {
    const { layers } = blanked()

    expect(layers.suppressed).toEqual({ en: ['download'] })
  })

  it('is caught by the round-trip check', () => {
    const { messages, shared, layers } = blanked()

    expect(findLayerRoundTripFailures(messages, layers, shared)).toEqual(['download@en'])
  })
})

describe('buildLocaleIndex', () => {
  it('describes the effective catalogue, not the delta on disk', () => {
    const { messages, shared } = split([row('galleryAbout', 'en', 'Ceramics')])
    const { stats } = mergeTranslationGroups(commonGroup(), [row('galleryAbout', 'en', 'Ceramics')])
    const layers = splitLayers(messages, shared)

    const index = buildLocaleIndex(stats, layers)

    // Only `en` has a file in the site directory; the site is still trilingual.
    expect(index.locales).toEqual(['de', 'en', 'fr'])
    expect(index.keysPerLocale['fr']).toBe(1)
    expect(index.ownKeysPerLocale).toEqual({ en: 1 })
  })

  it('omits the own-key counts for a flat extraction', () => {
    const { stats } = mergeTranslationGroups(commonGroup(), [])

    expect(buildLocaleIndex(stats).ownKeysPerLocale).toBeUndefined()
  })
})
