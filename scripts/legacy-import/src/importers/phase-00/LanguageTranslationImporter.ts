import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';
import { mapLanguageCode } from '../../utils/CodeMappings.js';

interface LegacyLanguageName {
  lang_id: string;
  lang: string;
  name: string;
}

/**
 * Imports language translations from mwnf3.langnames
 * Maps to LanguageTranslation model
 */
export class LanguageTranslationImporter extends BaseImporter {
  getName(): string {
    return 'LanguageTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      // Query language translations
      const languageNames = await this.context.legacyDb.query<LegacyLanguageName>(
        'SELECT * FROM mwnf3.langnames ORDER BY lang_id, lang'
      );

      if (languageNames.length === 0) {
        this.logInfo('No language translations found');
        return result;
      }

      // Import each language translation
      for (const languageName of languageNames) {
        try {
          const imported = await this.importLanguageTranslation(languageName);
          if (imported) {
            result.imported++;
            this.showProgress();
          } else {
            result.skipped++;
            this.showSkipped();
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          // Log detailed error info
          if (error && typeof error === 'object' && 'response' in error) {
            const axiosError = error as { response?: { status?: number; data?: unknown } };
            this.logError(
              `LanguageTranslationImporter:${languageName.lang_id}:${languageName.lang}`,
              error instanceof Error ? error : new Error(message),
              { responseData: axiosError.response?.data }
            );
          }
          result.errors.push(`${languageName.lang_id}:${languageName.lang}: ${message}`);
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

  private async importLanguageTranslation(languageName: LegacyLanguageName): Promise<boolean> {
    // Format backward_compatibility
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'langnames',
      pkValues: [languageName.lang_id, languageName.lang],
    });

    // Check if already imported
    if (this.context.tracker.exists(backwardCompat)) {
      return false;
    }

    if (this.context.dryRun) {
      this.logInfo(
        `[DRY-RUN] Would import language translation: ${languageName.lang_id}:${languageName.lang}`
      );
      return true;
    }

    // Map 2-character codes to 3-character codes
    const languageId = mapLanguageCode(languageName.lang_id);
    const displayLanguageId = mapLanguageCode(languageName.lang);

    try {
      await this.context.apiClient.languageTranslation.languageTranslationStore({
        language_id: languageId,
        display_language_id: displayLanguageId,
        name: languageName.name,
        backward_compatibility: backwardCompat,
      });

      // Register in tracker
      this.context.tracker.register({
        uuid: backwardCompat, // Use backward_compat as UUID since we don't get it back
        backwardCompatibility: backwardCompat,
        entityType: 'context', // Use valid entityType
        createdAt: new Date(),
      });

      return true;
    } catch (error) {
      // If 422, translation already exists - skip silently
      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { status?: number } };
        if (axiosError.response?.status === 422) {
          return false; // Skipped
        }
      }
      throw error; // Re-throw non-422 errors
    }
  }
}
