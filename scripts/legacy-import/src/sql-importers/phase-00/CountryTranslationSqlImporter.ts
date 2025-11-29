import { BaseSqlImporter, type ImportResult } from '../base/BaseSqlImporter.js';
import { v4 as uuidv4 } from 'uuid';
import type { Connection } from 'mysql2/promise';
import type { LegacyDatabase } from '../../database/LegacyDatabase.js';
import { mapLanguageCode, mapCountryCode } from '../../utils/CodeMappings.js';

interface LegacyCountryName {
  country: string;
  lang: string;
  name: string;
}

/**
 * Imports country translations from mwnf3.countrynames
 * Maps to country_translations table
 */
export class CountryTranslationSqlImporter extends BaseSqlImporter {
  private legacyDb: LegacyDatabase;

  constructor(db: Connection, tracker: Map<string, string>, legacyDb: LegacyDatabase) {
    super(db, tracker);
    this.legacyDb = legacyDb;
  }

  getName(): string {
    return 'CountryTranslationSqlImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      this.log('Importing country translations from mwnf3.countrynames...');

      // Query country translations
      const countryNames = await this.legacyDb.query<LegacyCountryName>(
        'SELECT * FROM mwnf3.countrynames ORDER BY country, lang'
      );

      this.log(`Found ${countryNames.length} country translations`);

      let processed = 0;
      for (const countryName of countryNames) {
        try {
          const success = await this.importCountryTranslation(countryName);
          if (success) {
            result.imported++;
          } else {
            result.skipped++;
          }
          processed++;
          this.showProgress(processed, countryNames.length);
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${countryName.country}:${countryName.lang}: ${message}`);
          this.logError(
            `Failed to import country translation ${countryName.country}:${countryName.lang}`,
            error
          );
        }
      }

      console.log('');
      this.logSuccess(`Imported ${result.imported}, skipped ${result.skipped}`);
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(message);
      this.logError('Failed to import country translations', error);
    }

    return result;
  }

  private async importCountryTranslation(countryName: LegacyCountryName): Promise<boolean> {
    // Map legacy 2-char codes to ISO 639-3 (languages) and ISO 3166-1 alpha-3 (countries)
    const countryId = mapCountryCode(countryName.country);
    const languageId = mapLanguageCode(countryName.lang);

    // Skip invalid country codes (special codes like 'pd' and 'ww' map to 5-char codes that don't fit the schema)
    if (countryId.length > 3) {
      this.log(
        `Skipping country translation for special code: ${countryName.country} (maps to ${countryId})`
      );
      return false;
    }

    const backwardCompat = this.formatBackwardCompat('mwnf3', 'countrynames', [
      countryName.country,
      countryName.lang,
    ]);

    // Check if already exists
    if (await this.exists('country_translations', backwardCompat)) {
      return false;
    }

    // Insert country translation
    const translationId = uuidv4();
    try {
      await this.db.execute(
        `INSERT INTO country_translations (id, country_id, language_id, name, backward_compatibility, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)`,
        [translationId, countryId, languageId, countryName.name, backwardCompat, this.now, this.now]
      );

      this.tracker.set(backwardCompat, translationId);
      return true;
    } catch (error) {
      // Check if it's a duplicate key error (already exists via another path)
      const message = error instanceof Error ? error.message : String(error);
      if (message.includes('Duplicate entry') || message.includes('unique')) {
        // Already exists, silently skip
        return false;
      }
      // Re-throw other errors
      throw error;
    }
  }
}
