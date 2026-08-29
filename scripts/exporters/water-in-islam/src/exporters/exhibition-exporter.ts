import type { ExportResult } from '../core/types.js'
import { bannerRefToBackwardCompatibility } from '../core/banner-reference.js'
import { BaseExporter } from './base-exporter.js'

interface TranslationRow {
  collection_id: string
  language_id: string
  title: string | null
  description: string | null
}

interface LogoRow {
  id: string
  path: string
  original_name: string | null
  alt_text: string | null
  display_order: number
}

interface PartnerStripRow {
  partner_id: string
  level: string | null
  visible: number | boolean | null
}

interface SiblingRow {
  id: string
  backward_compatibility: string
  type: string
  extra: unknown
}

interface SiblingChromeRow {
  collection_id: string
  extra: unknown
}

/**
 * `exhibition.json` — the site anchor, richer than a gallery's.
 *
 * Legacy kept this in four places at once: the per-instance `.env`
 * (DXA_CONSTRAINT_*), the `thg_gallery` row, `thg_gallery_url`, and the curated
 * per-language `exhibition_i18n` row. A data package is a pre-scoped API
 * instance, so all of it collapses into one file.
 *
 * Two fields deserve their own note because they are easy to conflate:
 *
 * - **`languages`** is the site's UI language roster (`thg_gallery_lang`), and
 *   **`languages_enabled`** is the subset legacy actually publishes
 *   (`exhibition_i18n.enabled = 'Y'`). On Colours they differ: the roster is
 *   de+en, but only **en** is enabled, and the live German instance proves the
 *   difference is load bearing rather than cosmetic — it answers
 *   `exhibitionTitle: null`, `items/count: 5` and `events/count: 0`, i.e. it is
 *   a shell. Per decision Q2 the websites ship as per-language builds, so this
 *   is the field that decides which builds exist. The package still carries the
 *   German curated texts (themes have full de coverage) so a de build becomes
 *   possible the day someone flips `enabled`.
 *
 * - **`featured`** and `hidden` are two independent legacy flags sharing one
 *   enum — see isFeatured/isHidden below.
 */
export class ExhibitionExporter extends BaseExporter {
  getName(): string {
    return 'Exhibition'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting exhibition.json...')

    const langCodeMap = await this.buildLangCodeMap()
    const chrome = this.exhibition.chrome

    const translations = await this.db.query<TranslationRow>(
      `SELECT collection_id, language_id, title, description
       FROM collection_translations
       WHERE collection_id = ?
       ORDER BY language_id`,
      [this.exhibition.id]
    )

    const titles: Record<string, string> = {}
    const subtitles: Record<string, string> = {}
    const headlines: Record<string, string> = {}
    const abouts: Record<string, string> = {}
    const languagesEnabled: string[] = []
    let missingSplitFields = 0

    for (const row of translations) {
      const code = langCodeMap.get(row.language_id)
      if (!code) continue
      if (row.title) titles[code] = row.title

      const i18n = this.exhibition.i18n.get(row.language_id)
      if (i18n?.enabled === 'Y') languagesEnabled.push(code)

      // The three curated texts, as separate fields when the import preserved
      // them and as a single blob when it did not — see the fallback note below.
      if (i18n?.subtitle || i18n?.heading || i18n?.about) {
        if (i18n.subtitle) subtitles[code] = i18n.subtitle
        if (i18n.heading) headlines[code] = i18n.heading
        if (i18n.about) abouts[code] = i18n.about
      } else if (row.description) {
        // Fallback for a database imported before metanull/inventory-app#1546:
        // ThgGalleryTranslationImporter joined subtitle + heading + about into
        // `description` with blank lines between them, and that join is not
        // reversible — `about` contains blank lines of its own. Rather than
        // guess a split, the whole blob ships as `abouts` (the field whose
        // legacy counterpart is a free-text body) and the run logs how many
        // languages are degraded, so a package built on a stale database is
        // visibly rather than silently incomplete.
        abouts[code] = row.description
        missingSplitFields += 1
      }
    }

    if (missingSplitFields > 0) {
      this.logger.warning(
        `exhibition.json: ${missingSplitFields} language(s) carry no split ` +
          `subtitle/heading/about — this database predates the exhibition_i18n ` +
          `text fix. Run the importer's 'exhibition-i18n-text-backfill' step ` +
          `(or a fresh import) and re-export to populate them.`
      )
    }

    const [bannerItemId, homepageItemId] = await Promise.all([
      this.resolveItemReference(chrome.banner_item),
      this.resolveItemReference(chrome.homepage_item),
    ])

    const [logos, partners] = await Promise.all([this.exportLogos(), this.exportPartnerStrip()])

    // popup_logo / popup_logo_show are per-language HTML blocks, so they follow
    // the same per-language shape as the texts above rather than a single flag.
    const popupLogos: Record<string, string> = {}
    const popupLogoShow: Record<string, boolean> = {}
    const bannerCaptions: Record<string, string> = {}
    for (const [languageId, i18n] of this.exhibition.i18n) {
      const code = langCodeMap.get(languageId)
      if (!code) continue
      if (i18n.popup_logo) popupLogos[code] = i18n.popup_logo
      popupLogoShow[code] = i18n.popup_logo_show === 'Y'
      if (i18n.exh_img_caption) bannerCaptions[code] = i18n.exh_img_caption
    }

    const output = {
      id: this.exhibition.id,
      backward_compatibility: this.exhibition.backwardCompatibility,
      kind: 'exhibition',
      // The legacy slug is load-bearing identity, kept verbatim (underscores and
      // all) even though the package and folder name is its kebab-cased form.
      slug: this.exhibition.slug,
      legacy_host: this.exhibition.host,
      mwnf3_project_id: this.exhibition.mwnf3ProjectId,
      languages: Object.keys(titles).sort(),
      languages_enabled: languagesEnabled.sort(),
      titles,
      subtitles,
      headlines,
      abouts,
      popup_logos: popupLogos,
      popup_logo_show: popupLogoShow,
      banner_captions: bannerCaptions,
      banner_item_id: bannerItemId,
      homepage_item_id: homepageItemId,
      // Site chrome images live on the legacy media server and were never
      // imported, so the package carries the legacy path only — the viewer
      // supplies the host, exactly as the legacy client did through
      // VUE_APP_IMAGES_URL. Sponsor logos are the exception: those WERE
      // imported, so `logos[].image_url` below is a resolved absolute URL.
      image_path: chrome.image ?? null,
      banner_image_path: chrome.banner_image ?? null,
      homepage_image_path: chrome.homepage_image ?? null,
      has_timeline: bitToBoolean(chrome.has_timeline),
      has_country_timeline: bitToBoolean(chrome.has_country_timeline),
      featured: isFeatured(chrome.featured),
      hidden: isHidden(chrome.status),
      live_date: chrome.live_date ?? null,
      logos,
      partners,
      hidden_partner_ids: await this.resolveHiddenPartnerIds(),
      sibling_sites: await this.exportSiblings(langCodeMap),
    }

    await this.writeJson('exhibition.json', output)
    this.logger.success(
      `exhibition.json (${this.exhibition.slug ?? this.exhibition.backwardCompatibility}, ` +
        `${output.languages.length} languages / ${output.languages_enabled.length} enabled, ` +
        `${logos.length} logos, ${partners.length} strip partners, ` +
        `${output.sibling_sites.length} siblings)`
    )

    return { file: 'exhibition.json', count: 1 }
  }

  /**
   * `exhibition_logo` → `collection_images`, the sponsor logos on the bottom
   * banner.
   *
   * Only the image itself, its alt text and its order survive the import:
   * `collection_images` has no `extra` column, so the legacy row's `label`,
   * `link`, `category_id` and `visible` are read by the importer and then
   * dropped. On Colours that costs the one logo its caption ("United Nations
   * Alliance of Civilizations"), its href (unaoc.org) and its "Footer 2"
   * category. Recording the gap here rather than silently shipping a
   * link-less logo — see the exporter README.
   */
  private async exportLogos(): Promise<unknown[]> {
    const rows = await this.db.query<LogoRow>(
      `SELECT id, path, original_name, alt_text, display_order
       FROM collection_images
       WHERE collection_id = ?
       ORDER BY display_order, id`,
      [this.exhibition.id]
    )

    return rows.map(row => ({
      id: row.id,
      image_url: this.imageUrl(row.path),
      legacy_path: row.original_name,
      alt_text: row.alt_text,
      display_order: row.display_order,
    }))
  }

  /**
   * `exhibition_partner` → `collection_partner`, the partner strip legacy
   * renders on every page. `level` is the legacy category (Colours' single
   * entry is `other_contributor`, the UN Alliance of Civilisations, which is an
   * `institution` rather than a museum).
   */
  private async exportPartnerStrip(): Promise<unknown[]> {
    const rows = await this.db.query<PartnerStripRow>(
      `SELECT partner_id, level, visible
       FROM collection_partner
       WHERE collection_id = ?
       ORDER BY level, partner_id`,
      [this.exhibition.id]
    )

    return rows.map((row, index) => ({
      partner_id: row.partner_id,
      category: row.level,
      visible: row.visible === 1 || row.visible === true,
      display_order: index + 1,
    }))
  }

  /**
   * Gap E6 — `exhibition_hidden_mwnf3_museums`, imported into
   * `extra.thg_gallery.hidden_partners`. Such a partner is excluded from every
   * list and profile page while its items still render, so the package flags
   * them rather than dropping them. This is the exhibition the field was
   * written for — 13 museums here against none on Colours — so it is also the
   * first package where a viewer's E6 handling is actually exercised rather
   * than merely present.
   */
  private async resolveHiddenPartnerIds(): Promise<string[]> {
    const raw = (this.exhibition.anchor as { hidden_partners?: unknown }).hidden_partners
    if (!Array.isArray(raw) || raw.length === 0) return []

    const keys = raw
      .map(entry =>
        typeof entry === 'string'
          ? entry
          : ((entry as { backward_compatibility?: string })?.backward_compatibility ?? null)
      )
      .filter((key): key is string => !!key)

    if (keys.length === 0) return []

    const rows = await this.db.query<{ id: string }>(
      `SELECT id FROM partners WHERE backward_compatibility IN (${this.placeholders(keys.length)})`,
      keys
    )

    if (rows.length !== keys.length) {
      this.logger.warning(
        `exhibition.json: ${keys.length - rows.length} hidden-partner reference(s) did not resolve`
      )
    }
    return rows.map(row => row.id)
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
        this.logger.warning(`Unparseable item reference, dropped: ${reference}`)
      }
      return null
    }

    const rows = await this.db.query<{ id: string }>(
      `SELECT id FROM items WHERE backward_compatibility = ?`,
      [backwardCompatibility]
    )

    if (!rows[0]) {
      this.logger.warning(`Item reference does not resolve: ${backwardCompatibility}`)
      return null
    }
    return rows[0].id
  }

  /**
   * The sibling strip — the other DXA sites, galleries and exhibitions alike.
   *
   * Per decision Q3 each entry is a REFERENCE, not a link: the exporter records
   * identity plus whatever metadata the import carried (slug, legacy host, kind)
   * and never constructs a target URL. Resolution is a later, separate
   * mechanism, and a viewer degrades gracefully where a reference cannot be
   * resolved yet.
   */
  private async exportSiblings(langCodeMap: Map<string, string>): Promise<unknown[]> {
    const siblings = await this.db.query<SiblingRow>(
      `SELECT id, backward_compatibility, type, extra
       FROM collections
       WHERE type IN ('gallery', 'exhibition')
         AND id <> ?
         AND backward_compatibility LIKE 'mwnf3\\_thematic\\_gallery:thg\\_gallery:%'
       ORDER BY backward_compatibility`,
      [this.exhibition.id]
    )

    if (siblings.length === 0) return []

    const siblingIds = siblings.map(s => s.id)
    const placeholders = this.placeholders(siblingIds.length)

    const [titles, chromeRows] = await Promise.all([
      this.db.query<TranslationRow>(
        `SELECT collection_id, language_id, title, description
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
          kind: sibling.type,
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
 * `featured` and `status` are two INDEPENDENT flags that happen to share the
 * enum('A','H'), which is why they are easy to conflate:
 *
 *   status:   A = Active, H = Hidden — visibility of the site everywhere.
 *   featured: A = highlighted in the portal's "featured galleries" strip,
 *             H = not highlighted. Default 'H'.
 *
 * Both meanings are spelled out in the column comments of
 * `.legacy-database/ddl/creation/mwnf3_thematic_gallery_thg_gallery.sql`, and
 * `water_in_islam` is one of the ten hand-picked `featured = 'A'`
 * rows.
 *
 * dxa-api gets `featured` WRONG: `WithTHGTemporaryTables.php` copies the
 * `hidden` projection — `CASE WHEN featured = 'A' THEN 0 ELSE 1 END` — without
 * flipping the polarity, so its JSON reports the inverse of the record. This
 * exhibition is the same mirror case as carpets: the record says `featured='A'`
 * and the live API answers `featured: false`. The defect never surfaces on the
 * legacy sites (the featured endpoint ignores the flag and dxa-client never
 * reads it), so packages ship the documented meaning rather than the bug — this
 * is the one field where a live-API parity check is expected to disagree.
 */
export function isFeatured(flag: string | undefined | null): boolean {
  return flag === 'A'
}

/** `status = 'A'` is the visible state; anything else hides the site. */
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
