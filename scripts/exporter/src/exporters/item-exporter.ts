import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

interface ItemRow {
  id: string
  type: string
  internal_name: string
  backward_compatibility: string | null
  parent_id: string | null
  partner_id: string | null
  country_id: string | null
  collection_id: string | null
  project_id: string | null
  owner_reference: string | null
  mwnf_reference: string | null
  start_date: number | null
  end_date: number | null
  display_order: number | null
  latitude: string | null
  longitude: string | null
}

interface ItemTranslationRow {
  item_id: string
  language_id: string
  name: string
  alternate_name: string | null
  description: string | null
  type: string | null
  holder: string | null
  owner: string | null
  initial_owner: string | null
  dates: string | null
  location: string | null
  dimensions: string | null
  place_of_production: string | null
  method_for_datation: string | null
  method_for_provenance: string | null
  provenance: string | null
  obtention: string | null
  bibliography: string | null
  author_name: string | null
  copy_editor_name: string | null
  translator_name: string | null
  translation_copy_editor_name: string | null
}

interface PictureItemRow {
  picture_id: string
  item_id: string // parent_id
  display_order: number | null
  path: string
  alt_text: string | null
}

interface PictureTranslationRow {
  picture_id: string
  language_id: string
  caption: string | null // name field on picture item translations
  extra: string | null // JSON: { photographer, copyright }
}

interface ItemDynastyRow {
  item_id: string
  dynasty_id: string
}

interface ItemItemLinkRow {
  source_id: string
  target_id: string
}

interface ItemTagRow {
  item_id: string
  tag: string
}

export class ItemExporter extends BaseExporter {
  getName(): string {
    return 'Items'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting items.json...')

    const ph = this.placeholders(this.projectIds.length)

    // Exclude 'picture' child items — those are exported as images on their parent.
    const items = await this.db.query<ItemRow>(
      `SELECT id, type, internal_name, backward_compatibility, parent_id,
              partner_id, country_id, collection_id, project_id,
              owner_reference, mwnf_reference, start_date, end_date,
              display_order, latitude, longitude
       FROM items
       WHERE project_id IN (${ph})
         AND type IN ('object', 'monument', 'detail')
       ORDER BY type, display_order, internal_name`,
      this.projectIds
    )

    if (items.length === 0) {
      await this.writeJson('items.json', [])
      this.logger.warning('items.json (0 items)')
      return { file: 'items.json', count: 0 }
    }

    const itemIds = items.map(i => i.id)
    const itemPh = this.placeholders(itemIds.length)
    const langCodeMap = await this.buildLangCodeMap()

    // ── 1. Content translations (name, description, …) ──────────────────────
    const translations = await this.db.query<ItemTranslationRow>(
      `SELECT it.item_id, it.language_id,
              it.name, it.alternate_name, it.description,
              it.type, it.holder, it.owner, it.initial_owner, it.dates,
              it.location, it.dimensions, it.place_of_production,
              it.method_for_datation, it.method_for_provenance,
              it.provenance, it.obtention, it.bibliography,
              a1.name AS author_name,
              a2.name AS copy_editor_name,
              a3.name AS translator_name,
              a4.name AS translation_copy_editor_name
       FROM item_translations it
       LEFT JOIN authors a1 ON a1.id = it.author_id
       LEFT JOIN authors a2 ON a2.id = it.text_copy_editor_id
       LEFT JOIN authors a3 ON a3.id = it.translator_id
       LEFT JOIN authors a4 ON a4.id = it.translation_copy_editor_id
       WHERE it.item_id IN (${itemPh})`,
      itemIds
    )

    // ── 2. Images via picture child items ────────────────────────────────────
    // Each image is a child item of type 'picture'. It carries:
    //   - items.display_order  → position in the gallery
    //   - item_images.path     → the file path
    //   - item_translations.name (caption, per language)
    //   - item_translations.extra JSON { photographer, copyright }
    const pictureItems = await this.db.query<PictureItemRow>(
      `SELECT pic.id AS picture_id, pic.parent_id AS item_id,
              pic.display_order, ii.path, ii.alt_text
       FROM items pic
       JOIN item_images ii ON ii.item_id = pic.id
       WHERE pic.type = 'picture'
         AND pic.parent_id IN (${itemPh})
       ORDER BY pic.parent_id, pic.display_order`,
      itemIds
    )

    let pictureTranslations: PictureTranslationRow[] = []
    if (pictureItems.length > 0) {
      const pictureIds = [...new Set(pictureItems.map(p => p.picture_id))]
      pictureTranslations = await this.db.query<PictureTranslationRow>(
        `SELECT item_id AS picture_id, language_id, name AS caption, extra
         FROM item_translations
         WHERE item_id IN (${this.placeholders(pictureIds.length)})`,
        pictureIds
      )
    }

    // ── 3. Dynasty, tag, and item-item links ────────────────────────────────
    const [dynastyLinks, tagLinks, itemItemLinks] = await Promise.all([
      this.db.query<ItemDynastyRow>(
        `SELECT item_id, dynasty_id FROM item_dynasty WHERE item_id IN (${itemPh})`,
        itemIds
      ),
      this.db.query<ItemTagRow>(
        `SELECT it2.item_id, t.description AS tag
         FROM item_tag it2
         JOIN tags t ON t.id = it2.tag_id
         WHERE it2.item_id IN (${itemPh})`,
        itemIds
      ),
      this.db.query<ItemItemLinkRow>(
        `SELECT source_id, target_id FROM item_item_links
         WHERE source_id IN (${itemPh}) OR target_id IN (${itemPh})`,
        [...itemIds, ...itemIds]
      ),
    ])

    // ── Build maps ───────────────────────────────────────────────────────────

    // item_id -> lang_code -> translation fields
    const translationMap = new Map<string, Record<string, Record<string, unknown>>>()
    for (const t of translations) {
      if (!translationMap.has(t.item_id)) translationMap.set(t.item_id, {})
      const code = langCodeMap.get(t.language_id)
      if (!code) continue
      translationMap.get(t.item_id)![code] = {
        name: t.name,
        alternate_name: t.alternate_name,
        description: t.description,
        type: t.type,
        holder: t.holder,
        owner: t.owner,
        initial_owner: t.initial_owner,
        dates: t.dates,
        location: t.location,
        dimensions: t.dimensions,
        place_of_production: t.place_of_production,
        method_for_datation: t.method_for_datation,
        method_for_provenance: t.method_for_provenance,
        provenance: t.provenance,
        obtention: t.obtention,
        bibliography: t.bibliography,
        author: t.author_name,
        copy_editor: t.copy_editor_name,
        translator: t.translator_name,
        translation_copy_editor: t.translation_copy_editor_name,
      }
    }

    // Write one translations/items.{lang}.json per language (null fields omitted)
    const byLang = new Map<string, Record<string, unknown>>()
    for (const [itemId, langMap] of translationMap) {
      for (const [langCode, fields] of Object.entries(langMap)) {
        if (!byLang.has(langCode)) byLang.set(langCode, {})
        byLang.get(langCode)![itemId] = this.stripNulls(fields)
      }
    }
    await this.writeTranslationFiles('items', byLang)

    // picture_id -> lang_code -> { caption, photographer, copyright }
    const picTransMap = new Map<
      string,
      Record<
        string,
        { caption: string | null; photographer: string | null; copyright: string | null }
      >
    >()
    for (const t of pictureTranslations) {
      if (!picTransMap.has(t.picture_id)) picTransMap.set(t.picture_id, {})
      const code = langCodeMap.get(t.language_id)
      if (!code) continue
      const extra = parseJson(t.extra) as Record<string, string> | null
      picTransMap.get(t.picture_id)![code] = {
        caption: t.caption,
        photographer: extra?.photographer ?? null,
        copyright: extra?.copyright ?? null,
      }
    }

    // item_id -> images[] (built from picture children)
    const imageMap = new Map<string, ImageEntry[]>()
    for (const pic of pictureItems) {
      if (!imageMap.has(pic.item_id)) imageMap.set(pic.item_id, [])
      const perLang = picTransMap.get(pic.picture_id) ?? {}

      // photographer/copyright are not language-specific; pick from first available lang
      const firstLang = Object.values(perLang)[0]

      // Captions keyed by lang code — skip langs where caption is null
      const captions: Record<string, string> = {}
      for (const [lang, t] of Object.entries(perLang)) {
        if (t.caption !== null) captions[lang] = t.caption
      }

      imageMap.get(pic.item_id)!.push({
        url: this.imageUrl(pic.path),
        display_order: pic.display_order,
        captions,
        photographer: firstLang?.photographer ?? null,
        copyright: firstLang?.copyright ?? null,
      })
    }

    // item_id -> dynasty_ids[]
    const dynastyMap = new Map<string, string[]>()
    for (const link of dynastyLinks) {
      if (!dynastyMap.has(link.item_id)) dynastyMap.set(link.item_id, [])
      dynastyMap.get(link.item_id)!.push(link.dynasty_id)
    }

    // item_id -> tags[]
    const tagMap = new Map<string, string[]>()
    for (const link of tagLinks) {
      if (!tagMap.has(link.item_id)) tagMap.set(link.item_id, [])
      tagMap.get(link.item_id)!.push(link.tag)
    }

    // item_id -> related_item_ids[] (bidirectional; only items present in this export)
    const itemIdSet = new Set(itemIds)
    const relatedMap = new Map<string, string[]>()
    for (const link of itemItemLinks) {
      if (itemIdSet.has(link.source_id) && itemIdSet.has(link.target_id)) {
        if (!relatedMap.has(link.source_id)) relatedMap.set(link.source_id, [])
        relatedMap.get(link.source_id)!.push(link.target_id)
        if (!relatedMap.has(link.target_id)) relatedMap.set(link.target_id, [])
        relatedMap.get(link.target_id)!.push(link.source_id)
      }
    }

    const output = items.map(item => ({
      id: item.id,
      type: item.type,
      internal_name: item.internal_name,
      parent_id: item.parent_id,
      partner_id: item.partner_id,
      country_id: item.country_id,
      project_id: item.project_id,
      owner_reference: item.owner_reference,
      mwnf_reference: item.mwnf_reference,
      start_date: item.start_date,
      end_date: item.end_date,
      latitude: item.latitude !== null ? parseFloat(item.latitude) : null,
      longitude: item.longitude !== null ? parseFloat(item.longitude) : null,
      images: imageMap.get(item.id) ?? [],
      dynasty_ids: dynastyMap.get(item.id) ?? [],
      related_item_ids: relatedMap.get(item.id) ?? [],
      tags: tagMap.get(item.id) ?? [],
    }))

    await this.writeJson('items.json', output)
    this.logger.success(`items.json (${output.length} items)`)

    return { file: 'items.json', count: output.length }
  }
}

interface ImageEntry {
  url: string
  display_order: number | null
  captions: Record<string, string>
  photographer: string | null
  copyright: string | null
}

function parseJson(raw: string | null): unknown | null {
  if (!raw) return null
  try {
    return JSON.parse(raw) as unknown
  } catch {
    return null
  }
}
