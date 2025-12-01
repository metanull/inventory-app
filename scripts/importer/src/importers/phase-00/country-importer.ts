/**
 * Country Importer
 *
 * Imports countries from the production JSON file in database/seeders/data/countries.json.
 * This is the same data source used by the Laravel seeder and the legacy SQL importer.
 */

import { readFileSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

// Get the directory of the current module for robust path resolution
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

/**
 * Country data structure from the JSON file
 */
interface CountryJsonData {
  id: string; // ISO 3166-1 alpha-3 code (e.g., 'usa', 'fra')
  internal_name: string;
  backward_compatibility: string; // ISO 3166-1 alpha-2 code (e.g., 'us', 'fr')
}

export class CountryImporter extends BaseImporter {
  getName(): string {
    return 'CountryImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Loading countries from production JSON file...');

      // Load countries from production JSON file (same as Laravel seeder)
      // Path is from scripts/importer/src/importers/phase-00 to database/seeders/data
      const countriesPath = join(__dirname, '../../../../../database/seeders/data/countries.json');
      const fileContent = readFileSync(countriesPath, 'utf-8');
      const countries = JSON.parse(fileContent) as CountryJsonData[];

      this.logInfo(`Found ${countries.length} countries to import`);

      for (const country of countries) {
        try {
          // Use backward_compatibility as the tracking key (consistent with legacy importer)
          const backwardCompat = country.backward_compatibility;

          // Check if already exists in tracker (pass entityType to avoid collisions with languages)
          if (this.entityExists(backwardCompat, 'country')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Collect sample for foundation data
          this.collectSample('country', country as unknown as Record<string, unknown>, 'foundation');

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import country: ${country.id} (${country.internal_name})`
            );
            // Register for tracking even in dry-run
            this.registerEntity(country.id, backwardCompat, 'country');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write country using strategy
          // Note: Only pass fields that exist in the database table
          // The countries table has: id, internal_name, backward_compatibility
          await this.context.strategy.writeCountry({
            id: country.id,
            internal_name: country.internal_name,
            backward_compatibility: backwardCompat,
          });

          this.registerEntity(country.id, backwardCompat, 'country');

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${country.id}: ${message}`);
          this.logError(`Country ${country.id}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to load countries from JSON: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}

/**
 * Country Translation Importer
 *
 * Imports country translations from the legacy database.
 * This must run after CountryImporter has imported countries from JSON.
 */
export class CountryTranslationImporter extends BaseImporter {
  getName(): string {
    return 'CountryTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing country translations from legacy database...');

      // Import the transformer
      const { transformCountryTranslation } = await import('../../domain/transformers/index.js');

      // Query country translations from legacy database
      interface LegacyCountryName {
        country: string;
        lang: string;
        name: string;
      }
      const countryNames = await this.context.legacyDb.query<LegacyCountryName>(
        'SELECT country, lang, name FROM mwnf3.countrynames ORDER BY country, lang'
      );

      this.logInfo(`Found ${countryNames.length} country translations to import`);

      for (const legacy of countryNames) {
        try {
          // Collect sample
          this.collectSample(
            'country_translation',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import country translation: ${legacy.country} (${legacy.lang})`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // Transform and write country translation
          // The transformer will map legacy 2-char code to ISO 3-char code
          // If the country doesn't exist, the database foreign key will reject it (handled in catch block)
          const transformed = transformCountryTranslation({ code: legacy.country, lang: legacy.lang, name: legacy.name });
          await this.context.strategy.writeCountryTranslation(transformed.data);

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          // Skip this translation if country or language doesn't exist (foreign key violation)
          if (message.includes('foreign key constraint fails')) {
            result.skipped++;
            this.showSkipped();
          } else {
            result.errors.push(`${legacy.country}:${legacy.lang}: ${message}`);
            this.logError(`Country translation ${legacy.country}:${legacy.lang}`, error);
            this.showError();
          }
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query country translations: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}
