/**
 * THG Gallery MWNF3 Object Importer
 *
 * Imports thg_gallery_mwnf3_objects entries, linking mwnf3 objects to THG gallery collections.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.thg_gallery_mwnf3_objects (gallery_id, objects_project_id, objects_country, objects_museum_id, objects_number)
 *
 * New schema:
 * - collection_item pivot (collection_id, item_id) via attachItemsToCollection
 *
 * Item backward_compatibility format: mwnf3:objects:{project}:{country}:{museum}:{number}
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy thg_gallery_mwnf3_objects structure
 */
interface LegacyThgGalleryMwnf3Object {
  gallery_id: number;
  objects_project_id: string;
  objects_country: string;
  objects_museum_id: string;
  objects_number: number;
}

export class ThgGalleryMwnf3ObjectImporter extends BaseImporter {
  getName(): string {
    return 'ThgGalleryMwnf3ObjectImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing THG gallery -> mwnf3 object associations...');

      // Query thg_gallery_mwnf3_objects entries from legacy database
      let galleryObjects: LegacyThgGalleryMwnf3Object[];
      try {
        galleryObjects = await this.context.legacyDb.query<LegacyThgGalleryMwnf3Object>(
          `SELECT gallery_id, objects_project_id, objects_country, objects_museum_id, objects_number
           FROM mwnf3_thematic_gallery.thg_gallery_mwnf3_objects
           ORDER BY gallery_id, objects_project_id, objects_country, objects_museum_id, objects_number`
        );
      } catch (queryError) {
        const message = queryError instanceof Error ? queryError.message : String(queryError);
        if (message.includes("doesn't exist") || message.includes('Unknown column')) {
          this.logInfo(
            `⚠️ Skipping: Legacy thg_gallery_mwnf3_objects table not available (${message})`
          );
          result.warnings = result.warnings || [];
          result.warnings.push(`Legacy thg_gallery_mwnf3_objects table not available: ${message}`);
          return result;
        }
        throw queryError;
      }

      this.logInfo(`Found ${galleryObjects.length} gallery-object associations to process`);

      // Group items by collection for efficient batch attachment
      const collectionItems: Map<string, string[]> = new Map();
      let skippedNoItem = 0;
      let skippedNoCollection = 0;

      for (const legacy of galleryObjects) {
        try {
          // Build backward_compatibility for the mwnf3 object
          const itemBackwardCompat = `mwnf3:objects:${legacy.objects_project_id}:${legacy.objects_country}:${legacy.objects_museum_id}:${legacy.objects_number}`;

          // Get the item ID from tracker or database (items are from earlier phases)
          const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');
          if (!itemId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: mwnf3 object not found (${itemBackwardCompat})`
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
            'thg_gallery_mwnf3_object',
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
            `Gallery ${legacy.gallery_id} object ${legacy.objects_project_id}:${legacy.objects_country}:${legacy.objects_museum_id}:${legacy.objects_number}: ${message}`
          );
          this.logError(`Gallery ${legacy.gallery_id} mwnf3 object`, message);
          this.showError();
        }
      }

      // Log skipped statistics
      if (skippedNoItem > 0) {
        this.logInfo(`Skipped ${skippedNoItem} items not found in tracker/database`);
      }
      if (skippedNoCollection > 0) {
        this.logInfo(`Skipped ${skippedNoCollection} items with missing collection`);
      }

      // Batch attach items to collections
      if (!this.isDryRun && !this.isSampleOnlyMode) {
        this.logInfo(`Attaching mwnf3 objects to ${collectionItems.size} collections...`);
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
      this.logError('ThgGalleryMwnf3ObjectImporter', message);
    }

    return result;
  }
}
