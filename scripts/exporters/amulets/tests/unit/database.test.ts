import { describe, expect, it, vi } from 'vitest'

import { Database, legacyProjectKey } from '../../src/core/database.js'

/**
 * A gallery's members are borrowed from other databases, so the source project
 * shown on each item sheet is per-item data, resolved from the project's
 * backward_compatibility key. The two keyspaces differ in shape and case.
 */
describe('legacyProjectKey', () => {
  it('reads mwnf3 project codes', () => {
    expect(legacyProjectKey('mwnf3:projects:EPM')).toBe('EPM')
    expect(legacyProjectKey('mwnf3:projects:ISL')).toBe('ISL')
  })

  it('reads Sharing History project keys, which are lowercase by convention', () => {
    expect(legacyProjectKey('mwnf3_sharing_history:sh_projects:awe')).toBe('awe')
  })

  it('returns null when there is no project', () => {
    expect(legacyProjectKey(null)).toBeNull()
    expect(legacyProjectKey('')).toBeNull()
  })
})

describe('Database.resolveGallery', () => {
  const galleryRow = {
    id: 'gallery-uuid',
    backward_compatibility: 'mwnf3_thematic_gallery:thg_gallery:4',
    type: 'gallery',
    extra: {
      thg_gallery: {
        slug: 'amulets_and_talismans',
        host: 'https://amulets.museumwnf.org',
        mwnf3_project_id: 'AMU',
      },
    },
  }

  it('reads the anchor the importer wrote to collections.extra', async () => {
    const db = new Database()
    const querySpy = vi
      .spyOn(db, 'query')
      .mockResolvedValueOnce([galleryRow])
      // resolveProjectId
      .mockResolvedValueOnce([{ id: 'project-amu-uuid' }])
      // loadGalleryChrome
      .mockResolvedValueOnce([{ extra: { thg_gallery: { featured: 'H', status: 'A' } } }])

    const gallery = await db.resolveGallery('mwnf3_thematic_gallery:thg_gallery:4')

    expect(gallery.slug).toBe('amulets_and_talismans')
    expect(gallery.host).toBe('https://amulets.museumwnf.org')
    expect(gallery.mwnf3ProjectId).toBe('AMU')
    expect(gallery.chrome.featured).toBe('H')

    // The legacy code is turned into the inventory project UUID here, so the
    // MWNF-384 partner branch compares ids rather than a hardcoded 'AMU'.
    expect(gallery.projectId).toBe('project-amu-uuid')
    expect(querySpy.mock.calls[1]?.[1]).toEqual(['mwnf3:projects:AMU'])
  })

  it('leaves projectId null when the gallery has no mwnf3 project', async () => {
    const db = new Database()
    vi.spyOn(db, 'query')
      .mockResolvedValueOnce([{ ...galleryRow, extra: { thg_gallery: { slug: 'galleries' } } }])
      .mockResolvedValueOnce([])

    const gallery = await db.resolveGallery('mwnf3_thematic_gallery:thg_gallery:45')

    // Galleries 43 and 45 have no mwnf3_project_id at all — the lookup must be
    // skipped entirely rather than searching for 'mwnf3:projects:null'.
    expect(gallery.mwnf3ProjectId).toBeNull()
    expect(gallery.projectId).toBeNull()
  })

  it('skips language rows with no thg_gallery chrome rather than returning empty', async () => {
    const db = new Database()
    vi.spyOn(db, 'query')
      .mockResolvedValueOnce([galleryRow])
      .mockResolvedValueOnce([{ id: 'project-amu-uuid' }])
      .mockResolvedValueOnce([
        { extra: { something_else: true } },
        { extra: { thg_gallery: { banner_item: 'mwnf#obj#EPM;at;Mus22;51' } } },
      ])

    const gallery = await db.resolveGallery('mwnf3_thematic_gallery:thg_gallery:4')

    expect(gallery.chrome.banner_item).toBe('mwnf#obj#EPM;at;Mus22;51')
  })

  it('fails loudly when the gallery is absent', async () => {
    const db = new Database()
    vi.spyOn(db, 'query').mockResolvedValue([])

    await expect(db.resolveGallery('mwnf3_thematic_gallery:thg_gallery:4')).rejects.toThrow(
      'Gallery collection not found'
    )
  })

  /**
   * Exhibitions live in the same table and would otherwise export as a
   * half-empty gallery package — no themes, no curated texts, no sign anything
   * is missing. Refusing is the only safe answer.
   */
  it('refuses an exhibition collection', async () => {
    const db = new Database()
    vi.spyOn(db, 'query').mockResolvedValue([
      { ...galleryRow, type: 'exhibition', backward_compatibility: 'mwnf3_thematic_gallery:thg_gallery:47' },
    ])

    await expect(db.resolveGallery('mwnf3_thematic_gallery:thg_gallery:47')).rejects.toThrow(
      "is a 'exhibition', not a 'gallery'"
    )
  })
})

describe('Database.resolveItemProjects', () => {
  it('maps each member item to its own project key and context', async () => {
    const db = new Database()
    vi.spyOn(db, 'query').mockResolvedValue([
      { id: 'p-epm', backward_compatibility: 'mwnf3:projects:EPM', context_id: 'ctx-epm' },
      { id: 'p-awe', backward_compatibility: 'mwnf3_sharing_history:sh_projects:awe', context_id: 'ctx-awe' },
    ])

    const { projectKeys, ownContextIds } = await db.resolveItemProjects([
      { item_id: 'item-1', project_id: 'p-epm' },
      { item_id: 'item-2', project_id: 'p-awe' },
      { item_id: 'item-3', project_id: null },
    ])

    expect(projectKeys.get('item-1')).toBe('EPM')
    expect(projectKeys.get('item-2')).toBe('awe')
    expect(projectKeys.has('item-3')).toBe(false)
    expect(ownContextIds.get('item-1')).toBe('ctx-epm')
    expect(ownContextIds.get('item-2')).toBe('ctx-awe')
  })

  it('does not query when no member has a project', async () => {
    const db = new Database()
    const querySpy = vi.spyOn(db, 'query')

    const { projectKeys } = await db.resolveItemProjects([{ item_id: 'item-1', project_id: null }])

    expect(projectKeys.size).toBe(0)
    expect(querySpy).not.toHaveBeenCalled()
  })
})
