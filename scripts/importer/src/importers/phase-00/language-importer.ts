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

          // Check if already exists in tracker
          if (this.entityExists(backwardCompat)) {
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
          await this.context.strategy.writeLanguage({
            id: language.id,
            internal_name: language.internal_name,
            backward_compatibility: backwardCompat,
            is_default: language.is_default,
            is_enabled: true,
          });

          this.registerEntity(language.id, backwardCompat, 'language');

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
 * Note: Language translations are not imported from JSON files.
 * The language names are already set in the languages.json file via internal_name.
 * This importer is kept as a no-op for compatibility with the import orchestration.
 *
 * If language translations from the legacy database are needed in the future,
 * they should be queried from mwnf3.langnames with proper mapping.
 */
export class LanguageTranslationImporter extends BaseImporter {
  getName(): string {
    return 'LanguageTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    this.logInfo('Language translations are embedded in languages.json - skipping legacy import');
    this.logInfo('Each language has its internal_name set from the production JSON file');

    this.showSummary(result.imported, result.skipped, result.errors.length);
    result.success = true;
    return result;
  }
}
