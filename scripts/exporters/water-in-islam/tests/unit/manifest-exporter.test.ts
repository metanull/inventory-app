import { describe, expect, it, vi } from 'vitest'

import { ManifestExporter } from '../../src/exporters/manifest-exporter.js'
import type { ExportContext } from '../../src/core/types.js'

/**
 * `manifest.site` is the one thing a website reads before it mounts: the
 * languages it offers, in switcher order with their native labels, and its
 * name per language. For an exhibition the offered languages are the ones
 * legacy publishes (`exhibition_i18n.enabled`), not every language the
 * collection carries a title in.
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
    exhibition: {
      id: 'exhibition-uuid',
      backwardCompatibility: 'mwnf3_thematic_gallery:thg_gallery:56',
      slug: 'water_in_islam',
      host: 'https://exhibitions.museumwnf.org',
      mwnf3ProjectId: 'GalEx6',
      projectId: null,
      anchor: {},
      chrome: {},
      i18n: new Map([
        ['eng', { enabled: 'Y' }],
        ['deu', { enabled: 'N' }],
      ]),
    },
    themes: [],
    memberItemIds: ['a'],
    itemProjectKeys: new Map(),
    itemOwnContextIds: new Map(),
    baseUrl: 'https://example.org',
    logger: { info: vi.fn(), success: vi.fn(), warning: vi.fn(), error: vi.fn() } as unknown as ExportContext['logger'],
  } as unknown as ExportContext
}

describe('ManifestExporter', () => {
  it('offers only the published languages, with native labels, and names every titled one', async () => {
    const context = contextWith({
      languages: [
        { id: 'eng', backward_compatibility: 'en' },
        { id: 'deu', backward_compatibility: 'de' },
      ],
      translations: [
        { language_id: 'deu', title: 'Wasser im Islam' },
        { language_id: 'eng', title: 'Water in Islam' },
      ],
      labels: [{ language_id: 'eng', name: 'English' }],
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
    expect(manifest.languages).toEqual(['de', 'en'])
    expect(manifest.site.key).toBe('water-in-islam')
    expect(manifest.site.languages).toEqual([{ code: 'en', label: 'English' }])
    expect(manifest.site.names).toEqual({ en: 'Water in Islam', de: 'Wasser im Islam' })
  })
})
