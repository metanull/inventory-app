/**
 * Travels Trail Importer
 *
 * Creates Collection records for each trail (exhibition trail) from the Travels database.
 * Trails are top-level exhibition groupings like "IAM - Egypt" or "IAM - Spain".
 *
 * Legacy schema:
 * - mwnf3_travels.trails (project_id, country, lang, number, title, subtitle, description, ...)
 *   - Composite key: (project_id, country, number) identifies unique trail
 *   - Multiple rows per trail (one per language)
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, ...)
 *
 * Mapping:
 * - (project_id, country, number) → backward_compatibility (mwnf3_travels:trail:{project_id}:{country}:{number})
 * - title → used to create internal_name (slugified)
 * - type = 'exhibition trail'
 * - parent_id = travels root collection
 * - country_id = looked up from country code
 *
 * Dependencies:
 * - TravelsContextImporter (must run first)
 * - TravelsRootCollectionImporter (must run first)
 * - CountryImporter (for country lookup)
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
 * Legacy trail structure (one row per language)
 */
interface LegacyTrail {
  project_id: string;
  country: string;
  lang: string;
  number: number;
  title: string;
  subtitle: string | null;
  description: string | null;
  curated_by: string | null;
  local_coordinator: string | null;
  photo_by: string | null;
  museum_id: string | null;
  region_territory: string | null;
}

export class TravelsTrailImporter extends BaseImporter {
  private travelsContextId: string | null = null;
  private travelsRootId: string | null = null;
  private defaultLanguageId: string = 'eng';

  getName(): string {
    return 'TravelsTrailImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Looking up Travels context and root collection...');

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

      // Get the Travels root collection
      const travelsRootBackwardCompat = 'mwnf3_travels:root';
      this.travelsRootId = await this.getEntityUuidAsync(travelsRootBackwardCompat, 'collection');

      if (!this.travelsRootId) {
        throw new Error(
          `Travels root collection not found (${travelsRootBackwardCompat}). Run TravelsRootCollectionImporter first.`
        );
      }

      // Get default language ID
      this.defaultLanguageId = await this.getDefaultLanguageIdAsync();

      this.logInfo(`Found Travels context: ${this.travelsContextId}`);
      this.logInfo(`Found Travels root: ${this.travelsRootId}`);
      this.logInfo('Importing trails...');

      // Query unique trails from legacy database (use English for names, or first available)
      // Group by project_id, country, number to get unique trails
      const trails = await this.context.legacyDb.query<LegacyTrail>(
        `SELECT project_id, country, lang, number, title, subtitle, description, 
                curated_by, local_coordinator, photo_by, museum_id, region_territory
         FROM mwnf3_travels.trails 
         WHERE lang = 'en'
         ORDER BY project_id, country, number`
      );

      this.logInfo(`Found ${trails.length} trails to import`);

      for (const legacy of trails) {
        try {
          const backwardCompat = `mwnf3_travels:trail:${legacy.project_id}:${legacy.country}:${legacy.number}`;

          // Check if already exists
          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Look up country ID from legacy 2-letter code
          const countryId = await this.getEntityUuidAsync(legacy.country, 'country');

          // Create internal name from project_id, country, and title
          const internalName = `trail_${legacy.project_id}_${legacy.country}_${legacy.number}_${slugify(legacy.title || 'unnamed')}`;

          // Collect sample
          this.collectSample(
            'trail',
            legacy as unknown as Record<string, unknown>,
            'success',
            `Trail ${legacy.project_id}/${legacy.country}/${legacy.number}`
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create trail: ${internalName}`
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
            parent_id: this.travelsRootId,
            type: 'exhibition trail',
            latitude: null,
            longitude: null,
            map_zoom: null,
            country_id: countryId || null,
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');
          result.imported++;
          this.showProgress();
        } catch (error) {
          result.success = false;
          const errorMessage = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Error importing trail ${legacy.project_id}/${legacy.country}/${legacy.number}: ${errorMessage}`
          );
          this.logError('TravelsTrailImporter', errorMessage, {
            project_id: legacy.project_id,
            country: legacy.country,
            number: legacy.number,
          });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in trail import: ${errorMessage}`);
      this.logError('TravelsTrailImporter', errorMessage);
      this.showError();
    }

    return result;
  }
}
