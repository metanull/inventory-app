import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

interface PartnerRow {
  id: string
  type: string
  internal_name: string
  backward_compatibility: string | null
  country_id: string | null
  latitude: string | null
  longitude: string | null
  monument_item_id: string | null
}

interface PartnerTranslationRow {
  partner_id: string
  language_id: string
  name: string
  description: string | null
  city_display: string | null
  contact_website: string | null
  contact_phone: string | null
  contact_email_general: string | null
}

interface PartnerImageRow {
  partner_id: string
  path: string
  alt_text: string | null
  display_order: number
}

interface PartnerLogoRow {
  partner_id: string
  path: string
  logo_type: string
  alt_text: string | null
  display_order: number
}

export class PartnerExporter extends BaseExporter {
  getName(): string {
    return 'Partners'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting partners.json...')

    const ph = this.placeholders(this.projectIds.length)

    // Partners that hold at least one object/monument in these projects.
    // items.partner_id is the authoritative link: museums hold objects,
    // institutions hold monuments.
    const partners = await this.db.query<PartnerRow>(
      `SELECT DISTINCT p.id, p.type, p.internal_name, p.backward_compatibility,
              p.country_id, p.latitude, p.longitude, p.monument_item_id
       FROM partners p
       JOIN items i ON i.partner_id = p.id
       WHERE i.project_id IN (${ph})
         AND i.type IN ('object', 'monument', 'detail')
       ORDER BY p.type, p.internal_name`,
      this.projectIds
    )

    if (partners.length === 0) {
      await this.writeJson('partners.json', [])
      this.logger.warning('partners.json (0 partners)')
      return { file: 'partners.json', count: 0 }
    }

    const partnerIds = partners.map(p => p.id)
    const partnerPh = this.placeholders(partnerIds.length)
    const langCodeMap = await this.buildLangCodeMap()

    const [translations, images, logos] = await Promise.all([
      this.db.query<PartnerTranslationRow>(
        `SELECT partner_id, language_id, name, description, city_display,
                contact_website, contact_phone, contact_email_general
         FROM partner_translations
         WHERE partner_id IN (${partnerPh})`,
        partnerIds
      ),
      this.db.query<PartnerImageRow>(
        `SELECT partner_id, path, alt_text, display_order
         FROM partner_images
         WHERE partner_id IN (${partnerPh})
         ORDER BY partner_id, display_order`,
        partnerIds
      ),
      this.db.query<PartnerLogoRow>(
        `SELECT partner_id, path, logo_type, alt_text, display_order
         FROM partner_logos
         WHERE partner_id IN (${partnerPh})
         ORDER BY partner_id, display_order`,
        partnerIds
      ),
    ])

    // partner_id -> lang_code -> fields
    const translationMap = new Map<string, Record<string, Record<string, string | null>>>()
    for (const t of translations) {
      if (!translationMap.has(t.partner_id)) translationMap.set(t.partner_id, {})
      const code = langCodeMap.get(t.language_id)
      if (code) {
        translationMap.get(t.partner_id)![code] = {
          name: t.name,
          description: t.description,
          city: t.city_display,
          website: t.contact_website,
          phone: t.contact_phone,
          email: t.contact_email_general,
        }
      }
    }

    // Write one translations/partners.{lang}.json per language (null fields omitted)
    const byLang = new Map<string, Record<string, unknown>>()
    for (const [partnerId, langMap] of translationMap) {
      for (const [langCode, fields] of Object.entries(langMap)) {
        if (!byLang.has(langCode)) byLang.set(langCode, {})
        byLang.get(langCode)![partnerId] = this.stripNulls(fields as Record<string, unknown>)
      }
    }
    await this.writeTranslationFiles('partners', byLang)

    // partner_id -> images[]
    const imageMap = new Map<
      string,
      { url: string; alt_text: string | null; display_order: number }[]
    >()
    for (const img of images) {
      if (!imageMap.has(img.partner_id)) imageMap.set(img.partner_id, [])
      imageMap.get(img.partner_id)!.push({
        url: this.imageUrl(img.path, 'partner-picture'),
        alt_text: img.alt_text,
        display_order: img.display_order,
      })
    }

    // partner_id -> logos[]
    const logoMap = new Map<
      string,
      { url: string; logo_type: string; alt_text: string | null; display_order: number }[]
    >()
    for (const logo of logos) {
      if (!logoMap.has(logo.partner_id)) logoMap.set(logo.partner_id, [])
      logoMap.get(logo.partner_id)!.push({
        url: this.imageUrl(logo.path, 'partner-logo'),
        logo_type: logo.logo_type,
        alt_text: logo.alt_text,
        display_order: logo.display_order,
      })
    }

    const output = partners.map(p => ({
      id: p.id,
      type: p.type,
      country_id: p.country_id,
      latitude: p.latitude !== null ? parseFloat(p.latitude) : null,
      longitude: p.longitude !== null ? parseFloat(p.longitude) : null,
      monument_item_id: p.monument_item_id,
      images: imageMap.get(p.id) ?? [],
      logos: logoMap.get(p.id) ?? [],
    }))

    await this.writeJson('partners.json', output)
    this.logger.success(`partners.json (${output.length} partners)`)

    return { file: 'partners.json', count: output.length }
  }
}
