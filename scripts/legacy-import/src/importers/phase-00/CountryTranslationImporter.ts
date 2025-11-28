import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';
import { mapLanguageCode, mapCountryCode } from '../../utils/CodeMappings.js';

interface LegacyCountryName {
  country: string;
  lang: string;
  name: string;
}

/**
 * Imports country translations from mwnf3.countrynames
 * Maps to CountryTranslation model
 */
export class CountryTranslationImporter extends BaseImporter {
  getName(): string {
    return 'CountryTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      // Query country translations
      const countryNames = await this.context.legacyDb.query<LegacyCountryName>(
        'SELECT * FROM mwnf3.countrynames ORDER BY country, lang'
      );

      if (countryNames.length === 0) {
        this.logInfo('No country translations found');
        return result;
      }

      // Import each country translation
      for (const countryName of countryNames) {
        try {
          const imported = await this.importCountryTranslation(countryName);
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
              `CountryTranslationImporter:${countryName.country}:${countryName.lang}`,
              error instanceof Error ? error : new Error(message),
              { responseData: axiosError.response?.data }
            );
          }
          result.errors.push(`${countryName.country}:${countryName.lang}: ${message}`);
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

  private async importCountryTranslation(countryName: LegacyCountryName): Promise<boolean> {
    // Format backward_compatibility
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'countrynames',
      pkValues: [countryName.country, countryName.lang],
    });

    // Check if already imported
    if (this.context.tracker.exists(backwardCompat)) {
      return false;
    }

    // Collect sample for testing (BEFORE API calls)
    const countryId = mapCountryCode(countryName.country);
    const languageId = mapLanguageCode(countryName.lang);
    this.collectSample(
      'country_translation',
      countryName as unknown as Record<string, unknown>,
      'success',
      undefined,
      languageId
    );

    if (this.context.dryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import country translation: ${countryName.country}:${countryName.lang}`
      );

      if (this.isSampleOnlyMode) {
        this.context.tracker.register({
          entityType: 'context',
          uuid: `${countryId}:${languageId}`,
          backwardCompatibility: backwardCompat,
          createdAt: new Date(),
        });
      }
      return true;
    }

    try {
      await this.context.apiClient.countryTranslation.countryTranslationStore({
        country_id: countryId,
        language_id: languageId,
        name: countryName.name,
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
