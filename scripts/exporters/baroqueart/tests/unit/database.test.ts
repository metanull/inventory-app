import { describe, expect, it, vi } from 'vitest'

import { Database } from '../../src/core/database.js'

/**
 * resolveProjectIds / resolveContextIds must return ids in ARGUMENT order:
 * callers pair projectIds[i] with projectKeys[i] (partner-exporter.ts), while
 * the underlying `IN (...)` query gives no ordering guarantee — the DB is free
 * to return rows in any order.
 */
describe('Database.resolveProjectIds', () => {
  it('returns ids in argument order even when the DB returns rows in another order', async () => {
    const db = new Database()
    vi.spyOn(db, 'query').mockResolvedValue([
      // DB order deliberately reversed vs the argument order below
      { id: 'epm-uuid', backward_compatibility: 'mwnf3:projects:EPM' },
      { id: 'isl-uuid', backward_compatibility: 'mwnf3:projects:ISL' },
    ])

    const ids = await db.resolveProjectIds(['ISL', 'EPM'])

    expect(ids).toEqual(['isl-uuid', 'epm-uuid'])
  })

  it('throws when a requested project is missing', async () => {
    const db = new Database()
    vi.spyOn(db, 'query').mockResolvedValue([
      { id: 'isl-uuid', backward_compatibility: 'mwnf3:projects:ISL' },
    ])

    await expect(db.resolveProjectIds(['ISL', 'EPM'])).rejects.toThrow(
      'Projects not found for: mwnf3:projects:EPM'
    )
  })
})

describe('Database.resolveContextIds', () => {
  it('returns ids in argument order even when the DB returns rows in another order', async () => {
    const db = new Database()
    vi.spyOn(db, 'query').mockResolvedValue([
      { id: 'epm-ctx-uuid', backward_compatibility: 'mwnf3:projects:EPM' },
      { id: 'isl-ctx-uuid', backward_compatibility: 'mwnf3:projects:ISL' },
    ])

    const ids = await db.resolveContextIds(['ISL', 'EPM'])

    expect(ids).toEqual(['isl-ctx-uuid', 'epm-ctx-uuid'])
  })
})
