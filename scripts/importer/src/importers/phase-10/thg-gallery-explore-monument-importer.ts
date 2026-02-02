/**
 * THG Gallery Explore Monument Importer
 *
 * Imports thg_gallery_explore_monuments entries, linking Explore monuments to THG gallery collections.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.thg_gallery_explore_monuments (gallery_id, item_id)
 *   - item_id references explore.items.id (numeric ID in legacy Explore database)
 *
 * New schema:
 * - collection_item pivot (collection_id, item_id) via attachItemsToCollection
 *
 * Item backward_compatibility format: mwnf3_explore:monument:{explore_item_id}
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy thg_gallery_explore_monuments structure
 */
interface LegacyThgGalleryExploreMonument {
  gallery_id: number;
  item_id: number; // explore.items.id
}

export class ThgGalleryExploreMonumentImporter extends BaseImporter {
  getName(): string {
    return 'ThgGalleryExploreMonumentImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing THG gallery -> Explore monument associations...');

      // Query thg_gallery_explore_monuments entries from legacy database
      let galleryMonuments: LegacyThgGalleryExploreMonument[];
      try {
        galleryMonuments = await this.context.legacyDb.query<LegacyThgGalleryExploreMonument>(
          `SELECT gallery_id, item_id
           FROM mwnf3_thematic_gallery.thg_gallery_explore_monuments
           ORDER BY gallery_id, item_id`
        );
      } catch (queryError) {
        const message = queryError instanceof Error ? queryError.message : String(queryError);
        if (message.includes("doesn't exist") || message.includes('Unknown column')) {
          this.logInfo(
            `⚠️ Skipping: Legacy thg_gallery_explore_monuments table not available (${message})`
          );
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Legacy thg_gallery_explore_monuments table not available: ${message}`
          );
          return result;
        }
        throw queryError;
      }

      this.logInfo(
        `Found ${galleryMonuments.length} gallery-Explore monument associations to process`
      );

      // Group items by collection for efficient batch attachment
      const collectionItems: Map<string, string[]> = new Map();
      let skippedNoItem = 0;
      let skippedNoCollection = 0;

      for (const legacy of galleryMonuments) {
        try {
          // Build backward_compatibility for the Explore monument
          // Format matches Phase 6 Explore monument importer: mwnf3_explore:monument:{item_id}
          const itemBackwardCompat = `mwnf3_explore:monument:${legacy.item_id}`;

          // Get the item ID from tracker or database (items are from earlier phases)
          const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');
          if (!itemId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: Explore monument not found (${itemBackwardCompat})`
            );
            skippedNoItem++;
            continue;
          }

          // Get the collection ID for this gallery (Phase 10 internal)
          const galleryBackwardCompat = `mwnf3_thematic_gallery:thg_gallery:${legacy.gallery_id}`;
          const collectionId = await this.getEntityUuidAsync(galleryBackwardCompat, 'collection');
          if (!collectionId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: Collection not found (${galleryBackwardCompat})`
            );
            skippedNoCollection++;
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
            'thg_gallery_explore_monument',
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
            `Gallery ${legacy.gallery_id} Explore monument ${legacy.item_id}: ${message}`
          );
          this.logError(`Gallery ${legacy.gallery_id} Explore monument`, message);
          this.showError();
        }
      }

      // Log skipped statistics
      if (skippedNoItem > 0) {
        this.logInfo(`Skipped ${skippedNoItem} Explore monuments not found in tracker/database`);
      }
      if (skippedNoCollection > 0) {
        this.logInfo(`Skipped ${skippedNoCollection} Explore monuments with missing collection`);
      }

      // Batch attach items to collections
      if (!this.isDryRun && !this.isSampleOnlyMode) {
        this.logInfo(`Attaching Explore monuments to ${collectionItems.size} collections...`);
        for (const [collectionId, itemIds] of collectionItems) {
          try {
            await this.context.strategy.attachItemsToCollection(collectionId, itemIds);
          } catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(`Failed to attach items to collection ${collectionId}: ${message}`);
            this.logError(`Collection ${collectionId}`, message);
          }
        }
      }

      this.showSummary(
        result.imported,
        result.skipped + skippedNoItem + skippedNoCollection,
        result.errors.length
      );
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgGalleryExploreMonumentImporter', message);
    }

    return result;
  }
}
