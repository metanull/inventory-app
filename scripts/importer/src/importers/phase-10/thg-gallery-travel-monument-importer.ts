/**
 * THG Gallery Travel Monument Importer
 *
 * Imports thg_gallery_travel_monuments entries, linking travel monuments to THG gallery collections.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.thg_gallery_travel_monuments (gallery_id, project_id, country_id, trail_id, itinerary_id, location_id, item_id)
 *
 * New schema:
 * - collection_item pivot (collection_id, item_id) via attachItemsToCollection
 *
 * Travel monument backward_compatibility format: mwnf3_travels:monument:{project_id}:{country}:{trail_id}:{itinerary_id}:{location_id}:{number}
 *
 * Dependencies:
 * - Phase-07 TravelsMonumentImporter (must run first to create travel monument items)
 * - ThgGalleryImporter (must run first to create gallery collections)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy thg_gallery_travel_monuments structure
 */
interface LegacyThgGalleryTravelMonument {
  gallery_id: number;
  project_id: string;
  country_id: string;
  trail_id: number;
  itinerary_id: string;
  location_id: string;
  item_id: string; // This is the monument number within the location
}

export class ThgGalleryTravelMonumentImporter extends BaseImporter {
  getName(): string {
    return 'ThgGalleryTravelMonumentImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing THG gallery -> travel monument associations...');

      // Query thg_gallery_travel_monuments entries from legacy database
      let galleryMonuments: LegacyThgGalleryTravelMonument[];
      try {
        galleryMonuments = await this.context.legacyDb.query<LegacyThgGalleryTravelMonument>(
          `SELECT gallery_id, project_id, country_id, trail_id, itinerary_id, location_id, item_id
           FROM mwnf3_thematic_gallery.thg_gallery_travel_monuments
           ORDER BY gallery_id, project_id, country_id, trail_id, itinerary_id, location_id, item_id`
        );
      } catch (queryError) {
        const message = queryError instanceof Error ? queryError.message : String(queryError);
        if (message.includes("doesn't exist") || message.includes('Unknown column')) {
          this.logInfo(
            `⚠️ Skipping: Legacy thg_gallery_travel_monuments table not available (${message})`
          );
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Legacy thg_gallery_travel_monuments table not available: ${message}`
          );
          return result;
        }
        throw queryError;
      }

      this.logInfo(
        `Found ${galleryMonuments.length} gallery-travel monument associations to process`
      );

      // Group items by collection for efficient batch attachment
      const collectionItems: Map<string, string[]> = new Map();
      let skippedNoItem = 0;
      let skippedNoCollection = 0;

      for (const legacy of galleryMonuments) {
        try {
          // Build backward_compatibility for the travel monument
          // Format: mwnf3_travels:monument:{project_id}:{country}:{trail_id}:{itinerary_id}:{location_id}:{number}
          const itemBackwardCompat = `mwnf3_travels:monument:${legacy.project_id}:${legacy.country_id}:${legacy.trail_id}:${legacy.itinerary_id}:${legacy.location_id}:${legacy.item_id}`;

          // Get the item ID from tracker or database (items are from phase-07)
          const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');
          if (!itemId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: travel monument not found (${itemBackwardCompat})`
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
            'thg_gallery_travel_monument',
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
            `Gallery ${legacy.gallery_id} travel monument ${legacy.project_id}:${legacy.country_id}:${legacy.trail_id}:${legacy.itinerary_id}:${legacy.location_id}:${legacy.item_id}: ${message}`
          );
          this.logError(`Gallery ${legacy.gallery_id} travel monument`, error);
          this.showError();
        }
      }

      // Log skipped statistics
      if (skippedNoItem > 0) {
        this.logInfo(`Skipped ${skippedNoItem} travel monuments not found in tracker/database`);
      }
      if (skippedNoCollection > 0) {
        this.logInfo(`Skipped ${skippedNoCollection} travel monuments with missing collection`);
      }

      // Batch attach items to collections
      if (!this.isDryRun && !this.isSampleOnlyMode) {
        this.logInfo(`Attaching travel monuments to ${collectionItems.size} collections...`);
        for (const [collectionId, itemIds] of collectionItems) {
          try {
            await this.context.strategy.attachItemsToCollection(collectionId, itemIds);
          } catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(
              `Failed to attach travel items to collection ${collectionId}: ${message}`
            );
            this.logError(`Collection ${collectionId}`, error);
          }
        }
      }

      this.showSummary(result.imported, skippedNoItem + skippedNoCollection, result.errors.length);
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in THG gallery travel monument import: ${errorMessage}`);
      this.logError('ThgGalleryTravelMonumentImporter', error);
      this.showError();
    }

    return result;
  }
}
