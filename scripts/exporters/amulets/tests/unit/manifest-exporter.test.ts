import { describe, expect, it, vi } from 'vitest'

import { ManifestExporter } from '../../src/exporters/manifest-exporter.js'
import type { ExportContext } from '../../src/core/types.js'

/**
 * `manifest.site` is the one thing a website reads before it mounts: the
 * languages it offers, in switcher order with their native labels, and its
 * name per language. Everything else in the package is loaded lazily, so a
 * mistake here is a website that boots with no languages and no name.
 */
function contextWith(rows: Record<string, unknown[]>): ExportContext {
  const query = vi.fn(async (sql: string) => {
    if (sql.includes('FROM languages')) return rows.languages
    if (sql.includes('FROM collection_translations')) return rows.translations
    if (sql.includes('FROM language_translations')) return rows.labels
    throw new Error(`Unexpected query: ${sql}`)
  })
  return {
    db: { query } as unknown as ExportContext['db'],
    outputDir: '/tmp/none',
    gallery: {
      id: 'gallery-uuid',
      backwardCompatibility: 'mwnf3_thematic_gallery:thg_gallery:9',
      slug: 'carpets',
      host: 'https://carpets.museumwnf.org',
      mwnf3ProjectId: 'DCA',
      projectId: null,
      anchor: {},
      chrome: {},
    },
    memberItemIds: ['a', 'b'],
    itemProjectKeys: new Map(),
    itemOwnContextIds: new Map(),
    baseUrl: 'https://example.org',
    logger: { info: vi.fn(), success: vi.fn(), warning: vi.fn(), error: vi.fn() } as unknown as ExportContext['logger'],
  }
}

describe('ManifestExporter', () => {
  it('writes the site languages in switcher order with native labels, and the names', async () => {
    const context = contextWith({
      languages: [
        { id: 'eng', backward_compatibility: 'en' },
        { id: 'fra', backward_compatibility: 'fr' },
        { id: 'ara', backward_compatibility: 'ar' },
        { id: 'swa', backward_compatibility: null },
      ],
      translations: [
        { language_id: 'fra', title: 'Tapis' },
        { language_id: 'eng', title: 'Carpets' },
        { language_id: 'ara', title: 'السجاد' },
        { language_id: 'swa', title: 'ignored' },
      ],
      labels: [
        { language_id: 'eng', name: 'English' },
        { language_id: 'fra', name: 'Français' },
      ],
    })
    const exporter = new ManifestExporter(context)
    const written: unknown[] = []
    vi.spyOn(exporter as unknown as { writeJson: (f: string, d: unknown) => Promise<void> }, 'writeJson').mockImplementation(
      async (_file, data) => {
        written.push(data)
      }
    )

    await exporter.export()

    const manifest = written[0] as { site: { key: string; languages: unknown[]; names: unknown }; languages: string[] }
    expect(manifest.languages).toEqual(['ar', 'en', 'fr'])
    expect(manifest.site.key).toBe('amulets')
    expect(manifest.site.languages).toEqual([
      { code: 'ar', label: 'AR' },
      { code: 'en', label: 'English' },
      { code: 'fr', label: 'Français' },
    ])
    expect(manifest.site.names).toEqual({ en: 'Carpets', fr: 'Tapis', ar: 'السجاد' })
  })
})
