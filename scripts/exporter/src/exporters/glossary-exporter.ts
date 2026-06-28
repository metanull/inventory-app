import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

interface GlossaryRow {
  id: string
  internal_name: string
  backward_compatibility: string | null
}

interface GlossaryTranslationRow {
  glossary_id: string
  language_id: string
  definition: string
}

interface GlossarySpellingRow {
  glossary_id: string
  language_id: string
  spelling: string
}

export class GlossaryExporter extends BaseExporter {
  getName(): string {
    return 'Glossary'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting glossary.json...')

    const entries = await this.db.query<GlossaryRow>(
      `SELECT id, internal_name, backward_compatibility FROM glossaries ORDER BY internal_name`
    )

    if (entries.length === 0) {
      await this.writeJson('glossary.json', [])
      this.logger.warning('glossary.json (0 entries)')
      return { file: 'glossary.json', count: 0 }
    }

    const glossaryIds = entries.map(e => e.id)
    const glossPh = this.placeholders(glossaryIds.length)

    const [translations, spellings] = await Promise.all([
      this.db.query<GlossaryTranslationRow>(
        `SELECT glossary_id, language_id, definition FROM glossary_translations WHERE glossary_id IN (${glossPh})`,
        glossaryIds
      ),
      this.db.query<GlossarySpellingRow>(
        `SELECT glossary_id, language_id, spelling FROM glossary_spellings WHERE glossary_id IN (${glossPh})`,
        glossaryIds
      ),
    ])

    const langCodeMap = await this.buildLangCodeMap()

    // Build: glossary_id -> lang_code -> definition
    const definitionMap = new Map<string, Record<string, string>>()
    for (const t of translations) {
      if (!definitionMap.has(t.glossary_id)) {
        definitionMap.set(t.glossary_id, {})
      }
      const code = langCodeMap.get(t.language_id)
      if (code) {
        definitionMap.get(t.glossary_id)![code] = t.definition
      }
    }

    // Build: glossary_id -> lang_code -> spellings[]
    const spellingMap = new Map<string, Record<string, string[]>>()
    for (const s of spellings) {
      if (!spellingMap.has(s.glossary_id)) {
        spellingMap.set(s.glossary_id, {})
      }
      const code = langCodeMap.get(s.language_id)
      if (code) {
        const map = spellingMap.get(s.glossary_id)!
        if (!map[code]) {
          map[code] = []
        }
        map[code].push(s.spelling)
      }
    }

    // Collect all language codes that appear in either map
    const allLangs = new Set<string>()
    for (const m of definitionMap.values()) Object.keys(m).forEach(k => allLangs.add(k))
    for (const m of spellingMap.values()) Object.keys(m).forEach(k => allLangs.add(k))

    // Write one translations/glossary.{lang}.json per language
    const byLang = new Map<string, Record<string, unknown>>()
    for (const entry of entries) {
      const defs = definitionMap.get(entry.id) ?? {}
      const spells = spellingMap.get(entry.id) ?? {}
      for (const lang of allLangs) {
        if (!byLang.has(lang)) byLang.set(lang, {})
        const obj: Record<string, unknown> = {}
        if (defs[lang] !== undefined) obj['definition'] = defs[lang]
        if (spells[lang] !== undefined) obj['spellings'] = spells[lang]
        if (Object.keys(obj).length > 0) byLang.get(lang)![entry.id] = obj
      }
    }
    await this.writeTranslationFiles('glossary', byLang)

    const output = entries.map(entry => ({
      id: entry.id,
      word: entry.internal_name,
    }))

    await this.writeJson('glossary.json', output)
    this.logger.success(`glossary.json (${output.length} entries)`)

    return { file: 'glossary.json', count: output.length }
  }
}
