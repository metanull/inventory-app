/**
 * Travels Location Importer
 *
 * Creates Collection records for each location from the Travels database.
 * Locations are places within an itinerary, like "Cairo" or "Alexandria".
 *
 * Legacy schema:
 * - mwnf3_travels.tr_locations (project_id, country, itinerary_id, number, lang, trail_id, title)
 *   - Composite key: (project_id, country, trail_id, itinerary_id, number)
 *   - Multiple rows per location (one per language)
 *   - itinerary_id references tr_itineraries.number (Roman numerals like 'I', 'II')
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, ...)
 *
 * Mapping:
 * - (project_id, country, trail_id, itinerary_id, number) → backward_compatibility
 * - title → internal_name (default language first, then first named translation)
 * - type = 'location'
 * - parent_id = itinerary collection
 *
 * Dependencies:
 * - TravelsContextImporter
 * - TravelsItineraryImporter (must run first)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';
import { selectItemInternalName } from '../../domain/transformers/item-internal-name-transformer.js';

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

      // Query all location rows and group by project/country/trail/itinerary/number
      const locations = await this.context.legacyDb.query<LegacyLocation>(
        `SELECT project_id, country, itinerary_id, number, lang, trail_id, title
        FROM mwnf3_travels.tr_locations 
         ORDER BY project_id, country, trail_id, itinerary_id, number, lang`
      );

      const groupedLocations = new Map<string, LegacyLocation[]>();
      for (const location of locations) {
        const key = `${location.project_id}:${location.country}:${location.trail_id}:${location.itinerary_id}:${location.number}`;
        const existingLocations = groupedLocations.get(key);
        if (existingLocations) {
          existingLocations.push(location);
          continue;
        }

        groupedLocations.set(key, [location]);
      }

      this.logInfo(`Found ${groupedLocations.size} locations to import`);

      for (const locationGroup of groupedLocations.values()) {
        const legacy = locationGroup[0]!;
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
            this.logWarning(`Parent itinerary not found for location: ${backwardCompat}`, {
              itineraryBackwardCompat,
            });
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const internalNameCandidates = [];
          for (const translation of locationGroup) {
            internalNameCandidates.push({
              languageId: mapLanguageCode(translation.lang),
              value: translation.title,
            });
          }

          const selectedInternalName = selectItemInternalName(
            internalNameCandidates,
            this.defaultLanguageId,
            'Travels location',
            backwardCompat
          );
          if (selectedInternalName.warning) {
            this.logWarning(selectedInternalName.warning);
          }

          // Collect sample
          this.collectSample(
            'location',
            legacy as unknown as Record<string, unknown>,
            'success',
            `Location ${legacy.project_id}/${legacy.country}/${legacy.trail_id}/${legacy.itinerary_id}/${legacy.number}`
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create location: ${selectedInternalName.internalName}`
            );
            this.registerEntity('', backwardCompat, 'collection');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write collection
          const collectionId = await this.context.strategy.writeCollection({
            internal_name: selectedInternalName.internalName,
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
          this.logError('TravelsLocationImporter', errorMessage, {
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
      this.logError('TravelsLocationImporter', errorMessage);
      this.showError();
    }

    return result;
  }
}
