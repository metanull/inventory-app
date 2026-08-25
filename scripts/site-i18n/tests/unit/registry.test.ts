import { describe, expect, it } from 'vitest'

import { collectWarnings, isHidden, outputName, selectSites } from '../../src/registry.js'
import type { SiteRegistryEntry } from '../../src/core/types.js'

const site = (overrides: Partial<SiteRegistryEntry> = {}): SiteRegistryEntry => ({
  galleryId: 4,
  thgProjectId: 'THG',
  kind: 'gallery',
  mwnf3ProjectId: 'AMU',
  slug: 'amulets_and_talismans',
  host: 'https://amulets.museumwnf.org',
  name: 'Amulets and Talismans',
  status: 'A',
  i18nGroupId: 21,
  i18nCommonGroupId: 59,
  ...overrides,
})

const REGISTRY: SiteRegistryEntry[] = [
  site(),
  site({
    galleryId: 9,
    mwnf3ProjectId: 'DCA',
    slug: 'carpets',
    name: 'Carpets',
    i18nGroupId: 18,
    host: 'https://carpets.museumwnf.org',
  }),
  site({
    galleryId: 47,
    thgProjectId: 'EXH',
    kind: 'exhibition',
    mwnf3ProjectId: 'EXHCOLOUR',
    slug: 'the_use_of_colours_in_art',
    name: 'The Use of Colours in Art',
    i18nGroupId: 65,
  }),
  site({
    galleryId: 55,
    thgProjectId: 'EXH',
    kind: 'exhibition',
    mwnf3ProjectId: 'GalEx5',
    slug: 'lost_memories',
    name: 'The Hijaz Railway',
    status: 'H',
    i18nGroupId: 74,
  }),
]

describe('selectSites', () => {
  it('matches a gallery id', () => {
    const { selected } = selectSites(REGISTRY, ['9'])
    expect(selected.map((s) => s.galleryId)).toEqual([9])
  })

  it('matches a slug', () => {
    const { selected } = selectSites(REGISTRY, ['carpets'])
    expect(selected.map((s) => s.galleryId)).toEqual([9])
  })

  it('matches an mwnf3 project code regardless of case', () => {
    const { selected } = selectSites(REGISTRY, ['dca', 'AMU'])
    expect(selected.map((s) => s.galleryId)).toEqual([9, 4])
  })

  it('does not select the same site twice', () => {
    const { selected } = selectSites(REGISTRY, ['9', 'carpets', 'DCA'])
    expect(selected.map((s) => s.galleryId)).toEqual([9])
  })

  it('reports selectors that match nothing', () => {
    const { selected, unmatched } = selectSites(REGISTRY, ['carpets', 'nope'])
    expect(selected.map((s) => s.galleryId)).toEqual([9])
    expect(unmatched).toEqual(['nope'])
  })
})

describe('isHidden', () => {
  it('is true only for status H', () => {
    expect(isHidden(site({ status: 'H' }))).toBe(true)
    expect(isHidden(site({ status: 'A' }))).toBe(false)
  })
})

describe('outputName', () => {
  it('uses the slug', () => {
    expect(outputName(site({ slug: 'carpets' }))).toBe('carpets')
  })

  it('falls back to the gallery id when the slug is missing', () => {
    expect(outputName(site({ galleryId: 12, slug: null }))).toBe('gallery-12')
    expect(outputName(site({ galleryId: 12, slug: '  ' }))).toBe('gallery-12')
  })

  it('strips characters a filesystem cannot take', () => {
    // Gallery 55's real slug. A colon is legal in a URL path and illegal in a
    // Windows path.
    expect(
      outputName(
        site({ slug: 'lost_memories_along_the_hijaz_railway:_from_istanbul_to_mecca' })
      )
    ).toBe('lost_memories_along_the_hijaz_railway_from_istanbul_to_mecca')
  })

  it('does not touch a slug that is already safe', () => {
    expect(outputName(site({ slug: 'wall_paintings_and_frescoes' }))).toBe(
      'wall_paintings_and_frescoes'
    )
    expect(outputName(site({ slug: 'the-table-is-set' }))).toBe('the-table-is-set')
  })

  it('falls back to the gallery id when nothing usable survives', () => {
    expect(outputName(site({ galleryId: 7, slug: '///' }))).toBe('gallery-7')
  })
})

describe('collectWarnings', () => {
  const anyRow = [{ wordId: 'a', langId: 'en', value: 'A' }]

  it('says nothing about a healthy site', () => {
    expect(collectWarnings(site(), anyRow, anyRow)).toEqual([])
  })

  it('warns when the common group is NULL', () => {
    // Gallery 56 (Water).
    const warnings = collectWarnings(site({ i18nCommonGroupId: null }), [], anyRow)
    expect(warnings.join(' ')).toContain('No common i18n group')
  })

  it('warns when the site group resolves to no rows', () => {
    // Galleries 15, 31, 42, 43 and 44 point at groups that do not exist.
    const warnings = collectWarnings(site({ i18nGroupId: 63 }), anyRow, [])
    expect(warnings.join(' ')).toContain('has no rows')
  })

  it('warns about a missing slug and a missing host', () => {
    const warnings = collectWarnings(site({ slug: null, host: null }), anyRow, anyRow)
    expect(warnings).toHaveLength(2)
    expect(warnings.join(' ')).toContain('No slug')
    expect(warnings.join(' ')).toContain('No canonical host')
  })
})
