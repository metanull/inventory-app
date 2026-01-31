/**
 * Travels Itinerary Importer
 *
 * Creates Collection records for each itinerary from the Travels database.
 * Itineraries are routes within a trail, like "Itinerary I - The Seat of the Sultanate".
 *
 * Legacy schema:
 * - mwnf3.tr_itineraries (project_id, country, number, lang, trail_id, title, description, days)
 *   - Composite key: (project_id, country, trail_id, number) identifies unique itinerary
 *   - Multiple rows per itinerary (one per language)
 *   - trail_id references trails.number
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, ...)
 *
 * Mapping:
 * - (project_id, country, trail_id, number) → backward_compatibility
 * - title → used to create internal_name (slugified)
 * - type = 'itinerary'
 * - parent_id = trail collection
 *
 * Dependencies:
 * - TravelsContextImporter
 * - TravelsTrailImporter (must run first)
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

      // Query unique itineraries from legacy database (use English for names)
      const itineraries = await this.context.legacyDb.query<LegacyItinerary>(
        `SELECT project_id, country, number, lang, trail_id, title, description, days
         FROM mwnf3.tr_itineraries 
         WHERE lang = 'en'
         ORDER BY project_id, country, trail_id, number`
      );

      this.logInfo(`Found ${itineraries.length} itineraries to import`);

      for (const legacy of itineraries) {
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
            this.logWarning(
              `Parent trail not found for itinerary: ${backwardCompat}`,
              { trailBackwardCompat }
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Create internal name
          const internalName = `itin_${legacy.project_id}_${legacy.country}_${legacy.trail_id}_${legacy.number}_${slugify(legacy.title || 'unnamed')}`;

          // Collect sample
          this.collectSample(
            'itinerary',
            legacy as unknown as Record<string, unknown>,
            'success',
            `Itinerary ${legacy.project_id}/${legacy.country}/${legacy.trail_id}/${legacy.number}`
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create itinerary: ${internalName}`
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
          this.logError('TravelsItineraryImporter', error, {
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
      this.logError('TravelsItineraryImporter', error);
      this.showError();
    }

    return result;
  }
}
