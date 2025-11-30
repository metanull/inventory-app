/**
 * Country Importer
 *
 * Imports countries and country translations from legacy database.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { transformCountry, transformCountryTranslation } from '../../domain/transformers/index.js';
import type { LegacyCountry, LegacyCountryName } from '../../domain/types/index.js';

export class CountryImporter extends BaseImporter {
  getName(): string {
    return 'CountryImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing countries...');

      // Query legacy countries
      const countries = await this.context.legacyDb.query<LegacyCountry>(
        'SELECT * FROM mwnf3.countries ORDER BY code'
      );

      this.logInfo(`Found ${countries.length} countries`);

      for (const legacy of countries) {
        try {
          const transformed = transformCountry(legacy);

          // Check if already exists
          if (this.entityExists(transformed.backwardCompatibility)) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Collect sample for foundation data
          this.collectSample(
            'country',
            legacy as unknown as Record<string, unknown>,
            'foundation'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(`[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import country: ${legacy.code}`);
            // Register for tracking even in dry-run
            this.registerEntity(transformed.data.id, transformed.backwardCompatibility, 'country');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write country using strategy
          await this.context.strategy.writeCountry(transformed.data);
          this.registerEntity(transformed.data.id, transformed.backwardCompatibility, 'country');

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${legacy.code}: ${message}`);
          this.logError(`Country ${legacy.code}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query countries: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}

/**
 * Country Translation Importer
 *
 * Imports country name translations.
 */
export class CountryTranslationImporter extends BaseImporter {
  getName(): string {
    return 'CountryTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing country translations...');

      // Query legacy country names
      const countryNames = await this.context.legacyDb.query<LegacyCountryName>(
        'SELECT * FROM mwnf3.countrynames ORDER BY code, lang'
      );

      this.logInfo(`Found ${countryNames.length} country translations`);

      for (const legacy of countryNames) {
        try {
          const transformed = transformCountryTranslation(legacy);

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(`[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import country translation: ${legacy.code}:${legacy.lang}`);
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write country translation using strategy
          await this.context.strategy.writeCountryTranslation(transformed.data);

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${legacy.code}:${legacy.lang}: ${message}`);
          this.logError(`Country translation ${legacy.code}:${legacy.lang}`, error);
          this.showError();
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
