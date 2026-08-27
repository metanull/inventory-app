import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

interface CountryRow {
  id: string
  internal_name: string
  backward_compatibility: string
}

interface CountryTranslationRow {
  country_id: string
  language_id: string
  name: string
}

/**
 * `countries.json` — scoped to the gallery, not the whole world.
 *
 * The country dropdown on the collection search is built from the member items'
 * countries (legacy `/items/countries`), and the partners page groups by the
 * holding museum's country, which is not always the item's. Both sets are
 * needed; nothing else is.
 */
export class CountryExporter extends BaseExporter {
  getName(): string {
    return 'Countries'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting countries.json...')

    if (this.memberItemIds.length === 0) {
      await this.writeJson('countries.json', [])
      this.logger.warning('countries.json (0 — gallery has no member items)')
      return { file: 'countries.json', count: 0 }
    }

    const itemPh = this.placeholders(this.memberItemIds.length)
    const used = await this.db.query<{ country_id: string }>(
      `SELECT DISTINCT i.country_id
       FROM items i
       WHERE i.id IN (${itemPh}) AND i.country_id IS NOT NULL

       UNION

       SELECT DISTINCT p.country_id
       FROM partners p
       JOIN items i ON i.partner_id = p.id
       WHERE i.id IN (${itemPh}) AND p.country_id IS NOT NULL`,
      [...this.memberItemIds, ...this.memberItemIds]
    )

    const countryIds = used.map(row => row.country_id)
    if (countryIds.length === 0) {
      await this.writeJson('countries.json', [])
      this.logger.warning('countries.json (0 countries referenced)')
      return { file: 'countries.json', count: 0 }
    }

    const countryPh = this.placeholders(countryIds.length)
    const [countries, translations] = await Promise.all([
      this.db.query<CountryRow>(
        `SELECT id, internal_name, backward_compatibility
         FROM countries WHERE id IN (${countryPh}) ORDER BY id`,
        countryIds
      ),
      this.db.query<CountryTranslationRow>(
        `SELECT country_id, language_id, name
         FROM country_translations WHERE country_id IN (${countryPh})`,
        countryIds
      ),
    ])

    const langCodeMap = await this.buildLangCodeMap()

    const byLang = new Map<string, Record<string, unknown>>()
    for (const row of translations) {
      const code = langCodeMap.get(row.language_id)
      if (!code) continue
      const bucket = byLang.get(code) ?? {}
      bucket[row.country_id] = { name: row.name }
      byLang.set(code, bucket)
    }
    await this.writeTranslationFiles('countries', byLang)

    const output = countries.map(country => ({
      id: country.id,
      code: country.backward_compatibility,
      internal_name: country.internal_name,
    }))

    await this.writeJson('countries.json', output)
    this.logger.success(`countries.json (${output.length} countries)`)

    return { file: 'countries.json', count: output.length }
  }
}
