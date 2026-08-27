import type { ExportResult } from '../core/types.js'
import { bannerRefToBackwardCompatibility } from '../core/banner-reference.js'
import { BaseExporter } from './base-exporter.js'

interface GalleryTranslationRow {
  collection_id: string
  language_id: string
  title: string | null
}

interface SiblingRow {
  id: string
  backward_compatibility: string
  extra: unknown
}

interface SiblingChromeRow {
  collection_id: string
  extra: unknown
}

/**
 * `gallery.json` — the site anchor.
 *
 * Legacy kept this in three places at once: the per-instance `.env`
 * (DXA_CONSTRAINT_*), the `thg_gallery` row, and `thg_gallery_url`. A data
 * package is a pre-scoped API instance, so all of it collapses into one file.
 */
export class GalleryExporter extends BaseExporter {
  getName(): string {
    return 'Gallery'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting gallery.json...')

    const langCodeMap = await this.buildLangCodeMap()
    const chrome = this.gallery.chrome

    const translations = await this.db.query<GalleryTranslationRow>(
      `SELECT collection_id, language_id, title
       FROM collection_translations
       WHERE collection_id = ?
       ORDER BY language_id`,
      [this.gallery.id]
    )

    const names: Record<string, string> = {}
    for (const row of translations) {
      const code = langCodeMap.get(row.language_id)
      if (code && row.title) names[code] = row.title
    }

    const [bannerItemId, homepageItemId] = await Promise.all([
      this.resolveItemReference(chrome.banner_item),
      this.resolveItemReference(chrome.homepage_item),
    ])

    const output = {
      id: this.gallery.id,
      backward_compatibility: this.gallery.backwardCompatibility,
      kind: 'gallery',
      // The legacy slug is load-bearing identity, kept verbatim (underscores and
      // all) even though the package/folder name is its kebab-cased short form.
      slug: this.gallery.slug,
      legacy_host: this.gallery.host,
      mwnf3_project_id: this.gallery.mwnf3ProjectId,
      // The UI languages the gallery actually shipped in (thg_gallery_lang).
      languages: Object.keys(names).sort(),
      names,
      banner_item_id: bannerItemId,
      homepage_item_id: homepageItemId,
      // Gallery chrome images live on the legacy media server and were never
      // imported, so the package carries the legacy path only — the viewer
      // supplies the host, exactly as the legacy client did through
      // VUE_APP_IMAGES_URL.
      image_path: chrome.image ?? null,
      banner_image_path: chrome.banner_image ?? null,
      homepage_image_path: chrome.homepage_image ?? null,
      has_timeline: bitToBoolean(chrome.has_timeline),
      has_country_timeline: bitToBoolean(chrome.has_country_timeline),
      featured: isFeatured(chrome.featured),
      hidden: isHidden(chrome.status),
      live_date: chrome.live_date ?? null,
      sibling_galleries: await this.exportSiblings(langCodeMap),
    }

    await this.writeJson('gallery.json', output)
    this.logger.success(
      `gallery.json (${this.gallery.slug ?? this.gallery.backwardCompatibility}, ` +
        `${output.languages.length} languages, ${output.sibling_galleries.length} siblings)`
    )

    return { file: 'gallery.json', count: 1 }
  }

  /**
   * `thg_gallery.banner_item` / `homepage_item` name an item by its legacy
   * composite key. Unlike the cross-site links of decision Q3 this one resolves
   * locally — the referenced item is in the same inventory database — so the
   * package ships the resolved UUID and the viewer needs no lookup table.
   */
  private async resolveItemReference(reference: string | undefined): Promise<string | null> {
    const backwardCompatibility = bannerRefToBackwardCompatibility(reference)
    if (!backwardCompatibility) {
      if (reference) {
        this.logger.warning(`Unparseable gallery item reference, dropped: ${reference}`)
      }
      return null
    }

    const rows = await this.db.query<{ id: string }>(
      `SELECT id FROM items WHERE backward_compatibility = ?`,
      [backwardCompatibility]
    )

    if (!rows[0]) {
      this.logger.warning(`Gallery item reference does not resolve: ${backwardCompatibility}`)
      return null
    }
    return rows[0].id
  }

  /**
   * The sibling-gallery strip (`/thg/galleries/featured` — legacy picks four at
   * random per request). The exporter ships the whole live roster and lets the
   * viewer choose, so the strip stays random without a server.
   *
   * Per decision Q3 each entry is a REFERENCE, not a link: the exporter records
   * identity plus whatever metadata the import carried (slug, legacy host) and
   * never constructs a target URL. Resolution is a later, separate mechanism.
   */
  private async exportSiblings(langCodeMap: Map<string, string>): Promise<unknown[]> {
    const siblings = await this.db.query<SiblingRow>(
      `SELECT id, backward_compatibility, extra
       FROM collections
       WHERE type = 'gallery'
         AND id <> ?
         AND backward_compatibility LIKE 'mwnf3\\_thematic\\_gallery:thg\\_gallery:%'
       ORDER BY backward_compatibility`,
      [this.gallery.id]
    )

    if (siblings.length === 0) return []

    const siblingIds = siblings.map(s => s.id)
    const placeholders = this.placeholders(siblingIds.length)

    const [titles, chromeRows] = await Promise.all([
      this.db.query<GalleryTranslationRow>(
        `SELECT collection_id, language_id, title
         FROM collection_translations
         WHERE collection_id IN (${placeholders})`,
        siblingIds
      ),
      this.db.query<SiblingChromeRow>(
        `SELECT collection_id, extra
         FROM collection_translations
         WHERE collection_id IN (${placeholders}) AND extra IS NOT NULL`,
        siblingIds
      ),
    ])

    const namesById = new Map<string, Record<string, string>>()
    for (const row of titles) {
      const code = langCodeMap.get(row.language_id)
      if (!code || !row.title) continue
      if (!namesById.has(row.collection_id)) namesById.set(row.collection_id, {})
      namesById.get(row.collection_id)![code] = row.title
    }

    const chromeById = new Map<string, Record<string, unknown>>()
    for (const row of chromeRows) {
      if (chromeById.has(row.collection_id)) continue
      const parsed = parseJson<{ thg_gallery?: Record<string, unknown> }>(row.extra)
      if (parsed?.thg_gallery) chromeById.set(row.collection_id, parsed.thg_gallery)
    }

    return siblings
      .map(sibling => {
        const anchor =
          parseJson<{ thg_gallery?: Record<string, unknown> }>(sibling.extra)?.thg_gallery ?? {}
        const chrome = chromeById.get(sibling.id) ?? {}
        return {
          id: sibling.id,
          backward_compatibility: sibling.backward_compatibility,
          slug: (anchor['slug'] as string | undefined) ?? null,
          // Imported metadata, NOT a resolved link (decision Q3).
          legacy_host: (anchor['host'] as string | undefined) ?? null,
          names: namesById.get(sibling.id) ?? {},
          image_path: (chrome['image'] as string | undefined) ?? null,
          featured: isFeatured(chrome['featured'] as string | undefined),
          hidden: isHidden(chrome['status'] as string | undefined),
          live_date: (chrome['live_date'] as string | undefined) ?? null,
        }
      })
      .filter(sibling => !sibling.hidden)
  }
}

/**
 * Legacy `featured` is INVERTED, and deliberately reproduced that way:
 * dxa-api computes `CASE WHEN featured = 'A' THEN 0 ELSE 1 END`
 * (app/MWNF/SQL/thg/WithTHGTemporaryTables.php), so 'A' means NOT featured.
 * Amulets stores 'H' and the live site reports featured: true.
 */
export function isFeatured(flag: string | undefined | null): boolean {
  return flag !== undefined && flag !== null && flag !== 'A'
}

/** `status = 'A'` is the visible state; anything else hides the gallery. */
export function isHidden(status: string | undefined | null): boolean {
  return status !== 'A'
}

/**
 * `has_timeline` / `has_country_timeline` are MySQL bit(1) columns. The importer
 * normalizes them to JSON booleans, but rows written before that fix still hold
 * the raw mysql2 Buffer shape (`{"type":"Buffer","data":[1]}`), so both forms
 * are accepted here rather than silently reading a truthy object as `true`.
 */
export function bitToBoolean(value: unknown): boolean {
  if (typeof value === 'boolean') return value
  if (typeof value === 'number') return value !== 0
  if (typeof value === 'string') return value === '1' || value.toLowerCase() === 'true'
  if (value && typeof value === 'object') {
    const data = (value as { data?: unknown }).data
    if (Array.isArray(data)) return data[0] === 1
  }
  return false
}

function parseJson<T>(raw: unknown): T | null {
  if (raw == null) return null
  if (typeof raw === 'object') return raw as T
  try {
    return JSON.parse(raw as string) as T
  } catch {
    return null
  }
}
