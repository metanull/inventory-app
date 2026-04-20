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
import { mapCountryCode, mapLanguageCode } from '../../utils/code-mappings.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';

// Get the directory of the current module for robust path resolution
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

/**
 * Country data structure from the JSON file
 */
interface CountryJsonData {
  id: string; // ISO 3166-1 alpha-3 code (e.g., 'usa', 'fra')
  internal_name: string;
  backward_compatibility: string | null; // Legacy 2-char code, null for non-legacy countries
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
          // Tracking key: BC for legacy countries (enables lookup by legacy code),
          // id for non-legacy countries (enables dedup only — no legacy code exists)
          const trackingKey: string =
            country.backward_compatibility !== null ? country.backward_compatibility : country.id;

          // Check if already exists in tracker (pass entityType to avoid collisions with languages)
          if (await this.entityExistsAsync(trackingKey, 'country')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Collect sample for foundation data
          this.collectSample(
            'country',
            country as unknown as Record<string, unknown>,
            'foundation'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import country: ${country.id} (${country.internal_name})`
            );
            // Register for tracking even in dry-run
            this.registerEntity(country.id, trackingKey, 'country');
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
            backward_compatibility: country.backward_compatibility,
          });

          this.registerEntity(country.id, trackingKey, 'country');

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${country.id}: ${message}`);
          this.logError(`Country ${country.id}`, message);
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

          // Validate FK references before write
          const countryExists = await this.entityExistsAsync(legacy.country, 'country');
          if (!countryExists) {
            // Code might be a secondary/alias code (e.g., 'ix' for Italy/Sicily, 'px' for Palestine)
            // Verify via code-mappings that it resolves to a known country
            try {
              mapCountryCode(legacy.country);
            } catch {
              this.logWarning(`Country '${legacy.country}' not found, skipping translation`);
              result.skipped++;
              this.showSkipped();
              continue;
            }
          }
          const langExists = await this.entityExistsAsync(legacy.lang, 'language');
          if (!langExists) {
            this.logWarning(
              `Language '${legacy.lang}' not found, skipping translation for country '${legacy.country}'`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          await this.context.strategy.writeCountryTranslation({
            country_id: mapCountryCode(legacy.country),
            language_id: mapLanguageCode(legacy.lang),
            name: legacy.name,
            backward_compatibility: formatBackwardCompatibility({
              schema: 'mwnf3',
              table: 'countrynames',
              pkValues: [legacy.country, legacy.lang],
            }),
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          if (message.includes('Duplicate')) {
            this.logSkip(
              `Country translation ${legacy.country}:${legacy.lang}: duplicate, skipping`
            );
            result.skipped++;
            this.showSkipped();
          } else {
            result.errors.push(`${legacy.country}:${legacy.lang}: ${message}`);
            this.logError(`Country translation ${legacy.country}:${legacy.lang}`, message);
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
