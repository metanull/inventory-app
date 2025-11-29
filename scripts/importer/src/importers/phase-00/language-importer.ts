/**
 * Language Importer
 *
 * Imports languages and language translations from legacy database.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { transformLanguage, transformLanguageTranslation } from '../../domain/transformers/index.js';
import type { LegacyLanguage, LegacyLanguageName } from '../../domain/types/index.js';

export class LanguageImporter extends BaseImporter {
  getName(): string {
    return 'LanguageImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing languages...');

      // Query legacy languages
      const languages = await this.context.legacyDb.query<LegacyLanguage>(
        'SELECT * FROM mwnf3.langs ORDER BY code'
      );

      this.logInfo(`Found ${languages.length} languages`);

      for (const legacy of languages) {
        try {
          const transformed = transformLanguage(legacy);

          // Check if already exists
          if (this.entityExists(transformed.backwardCompatibility)) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Collect sample for foundation data
          this.collectSample(
            'language',
            legacy as unknown as Record<string, unknown>,
            'foundation'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(`[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import language: ${legacy.code}`);
            // Register for tracking even in dry-run
            this.registerEntity(transformed.data.id, transformed.backwardCompatibility, 'language');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write language using strategy
          await this.context.strategy.writeLanguage(transformed.data);
          this.registerEntity(transformed.data.id, transformed.backwardCompatibility, 'language');

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${legacy.code}: ${message}`);
          this.logError(`Language ${legacy.code}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query languages: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}

/**
 * Language Translation Importer
 *
 * Imports language name translations.
 */
export class LanguageTranslationImporter extends BaseImporter {
  getName(): string {
    return 'LanguageTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing language translations...');

      // Query legacy language names
      const languageNames = await this.context.legacyDb.query<LegacyLanguageName>(
        'SELECT * FROM mwnf3.langnames ORDER BY code, lang'
      );

      this.logInfo(`Found ${languageNames.length} language translations`);

      for (const legacy of languageNames) {
        try {
          const transformed = transformLanguageTranslation(legacy);

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(`[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import language translation: ${legacy.code}:${legacy.lang}`);
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write language translation using strategy
          await this.context.strategy.writeLanguageTranslation(transformed.data);

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${legacy.code}:${legacy.lang}: ${message}`);
          this.logError(`Language translation ${legacy.code}:${legacy.lang}`, error);
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
