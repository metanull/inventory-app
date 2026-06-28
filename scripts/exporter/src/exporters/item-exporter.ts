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
  extra: string | null
  author_name: string | null
  copy_editor_name: string | null
  translator_name: string | null
  translation_copy_editor_name: string | null
}

interface ItemImageRow {
  item_id: string
  path: string
  alt_text: string | null
  display_order: number
}

interface ItemDynastyRow {
  item_id: string
  dynasty_id: string
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

    // Exclude 'picture' child items — those are internal importer artefacts.
    // We export objects, monuments, and monument details.
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

    const [translations, images, dynastyLinks, tagLinks] = await Promise.all([
      // Join authors to resolve their names directly — avoids a second round-trip
      this.db.query<ItemTranslationRow>(
        `SELECT it.item_id, it.language_id,
                it.name, it.alternate_name, it.description,
                it.type, it.holder, it.owner, it.initial_owner, it.dates,
                it.location, it.dimensions, it.place_of_production,
                it.method_for_datation, it.method_for_provenance,
                it.provenance, it.obtention, it.bibliography, it.extra,
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
      ),
      // item_images: the importer attaches the first picture's image directly to the
      // parent object/monument. Only these direct attachments are exported here.
      this.db.query<ItemImageRow>(
        `SELECT item_id, path, alt_text, display_order
         FROM item_images
         WHERE item_id IN (${itemPh})
         ORDER BY item_id, display_order`,
        itemIds
      ),
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
    ])

    // item_id -> lang_code -> translation fields
    const translationMap = new Map<string, Record<string, Record<string, unknown>>>()
    for (const t of translations) {
      if (!translationMap.has(t.item_id)) {
        translationMap.set(t.item_id, {})
      }
      const code = langCodeMap.get(t.language_id)
      if (!code) continue

      const extraParsed = parseJson(t.extra)

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
        ...(extraParsed ? { extra: extraParsed } : {}),
      }
    }

    // item_id -> images[]
    const imageMap = new Map<
      string,
      { url: string; alt_text: string | null; display_order: number }[]
    >()
    for (const img of images) {
      if (!imageMap.has(img.item_id)) imageMap.set(img.item_id, [])
      imageMap.get(img.item_id)!.push({
        url: this.imageUrl(img.path),
        alt_text: img.alt_text,
        display_order: img.display_order,
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

    const output = items.map(item => ({
      id: item.id,
      type: item.type,
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
      translations: translationMap.get(item.id) ?? {},
      images: imageMap.get(item.id) ?? [],
      dynasty_ids: dynastyMap.get(item.id) ?? [],
      tags: tagMap.get(item.id) ?? [],
    }))

    await this.writeJson('items.json', output)
    this.logger.success(`items.json (${output.length} items)`)

    return { file: 'items.json', count: output.length }
  }
}

function parseJson(raw: string | null): unknown | null {
  if (!raw) return null
  try {
    return JSON.parse(raw) as unknown
  } catch {
    return null
  }
}
