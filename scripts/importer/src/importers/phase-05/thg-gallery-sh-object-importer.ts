/**
 * THG Gallery SH Object Importer
 *
 * Imports thg_gallery_sh_objects entries, linking Sharing History objects to THG gallery collections.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.thg_gallery_sh_objects (gallery_id, sh_objects_project_id, sh_objects_country, sh_objects_number)
 *
 * New schema:
 * - collection_item pivot (collection_id, item_id) via attachItemsToCollection
 *
 * Item backward_compatibility format: mwnf3_sharing_history:sh_objects:{project}:{country}:{number}
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy thg_gallery_sh_objects structure
 */
interface LegacyThgGalleryShObject {
  gallery_id: number;
  sh_objects_project_id: string;
  sh_objects_country: string;
  sh_objects_number: number;
}

export class ThgGalleryShObjectImporter extends BaseImporter {
  getName(): string {
    return 'ThgGalleryShObjectImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing THG gallery -> SH object associations...');

      // Query thg_gallery_sh_objects entries from legacy database
      let galleryObjects: LegacyThgGalleryShObject[];
      try {
        galleryObjects = await this.context.legacyDb.query<LegacyThgGalleryShObject>(
          `SELECT gallery_id, sh_objects_project_id, sh_objects_country, sh_objects_number
           FROM mwnf3_thematic_gallery.thg_gallery_sh_objects
           ORDER BY gallery_id, sh_objects_project_id, sh_objects_country, sh_objects_number`
        );
      } catch (queryError) {
        const message = queryError instanceof Error ? queryError.message : String(queryError);
        if (message.includes("doesn't exist") || message.includes('Unknown column')) {
          this.logInfo(`⚠️ Skipping: Legacy thg_gallery_sh_objects table not available (${message})`);
          result.warnings = result.warnings || [];
          result.warnings.push(`Legacy thg_gallery_sh_objects table not available: ${message}`);
          return result;
        }
        throw queryError;
      }

      this.logInfo(`Found ${galleryObjects.length} gallery-SH object associations to process`);

      // Group items by collection for efficient batch attachment
      const collectionItems: Map<string, string[]> = new Map();
      let skippedNoItem = 0;
      let skippedNoCollection = 0;

      for (const legacy of galleryObjects) {
        try {
          // Build backward_compatibility for the SH object (normalized to lowercase)
          // Format matches Phase 3 SH object importer: mwnf3_sharing_history:sh_objects:{project}:{country}:{number}
          const itemBackwardCompat = `mwnf3_sharing_history:sh_objects:${legacy.sh_objects_project_id.toLowerCase()}:${legacy.sh_objects_country.toLowerCase()}:${legacy.sh_objects_number}`;

          // Get the item ID from tracker or database (items are from Phase 3)
          const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');
          if (!itemId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: SH object not found (${itemBackwardCompat})`
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
            'thg_gallery_sh_object',
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
            `Gallery ${legacy.gallery_id} SH object ${legacy.sh_objects_project_id}:${legacy.sh_objects_country}:${legacy.sh_objects_number}: ${message}`
          );
          this.logError(
            `Gallery ${legacy.gallery_id} SH object`,
            error
          );
          this.showError();
        }
      }

      // Log skipped statistics
      if (skippedNoItem > 0) {
        this.logInfo(`Skipped ${skippedNoItem} SH objects not found in tracker/database`);
      }
      if (skippedNoCollection > 0) {
        this.logInfo(`Skipped ${skippedNoCollection} SH objects with missing collection`);
      }

      // Batch attach items to collections
      if (!this.isDryRun && !this.isSampleOnlyMode) {
        this.logInfo(`Attaching SH objects to ${collectionItems.size} collections...`);
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

      this.showSummary(
        result.imported,
        result.skipped + skippedNoItem + skippedNoCollection,
        result.errors.length
      );
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgGalleryShObjectImporter', error);
    }

    return result;
  }
}
