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
      // Note: The legacy schema may not have this table or may have different columns - handle gracefully
      let themeItems: LegacyThemeItem[];
      try {
        themeItems = await this.context.legacyDb.query<LegacyThemeItem>(
          `SELECT gallery_id, theme_id, item_id,
                  mwnf3_object_project_id, mwnf3_object_country_id, mwnf3_object_partner_id, mwnf3_object_item_id,
                  mwnf3_monument_project_id, mwnf3_monument_country_id, mwnf3_monument_partner_id, mwnf3_monument_item_id,
                  mwnf3_monument_detail_project_id, mwnf3_monument_detail_country_id, mwnf3_monument_detail_partner_id,
                  mwnf3_monument_detail_item_id, mwnf3_monument_detail_detail_id
           FROM mwnf3_thematic_gallery.theme_item
           ORDER BY gallery_id, theme_id, item_id`
        );
      } catch (queryError) {
        const message = queryError instanceof Error ? queryError.message : String(queryError);
        if (message.includes("doesn't exist") || message.includes('Unknown column')) {
          this.logInfo(
            `⚠️ Skipping: Legacy theme_item table not available (${message})`
          );
          result.warnings = result.warnings || [];
          result.warnings.push(`Legacy theme_item table not available: ${message}`);
          return result;
        }
        throw queryError;
      }

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

          // Get the item ID from tracker or database (items are from earlier phases)
          const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');
          if (!itemId) {
            // Item not found in tracker or database - might not be imported yet
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme item ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}: Item not found (${itemBackwardCompat})`
            );
            skippedNoItem++;
            continue;
          }

          // Get the collection ID for this gallery (Phase 05 internal)
          const galleryBackwardCompat = `mwnf3_thematic_gallery:thg_gallery:${legacy.gallery_id}`;
          const collectionId = await this.getEntityUuidAsync(galleryBackwardCompat, 'collection');
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
    // Format: mwnf3:objects:PROJECT:COUNTRY:MUSEUM:NUMBER (matching object-transformer.ts)
    if (
      legacy.mwnf3_object_project_id &&
      legacy.mwnf3_object_country_id &&
      legacy.mwnf3_object_partner_id &&
      legacy.mwnf3_object_item_id !== null
    ) {
      return `mwnf3:objects:${legacy.mwnf3_object_project_id}:${legacy.mwnf3_object_country_id}:${legacy.mwnf3_object_partner_id}:${legacy.mwnf3_object_item_id}`;
    }

    // Check mwnf3_monument reference
    // Format: mwnf3:monuments:PROJECT:COUNTRY:INSTITUTION:NUMBER (matching monument-transformer.ts)
    if (
      legacy.mwnf3_monument_project_id &&
      legacy.mwnf3_monument_country_id &&
      legacy.mwnf3_monument_partner_id &&
      legacy.mwnf3_monument_item_id !== null
    ) {
      return `mwnf3:monuments:${legacy.mwnf3_monument_project_id}:${legacy.mwnf3_monument_country_id}:${legacy.mwnf3_monument_partner_id}:${legacy.mwnf3_monument_item_id}`;
    }

    // Check mwnf3_monument_detail reference
    // Format: mwnf3:monument_details:PROJECT:COUNTRY:INSTITUTION:MONUMENT:DETAIL (matching monument-detail-transformer.ts)
    if (
      legacy.mwnf3_monument_detail_project_id &&
      legacy.mwnf3_monument_detail_country_id &&
      legacy.mwnf3_monument_detail_partner_id &&
      legacy.mwnf3_monument_detail_item_id !== null &&
      legacy.mwnf3_monument_detail_detail_id !== null
    ) {
      return `mwnf3:monument_details:${legacy.mwnf3_monument_detail_project_id}:${legacy.mwnf3_monument_detail_country_id}:${legacy.mwnf3_monument_detail_partner_id}:${legacy.mwnf3_monument_detail_item_id}:${legacy.mwnf3_monument_detail_detail_id}`;
    }

    // Not an mwnf3 item (sh, thg, explore, travel, etc.)
    return null;
  }
}
