import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

interface ExhibitionTranslationRow {
  language_id: string
  title: string | null
}

interface LanguageNameRow {
  language_id: string
  name: string
}

/**
 * `manifest.json` — what this package is and when it was made, and the one
 * thing a website reads before it mounts.
 *
 * `languages` lists every language the exhibition collection carries a
 * translation row for (`thg_gallery_lang`), not every language in the
 * database. It is metadata only: a website offers a language only where the
 * item translation files actually carry it, since an individual record may
 * carry more languages than the exhibition shipped in.
 *
 * `site` is what the website's `dataset.config.js` needs at boot and cannot
 * read from `exhibition.json` any more, now that every entity is loaded
 * lazily: the languages legacy actually publishes (`exhibition_i18n.enabled`
 * — the same set as `exhibition.json.languages_enabled`), each with its
 * native label, and the exhibition's title per language.
 */
export class ManifestExporter extends BaseExporter {
  getName(): string {
    return 'Manifest'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Writing manifest.json...')

    const langCodeMap = await this.buildLangCodeMap()
    const rows = await this.db.query<ExhibitionTranslationRow>(
      `SELECT language_id, title FROM collection_translations WHERE collection_id = ?`,
      [this.exhibition.id]
    )

    const names: Record<string, string> = {}
    const enabledIds: string[] = []
    const languages: string[] = []
    for (const row of rows) {
      const code = langCodeMap.get(row.language_id)
      if (!code) continue
      languages.push(code)
      if (row.title) names[code] = row.title
      if (this.exhibition.i18n.get(row.language_id)?.enabled === 'Y') enabledIds.push(row.language_id)
    }
    languages.sort()

    const manifest = {
      generatedAt: new Date().toISOString(),
      version: '1.0.0',
      site: {
        key: 'the-use-of-colours-in-art',
        languages: await this.siteLanguages(enabledIds, langCodeMap),
        names,
      },
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

  /**
   * The exhibition's published languages as the switcher shows them: the code
   * and the language's own name for itself (`language_translations` where the
   * display language is the language), the code in capitals where the table
   * has none. Alphabetical by code.
   */
  private async siteLanguages(
    languageIds: string[],
    langCodeMap: Map<string, string>
  ): Promise<{ code: string; label: string }[]> {
    if (languageIds.length === 0) return []
    const ph = this.placeholders(languageIds.length)
    const rows = await this.db.query<LanguageNameRow>(
      `SELECT language_id, name FROM language_translations
       WHERE language_id IN (${ph}) AND display_language_id = language_id`,
      languageIds
    )
    const labels = new Map(rows.map(row => [row.language_id, row.name]))
    return languageIds
      .map(id => ({ code: langCodeMap.get(id) as string, label: labels.get(id) || (langCodeMap.get(id) as string).toUpperCase() }))
      .sort((a, b) => (a.code < b.code ? -1 : a.code > b.code ? 1 : 0))
  }
}
