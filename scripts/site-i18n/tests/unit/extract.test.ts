/**
 * The merge is the whole tool, so these cases are drawn from what the legacy
 * data actually contains rather than from invented shapes:
 *
 * - every gallery group carries `goToFullSearch` in ar/es/fr while the common
 *   group has it in English only, so the legacy RIGHT JOIN drops three rows per
 *   gallery;
 * - exhibitions 52, 55 and 56 spell the key `Footer_logo_section_1` where the
 *   common group has `footer_logo_section_1`, so those overrides are dropped too;
 * - gallery 14's group contains a row with an empty `word_id`;
 * - `galleryAbout` and `galleryCredits` are HTML fragments.
 */

import { describe, expect, it } from 'vitest'

import { buildLocaleIndex, mergeTranslationGroups } from '../../src/extract.js'
import type { TranslationRow } from '../../src/core/types.js'

const row = (wordId: string, langId: string, value: string | null): TranslationRow => ({
  wordId,
  langId,
  value,
})

describe('mergeTranslationGroups', () => {
  it('uses the common group as the base', () => {
    const { messages } = mergeTranslationGroups(
      [row('about', 'en', 'About'), row('about', 'fr', 'À propos')],
      []
    )

    expect(messages['en']).toEqual({ about: 'About' })
    expect(messages['fr']).toEqual({ about: 'À propos' })
  })

  it('lets the site group override a common-group message', () => {
    const { messages, stats } = mergeTranslationGroups(
      [row('galleryAbout', 'en', 'Generic gallery blurb')],
      [row('galleryAbout', 'en', 'Amulets and Talismans')]
    )

    expect(messages['en']?.['galleryAbout']).toBe('Amulets and Talismans')
    expect(stats.overridden).toEqual(['galleryAbout@en'])
    expect(stats.added).toEqual([])
    expect(stats.droppedByLegacyRightJoin).toEqual([])
  })

  it('keeps site messages the legacy RIGHT JOIN discards, and records them', () => {
    // The real case: the common group has goToFullSearch in English only.
    const { messages, stats } = mergeTranslationGroups(
      [row('goToFullSearch', 'en', 'Go to full search')],
      [
        row('goToFullSearch', 'en', 'Go to full search'),
        row('goToFullSearch', 'ar', 'الانتقال إلى البحث الكامل'),
        row('goToFullSearch', 'es', 'Ir a la búsqueda completa'),
        row('goToFullSearch', 'fr', 'Aller à la recherche complète'),
      ]
    )

    expect(messages['ar']?.['goToFullSearch']).toBe('الانتقال إلى البحث الكامل')
    expect(messages['es']?.['goToFullSearch']).toBe('Ir a la búsqueda completa')
    expect(messages['fr']?.['goToFullSearch']).toBe('Aller à la recherche complète')
    expect(stats.droppedByLegacyRightJoin).toEqual([
      'goToFullSearch@ar',
      'goToFullSearch@es',
      'goToFullSearch@fr',
    ])
    expect(stats.overridden).toEqual(['goToFullSearch@en'])
  })

  it('treats a key that differs only in case as a distinct key', () => {
    // Exhibitions 52/55/56 use Footer_logo_section_1; the common group uses
    // lower case. Both survive here; legacy served only the common one.
    const { messages, stats } = mergeTranslationGroups(
      [row('footer_logo_section_1', 'en', 'Supported by')],
      [row('Footer_logo_section_1', 'en', 'Doha Launch Hosts')]
    )

    expect(messages['en']).toEqual({
      footer_logo_section_1: 'Supported by',
      Footer_logo_section_1: 'Doha Launch Hosts',
    })
    expect(stats.droppedByLegacyRightJoin).toEqual(['Footer_logo_section_1@en'])
  })

  it('converts HTML values to Markdown', () => {
    const { messages, stats } = mergeTranslationGroups(
      [],
      [
        row(
          'galleryAbout',
          'en',
          'Dedicated to <b>Amulets</b>, see <a href="https://islamicart.museumwnf.org/">Discover Islamic Art</a>.'
        ),
      ]
    )

    expect(messages['en']?.['galleryAbout']).toBe(
      'Dedicated to **Amulets**, see [Discover Islamic Art](https://islamicart.museumwnf.org/).'
    )
    expect(stats.markdownConverted).toBe(1)
  })

  it('leaves a plain string untouched and does not count it as converted', () => {
    const { messages, stats } = mergeTranslationGroups([], [row('about', 'en', 'About')])

    expect(messages['en']?.['about']).toBe('About')
    expect(stats.markdownConverted).toBe(0)
  })

  it('skips rows with an empty word_id', () => {
    const { messages, stats } = mergeTranslationGroups(
      [row('', 'en', 'orphan'), row('about', 'en', 'About')],
      [row('', 'fr', 'orphelin')]
    )

    expect(Object.keys(messages['en'] ?? {})).toEqual(['about'])
    expect(messages['fr']).toBeUndefined()
    expect(stats.emptyKeyRows).toBe(2)
  })

  it('omits empty and null values so vue-i18n can fall back', () => {
    const { messages, stats } = mergeTranslationGroups(
      [row('about', 'en', 'About'), row('about', 'fr', ''), row('credits', 'fr', null)],
      []
    )

    expect(messages['fr']).toBeUndefined()
    expect(stats.emptyValueRows).toBe(2)
  })

  it('flags keys containing a dot, which vue-i18n reads as a message path', () => {
    const { stats } = mergeTranslationGroups([row('foo.bar', 'en', 'Foo')], [])

    expect(stats.keysWithDots).toEqual(['foo.bar'])
  })

  it('sorts keys so a re-run produces an identical file', () => {
    const { messages } = mergeTranslationGroups(
      [row('zulu', 'en', 'Z'), row('alpha', 'en', 'A'), row('mike', 'en', 'M')],
      []
    )

    expect(Object.keys(messages['en']!)).toEqual(['alpha', 'mike', 'zulu'])
  })

  it('reports per-locale coverage', () => {
    const { stats } = mergeTranslationGroups(
      [row('a', 'en', '1'), row('b', 'en', '2'), row('a', 'fr', '1')],
      []
    )

    expect(stats.locales).toEqual(['en', 'fr'])
    expect(stats.keysPerLocale).toEqual({ en: 2, fr: 1 })
  })

  it('extracts a site with no common group from the site group alone', () => {
    // Gallery 56 (Water) has i18n_common_group_id = NULL.
    const { messages, stats } = mergeTranslationGroups([], [row('Footer_logo_section_1', 'en', 'X')])

    expect(messages['en']).toEqual({ Footer_logo_section_1: 'X' })
    expect(stats.commonRows).toBe(0)
  })
})

describe('buildLocaleIndex', () => {
  it('names English as the default and fallback locale', () => {
    const { stats } = mergeTranslationGroups(
      [row('a', 'en', '1'), row('a', 'ar', '١')],
      []
    )

    expect(buildLocaleIndex(stats)).toEqual({
      locales: ['ar', 'en'],
      defaultLocale: 'en',
      fallbackLocale: 'en',
      keysPerLocale: { ar: 1, en: 1 },
    })
  })
})
