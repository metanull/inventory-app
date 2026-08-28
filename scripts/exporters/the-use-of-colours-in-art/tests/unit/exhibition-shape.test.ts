import { describe, expect, it } from 'vitest'

import { bitToBoolean, isFeatured, isHidden } from '../../src/exporters/exhibition-exporter.js'
import { relatedLinkThemeKey } from '../../src/exporters/theme-exporter.js'
import {
  relatedContentKind,
  relatedContentLegacyId,
} from '../../src/exporters/related-content-exporter.js'

/**
 * `featured` and `status` share the enum('A','H') and mean different things.
 * dxa-api conflates them — `WithTHGTemporaryTables.php` builds `featured` from
 * the `hidden` projection without flipping the polarity — so the live API
 * reports `featured: false` for this exhibition even though its record says
 * 'A'. The package ships the documented meaning; these cases pin which one.
 */
describe('isFeatured / isHidden', () => {
  it('reads featured as "is it in the featured strip"', () => {
    expect(isFeatured('A')).toBe(true)
    expect(isFeatured('H')).toBe(false)
  })

  it('reads status as "is it visible at all", with A meaning visible', () => {
    expect(isHidden('A')).toBe(false)
    expect(isHidden('H')).toBe(true)
  })

  /**
   * Gallery 54 is status='H' + featured='A' in the legacy data, which is only
   * possible because the two flags are orthogonal. Reading either through the
   * other would make that row impossible to represent.
   */
  it('keeps the two flags independent', () => {
    expect(isFeatured('A')).toBe(true)
    expect(isHidden('H')).toBe(true)
  })

  it('treats an absent flag as not-featured and hidden', () => {
    expect(isFeatured(null)).toBe(false)
    expect(isFeatured(undefined)).toBe(false)
    expect(isHidden(null)).toBe(true)
  })
})

/**
 * `has_timeline` is a MySQL bit(1). Rows written before the importer normalised
 * it still hold a serialized Node Buffer, and `if (buffer)` is truthy for BOTH
 * `[0]` and `[1]` — so a naive read reports every exhibition as having a
 * timeline.
 */
describe('bitToBoolean', () => {
  it('accepts the normalised JSON booleans', () => {
    expect(bitToBoolean(true)).toBe(true)
    expect(bitToBoolean(false)).toBe(false)
  })

  it('unwraps a serialized mysql2 Buffer instead of reading it as truthy', () => {
    expect(bitToBoolean({ type: 'Buffer', data: [1] })).toBe(true)
    expect(bitToBoolean({ type: 'Buffer', data: [0] })).toBe(false)
  })

  it('accepts the numeric and string spellings', () => {
    expect(bitToBoolean(1)).toBe(true)
    expect(bitToBoolean(0)).toBe(false)
    expect(bitToBoolean('1')).toBe(true)
    expect(bitToBoolean('true')).toBe(true)
    expect(bitToBoolean('0')).toBe(false)
  })

  it('defaults to false for anything it does not recognise', () => {
    expect(bitToBoolean(null)).toBe(false)
    expect(bitToBoolean(undefined)).toBe(false)
    expect(bitToBoolean({})).toBe(false)
  })
})

/**
 * Gap E5: some `theme_item_related` rows point at a picture curated under a
 * DIFFERENT theme. The link is real and must be kept, but the viewer has to be
 * able to tell an in-theme neighbour from a cross-theme jump, which is what the
 * theme segment of the key is for.
 */
describe('relatedLinkThemeKey', () => {
  it('extracts the theme the relation was curated under', () => {
    expect(relatedLinkThemeKey('mwnf3_thematic_gallery:theme_item_related:47:10:1:2')).toBe(
      'mwnf3_thematic_gallery:theme:47:10'
    )
    expect(relatedLinkThemeKey('mwnf3_thematic_gallery:theme_item_related:47:5:4:6')).toBe(
      'mwnf3_thematic_gallery:theme:47:5'
    )
  })

  it('returns null for a key of any other family rather than mangling it', () => {
    expect(relatedLinkThemeKey('mwnf3:link:1234')).toBeNull()
    expect(relatedLinkThemeKey('mwnf3_explore:monument_sh:7')).toBeNull()
    expect(relatedLinkThemeKey(null)).toBeNull()
  })
})

/**
 * The importer files related content twice — once from the base table
 * (`language_id` null) and once per translation — so the exporter groups on the
 * legacy id and lets the translated row win. Getting the id wrong ships the
 * four link entries of this exhibition twice.
 */
describe('relatedContentLegacyId / relatedContentKind', () => {
  it('reads the same legacy id from both keyspaces', () => {
    expect(
      relatedContentLegacyId('mwnf3_thematic_gallery:exhibition_related_content:6:link')
    ).toBe('6')
    expect(
      relatedContentLegacyId('mwnf3_thematic_gallery:exhibition_related_content_i18n:6:en:link')
    ).toBe('6')
  })

  it('ignores keys from other families', () => {
    expect(relatedContentLegacyId('mwnf3_thematic_gallery:exhibition_logo:4')).toBeNull()
    expect(relatedContentLegacyId(null)).toBeNull()
  })

  it('separates an external link from a legacy-hosted document', () => {
    expect(relatedContentKind('mwnf3_thematic_gallery:exhibition_related_content:6:link')).toBe(
      'link'
    )
    expect(
      relatedContentKind('mwnf3_thematic_gallery:exhibition_related_content_i18n:1:en:document')
    ).toBe('document')
    expect(relatedContentKind('mwnf3_thematic_gallery:exhibition_related_content:6:other')).toBeNull()
  })
})
