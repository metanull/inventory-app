/**
 * Explore Monument Importer
 *
 * Creates Item records for each monument from the Explore database.
 * Monuments are placed in their respective location collections.
 *
 * Legacy schema:
 * - mwnf3_explore.exploremonument (monumentId, locationId, title, geoCoordinates, zoom, special_monument, related_monument)
 *
 * New schema:
 * - items (id, internal_name, backward_compatibility, latitude, longitude, map_zoom, ...)
 * - collection_item (collection_id, item_id) - linking items to location collections
 *
 * Mapping:
 * - monumentId → backward_compatibility (mwnf3_explore:monument:{monumentId})
 * - title → internal_name (slugified)
 * - geoCoordinates → latitude, longitude
 * - zoom → map_zoom
 * - locationId → collection link (via collection_item pivot)
 *
 * Dependencies:
 * - ExploreContextImporter
 * - ExploreLocationImporter (parent location collections must exist)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Convert a string to a URL-safe slug
 */
function slugify(text: string): string {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '')
    .substring(0, 100);
}

/**
 * Legacy monument structure
 */
interface LegacyMonument {
  monumentId: number;
  locationId: number | null;
  title: string;
  geoCoordinates: string | null;
  zoom: number | null;
  special_monument: string | null;
  related_monument: string | null;
}

/**
 * Parse legacy geoCoordinates format
 */
function parseGeoCoordinates(coords: string | null): [number | null, number | null] {
  if (!coords || !coords.trim()) {
    return [null, null];
  }
  const cleaned = coords.replace(/\s+/g, '').trim();
  const parts = cleaned.split(',');
  if (parts.length !== 2) {
    return [null, null];
  }
  const lat = parseFloat(parts[0]);
  const lon = parseFloat(parts[1]);
  if (isNaN(lat) || isNaN(lon)) {
    return [null, null];
  }
  return [lat, lon];
}

export class ExploreMonumentImporter extends BaseImporter {
  private exploreContextId: string | null = null;
  private locationCollectionCache: Map<number, string | null> = new Map();

  getName(): string {
    return 'ExploreMonumentImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Looking up Explore context...');

      // Get the Explore context ID
      const exploreContextBackwardCompat = 'mwnf3_explore:context';
      this.exploreContextId = await this.getEntityUuidAsync(
        exploreContextBackwardCompat,
        'context'
      );

      if (!this.exploreContextId) {
        throw new Error(
          `Explore context not found (${exploreContextBackwardCompat}). Run ExploreContextImporter first.`
        );
      }

      this.logInfo(`Found Explore context: ${this.exploreContextId}`);
      this.logInfo('Importing Explore monuments...');

      // Query monuments from legacy database
      const monuments = await this.context.legacyDb.query<LegacyMonument>(
        `SELECT monumentId, locationId, title, geoCoordinates, zoom, special_monument, related_monument
         FROM mwnf3_explore.exploremonument 
         WHERE title IS NOT NULL AND title != ''
         ORDER BY locationId, monumentId`
      );

      this.logInfo(`Found ${monuments.length} monuments to import`);

      for (const legacy of monuments) {
        try {
          const backwardCompat = `mwnf3_explore:monument:${legacy.monumentId}`;

          // Check if already exists
          if (await this.entityExistsAsync(backwardCompat, 'item')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Parse coordinates
          const [latitude, longitude] = parseGeoCoordinates(legacy.geoCoordinates);

          // Create internal name (with location context if available)
          const internalName = `explore_monument_${legacy.monumentId}_${slugify(legacy.title)}`;

          // Collect sample
          this.collectSample(
            'explore_monument',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create item: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'item');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write item using strategy
          const itemId = await this.context.strategy.writeItem({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            type: 'monument',
            collection_id: null,
            partner_id: null,
            country_id: null,
            project_id: null,
            parent_id: null,
            owner_reference: null,
            mwnf_reference: null,
            latitude,
            longitude,
            map_zoom: legacy.zoom ?? null,
          });

          this.registerEntity(itemId, backwardCompat, 'item');

          // Link item to location collection if available
          if (legacy.locationId) {
            const collectionId = await this.getLocationCollectionId(legacy.locationId);
            if (collectionId) {
              await this.context.strategy.writeCollectionItem({
                collection_id: collectionId,
                item_id: itemId,
                backward_compatibility: `${backwardCompat}:collection_link:${legacy.locationId}`,
                display_order: null,
              });
            }
          }

          result.imported++;
          this.showProgress();
        } catch (error) {
          result.success = false;
          const errorMessage = error instanceof Error ? error.message : String(error);
          result.errors.push(`Error importing monument ${legacy.monumentId}: ${errorMessage}`);
          this.logError('ExploreMonumentImporter', errorMessage, { monumentId: legacy.monumentId });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in monument import: ${errorMessage}`);
      this.logError('ExploreMonumentImporter', errorMessage);
      this.showError();
    }

    return result;
  }

  /**
   * Get location collection ID from cache or lookup
   */
  private async getLocationCollectionId(locationId: number): Promise<string | null> {
    if (this.locationCollectionCache.has(locationId)) {
      return this.locationCollectionCache.get(locationId) ?? null;
    }

    const backwardCompat = `mwnf3_explore:location:${locationId}`;
    const collectionId = await this.getEntityUuidAsync(backwardCompat, 'collection');

    this.locationCollectionCache.set(locationId, collectionId);

    return collectionId;
  }
}
