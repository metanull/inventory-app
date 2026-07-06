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
  extra: unknown
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
  caption: string | null // description field on picture item translations
  extra: unknown // JSON: { photographer, copyright }
}

interface ItemDynastyRow {
  item_id: string
  dynasty_id: string
}

interface ItemItemLinkRow {
  source_id: string
  target_id: string
  language_id: string
  justification: string | null
}

interface ItemTagRow {
  item_id: string
  tag: string
}

interface ItemGlossaryRow {
  item_id: string
  glossary_id: string
}

interface ItemArtistRow {
  item_id: string
  name: string
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

    const contextIds = this.context.contextIds
    const contextPh = this.placeholders(contextIds.length)

    // ── 1. Content translations (name, description, …) ──────────────────────
    // Filter by context_id so that explore-context translations (which may have
    // extra=null) do not overwrite the canonical project translations.
    const translations = await this.db.query<ItemTranslationRow>(
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
       WHERE it.item_id IN (${itemPh})
         AND it.context_id IN (${contextPh})`,
      [...itemIds, ...contextIds]
    )

    // ── 2. Images via picture child items ────────────────────────────────────
    // Each image is a child item of type 'picture'. It carries:
    //   - items.display_order  → position in the gallery
    //   - item_images.path     → the file path
    //   - item_translations.description (caption, per language)
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
        `SELECT item_id AS picture_id, language_id, description AS caption, extra
         FROM item_translations
         WHERE item_id IN (${this.placeholders(pictureIds.length)})`,
        pictureIds
      )
    }

    // ── 3. Dynasty, tag, glossary, artist, and item-item links ──────────────
    const [dynastyLinks, tagLinks, glossaryLinks, artistLinks, itemItemLinks] = await Promise.all([
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
      // Glossary terms used anywhere in this item's translations (any language),
      // via item_translations -> item_translation_spelling -> glossary_spellings.
      this.db.query<ItemGlossaryRow>(
        `SELECT DISTINCT it3.item_id, gs.glossary_id
         FROM item_translation_spelling its
         JOIN item_translations it3 ON it3.id = its.item_translation_id
         JOIN glossary_spellings gs ON gs.id = its.spelling_id
         WHERE it3.item_id IN (${itemPh})`,
        itemIds
      ),
      // Object artists (legacy `artist_` text, parsed into structured Artist
      // entities by the importer). Language-independent — `artists.name` has
      // no language column — so this is a top-level item field, not per-translation.
      this.db.query<ItemArtistRow>(
        `SELECT ai.item_id, a.name
         FROM artist_item ai
         JOIN artists a ON a.id = ai.artist_id
         WHERE ai.item_id IN (${itemPh})`,
        itemIds
      ),
      // Outgoing links only (source_id IN items). The legacy data model stores
      // directed links; fetching both directions and reversing doubles the list.
      // Justification texts are joined per link per language.
      this.db.query<ItemItemLinkRow>(
        `SELECT iil.source_id, iil.target_id,
                iilt.language_id, iilt.description AS justification
         FROM item_item_links iil
         LEFT JOIN item_item_link_translations iilt ON iilt.item_item_link_id = iil.id
         WHERE iil.source_id IN (${itemPh})`,
        itemIds
      ),
    ])

    // ── Build maps ───────────────────────────────────────────────────────────

    // item_id -> lang_code -> translation fields
    const translationMap = new Map<string, Record<string, Record<string, unknown>>>()
    for (const t of translations) {
      if (!translationMap.has(t.item_id)) translationMap.set(t.item_id, {})
      const code = langCodeMap.get(t.language_id)
      if (!code) continue

      // Fields without a dedicated column live in item_translations.extra JSON.
      // Each key is only ever set by the importer for the item type it applies
      // to (history/patrons/architects: monuments; workshop/scriber/binding_desc/
      // catalogue_holding_link/linkcatalogs: objects), so no type gating is needed here.
      const extra = t.extra ? (parseJson(t.extra) as Record<string, string> | null) : null

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
        history: extra?.history ?? null,
        patrons: extra?.patrons ?? null,
        architects: extra?.architects ?? null,
        workshop: extra?.workshop ?? null,
        scriber: extra?.scriber ?? null,
        binding_desc: extra?.binding_desc ?? null,
        catalogue_holding_link: extra?.catalogue_holding_link ?? null,
        linkcatalogs: extra?.linkcatalogs ?? null,
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

      // Captions keyed by lang code — skip langs with no caption text
      const captions: Record<string, string> = {}
      for (const [lang, t] of Object.entries(perLang)) {
        if (t.caption) captions[lang] = t.caption
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

    // item_id -> glossary_ids[]
    const glossaryMap = new Map<string, string[]>()
    for (const link of glossaryLinks) {
      if (!glossaryMap.has(link.item_id)) glossaryMap.set(link.item_id, [])
      glossaryMap.get(link.item_id)!.push(link.glossary_id)
    }

    // item_id -> artist_names[]
    const artistMap = new Map<string, string[]>()
    for (const link of artistLinks) {
      if (!artistMap.has(link.item_id)) artistMap.set(link.item_id, [])
      artistMap.get(link.item_id)!.push(link.name)
    }

    // item_id -> related item entries (outgoing links only; only targets present in this export)
    // source_id -> target_id -> lang_code -> justification text
    const itemIdSet = new Set(itemIds)
    const relatedMap = new Map<string, string[]>()
    const justificationMap = new Map<string, Map<string, Record<string, string>>>()
    for (const link of itemItemLinks) {
      if (!itemIdSet.has(link.target_id)) continue
      if (!relatedMap.has(link.source_id)) relatedMap.set(link.source_id, [])
      const existing = relatedMap.get(link.source_id)!
      if (!existing.includes(link.target_id)) existing.push(link.target_id)

      const code = langCodeMap.get(link.language_id)
      if (code && link.justification) {
        if (!justificationMap.has(link.source_id)) justificationMap.set(link.source_id, new Map())
        const tgtMap = justificationMap.get(link.source_id)!
        if (!tgtMap.has(link.target_id)) tgtMap.set(link.target_id, {})
        tgtMap.get(link.target_id)![code] = link.justification
      }
    }

    const output = items.map(item => ({
      id: item.id,
      type: item.type,
      internal_name: item.internal_name,
      backward_compatibility: item.backward_compatibility,
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
      related_items: (relatedMap.get(item.id) ?? []).map(targetId => ({
        id: targetId,
        justifications: justificationMap.get(item.id)?.get(targetId) ?? {},
      })),
      tags: tagMap.get(item.id) ?? [],
      glossary_ids: glossaryMap.get(item.id) ?? [],
      artist_names: artistMap.get(item.id) ?? [],
      languages: Object.keys(translationMap.get(item.id) ?? {}).sort(),
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

/**
 * Parses a MySQL JSON column value. mysql2 auto-decodes native JSON columns
 * into JS objects already, so `raw` is usually an object/array, not a string
 * — only fall back to JSON.parse for the (defensive) string case.
 */
function parseJson(raw: unknown): unknown | null {
  if (raw == null) return null
  if (typeof raw === 'object') return raw
  try {
    return JSON.parse(raw as string) as unknown
  } catch {
    return null
  }
}
