/**
 * THG Theme Item Importer
 *
 * Imports theme_item entries, linking items to theme collections via the collection_item pivot.
 * Items are attached to their specific theme collection (not the parent gallery).
 * Imports mwnf3-resolvable items and SH (Sharing History) items.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.theme_item (gallery_id, theme_id, various item reference columns)
 *
 * New schema:
 * - collection_item pivot (collection_id, item_id) via attachItemsToCollection
 *   - collection_id = theme collection (mwnf3_thematic_gallery:theme:{gallery_id}:{theme_id})
 *
 * Supported item references:
 * - mwnf3_object_{project}_{country}_{partner}_{item}
 * - mwnf3_monument_{project}_{country}_{partner}_{item}
 * - mwnf3_monument_detail_{project}_{country}_{partner}_{item}_{detail}
 * - sh_object_{project}_{country}_{item}
 * - sh_monument_{project}_{country}_{item}
 * - sh_monument_detail_{project}_{country}_{item}_{detail}
 *
 * Dependencies:
 * - ThgThemeImporter (must run first to create theme collections)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy theme_item structure (mwnf3 and SH columns)
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
  // SH (Sharing History) object references
  sh_object_project_id: string | null;
  sh_object_country_id: string | null;
  sh_object_item_id: number | null;
  // SH monument references
  sh_monument_project_id: string | null;
  sh_monument_country_id: string | null;
  sh_monument_item_id: number | null;
  // SH monument detail references
  sh_monument_detail_project_id: string | null;
  sh_monument_detail_country_id: string | null;
  sh_monument_detail_item_id: number | null;
  sh_monument_detail_detail_id: number | null;
}

export class ThgThemeItemImporter extends BaseImporter {
  getName(): string {
    return 'ThgThemeItemImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing theme-item associations to theme collections...');

      // Query theme_item entries from legacy database
      // Note: The legacy schema may not have this table or may have different columns - handle gracefully
      let themeItems: LegacyThemeItem[];
      try {
        themeItems = await this.context.legacyDb.query<LegacyThemeItem>(
          `SELECT gallery_id, theme_id, item_id,
                  mwnf3_object_project_id, mwnf3_object_country_id, mwnf3_object_partner_id, mwnf3_object_item_id,
                  mwnf3_monument_project_id, mwnf3_monument_country_id, mwnf3_monument_partner_id, mwnf3_monument_item_id,
                  mwnf3_monument_detail_project_id, mwnf3_monument_detail_country_id, mwnf3_monument_detail_partner_id,
                  mwnf3_monument_detail_item_id, mwnf3_monument_detail_detail_id,
                  sh_object_project_id, sh_object_country_id, sh_object_item_id,
                  sh_monument_project_id, sh_monument_country_id, sh_monument_item_id,
                  sh_monument_detail_project_id, sh_monument_detail_country_id, sh_monument_detail_item_id, sh_monument_detail_detail_id
           FROM mwnf3_thematic_gallery.theme_item
           ORDER BY gallery_id, theme_id, item_id`
        );
      } catch (queryError) {
        const message = queryError instanceof Error ? queryError.message : String(queryError);
        if (message.includes("doesn't exist") || message.includes('Unknown column')) {
          this.logInfo(`⚠️ Skipping: Legacy theme_item table not available (${message})`);
          result.warnings = result.warnings || [];
          result.warnings.push(`Legacy theme_item table not available: ${message}`);
          return result;
        }
        throw queryError;
      }

      this.logInfo(`Found ${themeItems.length} theme-item associations to process`);

      // Group items by theme collection for efficient batch attachment
      const collectionItems: Map<string, string[]> = new Map();
      let skippedNonMwnf3 = 0;
      let skippedNoItem = 0;
      let skippedNoTheme = 0;

      for (const legacy of themeItems) {
        try {
          // Resolve the item reference
          const itemBackwardCompat = this.resolveItemReference(legacy);

          if (!itemBackwardCompat) {
            // Not an mwnf3/SH item - skip for now
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

          // Get the theme collection ID (themes are now collections)
          const themeBackwardCompat = `mwnf3_thematic_gallery:theme:${legacy.gallery_id}:${legacy.theme_id}`;
          const themeCollectionId = await this.getEntityUuidAsync(themeBackwardCompat, 'collection');
          if (!themeCollectionId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme item ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}: Theme collection not found (${themeBackwardCompat})`
            );
            skippedNoTheme++;
            continue;
          }

          // Add to theme collection batch
          if (!collectionItems.has(themeCollectionId)) {
            collectionItems.set(themeCollectionId, []);
          }
          const items = collectionItems.get(themeCollectionId)!;
          if (!items.includes(itemId)) {
            items.push(itemId);
          }

          // Collect sample
          this.collectSample(
            'thg_theme_item',
            {
              ...legacy,
              resolved_item_backward_compat: itemBackwardCompat,
              resolved_theme_collection: themeBackwardCompat,
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
            message
          );
          this.showError();
        }
      }

      // Log skipped statistics
      if (skippedNonMwnf3 > 0) {
        this.logInfo(
          `Skipped ${skippedNonMwnf3} non-mwnf3/SH items (may be imported via other importers)`
        );
      }
      if (skippedNoItem > 0) {
        this.logInfo(`Skipped ${skippedNoItem} items not found in tracker`);
      }
      if (skippedNoTheme > 0) {
        this.logInfo(`Skipped ${skippedNoTheme} items with missing theme collection`);
      }

      // Batch attach items to theme collections
      if (!this.isDryRun && !this.isSampleOnlyMode) {
        this.logInfo(`Attaching items to ${collectionItems.size} theme collections...`);
        for (const [collectionId, itemIds] of collectionItems) {
          try {
            await this.context.strategy.attachItemsToCollection(collectionId, itemIds);
          } catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(
              `Failed to attach items to theme collection ${collectionId}: ${message}`
            );
            this.logError(`Theme collection ${collectionId}`, message);
          }
        }
      }

      this.showSummary(
        result.imported,
        result.skipped + skippedNonMwnf3 + skippedNoItem + skippedNoTheme,
        result.errors.length
      );
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgThemeItemImporter', message);
    }

    return result;
  }

  /**
   * Resolve item reference from theme_item to backward_compatibility string
   * Returns null if not a resolvable item (mwnf3 or SH)
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

    // Check SH (Sharing History) object reference
    // Format: mwnf3_sharing_history:sh_objects:PROJECT:COUNTRY:NUMBER
    if (
      legacy.sh_object_project_id &&
      legacy.sh_object_country_id &&
      legacy.sh_object_item_id !== null
    ) {
      return `mwnf3_sharing_history:sh_objects:${legacy.sh_object_project_id}:${legacy.sh_object_country_id}:${legacy.sh_object_item_id}`;
    }

    // Check SH monument reference
    // Format: mwnf3_sharing_history:sh_monuments:PROJECT:COUNTRY:NUMBER
    if (
      legacy.sh_monument_project_id &&
      legacy.sh_monument_country_id &&
      legacy.sh_monument_item_id !== null
    ) {
      return `mwnf3_sharing_history:sh_monuments:${legacy.sh_monument_project_id}:${legacy.sh_monument_country_id}:${legacy.sh_monument_item_id}`;
    }

    // Check SH monument detail reference
    // Format: mwnf3_sharing_history:sh_monument_details:PROJECT:COUNTRY:NUMBER:DETAIL
    if (
      legacy.sh_monument_detail_project_id &&
      legacy.sh_monument_detail_country_id &&
      legacy.sh_monument_detail_item_id !== null &&
      legacy.sh_monument_detail_detail_id !== null
    ) {
      return `mwnf3_sharing_history:sh_monument_details:${legacy.sh_monument_detail_project_id}:${legacy.sh_monument_detail_country_id}:${legacy.sh_monument_detail_item_id}:${legacy.sh_monument_detail_detail_id}`;
    }

    // Not a resolvable item (thg, explore, travel, etc.)
    return null;
  }
}
