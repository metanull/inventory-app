import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { readFileSync } from 'fs';
import { resolve } from 'path';

interface LanguageData {
  id: string;
  internal_name: string;
  backward_compatibility: string;
  is_default: boolean;
}

/**
 * Phase 0: Ensures Language reference data exists
 * Uses EXACT same production data as ProductionDataSeeder (languages.json)
 * Skips if already exists (idempotent)
 */
export class LanguageImporter extends BaseImporter {
  getName(): string {
    return 'LanguageImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    // Read production languages.json file
    // process.cwd() is scripts/legacy-import, go up 2 levels to reach project root
    const languagesPath = resolve(process.cwd(), '../../database/seeders/data/languages.json');
    let languages: LanguageData[];

    try {
      const fileContent = readFileSync(languagesPath, 'utf-8');
      languages = JSON.parse(fileContent) as LanguageData[];
      this.logInfo(`Loaded ${languages.length} languages from production data file`);
    } catch (error) {
      result.errors.push(
        `Failed to read languages.json: ${error instanceof Error ? error.message : String(error)}`
      );
      result.success = false;
      return result;
    }

    for (const language of languages) {
      try {
        if (this.context.dryRun) {
          this.logInfo(`[DRY-RUN] Would create language: ${language.id}`);
          result.imported++;
          continue;
        }

        // Try to create
        try {
          // Exclude is_default - it's prohibited in StoreLanguageRequest
          // Must be set via separate languageSetDefault endpoint
          const { is_default, ...languageData } = language;
          await this.context.apiClient.language.languageStore(languageData);

          // If this is English (default), set it as default
          if (is_default && language.id === 'eng') {
            await this.context.apiClient.language.languageSetDefault('eng', { is_default: true });
            this.logInfo('Set English (eng) as default language');
          }

          result.imported++;
          this.showProgress();
        } catch (createError) {
          // If 422, it already exists - verify it matches
          if (createError && typeof createError === 'object' && 'response' in createError) {
            const axiosError = createError as { response?: { status?: number } };
            if (axiosError.response?.status === 422) {
              // Already exists - verify it matches
              try {
                const existing = await this.context.apiClient.language.languageShow(language.id);
                const existingData = existing.data.data;

                // Compare critical fields
                if (
                  existingData.internal_name !== language.internal_name ||
                  existingData.backward_compatibility !== language.backward_compatibility
                ) {
                  result.errors.push(
                    `${language.id}: EXISTS but MISMATCH! Expected: {name: "${language.internal_name}", bc: "${language.backward_compatibility}"}, ` +
                      `Got: {name: "${existingData.internal_name}", bc: "${existingData.backward_compatibility}"}`
                  );
                  this.showError();
                  continue;
                }

                // Matches - safe to skip
                result.skipped++;
                this.showSkipped();
                continue;
              } catch {
                // If show fails, treat as create error
                throw createError;
              }
            }
          }
          throw createError; // Re-throw if not 422
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`${language.id}: ${message}`);
        this.showError();
      }
    }
    this.showSummary(result.imported, result.skipped, result.errors.length);

    result.success = result.errors.length === 0;

    if (!result.success) {
      this.logInfo(
        `CRITICAL: Language import failed with ${result.errors.length} errors. Cannot proceed.`
      );
    }

    return result;
  }
}
