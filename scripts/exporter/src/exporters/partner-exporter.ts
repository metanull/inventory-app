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

// Shape written by the importer's museum/institution transformers into
// partner_translations.extra. Contact persons and extra URLs are legacy
// fields on the museum/institution row itself (not per-language), so the
// same values are duplicated across every language row for a given partner.
interface PartnerExtraFields {
  contact_person_1?: ContactPerson
  contact_person_2?: ContactPerson
  urls?: Array<{ url: string; title?: string }>
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

interface PartnerLevelRow {
  partner_id: string
  level: string | null
}

interface GroupMembershipRow {
  collection_id: string
  partner_id: string
  level: string | null
}

// Legacy tiers, most to least prominent. A partner attached at multiple
// levels across the exported projects is reported at its most prominent tier.
const LEVEL_RANK: Record<string, number> = {
  partner: 0,
  associated_partner: 1,
  minor_contributor: 2,
}

export class PartnerExporter extends BaseExporter {
  getName(): string {
    return 'Partners'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting partners.json...')

    const ph = this.placeholders(this.projectIds.length)

    // Partners listed in the curated legacy hierarchy (partner_museums/partner_institutions
    // and their associated/minor tiers, imported into collection_partner) for these projects.
    // This is deliberately narrower than "owns an item in this project": most institutions
    // attached to a monument are just the country's generic administrative authority (e.g.
    // a Ministry of Culture recorded as the monument's owner), not an actual listed project
    // partner — legacy's Partners page only ever shows the curated hierarchy, never every
    // institution that happens to own an item.
    //
    // `level IS NOT NULL` is what actually enforces that narrowing: monument/object import
    // also attaches every item's owning partner to collection_partner (same collection_type
    //='project') for ownership tracking, but leaves `level` null since that generic
    // attachment was never curated onto the legacy Partners page. Only the dedicated
    // hierarchy importers (partner-hierarchy-importer.ts / institution-hierarchy-importer.ts)
    // set a level, so requiring one here excludes the generic item-owner rows.
    const partners = await this.db.query<PartnerRow>(
      `SELECT DISTINCT p.id, p.type, p.internal_name, p.backward_compatibility,
              p.country_id, p.latitude, p.longitude, p.map_zoom, p.monument_item_id
       FROM partners p
       WHERE EXISTS (
         SELECT 1
         FROM collection_partner cp
         JOIN collections c ON c.id = cp.collection_id
         JOIN projects proj ON proj.context_id = c.context_id
         WHERE cp.collection_type = 'project'
           AND cp.visible = true
           AND cp.level IS NOT NULL
           AND cp.partner_id = p.id
           AND proj.id IN (${ph})
       )
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

    const [translations, images, logos, levels] = await Promise.all([
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
      // Legacy tier (partner / associated_partner / minor_contributor), scoped to the
      // collections that represent the exported projects themselves.
      this.db.query<PartnerLevelRow>(
        `SELECT cp.partner_id, cp.level
         FROM collection_partner cp
         JOIN collections c ON c.id = cp.collection_id
         JOIN projects proj ON proj.context_id = c.context_id
         WHERE cp.collection_type = 'project'
           AND cp.visible = true
           AND proj.id IN (${ph})
           AND cp.partner_id IN (${partnerPh})`,
        [...this.projectIds, ...partnerIds]
      ),
    ])

    // Per-context hierarchy: partner-hierarchy-importer.ts / institution-hierarchy-importer.ts
    // give each tier-1 partner_museums/partner_institutions row its own "group" collection
    // (collection_type='collection', internal_name 'partner_group:...') and attach that
    // partner plus every associated/further-associated museum or institution legacy links
    // to it via `associated_museums.partner_id` to that same collection. The member with
    // level='partner' in a group is that group's owner (the legacy top-level partner); every
    // other member's parent_id is that owner's id. A partner can own or belong to at most one
    // group per project (mirrors legacy: one partner_museums/partner_institutions row per
    // museum/institution per project).
    const groupMemberships = await this.db.query<GroupMembershipRow>(
      `SELECT cp.collection_id, cp.partner_id, cp.level
       FROM collection_partner cp
       JOIN collections c ON c.id = cp.collection_id
       WHERE cp.collection_type = 'collection'
         AND c.internal_name LIKE 'partner_group:%'
         AND c.context_id IN (SELECT p.context_id FROM projects p WHERE p.id IN (${ph}))
         AND cp.partner_id IN (${partnerPh})`,
      [...this.projectIds, ...partnerIds]
    )

    // partner_id -> lang_code -> fields
    const translationMap = new Map<string, Record<string, Record<string, string | null>>>()
    // partner_id -> contact persons / extra URLs (same across languages, take the first seen)
    const contactMap = new Map<string, PartnerExtraFields>()
    for (const t of translations) {
      if (!translationMap.has(t.partner_id)) translationMap.set(t.partner_id, {})
      const code = langCodeMap.get(t.language_id)
      if (code) {
        translationMap.get(t.partner_id)![code] = {
          name: t.name,
          description: t.description,
          city: t.city_display,
          address: t.address_notes,
          website: t.contact_website,
          phone: t.contact_phone,
          email: t.contact_email_general,
        }
      }
      if (!contactMap.has(t.partner_id) && t.extra) {
        const extra = parseJson<PartnerExtraFields>(t.extra)
        if (extra && (extra.contact_person_1 || extra.contact_person_2 || extra.urls)) {
          contactMap.set(t.partner_id, {
            contact_person_1: extra.contact_person_1,
            contact_person_2: extra.contact_person_2,
            urls: extra.urls,
          })
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
      {
        url: string
        alt_text: string | null
        display_order: number
        photographer: string | null
        copyright: string | null
      }[]
    >()
    for (const img of images) {
      if (!imageMap.has(img.partner_id)) imageMap.set(img.partner_id, [])
      const extra = img.extra ? parseJson<{ photographer?: string; copyright?: string }>(img.extra) : null
      imageMap.get(img.partner_id)!.push({
        url: this.imageUrl(img.path),
        alt_text: img.alt_text,
        display_order: img.display_order,
        photographer: extra?.photographer ?? null,
        copyright: extra?.copyright ?? null,
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
        url: this.imageUrl(logo.path),
        logo_type: logo.logo_type,
        alt_text: logo.alt_text,
        display_order: logo.display_order,
      })
    }

    // partner_id -> most prominent level across the exported projects
    const levelMap = new Map<string, string>()
    for (const row of levels) {
      if (!row.level) continue
      const current = levelMap.get(row.partner_id)
      if (!current || (LEVEL_RANK[row.level] ?? 99) < (LEVEL_RANK[current] ?? 99)) {
        levelMap.set(row.partner_id, row.level)
      }
    }

    // collection_id -> owner partner_id (the member with level='partner')
    const groupOwnerMap = new Map<string, string>()
    for (const row of groupMemberships) {
      if (row.level === 'partner') groupOwnerMap.set(row.collection_id, row.partner_id)
    }
    // partner_id -> parent partner_id (the owner of the group(s) this partner belongs to,
    // excluding the group(s) it owns itself)
    const parentMap = new Map<string, string>()
    for (const row of groupMemberships) {
      if (row.level === 'partner') continue
      const owner = groupOwnerMap.get(row.collection_id)
      if (owner && owner !== row.partner_id) parentMap.set(row.partner_id, owner)
    }

    const output = partners.map(p => ({
      id: p.id,
      type: p.type,
      backward_compatibility: p.backward_compatibility,
      country_id: p.country_id,
      latitude: p.latitude !== null ? parseFloat(p.latitude) : null,
      longitude: p.longitude !== null ? parseFloat(p.longitude) : null,
      map_zoom: p.map_zoom,
      monument_item_id: p.monument_item_id,
      level: levelMap.get(p.id) ?? null,
      parent_id: parentMap.get(p.id) ?? null,
      contact_person_1: contactMap.get(p.id)?.contact_person_1 ?? null,
      contact_person_2: contactMap.get(p.id)?.contact_person_2 ?? null,
      additional_urls: contactMap.get(p.id)?.urls ?? [],
      images: imageMap.get(p.id) ?? [],
      logos: logoMap.get(p.id) ?? [],
    }))

    await this.writeJson('partners.json', output)
    this.logger.success(`partners.json (${output.length} partners)`)

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
