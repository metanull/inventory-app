import { describe, expect, it } from 'vitest'

import { bitToBoolean, isFeatured, isHidden } from '../../src/exporters/gallery-exporter.js'

/**
 * The legacy `featured` flag is INVERTED, and the temptation to "fix" it on the
 * way through is exactly what these tests guard against. dxa-api computes
 * `CASE WHEN featured = 'A' THEN 0 ELSE 1 END`
 * (app/MWNF/SQL/thg/WithTHGTemporaryTables.php), so 'A' means NOT featured —
 * amulets stores 'H' and the live site reports featured: true.
 */
describe('isFeatured', () => {
  it("treats 'A' as NOT featured, reproducing the legacy inversion", () => {
    expect(isFeatured('A')).toBe(false)
  })

  it("treats 'H' as featured — the amulets case, matching the live API", () => {
    expect(isFeatured('H')).toBe(true)
  })

  it('treats a missing flag as not featured', () => {
    expect(isFeatured(undefined)).toBe(false)
    expect(isFeatured(null)).toBe(false)
  })
})

describe('isHidden', () => {
  it("shows a gallery only when status is 'A'", () => {
    expect(isHidden('A')).toBe(false)
    expect(isHidden('H')).toBe(true)
    expect(isHidden(undefined)).toBe(true)
  })
})

/**
 * `has_timeline` / `has_country_timeline` are MySQL bit(1). The importer
 * normalizes them to JSON booleans, but rows written before that fix still hold
 * the raw mysql2 Buffer shape — and a naive read would treat that object as
 * truthy and report a timeline the site does not have.
 */
describe('bitToBoolean', () => {
  it('accepts the normalized boolean form', () => {
    expect(bitToBoolean(true)).toBe(true)
    expect(bitToBoolean(false)).toBe(false)
  })

  it('accepts the raw mysql2 Buffer form left by older imports', () => {
    expect(bitToBoolean({ type: 'Buffer', data: [1] })).toBe(true)
    expect(bitToBoolean({ type: 'Buffer', data: [0] })).toBe(false)
  })

  it('accepts numeric and string encodings', () => {
    expect(bitToBoolean(1)).toBe(true)
    expect(bitToBoolean(0)).toBe(false)
    expect(bitToBoolean('1')).toBe(true)
    expect(bitToBoolean('true')).toBe(true)
    expect(bitToBoolean('0')).toBe(false)
  })

  it('defaults to false for absent or unrecognized values', () => {
    expect(bitToBoolean(null)).toBe(false)
    expect(bitToBoolean(undefined)).toBe(false)
    expect(bitToBoolean({})).toBe(false)
  })
})
