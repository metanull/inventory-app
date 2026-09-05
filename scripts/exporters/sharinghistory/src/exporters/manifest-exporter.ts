import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

interface LanguageNameRow {
  language_id: string
  name: string
}

interface ProjectNameRow {
  language_id: string
  title: string | null
}

/**
 * `manifest.json` — what this package is and when it was made, and the one
 * thing a website reads before it mounts.
 *
 * `languages` lists every language in the database, as it always has. `site`
 * is what the website's `dataset.config.js` needs at boot: the languages the
 * exported items actually carry a translation in, in switcher order, each
 * with its native label, and the name of the primary project per language.
 */
export class ManifestExporter extends BaseExporter {
  getName(): string {
    return 'Manifest'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Writing manifest.json...')

    const langRows = await this.db.query<{ backward_compatibility: string }>(
      `SELECT backward_compatibility FROM languages WHERE backward_compatibility IS NOT NULL ORDER BY id`
    )

    const manifest = {
      generatedAt: new Date().toISOString(),
      projectKeys: this.context.projectKeys,
      projectIds: this.context.projectIds,
      version: '1.0.0',
      site: {
        key: 'sharinghistory',
        languages: await this.siteLanguages(),
        names: await this.siteNames(),
      },
      languages: langRows.map(r => r.backward_compatibility),
    }

    await this.writeJson('manifest.json', manifest)
    this.logger.success(`manifest.json (${manifest.site.languages.map(l => l.code).join(', ')})`)

    return { file: 'manifest.json', count: 1 }
  }

  /**
   * The languages at least one exported item is translated in, as the
   * switcher shows them: the code and the language's own name for itself
   * (`language_translations` where the display language is the language), the
   * code in capitals where the table has none. Alphabetical by code, which is
   * the order these websites have always used.
   */
  private async siteLanguages(): Promise<{ code: string; label: string }[]> {
    const ph = this.placeholders(this.projectIds.length)
    const rows = await this.db.query<LanguageNameRow & { code: string }>(
      `SELECT DISTINCT l.id AS language_id, l.backward_compatibility AS code, lt.name
       FROM item_translations it
       JOIN items i ON i.id = it.item_id
       JOIN languages l ON l.id = it.language_id
       LEFT JOIN language_translations lt
         ON lt.language_id = l.id AND lt.display_language_id = l.id
       WHERE i.project_id IN (${ph}) AND l.backward_compatibility IS NOT NULL`,
      this.projectIds
    )
    return rows
      .map(row => ({ code: row.code, label: row.name || row.code.toUpperCase() }))
      .sort((a, b) => (a.code < b.code ? -1 : a.code > b.code ? 1 : 0))
  }

  /**
   * The name of the primary project per language, read from the collection
   * that represents the project: the importer gives that collection the
   * project's own `backward_compatibility` (`mwnf3:projects:<KEY>`).
   */
  private async siteNames(): Promise<Record<string, string>> {
    const primary = this.projectIds[0]
    if (!primary) return {}
    const rows = await this.db.query<ProjectNameRow>(
      `SELECT l.backward_compatibility AS language_id, ct.title
       FROM collection_translations ct
       JOIN collections c ON c.id = ct.collection_id
       JOIN projects p ON p.backward_compatibility = c.backward_compatibility
       JOIN languages l ON l.id = ct.language_id
       WHERE p.id = ? AND l.backward_compatibility IS NOT NULL`,
      [primary]
    )
    const names: Record<string, string> = {}
    for (const row of rows) {
      if (row.title) names[row.language_id] = row.title
    }
    return names
  }
}
