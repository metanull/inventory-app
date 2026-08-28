import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

/**
 * `manifest.json` — what this package is and when it was made.
 *
 * `languages` lists every language the exhibition collection carries a
 * translation row for (`thg_gallery_lang` — Colours: de, en), not every
 * language in the database. It is metadata only: following the baroqueart
 * decision, a viewer decides what it can offer from the translation files that
 * are actually present, since an individual record may carry more languages
 * than the exhibition shipped in.
 *
 * It is deliberately NOT the same list as `exhibition.json.languages_enabled`,
 * which is the smaller set legacy actually publishes (`exhibition_i18n.enabled`
 * — Colours: en only). See ExhibitionExporter for why the two differ.
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
      [this.exhibition.id]
    )

    const languages = rows
      .map(row => langCodeMap.get(row.language_id))
      .filter((code): code is string => !!code)
      .sort()

    const manifest = {
      generatedAt: new Date().toISOString(),
      version: '1.0.0',
      site: 'the-use-of-colours-in-art',
      kind: 'exhibition',
      exhibition: {
        id: this.exhibition.id,
        backward_compatibility: this.exhibition.backwardCompatibility,
        slug: this.exhibition.slug,
        mwnf3_project_id: this.exhibition.mwnf3ProjectId,
      },
      languages,
      itemCount: this.memberItemIds.length,
      themeCount: this.themes.length,
    }

    await this.writeJson('manifest.json', manifest)
    this.logger.success(`manifest.json (${languages.join(', ')})`)

    return { file: 'manifest.json', count: 1 }
  }
}
