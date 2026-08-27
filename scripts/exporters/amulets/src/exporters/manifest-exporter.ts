import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

/**
 * `manifest.json` — what this package is and when it was made.
 *
 * `languages` lists the gallery's own UI languages (`thg_gallery_lang`), not
 * every language in the database. It is metadata only: following the baroqueart
 * decision, a viewer decides what it can offer from the translation files that
 * are actually present, since an individual record may carry more languages
 * than the gallery shipped in.
 */
export class ManifestExporter extends BaseExporter {
  getName(): string {
    return 'Manifest'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Writing manifest.json...')

    const langCodeMap = await this.buildLangCodeMap()
    const rows = await this.db.query<{ language_id: string }>(
      `SELECT DISTINCT language_id FROM collection_translations WHERE collection_id = ?`,
      [this.gallery.id]
    )

    const languages = rows
      .map(row => langCodeMap.get(row.language_id))
      .filter((code): code is string => !!code)
      .sort()

    const manifest = {
      generatedAt: new Date().toISOString(),
      version: '1.0.0',
      site: 'amulets',
      kind: 'gallery',
      gallery: {
        id: this.gallery.id,
        backward_compatibility: this.gallery.backwardCompatibility,
        slug: this.gallery.slug,
        mwnf3_project_id: this.gallery.mwnf3ProjectId,
      },
      languages,
      itemCount: this.memberItemIds.length,
    }

    await this.writeJson('manifest.json', manifest)
    this.logger.success(`manifest.json (${languages.join(', ')})`)

    return { file: 'manifest.json', count: 1 }
  }
}
