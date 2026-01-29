/**
 * Explore Location Importer
 *
 * Creates Collection records for each location (city/town) from the Explore database.
 * Locations are placed under their respective country collections.
 *
 * Legacy schema:
 * - mwnf3_explore.locations (locationId, countryId, label, geoCoordinates, zoom, path, ...)
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, backward_compatibility, ...)
 *
 * Mapping:
 * - locationId → backward_compatibility (mwnf3_explore:location:{locationId})
 * - label → internal_name (slugified), title (translation)
 * - geoCoordinates → latitude, longitude
 * - zoom → map_zoom
 * - countryId → parent_id (via country collection lookup)
 * - type = 'location'
 *
 * Dependencies:
 * - ExploreContextImporter
 * - ExploreCountryImporter (parent country collections must exist)
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
 * Legacy location structure
 */
interface LegacyLocation {
  locationId: number;
  countryId: string;
  label: string;
  geoCoordinates: string | null;
  zoom: number | null;
  path: string | null;
  how_to_reach: string | null;
  info: string | null;
  contact: string | null;
  description: string | null;
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

export class ExploreLocationImporter extends BaseImporter {
  private exploreContextId: string | null = null;
  private defaultLanguageId: string = 'eng';
  private countryCollectionCache: Map<string, string | null> = new Map();

  getName(): string {
    return 'ExploreLocationImporter';
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
      this.logInfo('Importing Explore locations...');

      // Query locations from legacy database
      const locations = await this.context.legacyDb.query<LegacyLocation>(
        `SELECT locationId, countryId, label, geoCoordinates, zoom, path, how_to_reach, info, contact, description
         FROM mwnf3_explore.locations 
         WHERE label IS NOT NULL AND label != ''
         ORDER BY countryId, locationId`
      );

      this.logInfo(`Found ${locations.length} locations to import`);

      for (const legacy of locations) {
        try {
          const backwardCompat = `mwnf3_explore:location:${legacy.locationId}`;

          // Check if already exists
          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get parent country collection
          const parentId = await this.getCountryCollectionId(legacy.countryId);

          // Parse coordinates
          const [latitude, longitude] = parseGeoCoordinates(legacy.geoCoordinates);

          // Create internal name - include locationId for uniqueness
          const internalName = `location_${legacy.locationId}_${legacy.countryId}_${slugify(legacy.label)}`;

          // Collect sample
          this.collectSample(
            'explore_location',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create collection: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'collection');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write collection using strategy
          const collectionId = await this.context.strategy.writeCollection({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            context_id: this.exploreContextId,
            language_id: this.defaultLanguageId,
            parent_id: parentId,
            type: 'location',
            latitude,
            longitude,
            map_zoom: legacy.zoom,
            country_id: null, // Country reference is via parent collection
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');

          // Build description from available fields
          const descriptionParts: string[] = [];
          if (legacy.description) descriptionParts.push(legacy.description);
          if (legacy.how_to_reach) descriptionParts.push(`How to reach: ${legacy.how_to_reach}`);
          if (legacy.info) descriptionParts.push(legacy.info);
          if (legacy.contact) descriptionParts.push(`Contact: ${legacy.contact}`);

          // Create translation
          const translationBackwardCompat = `${backwardCompat}:translation:${this.defaultLanguageId}`;

          await this.context.strategy.writeCollectionTranslation({
            collection_id: collectionId,
            language_id: this.defaultLanguageId,
            context_id: this.exploreContextId!,
            backward_compatibility: translationBackwardCompat,
            title: legacy.label,
            description: descriptionParts.length > 0 ? descriptionParts.join('\n\n') : '',
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          result.success = false;
          const errorMessage = error instanceof Error ? error.message : String(error);
          result.errors.push(`Error importing location ${legacy.locationId}: ${errorMessage}`);
          this.logError('ExploreLocationImporter', error, { locationId: legacy.locationId });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in location import: ${errorMessage}`);
      this.logError('ExploreLocationImporter', error);
      this.showError();
    }

    return result;
  }

  /**
   * Get country collection ID from cache or lookup
   */
  private async getCountryCollectionId(countryId: string): Promise<string | null> {
    if (this.countryCollectionCache.has(countryId)) {
      return this.countryCollectionCache.get(countryId) ?? null;
    }

    const backwardCompat = `mwnf3_explore:country:${countryId}`;
    const collectionId = await this.getEntityUuidAsync(backwardCompat, 'collection');

    this.countryCollectionCache.set(countryId, collectionId);

    if (!collectionId) {
      this.logInfo(`Country collection not found for ${countryId}, location will have no parent`);
    }

    return collectionId;
  }
}
