import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

interface DynastyRow {
  id: string
  backward_compatibility: string | null
  from_ah: number | null
  to_ah: number | null
  from_ad: number | null
  to_ad: number | null
}

interface DynastyTranslationRow {
  dynasty_id: string
  language_id: string
  name: string | null
  also_known_as: string | null
  area: string | null
  history: string | null
  date_description_ah: string | null
  date_description_ad: string | null
}

export class DynastyExporter extends BaseExporter {
  getName(): string {
    return 'Dynasties'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting dynasties.json...')

    // The `dynasties` table has no project/context column of its own — legacy's
    // mwnf3.dynasties is imported wholesale (dynasty-importer.ts has no project filter),
    // and today it holds only ISL's 60 rows, so exporting the full table reproduces
    // legacy's dynasty list exactly. Scoping via item_dynasty instead (as this used to)
    // silently dropped the ~17 dynasties legacy lists that no cataloged item happens to
    // reference — legacy shows all defined dynasties regardless of item usage.
    // If a second project's dynasties are ever imported into this same table, this will
    // need real project scoping (a column on `dynasties`, mirroring the still-open
    // cross-project scoping problem in glossary-exporter.ts) rather than exporting everything.
    const dynasties = await this.db.query<DynastyRow>(
      `SELECT id, backward_compatibility, from_ah, to_ah, from_ad, to_ad
       FROM dynasties
       ORDER BY from_ad`
    )

    if (dynasties.length === 0) {
      await this.writeJson('dynasties.json', [])
      this.logger.warning('dynasties.json (0 — no dynasties linked to project items)')
      return { file: 'dynasties.json', count: 0 }
    }

    const dynastyIds = dynasties.map(d => d.id)
    const langCodeMap = await this.buildLangCodeMap()

    const translations = await this.db.query<DynastyTranslationRow>(
      `SELECT dynasty_id, language_id, name, also_known_as, area, history,
              date_description_ah, date_description_ad
       FROM dynasty_translations
       WHERE dynasty_id IN (${this.placeholders(dynastyIds.length)})`,
      dynastyIds
    )

    // dynasty_id -> lang_code -> translation fields
    const translationMap = new Map<string, Record<string, Record<string, string | null>>>()
    for (const t of translations) {
      if (!translationMap.has(t.dynasty_id)) {
        translationMap.set(t.dynasty_id, {})
      }
      const code = langCodeMap.get(t.language_id)
      if (code) {
        translationMap.get(t.dynasty_id)![code] = {
          name: t.name,
          also_known_as: t.also_known_as,
          area: t.area,
          history: t.history,
          date_description_ah: t.date_description_ah,
          date_description_ad: t.date_description_ad,
        }
      }
    }

    // Write one translations/dynasties.{lang}.json per language (null fields omitted)
    const byLang = new Map<string, Record<string, unknown>>()
    for (const [dynastyId, langMap] of translationMap) {
      for (const [langCode, fields] of Object.entries(langMap)) {
        if (!byLang.has(langCode)) byLang.set(langCode, {})
        byLang.get(langCode)![dynastyId] = this.stripNulls(fields as Record<string, unknown>)
      }
    }
    await this.writeTranslationFiles('dynasties', byLang)

    const output = dynasties.map(d => ({
      id: d.id,
      from_ah: d.from_ah,
      to_ah: d.to_ah,
      from_ad: d.from_ad,
      to_ad: d.to_ad,
    }))

    await this.writeJson('dynasties.json', output)
    this.logger.success(`dynasties.json (${output.length} dynasties)`)

    return { file: 'dynasties.json', count: output.length }
  }
}
