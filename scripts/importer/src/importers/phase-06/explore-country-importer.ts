/**
 * Explore Country Importer
 *
 * Creates Collection records for each unique country in the Explore locations.
 * Countries serve as containers under "Explore by Country".
 *
 * Legacy schema:
 * - mwnf3_explore.locations (countryId references existing countries)
 * - mwnf3_explore.explorecountry (countryId, showOnLocation, showOnMonument)
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, backward_compatibility, country_id)
 *
 * Mapping:
 * - countryId → backward_compatibility (mwnf3_explore:country:{countryId})
 * - countryId → country_id (FK to countries table)
 * - type = 'collection'
 * - parent_id = explore_by_country root collection
 *
 * Dependencies:
 * - ExploreContextImporter
 * - ExploreRootCollectionsImporter
 * - CountryImporter (countries must exist)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Country info from locations
 */
interface LegacyExploreCountry {
  countryId: string;
}

export class ExploreCountryImporter extends BaseImporter {
  private exploreContextId: string | null = null;
  private exploreByCountryId: string | null = null;
  private defaultLanguageId: string = 'eng';

  getName(): string {
    return 'ExploreCountryImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Looking up Explore context and root collection...');

      // Get the Explore context ID
      const exploreContextBackwardCompat = 'mwnf3_explore:context';
      this.exploreContextId = await this.getEntityUuidAsync(exploreContextBackwardCompat, 'context');

      if (!this.exploreContextId) {
        throw new Error(
          `Explore context not found (${exploreContextBackwardCompat}). Run ExploreContextImporter first.`
        );
      }

      // Get the "Explore by Country" root collection
      const exploreByCountryBackwardCompat = 'mwnf3_explore:root:explore_by_country';
      this.exploreByCountryId = await this.getEntityUuidAsync(
        exploreByCountryBackwardCompat,
        'collection'
      );

      if (!this.exploreByCountryId) {
        throw new Error(
          `Explore by Country collection not found (${exploreByCountryBackwardCompat}). Run ExploreRootCollectionsImporter first.`
        );
      }

      this.logInfo(`Found Explore context: ${this.exploreContextId}`);
      this.logInfo(`Found Explore by Country: ${this.exploreByCountryId}`);
      this.logInfo('Importing countries from Explore locations...');

      // Get distinct countries from locations table
      const countries = await this.context.legacyDb.query<LegacyExploreCountry>(
        `SELECT DISTINCT countryId FROM mwnf3_explore.locations WHERE countryId IS NOT NULL AND countryId != '' ORDER BY countryId`
      );

      this.logInfo(`Found ${countries.length} unique countries in Explore locations`);

      for (const legacy of countries) {
        try {
          const backwardCompat = `mwnf3_explore:country:${legacy.countryId}`;

          // Check if already exists
          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Map country ID (legacy uses 2-letter, our system uses 3-letter ISO codes)
          // We need to look up the country in our system
          const countryId = await this.mapCountryId(legacy.countryId);

          const internalName = `country_${legacy.countryId}`;

          // Collect sample
          this.collectSample('explore_country', legacy as unknown as Record<string, unknown>, 'success');

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
            parent_id: this.exploreByCountryId,
            type: 'collection',
            latitude: null,
            longitude: null,
            map_zoom: null,
            country_id: countryId,
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');

          // Create translation - use country name if available
          const countryName = await this.getCountryName(legacy.countryId);
          const translationBackwardCompat = `${backwardCompat}:translation:${this.defaultLanguageId}`;

          await this.context.strategy.writeCollectionTranslation({
            collection_id: collectionId,
            language_id: this.defaultLanguageId,
            context_id: this.exploreContextId,
            backward_compatibility: translationBackwardCompat,
            title: countryName || legacy.countryId.toUpperCase(),
            description: null,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          result.success = false;
          const errorMessage = error instanceof Error ? error.message : String(error);
          result.errors.push(`Error importing country ${legacy.countryId}: ${errorMessage}`);
          this.logError('ExploreCountryImporter', error, { countryId: legacy.countryId });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in country import: ${errorMessage}`);
      this.logError('ExploreCountryImporter', error);
      this.showError();
    }

    return result;
  }

  /**
   * Map 2-letter country code to 3-letter ISO code
   */
  private async mapCountryId(twoLetterCode: string): Promise<string | null> {
    // Common mappings from 2-letter to 3-letter ISO codes
    const mapping: Record<string, string> = {
      at: 'aut', // Austria
      cz: 'cze', // Czech Republic
      de: 'deu', // Germany
      eg: 'egy', // Egypt
      es: 'esp', // Spain
      hu: 'hun', // Hungary
      it: 'ita', // Italy
      jo: 'jor', // Jordan
      ma: 'mar', // Morocco
      pa: 'pse', // Palestine
      pl: 'pol', // Poland
      pt: 'prt', // Portugal
      sk: 'svk', // Slovakia
      sy: 'syr', // Syria
      tn: 'tun', // Tunisia
      tr: 'tur', // Turkey
    };

    const threeLetterCode = mapping[twoLetterCode.toLowerCase()];
    if (!threeLetterCode) {
      this.logInfo(`Unknown country code mapping: ${twoLetterCode}`);
      return null;
    }

    // Verify the country exists in our system
    const exists = await this.entityExistsAsync(`mwnf3:langs:${threeLetterCode}`, 'country');
    if (!exists) {
      // Try direct lookup
      const countryExists = await this.entityExistsAsync(`mwnf3:countries:${threeLetterCode}`, 'country');
      if (!countryExists) {
        this.logInfo(`Country ${threeLetterCode} not found in system`);
        return null;
      }
    }

    return threeLetterCode;
  }

  /**
   * Get country name from legacy database
   */
  private async getCountryName(countryId: string): Promise<string | null> {
    try {
      const result = await this.context.legacyDb.query<{ name: string }>(
        `SELECT name FROM mwnf3.countries WHERE id = ? LIMIT 1`,
        [countryId]
      );
      return result.length > 0 ? result[0].name : null;
    } catch {
      return null;
    }
  }
}
