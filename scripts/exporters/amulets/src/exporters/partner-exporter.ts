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
  map_zoom: number | null
  monument_item_id: string | null
  item_count: number
}

interface PartnerTranslationRow {
  partner_id: string
  language_id: string
  name: string
  description: string | null
  city_display: string | null
  address_notes: string | null
  contact_website: string | null
  contact_phone: string | null
  contact_email_general: string | null
  extra: unknown
}

interface ContactPerson {
  name?: string
  title?: string
  phone?: string
  fax?: string
  email?: string
}

interface PartnerExtraFields {
  contact_person_1?: ContactPerson
  contact_person_2?: ContactPerson
  urls?: Array<{ url: string; title?: string }>
  /** Legacy `museums.portal_display` — 'y' drives the home page featured strip. */
  portal_display?: string
  opening_hours?: string
  how_to_reach?: string
}

interface PartnerImageRow {
  partner_id: string
  path: string
  alt_text: string | null
  display_order: number
  extra: unknown
}

interface PartnerLogoRow {
  partner_id: string
  path: string
  logo_type: string
  alt_text: string | null
  display_order: number
}

/**
 * `partners.json` — the museums whose objects the gallery shows.
 *
 * Legacy builds this list (app/MWNF/SQL/mwnf3/Partners.blade.php) as: partners
 * holding an object of the gallery's native project, UNION partners holding an
 * object linked to the gallery, UNION museums created in the gallery's project
 * even when they hold nothing (MWNF-384). The first two branches are exactly
 * "holds a member item", which is what this query does.
 *
 * The third branch contributes nothing to amulets — legacy has no
 * `mwnf3.museums` row with `project_id = 'AMU'` — but a fork for a gallery whose
 * native project owns museums must add it, and the inventory DB does not
 * currently carry the museum→project link (`partners.project_id` is null for
 * every imported museum), so that fork needs an importer change first.
 */
export class PartnerExporter extends BaseExporter {
  getName(): string {
    return 'Partners'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting partners.json...')

    if (this.memberItemIds.length === 0) {
      await this.writeJson('partners.json', [])
      this.logger.warning('partners.json (0 — gallery has no member items)')
      return { file: 'partners.json', count: 0 }
    }

    const itemPh = this.placeholders(this.memberItemIds.length)

    const partners = await this.db.query<PartnerRow>(
      `SELECT p.id, p.type, p.internal_name, p.backward_compatibility,
              p.country_id, p.latitude, p.longitude, p.map_zoom, p.monument_item_id,
              COUNT(i.id) AS item_count
       FROM partners p
       JOIN items i ON i.partner_id = p.id AND i.id IN (${itemPh})
       GROUP BY p.id, p.type, p.internal_name, p.backward_compatibility,
                p.country_id, p.latitude, p.longitude, p.map_zoom, p.monument_item_id
       ORDER BY p.country_id, p.internal_name`,
      this.memberItemIds
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
        `SELECT partner_id, language_id, name, description, city_display, address_notes,
                contact_website, contact_phone, contact_email_general, extra
         FROM partner_translations
         WHERE partner_id IN (${partnerPh})`,
        partnerIds
      ),
      this.db.query<PartnerImageRow>(
        `SELECT partner_id, path, alt_text, display_order, extra
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

    const translationMap = new Map<string, Record<string, Record<string, string | null>>>()
    // Contact details, opening hours and the portal flag are properties of the
    // museum, not of a language, but the importer copies them onto every
    // language row — so the first row seen wins.
    const extraMap = new Map<string, PartnerExtraFields>()

    for (const row of translations) {
      const code = langCodeMap.get(row.language_id)
      if (code) {
        const byLang = translationMap.get(row.partner_id) ?? {}
        byLang[code] = {
          name: row.name,
          description: row.description,
          city: row.city_display,
          address: row.address_notes,
          website: row.contact_website,
          phone: row.contact_phone,
          email: row.contact_email_general,
        }
        translationMap.set(row.partner_id, byLang)
      }

      if (!extraMap.has(row.partner_id) && row.extra) {
        const extra = parseJson<PartnerExtraFields>(row.extra)
        if (extra) extraMap.set(row.partner_id, extra)
      }
    }

    const byLang = new Map<string, Record<string, unknown>>()
    for (const [partnerId, langMap] of translationMap) {
      for (const [langCode, fields] of Object.entries(langMap)) {
        const bucket = byLang.get(langCode) ?? {}
        bucket[partnerId] = this.stripNulls(fields as Record<string, unknown>)
        byLang.set(langCode, bucket)
      }
    }
    await this.writeTranslationFiles('partners', byLang)

    const imageMap = new Map<string, unknown[]>()
    for (const image of images) {
      const extra = parseJson<{ photographer?: string; copyright?: string }>(image.extra)
      const entry = {
        url: this.imageUrl(image.path),
        alt_text: image.alt_text,
        display_order: image.display_order,
        photographer: extra?.photographer ?? null,
        copyright: extra?.copyright ?? null,
      }
      const bucket = imageMap.get(image.partner_id)
      if (bucket) bucket.push(entry)
      else imageMap.set(image.partner_id, [entry])
    }

    const logoMap = new Map<string, unknown[]>()
    for (const logo of logos) {
      const entry = {
        url: this.imageUrl(logo.path),
        logo_type: logo.logo_type,
        alt_text: logo.alt_text,
        display_order: logo.display_order,
      }
      const bucket = logoMap.get(logo.partner_id)
      if (bucket) bucket.push(entry)
      else logoMap.set(logo.partner_id, [entry])
    }

    const output = partners.map(partner => {
      const extra = extraMap.get(partner.id)
      return {
        id: partner.id,
        type: partner.type,
        backward_compatibility: partner.backward_compatibility,
        country_id: partner.country_id,
        latitude: partner.latitude !== null ? parseFloat(partner.latitude) : null,
        longitude: partner.longitude !== null ? parseFloat(partner.longitude) : null,
        map_zoom: partner.map_zoom,
        monument_item_id: partner.monument_item_id,
        // Legacy `showOnPortal`. The home page shows a random subset of the
        // featured partners, so the package ships the flag and the viewer picks.
        featured: extra?.portal_display?.toLowerCase() === 'y',
        // Member items held here — the count the partners list prints, and the
        // reason a partner appears at all.
        item_count: Number(partner.item_count),
        contact_person_1: extra?.contact_person_1 ?? null,
        contact_person_2: extra?.contact_person_2 ?? null,
        additional_urls: extra?.urls ?? [],
        images: imageMap.get(partner.id) ?? [],
        logos: logoMap.get(partner.id) ?? [],
      }
    })

    await this.writeJson('partners.json', output)
    this.logger.success(
      `partners.json (${output.length} partners, ${output.filter(p => p.featured).length} featured)`
    )

    return { file: 'partners.json', count: output.length }
  }
}

/**
 * Parses a MySQL JSON column value. mysql2 auto-decodes native JSON columns
 * into JS objects already, so `raw` is usually an object/array, not a string
 * — only fall back to JSON.parse for the (defensive) string case.
 */
function parseJson<T>(raw: unknown): T | null {
  if (raw == null) return null
  if (typeof raw === 'object') return raw as T
  try {
    return JSON.parse(raw as string) as T
  } catch {
    return null
  }
}
