import { describe, expect, it, vi } from 'vitest'

import { ManifestExporter } from '../../src/exporters/manifest-exporter.js'
import type { ExportContext } from '../../src/core/types.js'

/**
 * `manifest.site` is the one thing a website reads before it mounts: the
 * languages it offers, in switcher order with their native labels, and its
 * name per language. For a project website the offered languages are the
 * ones at least one exported item is translated in — the manifest's
 * `languages` list is every language in the database, and most of them have
 * no content here.
 */
function contextWith(rows: Record<string, unknown[]>): ExportContext {
  const query = vi.fn(async (sql: string) => {
    if (sql.includes('FROM item_translations')) return rows.itemLanguages
    if (sql.includes('FROM collection_translations')) return rows.names
    if (sql.includes('FROM languages')) return rows.languages
    throw new Error(`Unexpected query: ${sql}`)
  })
  return {
    db: { query } as unknown as ExportContext['db'],
    outputDir: '/tmp/none',
    projectIds: ['isl-uuid', 'epm-uuid'],
    contextIds: ['isl-context', 'epm-context'],
    projectKeys: ['ISL', 'EPM'],
    baseUrl: 'https://example.org',
    logger: { info: vi.fn(), success: vi.fn(), warning: vi.fn(), error: vi.fn() } as unknown as ExportContext['logger'],
  }
}

describe('ManifestExporter', () => {
  it('offers the languages the items carry, with native labels, and names the primary project', async () => {
    const context = contextWith({
      languages: [
        { backward_compatibility: 'en' },
        { backward_compatibility: 'fr' },
        { backward_compatibility: 'fa' },
      ],
      itemLanguages: [
        { language_id: 'fra', code: 'fr', name: 'Français' },
        { language_id: 'eng', code: 'en', name: 'English' },
        { language_id: 'ara', code: 'ar', name: null },
      ],
      names: [
        { language_id: 'en', title: 'Discover Islamic Art' },
        { language_id: 'fr', title: 'Découvrir l’art islamique' },
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

    const manifest = written[0] as {
      site: { key: string; languages: unknown[]; names: unknown }
      languages: string[]
      projectKeys: string[]
    }
    expect(manifest.languages).toEqual(['en', 'fr', 'fa'])
    expect(manifest.projectKeys).toEqual(['ISL', 'EPM'])
    expect(manifest.site.key).toBe('baroqueart')
    expect(manifest.site.languages).toEqual([
      { code: 'ar', label: 'AR' },
      { code: 'en', label: 'English' },
      { code: 'fr', label: 'Français' },
    ])
    expect(manifest.site.names).toEqual({ en: 'Discover Islamic Art', fr: 'Découvrir l’art islamique' })
  })
})
