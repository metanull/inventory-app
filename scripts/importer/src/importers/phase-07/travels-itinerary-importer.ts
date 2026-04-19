/**
 * Travels Itinerary Importer
 *
 * Creates Collection records for each itinerary from the Travels database.
 * Itineraries are routes within a trail, like "Itinerary I - The Seat of the Sultanate".
 *
 * Legacy schema:
 * - mwnf3_travels.tr_itineraries (project_id, country, number, lang, trail_id, title, description, days)
 *   - Composite key: (project_id, country, trail_id, number) identifies unique itinerary
 *   - Multiple rows per itinerary (one per language)
 *   - trail_id references trails.number
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, ...)
 *
 * Mapping:
 * - (project_id, country, trail_id, number) → backward_compatibility
 * - title → internal_name (default language first, then first named translation)
 * - type = 'itinerary'
 * - parent_id = trail collection
 *
 * Dependencies:
 * - TravelsContextImporter
 * - TravelsTrailImporter (must run first)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';
import { selectItemInternalName } from '../../domain/transformers/item-internal-name-transformer.js';

/**
 * Legacy itinerary structure
 */
interface LegacyItinerary {
  project_id: string;
  country: string;
  number: string; // Roman numeral like 'I', 'II', etc.
  lang: string;
  trail_id: number;
  title: string;
  description: string | null;
  days: number | null;
}

export class TravelsItineraryImporter extends BaseImporter {
  private travelsContextId: string | null = null;
  private defaultLanguageId: string = 'eng';

  getName(): string {
    return 'TravelsItineraryImporter';
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

      this.logInfo('Importing itineraries...');

      // Query all itinerary rows and group by project/country/trail/number
      const itineraries = await this.context.legacyDb.query<LegacyItinerary>(
        `SELECT project_id, country, number, lang, trail_id, title, description, days
        FROM mwnf3_travels.tr_itineraries 
         ORDER BY project_id, country, trail_id, number, lang`
      );

      const groupedItineraries = new Map<string, LegacyItinerary[]>();
      for (const itinerary of itineraries) {
        const key = `${itinerary.project_id}:${itinerary.country}:${itinerary.trail_id}:${itinerary.number}`;
        const existingItineraries = groupedItineraries.get(key);
        if (existingItineraries) {
          existingItineraries.push(itinerary);
          continue;
        }

        groupedItineraries.set(key, [itinerary]);
      }

      this.logInfo(`Found ${groupedItineraries.size} itineraries to import`);

      for (const itineraryGroup of groupedItineraries.values()) {
        const legacy = itineraryGroup[0]!;
        try {
          const backwardCompat = `mwnf3_travels:itinerary:${legacy.project_id}:${legacy.country}:${legacy.trail_id}:${legacy.number}`;

          // Check if already exists
          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Find parent trail collection
          const trailBackwardCompat = `mwnf3_travels:trail:${legacy.project_id}:${legacy.country}:${legacy.trail_id}`;
          const trailId = await this.getEntityUuidAsync(trailBackwardCompat, 'collection');

          if (!trailId) {
            this.logWarning(`Parent trail not found for itinerary: ${backwardCompat}`, {
              trailBackwardCompat,
            });
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const internalNameCandidates = [];
          for (const translation of itineraryGroup) {
            internalNameCandidates.push({
              languageId: mapLanguageCode(translation.lang),
              value: translation.title,
            });
          }

          const selectedInternalName = selectItemInternalName(
            internalNameCandidates,
            this.defaultLanguageId,
            'Travels itinerary',
            backwardCompat
          );
          if (selectedInternalName.warning) {
            this.logWarning(selectedInternalName.warning);
          }

          // Collect sample
          this.collectSample(
            'itinerary',
            legacy as unknown as Record<string, unknown>,
            'success',
            `Itinerary ${legacy.project_id}/${legacy.country}/${legacy.trail_id}/${legacy.number}`
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create itinerary: ${selectedInternalName.internalName}`
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
            parent_id: trailId,
            type: 'itinerary',
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
            `Error importing itinerary ${legacy.project_id}/${legacy.country}/${legacy.trail_id}/${legacy.number}: ${errorMessage}`
          );
          this.logError('TravelsItineraryImporter', errorMessage, {
            project_id: legacy.project_id,
            country: legacy.country,
            trail_id: legacy.trail_id,
            number: legacy.number,
          });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in itinerary import: ${errorMessage}`);
      this.logError('TravelsItineraryImporter', errorMessage);
      this.showError();
    }

    return result;
  }
}
