/**
 * Language Importer
 *
 * Imports languages from the production JSON file in database/seeders/data/languages.json.
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
 * Language data structure from the JSON file
 */
interface LanguageJsonData {
  id: string; // ISO 639-3 code (e.g., 'eng', 'fra')
  internal_name: string;
  backward_compatibility: string; // ISO 639-1 code (e.g., 'en', 'fr')
  is_default: boolean;
}

export class LanguageImporter extends BaseImporter {
  getName(): string {
    return 'LanguageImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Loading languages from production JSON file...');

      // Load languages from production JSON file (same as Laravel seeder)
      // Path is from scripts/importer/src/importers/phase-00 to database/seeders/data
      const languagesPath = join(__dirname, '../../../../../database/seeders/data/languages.json');
      const fileContent = readFileSync(languagesPath, 'utf-8');
      const languages = JSON.parse(fileContent) as LanguageJsonData[];

      this.logInfo(`Found ${languages.length} languages to import`);

      for (const language of languages) {
        try {
          // Use backward_compatibility as the tracking key (consistent with legacy importer)
          const backwardCompat = language.backward_compatibility;

          // Check if already exists in tracker (pass entityType to avoid collisions with countries)
          if (this.entityExists(backwardCompat, 'language')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Collect sample for foundation data
          this.collectSample(
            'language',
            language as unknown as Record<string, unknown>,
            'foundation'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import language: ${language.id} (${language.internal_name})`
            );
            // Register for tracking even in dry-run
            this.registerEntity(language.id, backwardCompat, 'language');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write language using strategy
          // Note: Only pass fields that exist in the database table
          // The languages table has: id, internal_name, backward_compatibility, is_default
          await this.context.strategy.writeLanguage({
            id: language.id,
            internal_name: language.internal_name,
            backward_compatibility: backwardCompat,
            is_default: language.is_default,
          });

          this.registerEntity(language.id, backwardCompat, 'language');

          // Track default language ID for use by other importers
          if (language.is_default) {
            this.context.tracker.setMetadata('default_language_id', language.id);
            this.logInfo(`Tracked default language: ${language.id}`);
          }

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${language.id}: ${message}`);
          this.logError(`Language ${language.id}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to load languages from JSON: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}

/**
 * Language Translation Importer
 *
 * Imports language translations from the legacy database.
 * This must run after LanguageImporter has imported languages from JSON.
 */
export class LanguageTranslationImporter extends BaseImporter {
  getName(): string {
    return 'LanguageTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing language translations from legacy database...');

      // Import the transformer and code mappings
      const { transformLanguageTranslation } = await import('../../domain/transformers/index.js');
      const { mapLanguageCode } = await import('../../utils/code-mappings.js');

      // Query language translations from legacy database
      interface LegacyLanguageName {
        lang_id: string;
        lang: string;
        name: string;
      }
      const languageNames = await this.context.legacyDb.query<LegacyLanguageName>(
        'SELECT lang_id, lang, name FROM mwnf3.langnames ORDER BY lang_id, lang'
      );

      this.logInfo(`Found ${languageNames.length} language translations to import`);

      for (const legacy of languageNames) {
        try {
          // Map legacy 2-char code to ISO 3-char code
          const iso3Code = mapLanguageCode(legacy.lang_id);
          
          // Check if language exists in tracker (pass entityType to avoid collisions with countries)
          if (!this.entityExists(legacy.lang_id, 'language')) {
            this.logWarning(`Language ${legacy.lang_id} (${iso3Code}) not found, skipping translation for ${legacy.lang}`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Collect sample
          this.collectSample(
            'language_translation',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import language translation: ${legacy.lang_id} (${legacy.lang})`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // Transform and write language translation
          const transformed = transformLanguageTranslation({ code: legacy.lang_id, lang: legacy.lang, name: legacy.name });
          await this.context.strategy.writeLanguageTranslation(transformed.data);

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${legacy.lang_id}:${legacy.lang}: ${message}`);
          this.logError(`Language translation ${legacy.lang_id}:${legacy.lang}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query language translations: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}
