import { describe, expect, it } from 'vitest'

import {
  parentKeyForDetail,
  resolveDetailParents,
  type DetailRow,
} from '../../src/exporters/item-exporter.js'

/**
 * #1515: monument details are embedded as details[] on their parent monument,
 * never exported as top-level items. Details imported with parent_id = null
 * ("Parent monument not found" importer warnings) are recovered through their
 * backward_compatibility key; anything unresolvable is an orphan the exporter
 * drops with a warning.
 */
describe('parentKeyForDetail', () => {
  it('derives the parent monument key from a monument_details key', () => {
    expect(parentKeyForDetail('mwnf3:monument_details:bar:pt:inst01:12:3')).toBe(
      'mwnf3:monuments:bar:pt:inst01:12'
    )
  })

  it('returns null for null input', () => {
    expect(parentKeyForDetail(null)).toBeNull()
  })

  it('returns null for keys of other tables', () => {
    expect(parentKeyForDetail('mwnf3:monuments:bar:pt:inst01:12')).toBeNull()
    expect(parentKeyForDetail('mwnf3:objects:bar:pt:inst01:12')).toBeNull()
  })

  it('returns null for malformed monument_details keys', () => {
    expect(parentKeyForDetail('mwnf3:monument_details:bar:pt:inst01:12')).toBeNull()
    expect(parentKeyForDetail('mwnf3:monument_details:bar:pt:inst01:12:3:extra')).toBeNull()
  })
})

describe('resolveDetailParents', () => {
  const detail = (overrides: Partial<DetailRow>): DetailRow => ({
    id: 'detail-1',
    internal_name: 'Detail',
    backward_compatibility: null,
    parent_id: null,
    display_order: null,
    ...overrides,
  })

  const parents = [
    { id: 'mon-1', backward_compatibility: 'mwnf3:monuments:bar:pt:inst01:12' },
    { id: 'mon-2', backward_compatibility: 'mwnf3:monuments:bar:es:inst02:7' },
  ]

  it('assigns a detail to its parent via parent_id', () => {
    const d = detail({ parent_id: 'mon-1' })
    const { byParent, orphans, recovered } = resolveDetailParents([d], parents)
    expect(byParent.get('mon-1')).toEqual([d])
    expect(orphans).toEqual([])
    expect(recovered).toEqual([])
  })

  it('recovers a parent_id=null detail via its backward_compatibility key', () => {
    const d = detail({
      backward_compatibility: 'mwnf3:monument_details:bar:es:inst02:7:4',
    })
    const { byParent, orphans, recovered } = resolveDetailParents([d], parents)
    expect(byParent.get('mon-2')).toEqual([d])
    expect(orphans).toEqual([])
    expect(recovered).toEqual([d])
  })

  it('falls back to key recovery when parent_id points outside the export', () => {
    const d = detail({
      parent_id: 'not-exported',
      backward_compatibility: 'mwnf3:monument_details:bar:pt:inst01:12:1',
    })
    const { byParent, orphans, recovered } = resolveDetailParents([d], parents)
    expect(byParent.get('mon-1')).toEqual([d])
    expect(orphans).toEqual([])
    expect(recovered).toEqual([d])
  })

  it('returns unresolvable details as orphans, never assigning them', () => {
    const noKey = detail({ id: 'detail-nokey' })
    const badKey = detail({
      id: 'detail-badkey',
      backward_compatibility: 'mwnf3:monument_details:bar:it:instXX:99:1',
    })
    const { byParent, orphans, recovered } = resolveDetailParents([noKey, badKey], parents)
    expect(byParent.size).toBe(0)
    expect(orphans).toEqual([noKey, badKey])
    expect(recovered).toEqual([])
  })

  it('keeps sibling details in input order under their shared parent', () => {
    const d1 = detail({ id: 'd1', parent_id: 'mon-1' })
    const d2 = detail({
      id: 'd2',
      backward_compatibility: 'mwnf3:monument_details:bar:pt:inst01:12:2',
    })
    const { byParent } = resolveDetailParents([d1, d2], parents)
    expect(byParent.get('mon-1')?.map(d => d.id)).toEqual(['d1', 'd2'])
  })
})
