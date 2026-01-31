/**
 * Travels Location Importer
 *
 * Creates Collection records for each location from the Travels database.
 * Locations are places within an itinerary, like "Cairo" or "Alexandria".
 *
 * Legacy schema:
 * - mwnf3.tr_locations (project_id, country, itinerary_id, number, lang, trail_id, title)
 *   - Composite key: (project_id, country, trail_id, itinerary_id, number)
 *   - Multiple rows per location (one per language)
 *   - itinerary_id references tr_itineraries.number (Roman numerals like 'I', 'II')
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, ...)
 *
 * Mapping:
 * - (project_id, country, trail_id, itinerary_id, number) → backward_compatibility
 * - title → used to create internal_name (slugified)
 * - type = 'location'
 * - parent_id = itinerary collection
 *
 * Dependencies:
 * - TravelsContextImporter
 * - TravelsItineraryImporter (must run first)
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
  project_id: string;
  country: string;
  itinerary_id: string; // Roman numeral like 'I', 'II', etc.
  number: number;
  lang: string;
  trail_id: number;
  title: string;
}

export class TravelsLocationImporter extends BaseImporter {
  private travelsContextId: string | null = null;
  private defaultLanguageId: string = 'eng';

  getName(): string {
    return 'TravelsLocationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Looking up Travels context...');

      // Get the Travels context ID
      const travelsContextBackwardCompat = 'mwnf3_travels:context';
      this.travelsContextId = await this.getEntityUuidAsync(
        travelsContextBackwardCompat,
        'context'
      );

      if (!this.travelsContextId) {
        throw new Error(
          `Travels context not found (${travelsContextBackwardCompat}). Run TravelsContextImporter first.`
        );
      }

      // Get default language ID
      this.defaultLanguageId = await this.getDefaultLanguageIdAsync();

      this.logInfo('Importing locations...');

      // Query unique locations from legacy database (use English for names)
      const locations = await this.context.legacyDb.query<LegacyLocation>(
        `SELECT project_id, country, itinerary_id, number, lang, trail_id, title
         FROM mwnf3.tr_locations 
         WHERE lang = 'en'
         ORDER BY project_id, country, trail_id, itinerary_id, number`
      );

      this.logInfo(`Found ${locations.length} locations to import`);

      for (const legacy of locations) {
        try {
          const backwardCompat = `mwnf3_travels:location:${legacy.project_id}:${legacy.country}:${legacy.trail_id}:${legacy.itinerary_id}:${legacy.number}`;

          // Check if already exists
          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Find parent itinerary collection
          const itineraryBackwardCompat = `mwnf3_travels:itinerary:${legacy.project_id}:${legacy.country}:${legacy.trail_id}:${legacy.itinerary_id}`;
          const itineraryId = await this.getEntityUuidAsync(itineraryBackwardCompat, 'collection');

          if (!itineraryId) {
            this.logWarning(
              `Parent itinerary not found for location: ${backwardCompat}`,
              { itineraryBackwardCompat }
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Create internal name
          const internalName = `loc_${legacy.project_id}_${legacy.country}_${legacy.trail_id}_${legacy.itinerary_id}_${legacy.number}_${slugify(legacy.title || 'unnamed')}`;

          // Collect sample
          this.collectSample(
            'location',
            legacy as unknown as Record<string, unknown>,
            'success',
            `Location ${legacy.project_id}/${legacy.country}/${legacy.trail_id}/${legacy.itinerary_id}/${legacy.number}`
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create location: ${internalName}`
            );
            this.registerEntity('', backwardCompat, 'collection');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write collection
          const collectionId = await this.context.strategy.writeCollection({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            context_id: this.travelsContextId,
            language_id: this.defaultLanguageId,
            parent_id: itineraryId,
            type: 'location',
            latitude: null,
            longitude: null,
            map_zoom: null,
            country_id: null,
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');
          result.imported++;
          this.showProgress();
        } catch (error) {
          result.success = false;
          const errorMessage = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Error importing location ${legacy.project_id}/${legacy.country}/${legacy.trail_id}/${legacy.itinerary_id}/${legacy.number}: ${errorMessage}`
          );
          this.logError('TravelsLocationImporter', error, {
            project_id: legacy.project_id,
            country: legacy.country,
            trail_id: legacy.trail_id,
            itinerary_id: legacy.itinerary_id,
            number: legacy.number,
          });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in location import: ${errorMessage}`);
      this.logError('TravelsLocationImporter', error);
      this.showError();
    }

    return result;
  }
}
