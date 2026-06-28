import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

interface CollectionRow {
  id: string
  type: string
  internal_name: string
  backward_compatibility: string | null
  parent_id: string | null
  display_order: number | null
  country_id: string | null
  latitude: string | null
  longitude: string | null
}

interface CollectionTranslationRow {
  collection_id: string
  language_id: string
  title: string
  description: string | null
  quote: string | null
  url: string | null
  extra: string | null
}

interface CollectionImageRow {
  collection_id: string
  path: string
  alt_text: string | null
  display_order: number
}

interface CollectionItemRow {
  collection_id: string
  item_id: string
  display_order: number | null
}

export class CollectionExporter extends BaseExporter {
  getName(): string {
    return 'Collections'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting collections.json...')

    // Collections are scoped to a project via its context_id.
    // All collections (root + exhibitions + themes + pages) for a project
    // share the same context_id as the project itself.
    const ph = this.placeholders(this.projectIds.length)

    const collections = await this.db.query<CollectionRow>(
      `SELECT c.id, c.type, c.internal_name, c.backward_compatibility,
              c.parent_id, c.display_order, c.country_id, c.latitude, c.longitude
       FROM collections c
       WHERE c.context_id IN (
         SELECT p.context_id FROM projects p WHERE p.id IN (${ph})
       )
       ORDER BY c.parent_id IS NOT NULL, c.display_order, c.internal_name`,
      this.projectIds
    )

    if (collections.length === 0) {
      await this.writeJson('collections.json', [])
      this.logger.warning('collections.json (0 collections)')
      return { file: 'collections.json', count: 0 }
    }

    const collectionIds = collections.map(c => c.id)
    const colPh = this.placeholders(collectionIds.length)
    const langCodeMap = await this.buildLangCodeMap()

    const [translations, images, itemLinks] = await Promise.all([
      this.db.query<CollectionTranslationRow>(
        `SELECT collection_id, language_id, title, description, quote, url, extra
         FROM collection_translations
         WHERE collection_id IN (${colPh})`,
        collectionIds
      ),
      this.db.query<CollectionImageRow>(
        `SELECT collection_id, path, alt_text, display_order
         FROM collection_images
         WHERE collection_id IN (${colPh})
         ORDER BY collection_id, display_order`,
        collectionIds
      ),
      // Items in these collections, restricted to non-picture items from the project
      this.db.query<CollectionItemRow>(
        `SELECT ci.collection_id, ci.item_id, ci.display_order
         FROM collection_item ci
         JOIN items i ON i.id = ci.item_id
         WHERE ci.collection_id IN (${colPh})
           AND i.project_id IN (${ph})
           AND i.type IN ('object', 'monument', 'detail')
         ORDER BY ci.collection_id, ci.display_order`,
        [...collectionIds, ...this.projectIds]
      ),
    ])

    // collection_id -> lang_code -> fields
    const translationMap = new Map<string, Record<string, Record<string, unknown>>>()
    for (const t of translations) {
      if (!translationMap.has(t.collection_id)) translationMap.set(t.collection_id, {})
      const code = langCodeMap.get(t.language_id)
      if (!code) continue
      translationMap.get(t.collection_id)![code] = {
        title: t.title,
        description: t.description,
        quote: t.quote,
        url: t.url,
        ...(t.extra ? { extra: parseJson(t.extra) } : {}),
      }
    }

    // collection_id -> images[]
    const imageMap = new Map<
      string,
      { url: string; alt_text: string | null; display_order: number }[]
    >()
    for (const img of images) {
      if (!imageMap.has(img.collection_id)) imageMap.set(img.collection_id, [])
      imageMap.get(img.collection_id)!.push({
        url: this.imageUrl(img.path),
        alt_text: img.alt_text,
        display_order: img.display_order,
      })
    }

    // collection_id -> item_ids[]
    const itemMap = new Map<string, string[]>()
    for (const link of itemLinks) {
      if (!itemMap.has(link.collection_id)) itemMap.set(link.collection_id, [])
      itemMap.get(link.collection_id)!.push(link.item_id)
    }

    const output = collections.map(c => ({
      id: c.id,
      type: c.type,
      parent_id: c.parent_id,
      country_id: c.country_id,
      display_order: c.display_order,
      latitude: c.latitude !== null ? parseFloat(c.latitude) : null,
      longitude: c.longitude !== null ? parseFloat(c.longitude) : null,
      translations: translationMap.get(c.id) ?? {},
      images: imageMap.get(c.id) ?? [],
      item_ids: itemMap.get(c.id) ?? [],
    }))

    await this.writeJson('collections.json', output)
    this.logger.success(`collections.json (${output.length} collections)`)

    return { file: 'collections.json', count: output.length }
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
