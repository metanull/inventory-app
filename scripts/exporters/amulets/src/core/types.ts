import type { Database } from './database.js'
import type { Logger } from './logger.js'

/**
 * The gallery anchor stored on `collections.extra.thg_gallery` by the importer's
 * ThgGalleryImporter — the attributes that identify the gallery itself rather
 * than one of its language rows.
 */
export interface GalleryAnchor {
  mwnf3_project_id?: string
  slug?: string
  host?: string
  i18n_group_id?: number
  i18n_common_group_id?: number
}

/**
 * The per-language `thg_gallery` fields the importer copies onto every
 * `collection_translations.extra.thg_gallery` row. They describe the gallery,
 * not the language, so the exporter reads them once from any language row.
 */
export interface GalleryChrome {
  image?: string
  banner_image?: string
  banner_item?: string
  homepage_image?: string
  homepage_item?: string
  has_timeline?: unknown
  has_country_timeline?: unknown
  /** Legacy 'A'/'H' flags — see Gallery.isFeatured / isHidden for the mapping. */
  featured?: string
  status?: string
  live_date?: string
}

/** The resolved gallery this exporter is pinned to. */
export interface Gallery {
  /** Collection UUID. */
  id: string
  backwardCompatibility: string
  /** Legacy slug from `thg_gallery.link` (e.g. `amulets_and_talismans`). */
  slug: string | null
  /** Canonical public host from `thg_gallery_url` (e.g. `https://amulets.museumwnf.org`). */
  host: string | null
  /** Legacy mwnf3 project the gallery was created under (e.g. `AMU`). */
  mwnf3ProjectId: string | null
  anchor: GalleryAnchor
  chrome: GalleryChrome
}

export interface ExportContext {
  db: Database
  outputDir: string
  gallery: Gallery
  /** Item UUIDs of the gallery's membership union (native project ∪ link tables). */
  memberItemIds: string[]
  /** Item UUID → legacy project key of the item's OWN project (e.g. `EPM`, `ISL`, `awe`). */
  itemProjectKeys: Map<string, string>
  /** Item UUID → context UUID of the item's OWN project, for translation selection. */
  itemOwnContextIds: Map<string, string>
  baseUrl: string
  logger: Logger
}

export interface ExportResult {
  file: string
  count: number
}
