/**
 * THG Theme Item Importer
 *
 * Imports theme_item entries, linking items to collections via the collection_item pivot.
 * Only imports mwnf3-resolvable items (objects/monuments/details).
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.theme_item (gallery_id, theme_id, various item reference columns)
 *
 * New schema:
 * - collection_item pivot (collection_id, item_id) via attachItemsToCollection
 *
 * Supported item references (mwnf3 only for now):
 * - mwnf3_object_{project}_{country}_{partner}_{item}
 * - mwnf3_monument_{project}_{country}_{partner}_{item}
 * - mwnf3_monument_detail_{project}_{country}_{partner}_{item}_{detail}
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy theme_item structure (simplified - only mwnf3 columns)
 */
interface LegacyThemeItem {
  gallery_id: number;
  theme_id: number;
  item_id: number;
  sort_order: number;
  // mwnf3 object references
  mwnf3_object_project_id: string | null;
  mwnf3_object_country_id: string | null;
  mwnf3_object_partner_id: string | null;
  mwnf3_object_item_id: number | null;
  // mwnf3 monument references
  mwnf3_monument_project_id: string | null;
  mwnf3_monument_country_id: string | null;
  mwnf3_monument_partner_id: string | null;
  mwnf3_monument_item_id: number | null;
  // mwnf3 monument detail references
  mwnf3_monument_detail_project_id: string | null;
  mwnf3_monument_detail_country_id: string | null;
  mwnf3_monument_detail_partner_id: string | null;
  mwnf3_monument_detail_item_id: number | null;
  mwnf3_monument_detail_detail_id: number | null;
}

export class ThgThemeItemImporter extends BaseImporter {
  getName(): string {
    return 'ThgThemeItemImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing theme-item associations (mwnf3 items only)...');

      // Query theme_item entries from legacy database
      const themeItems = await this.context.legacyDb.query<LegacyThemeItem>(
        `SELECT gallery_id, theme_id, item_id, sort_order,
                mwnf3_object_project_id, mwnf3_object_country_id, mwnf3_object_partner_id, mwnf3_object_item_id,
                mwnf3_monument_project_id, mwnf3_monument_country_id, mwnf3_monument_partner_id, mwnf3_monument_item_id,
                mwnf3_monument_detail_project_id, mwnf3_monument_detail_country_id, mwnf3_monument_detail_partner_id,
                mwnf3_monument_detail_item_id, mwnf3_monument_detail_detail_id
         FROM mwnf3_thematic_gallery.theme_item
         ORDER BY gallery_id, theme_id, sort_order`
      );

      this.logInfo(`Found ${themeItems.length} theme-item associations to process`);

      // Group items by collection for efficient batch attachment
      const collectionItems: Map<string, string[]> = new Map();
      let skippedNonMwnf3 = 0;
      let skippedNoItem = 0;

      for (const legacy of themeItems) {
        try {
          // Resolve the item reference
          const itemBackwardCompat = this.resolveItemReference(legacy);

          if (!itemBackwardCompat) {
            // Not an mwnf3 item - skip for now
            skippedNonMwnf3++;
            continue;
          }

          // Get the item ID from tracker
          const itemId = this.getEntityUuid(itemBackwardCompat, 'item');
          if (!itemId) {
            // Item not found in tracker - might not be imported yet
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme item ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}: Item not found (${itemBackwardCompat})`
            );
            skippedNoItem++;
            continue;
          }

          // Get the collection ID for this gallery
          const galleryBackwardCompat = `thg_gallery.${legacy.gallery_id}`;
          const collectionId = this.getEntityUuid(galleryBackwardCompat, 'collection');
          if (!collectionId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme item ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}: Collection not found`
            );
            result.skipped++;
            continue;
          }

          // Add to collection batch
          if (!collectionItems.has(collectionId)) {
            collectionItems.set(collectionId, []);
          }
          const items = collectionItems.get(collectionId)!;
          if (!items.includes(itemId)) {
            items.push(itemId);
          }

          // Collect sample
          this.collectSample(
            'thg_theme_item',
            {
              ...legacy,
              resolved_item_backward_compat: itemBackwardCompat,
            } as unknown as Record<string, unknown>,
            'success'
          );

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Theme item ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}: ${message}`
          );
          this.logError(
            `Theme item ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}`,
            error
          );
          this.showError();
        }
      }

      // Log skipped statistics
      if (skippedNonMwnf3 > 0) {
        this.logInfo(`Skipped ${skippedNonMwnf3} non-mwnf3 items (will be imported in future phases)`);
      }
      if (skippedNoItem > 0) {
        this.logInfo(`Skipped ${skippedNoItem} items not found in tracker`);
      }

      // Batch attach items to collections
      if (!this.isDryRun && !this.isSampleOnlyMode) {
        this.logInfo(`Attaching items to ${collectionItems.size} collections...`);
        for (const [collectionId, itemIds] of collectionItems) {
          try {
            await this.context.strategy.attachItemsToCollection(collectionId, itemIds);
          } catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(`Failed to attach items to collection ${collectionId}: ${message}`);
            this.logError(`Collection ${collectionId}`, error);
          }
        }
      }

      this.showSummary(result.imported, result.skipped + skippedNonMwnf3 + skippedNoItem, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgThemeItemImporter', error);
    }

    return result;
  }

  /**
   * Resolve item reference from theme_item to backward_compatibility string
   * Returns null if not an mwnf3 item
   */
  private resolveItemReference(legacy: LegacyThemeItem): string | null {
    // Check mwnf3_object reference
    if (
      legacy.mwnf3_object_project_id &&
      legacy.mwnf3_object_country_id &&
      legacy.mwnf3_object_partner_id &&
      legacy.mwnf3_object_item_id !== null
    ) {
      return `mwnf3_object.${legacy.mwnf3_object_project_id}.${legacy.mwnf3_object_country_id}.${legacy.mwnf3_object_partner_id}.${legacy.mwnf3_object_item_id}`;
    }

    // Check mwnf3_monument reference
    if (
      legacy.mwnf3_monument_project_id &&
      legacy.mwnf3_monument_country_id &&
      legacy.mwnf3_monument_partner_id &&
      legacy.mwnf3_monument_item_id !== null
    ) {
      return `mwnf3_monument.${legacy.mwnf3_monument_project_id}.${legacy.mwnf3_monument_country_id}.${legacy.mwnf3_monument_partner_id}.${legacy.mwnf3_monument_item_id}`;
    }

    // Check mwnf3_monument_detail reference
    if (
      legacy.mwnf3_monument_detail_project_id &&
      legacy.mwnf3_monument_detail_country_id &&
      legacy.mwnf3_monument_detail_partner_id &&
      legacy.mwnf3_monument_detail_item_id !== null &&
      legacy.mwnf3_monument_detail_detail_id !== null
    ) {
      return `mwnf3_monument_detail.${legacy.mwnf3_monument_detail_project_id}.${legacy.mwnf3_monument_detail_country_id}.${legacy.mwnf3_monument_detail_partner_id}.${legacy.mwnf3_monument_detail_item_id}.${legacy.mwnf3_monument_detail_detail_id}`;
    }

    // Not an mwnf3 item (sh, thg, explore, travel, etc.)
    return null;
  }
}
