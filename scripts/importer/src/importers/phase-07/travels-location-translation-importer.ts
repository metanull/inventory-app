/**
 * Travels Location Translation Importer
 *
 * Creates CollectionTranslation records for each location in all available languages.
 *
 * Legacy schema:
 * - mwnf3.tr_locations (project_id, country, itinerary_id, number, lang, trail_id, title)
 *   - One row per language
 *
 * New schema:
 * - collection_translations (collection_id, language_id, context_id, title, description, ...)
 *
 * Dependencies:
 * - TravelsContextImporter
 * - TravelsLocationImporter (must run first)
 * - LanguageImporter (for language lookup)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy location translation structure
 */
interface LegacyLocationTranslation {
  project_id: string;
  country: string;
  itinerary_id: string;
  number: number;
  lang: string;
  trail_id: number;
  title: string;
}

export class TravelsLocationTranslationImporter extends BaseImporter {
  private travelsContextId: string | null = null;

  getName(): string {
    return 'TravelsLocationTranslationImporter';
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

      this.logInfo('Importing location translations...');

      // Query all location translations
      const translations = await this.context.legacyDb.query<LegacyLocationTranslation>(
        `SELECT project_id, country, itinerary_id, number, lang, trail_id, title
         FROM mwnf3.tr_locations 
         ORDER BY project_id, country, trail_id, itinerary_id, number, lang`
      );

      this.logInfo(`Found ${translations.length} location translations to import`);

      for (const legacy of translations) {
        try {
          const locationBackwardCompat = `mwnf3_travels:location:${legacy.project_id}:${legacy.country}:${legacy.trail_id}:${legacy.itinerary_id}:${legacy.number}`;
          const translationBackwardCompat = `${locationBackwardCompat}:translation:${legacy.lang}`;

          // Get parent location collection ID
          const locationId = await this.getEntityUuidAsync(locationBackwardCompat, 'collection');
          if (!locationId) {
            this.logWarning(`Location not found for translation: ${locationBackwardCompat}`, {
              lang: legacy.lang,
            });
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get language ID from legacy code
          const languageId = await this.getLanguageIdByLegacyCodeAsync(legacy.lang);
          if (!languageId) {
            this.logWarning(`Language not found: ${legacy.lang}`, { locationBackwardCompat });
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Check if translation already exists
          const existsCheck = await this.context.strategy.findByBackwardCompatibility(
            'collection_translations',
            translationBackwardCompat
          );
          if (existsCheck) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Collect sample
          this.collectSample(
            'location_translation',
            legacy as unknown as Record<string, unknown>,
            'success',
            `Location translation ${legacy.project_id}/${legacy.country}/${legacy.trail_id}/${legacy.itinerary_id}/${legacy.number}:${legacy.lang}`,
            legacy.lang
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create location translation: ${translationBackwardCompat}`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write translation
          // Note: Locations only have title, no description in legacy
          await this.context.strategy.writeCollectionTranslation({
            collection_id: locationId,
            language_id: languageId,
            context_id: this.travelsContextId!,
            backward_compatibility: translationBackwardCompat,
            title: legacy.title || '',
            description: null,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          result.success = false;
          const errorMessage = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Error importing location translation ${legacy.project_id}/${legacy.country}/${legacy.trail_id}/${legacy.itinerary_id}/${legacy.number}:${legacy.lang}: ${errorMessage}`
          );
          this.logError('TravelsLocationTranslationImporter', error, {
            project_id: legacy.project_id,
            country: legacy.country,
            trail_id: legacy.trail_id,
            itinerary_id: legacy.itinerary_id,
            number: legacy.number,
            lang: legacy.lang,
          });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in location translation import: ${errorMessage}`);
      this.logError('TravelsLocationTranslationImporter', error);
      this.showError();
    }

    return result;
  }
}
