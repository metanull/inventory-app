/**
 * MWNF3 Exhibition Item Importer
 *
 * Links items to mwnf3 exhibition page Collections via the collection_item pivot,
 * and imports custom images as CollectionImage records.
 *
 * Item references (exhibition_page_images.ref_item, artintro_page_images.ref_item):
 * - Format: 'O;ISL;jo;1;8' (type;project;country;partner;number) for objects
 * - Format: 'M;BAR;it;12;30' for monuments
 * - Empty ref_item = custom image (CollectionImage, not a pivot)
 *
 * Exhibition-level images (exhibition_images.ref_item):
 * - Same format, linked to exhibition Collection
 * - 92 item references + 2 custom images
 *
 * EAV metadata (exhibition_page_images_fields):
 * - detail_justification, detail_name → stored in collection_item pivot extra
 * - item_* fields → denormalized copies, skipped
 *
 * Image detail annotations (exhibition_page_image_details + fields):
 * - Stored as nested array in the parent page-image's collection_item extra
 *
 * Dependencies:
 * - Mwnf3ExhibitionImporter (hierarchy collections must exist)
 * - ObjectImporter, MonumentImporter (items must exist)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import type {
  Mwnf3LegacyExhibitionPageImage,
  Mwnf3LegacyExhibitionPageImageDetail,
  Mwnf3LegacyExhibitionLevelImage,
  Mwnf3LegacyEavField,
  Mwnf3LegacyArtintroPageImage,
} from '../../domain/types/index.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';

const MWNF3_SCHEMA = 'mwnf3';

/**
 * Parsed ref_item components.
 */
interface ParsedRefItem {
  type: 'object' | 'monument';
  projectId: string;
  country: string;
  partnerId: string;
  number: number;
}

/**
 * Pivoted EAV: non-empty editorial fields only.
 */
interface ImageEav {
  detail_name?: Record<string, string>; // lang → text
  detail_justification?: Record<string, string>; // lang → text
}

export class Mwnf3ExhibitionItemImporter extends BaseImporter {
  getName(): string {
    return 'Mwnf3ExhibitionItemImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing mwnf3 exhibition item references and custom images...');

      // ========================================================================
      // Pass 1: Page-level images (2,394 rows)
      // ========================================================================
      await this.importPageImages(result);

      // ========================================================================
      // Pass 2: Exhibition-level images (94 rows)
      // ========================================================================
      await this.importExhibitionLevelImages(result);

      // ========================================================================
      // Pass 3: Artintro page images (158 rows, all item references)
      // ========================================================================
      await this.importArtintroPageImages(result);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('Mwnf3ExhibitionItemImporter', message);
    }

    return result;
  }

  // --------------------------------------------------------------------------
  // Pass 1: Page-level images
  // --------------------------------------------------------------------------
  private async importPageImages(result: ImportResult): Promise<void> {
    const images = await this.context.legacyDb.query<Mwnf3LegacyExhibitionPageImage>(
      `SELECT image_id, page_id, n, n2, ref_item, picture
       FROM ${MWNF3_SCHEMA}.exhibition_page_images
       ORDER BY page_id, n, n2`
    );

    this.logInfo(`Found ${images.length} exhibition page images`);

    // Pre-load EAV data for editorial fields (detail_name, detail_justification only)
    const imageEavMap = await this.loadImageEav();

    // Pre-load detail annotations
    const detailMap = await this.loadImageDetails();

    for (const img of images) {
      try {
        const collectionBackwardCompat = `${MWNF3_SCHEMA}:exhibition_pages:${img.page_id}`;
        const collectionId = await this.getEntityUuidAsync(collectionBackwardCompat, 'collection');
        if (!collectionId) {
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Page image ${img.image_id}: Page collection not found (${collectionBackwardCompat})`
          );
          this.logWarning(
            `Page image ${img.image_id}: Page collection not found (${collectionBackwardCompat}), skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const parsed = this.parseRefItem(img.ref_item);

        if (parsed) {
          // Item reference → collection_item pivot
          const itemBackwardCompat = this.buildItemBackwardCompat(parsed);
          const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');
          if (!itemId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Page image ${img.image_id}: Item not found (${itemBackwardCompat})`
            );
            this.logWarning(
              `Page image ${img.image_id}: Item not found (${itemBackwardCompat}), skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Build extra with ordering, picture path, and editorial EAV
          const extra: Record<string, unknown> = {
            n: img.n,
            n2: img.n2,
            picture: img.picture,
          };

          // Merge EAV editorial data
          const eav = imageEavMap.get(img.image_id);
          if (eav) {
            if (eav.detail_name) extra.detail_name = eav.detail_name;
            if (eav.detail_justification) extra.detail_justification = eav.detail_justification;
          }

          // Merge detail annotations
          const details = detailMap.get(img.image_id);
          if (details && details.length > 0) {
            extra.details = details;
          }

          if (this.isDryRun || this.isSampleOnlyMode) {
            result.imported++;
            this.showProgress();
            continue;
          }

          await this.context.strategy.writeCollectionItem({
            collection_id: collectionId,
            item_id: itemId,
            display_order: img.n,
            extra,
          });

          result.imported++;
          this.showProgress();
        } else {
          // Custom image (empty ref_item) → CollectionImage
          if (this.isDryRun || this.isSampleOnlyMode) {
            result.imported++;
            this.showProgress();
            continue;
          }

          await this.context.strategy.writeCollectionImage({
            collection_id: collectionId,
            path: img.picture,
            original_name: img.picture.split('/').pop() || img.picture,
            mime_type: 'image/jpeg',
            size: 1, // Placeholder for ImageSyncTool
            alt_text: null,
            display_order: img.n,
          });

          result.imported++;
          this.showProgress();
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Page image ${img.image_id}: ${message}`);
        this.logError(`Page image ${img.image_id}`, message);
        this.showError();
      }
    }
  }

  // --------------------------------------------------------------------------
  // Pass 2: Exhibition-level images
  // --------------------------------------------------------------------------
  private async importExhibitionLevelImages(result: ImportResult): Promise<void> {
    const images = await this.context.legacyDb.query<Mwnf3LegacyExhibitionLevelImage>(
      `SELECT image_id, exhibition_id, n, n2, ref_item, picture
       FROM ${MWNF3_SCHEMA}.exhibition_images
       ORDER BY exhibition_id, n, n2`
    );

    this.logInfo(`Found ${images.length} exhibition-level images`);

    for (const img of images) {
      try {
        const collectionBackwardCompat = `${MWNF3_SCHEMA}:exhibitions:${img.exhibition_id}`;
        const collectionId = await this.getEntityUuidAsync(collectionBackwardCompat, 'collection');
        if (!collectionId) {
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Exhibition image ${img.image_id}: Exhibition collection not found (${collectionBackwardCompat})`
          );
          this.logWarning(
            `Exhibition image ${img.image_id}: Exhibition collection not found (${collectionBackwardCompat}), skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const parsed = this.parseRefItem(img.ref_item);

        if (parsed) {
          // Item reference → collection_item pivot
          const itemBackwardCompat = this.buildItemBackwardCompat(parsed);
          const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');
          if (!itemId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Exhibition image ${img.image_id}: Item not found (${itemBackwardCompat})`
            );
            this.logWarning(
              `Exhibition image ${img.image_id}: Item not found (${itemBackwardCompat}), skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          if (this.isDryRun || this.isSampleOnlyMode) {
            result.imported++;
            this.showProgress();
            continue;
          }

          await this.context.strategy.writeCollectionItem({
            collection_id: collectionId,
            item_id: itemId,
            display_order: img.n,
            extra: {
              n: img.n,
              n2: img.n2,
              picture: img.picture,
            },
          });

          result.imported++;
          this.showProgress();
        } else {
          // Custom image → CollectionImage
          if (this.isDryRun || this.isSampleOnlyMode) {
            result.imported++;
            this.showProgress();
            continue;
          }

          await this.context.strategy.writeCollectionImage({
            collection_id: collectionId,
            path: img.picture,
            original_name: img.picture.split('/').pop() || img.picture,
            mime_type: 'image/jpeg',
            size: 1,
            alt_text: null,
            display_order: img.n,
          });

          result.imported++;
          this.showProgress();
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Exhibition image ${img.image_id}: ${message}`);
        this.logError(`Exhibition image ${img.image_id}`, message);
        this.showError();
      }
    }
  }

  // --------------------------------------------------------------------------
  // Pass 3: Artintro page images (all item references, no custom)
  // --------------------------------------------------------------------------
  private async importArtintroPageImages(result: ImportResult): Promise<void> {
    const images = await this.context.legacyDb.query<Mwnf3LegacyArtintroPageImage>(
      `SELECT image_id, page_id, n, n2, ref_item, picture
       FROM ${MWNF3_SCHEMA}.artintro_page_images
       ORDER BY page_id, n, n2`
    );

    this.logInfo(`Found ${images.length} artintro page images`);

    for (const img of images) {
      try {
        const collectionBackwardCompat = `${MWNF3_SCHEMA}:artintro_pages:${img.page_id}`;
        const collectionId = await this.getEntityUuidAsync(collectionBackwardCompat, 'collection');
        if (!collectionId) {
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Artintro page image ${img.image_id}: Page collection not found (${collectionBackwardCompat})`
          );
          this.logWarning(
            `Artintro page image ${img.image_id}: Page collection not found, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const parsed = this.parseRefItem(img.ref_item);
        if (!parsed) {
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Artintro page image ${img.image_id}: Could not parse ref_item '${img.ref_item}'`
          );
          this.logWarning(
            `Artintro page image ${img.image_id}: Could not parse ref_item '${img.ref_item}', skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const itemBackwardCompat = this.buildItemBackwardCompat(parsed);
        const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');
        if (!itemId) {
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Artintro page image ${img.image_id}: Item not found (${itemBackwardCompat})`
          );
          this.logWarning(
            `Artintro page image ${img.image_id}: Item not found (${itemBackwardCompat}), skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        if (this.isDryRun || this.isSampleOnlyMode) {
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.writeCollectionItem({
          collection_id: collectionId,
          item_id: itemId,
          display_order: img.n,
          extra: {
            n: img.n,
            n2: img.n2,
            picture: img.picture,
          },
        });

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Artintro page image ${img.image_id}: ${message}`);
        this.logError(`Artintro page image ${img.image_id}`, message);
        this.showError();
      }
    }
  }

  // ==========================================================================
  // Helpers
  // ==========================================================================

  /**
   * Parse mwnf3 ref_item format: 'O;ISL;jo;1;8' or 'M;BAR;it;12;30'.
   * Returns null for empty/invalid references.
   */
  private parseRefItem(refItem: string): ParsedRefItem | null {
    if (!refItem || !refItem.trim()) return null;

    const parts = refItem.split(';').map((s) => s.trim());
    if (parts.length < 5) return null;

    const [typeCode, projectId, country, partnerId, numberStr] = parts;
    const number = parseInt(numberStr!, 10);
    if (isNaN(number)) return null;

    const type = typeCode === 'M' ? 'monument' : 'object';
    return { type, projectId: projectId!, country: country!, partnerId: partnerId!, number };
  }

  /**
   * Build a backward_compatibility key for an mwnf3 item from parsed ref_item.
   * Objects: mwnf3:objects:{project}:{country}:{museum_id}:{number}
   * Monuments: mwnf3:monuments:{project}:{country}:{institution_id}:{number}
   *
   * ref_item stores raw partner IDs (e.g., '1', '1_H', '12') that must be
   * converted to the museum/institution format used in the objects/monuments
   * tables: Mus01, Mus01_H, Mus12, Mon01, etc.
   */
  private buildItemBackwardCompat(parsed: ParsedRefItem): string {
    const table = parsed.type === 'monument' ? 'monuments' : 'objects';
    const prefix = parsed.type === 'monument' ? 'Mon' : 'Mus';
    const formattedPartnerId = this.formatPartnerId(prefix, parsed.partnerId);
    return `${MWNF3_SCHEMA}:${table}:${parsed.projectId.toLowerCase()}:${parsed.country.toLowerCase()}:${formattedPartnerId}:${parsed.number}`;
  }

  /**
   * Convert a raw ref_item partner ID to the museum/institution format.
   * Examples: '1' → 'Mus01', '1_H' → 'Mus01_H', '12' → 'Mus12'
   */
  private formatPartnerId(prefix: string, rawId: string): string {
    const underscoreIdx = rawId.indexOf('_');
    if (underscoreIdx !== -1) {
      const numPart = rawId.substring(0, underscoreIdx);
      const suffix = rawId.substring(underscoreIdx);
      return `${prefix}${numPart.padStart(2, '0')}${suffix}`;
    }
    return `${prefix}${rawId.padStart(2, '0')}`;
  }

  /**
   * Load and pivot exhibition_page_images_fields EAV for editorial fields only.
   * Returns Map<image_id, ImageEav>.
   */
  private async loadImageEav(): Promise<Map<number, ImageEav>> {
    const rows = await this.context.legacyDb.query<
      Mwnf3LegacyEavField & { image_id: number; n: number; n2: number }
    >(
      `SELECT image_id AS entity_id, n, n2, lang_id, field, value
       FROM ${MWNF3_SCHEMA}.exhibition_page_images_fields
       WHERE field IN ('detail_name', 'detail_justification')
         AND value IS NOT NULL AND value != ''
       ORDER BY image_id, lang_id, field`
    );

    const map = new Map<number, ImageEav>();
    for (const row of rows) {
      const imageId = row.entity_id;
      if (!map.has(imageId)) {
        map.set(imageId, {});
      }
      const eav = map.get(imageId)!;

      if (row.field === 'detail_name') {
        if (!eav.detail_name) eav.detail_name = {};
        eav.detail_name[row.lang_id] = convertHtmlToMarkdown(row.value);
      } else if (row.field === 'detail_justification') {
        if (!eav.detail_justification) eav.detail_justification = {};
        eav.detail_justification[row.lang_id] = convertHtmlToMarkdown(row.value);
      }
    }

    this.logInfo(`Loaded editorial EAV for ${map.size} page images`);
    return map;
  }

  /**
   * Load exhibition_page_image_details + EAV fields.
   * Returns Map<parent_image_id, detail_annotation[]>.
   */
  private async loadImageDetails(): Promise<Map<number, Array<Record<string, unknown>>>> {
    const details = await this.context.legacyDb.query<Mwnf3LegacyExhibitionPageImageDetail>(
      `SELECT image_detail_id AS detail_id, image_id, n, n2, ref_detail_item, picture_details
       FROM ${MWNF3_SCHEMA}.exhibition_page_image_details
       ORDER BY image_id, n, n2`
    );

    // Load EAV for details
    const detailEavRows = await this.context.legacyDb.query<
      Mwnf3LegacyEavField & { detail_id: number }
    >(
      `SELECT image_detail_id AS entity_id, lang_id, field, value
       FROM ${MWNF3_SCHEMA}.exhibition_page_image_details_fields
       WHERE field IN ('detail_name', 'detail_justification')
         AND value IS NOT NULL AND value != ''
       ORDER BY image_detail_id, lang_id, field`
    );

    // Pivot detail EAV
    const detailEav = new Map<number, ImageEav>();
    for (const row of detailEavRows) {
      const detailId = row.entity_id;
      if (!detailEav.has(detailId)) {
        detailEav.set(detailId, {});
      }
      const eav = detailEav.get(detailId)!;
      if (row.field === 'detail_name') {
        if (!eav.detail_name) eav.detail_name = {};
        eav.detail_name[row.lang_id] = convertHtmlToMarkdown(row.value);
      } else if (row.field === 'detail_justification') {
        if (!eav.detail_justification) eav.detail_justification = {};
        eav.detail_justification[row.lang_id] = convertHtmlToMarkdown(row.value);
      }
    }

    // Group by parent image_id
    const map = new Map<number, Array<Record<string, unknown>>>();
    for (const d of details) {
      if (!map.has(d.image_id)) {
        map.set(d.image_id, []);
      }
      const annotation: Record<string, unknown> = {
        n: d.n,
        n2: d.n2,
        ref_detail_item: d.ref_detail_item,
        picture_details: d.picture_details,
      };

      const eav = detailEav.get(d.detail_id);
      if (eav) {
        if (eav.detail_name) annotation.detail_name = eav.detail_name;
        if (eav.detail_justification) annotation.detail_justification = eav.detail_justification;
      }

      map.get(d.image_id)!.push(annotation);
    }

    this.logInfo(`Loaded ${details.length} detail annotations across ${map.size} parent images`);
    return map;
  }
}
