/**
 * Country Importer
 *
 * Imports countries from the production JSON file in database/seeders/data/countries.json.
 * This is the same data source used by the Laravel seeder and the legacy SQL importer.
 */

import { readFileSync } from 'fs';
import { join } from 'path';
import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

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
      // Path is relative from the importer package root to database/seeders/data
      const countriesPath = join(process.cwd(), '../../database/seeders/data/countries.json');
      const fileContent = readFileSync(countriesPath, 'utf-8');
      const countries = JSON.parse(fileContent) as CountryJsonData[];

      this.logInfo(`Found ${countries.length} countries to import`);

      for (const country of countries) {
        try {
          // Use backward_compatibility as the tracking key (consistent with legacy importer)
          const backwardCompat = country.backward_compatibility;

          // Check if already exists in tracker
          if (this.entityExists(backwardCompat)) {
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
          await this.context.strategy.writeCountry({
            id: country.id,
            internal_name: country.internal_name,
            backward_compatibility: backwardCompat,
            is_default: false,
            is_enabled: true,
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
 * Note: Country translations are not imported from JSON files.
 * The country names are already set in the countries.json file via internal_name.
 * This importer is kept as a no-op for compatibility with the import orchestration.
 *
 * If country translations from the legacy database are needed in the future,
 * they should be queried from mwnf3.countrynames with proper mapping.
 */
export class CountryTranslationImporter extends BaseImporter {
  getName(): string {
    return 'CountryTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    this.logInfo('Country translations are embedded in countries.json - skipping legacy import');
    this.logInfo('Each country has its internal_name set from the production JSON file');

    this.showSummary(result.imported, result.skipped, result.errors.length);
    result.success = true;
    return result;
  }
}
