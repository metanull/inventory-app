import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { readFileSync } from 'fs';
import { resolve } from 'path';

interface CountryData {
  id: string;
  internal_name: string;
  backward_compatibility: string;
}

/**
 * Phase 0: Ensures Country reference data exists
 * Uses EXACT same production data as ProductionDataSeeder (countries.json)
 * Skips if already exists (idempotent)
 */
export class CountryImporter extends BaseImporter {
  getName(): string {
    return 'CountryImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    // Read production countries.json file
    // __dirname is dist/importers/phase-00 or src/importers/phase-00, go up to scripts/legacy-import, then to project root
    const countriesPath = resolve(__dirname, '../../../../../database/seeders/data/countries.json');
    let countries: CountryData[];

    try {
      const fileContent = readFileSync(countriesPath, 'utf-8');
      countries = JSON.parse(fileContent) as CountryData[];
      this.logInfo(`Loaded ${countries.length} countries from production data file`);
    } catch (error) {
      result.errors.push(
        `Failed to read countries.json: ${error instanceof Error ? error.message : String(error)}`
      );
      result.success = false;
      return result;
    }

    for (const country of countries) {
      try {
        // Collect sample for testing (BEFORE API calls)
        this.collectSample('country', country, 'success');

        if (this.context.dryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create country: ${country.id}`
          );
          result.imported++;

          if (this.isSampleOnlyMode) {
            this.context.tracker.register({
              uuid: country.id,
              backwardCompatibility: country.backward_compatibility,
            });
          }
          continue;
        }

        // Try to create (only if not in sample-only mode)
        try {
          await this.context.apiClient.country.countryStore(country);
          result.imported++;
          this.showProgress();
        } catch (createError) {
          // If 422, it already exists - verify it matches
          if (createError && typeof createError === 'object' && 'response' in createError) {
            const axiosError = createError as { response?: { status?: number } };
            if (axiosError.response?.status === 422) {
              // Already exists - verify it matches
              try {
                const existing = await this.context.apiClient.country.countryShow(country.id);
                const existingData = existing.data.data;

                // Compare critical fields
                if (
                  existingData.internal_name !== country.internal_name ||
                  existingData.backward_compatibility !== country.backward_compatibility
                ) {
                  result.errors.push(
                    `${country.id}: EXISTS but MISMATCH! Expected: {name: "${country.internal_name}", bc: "${country.backward_compatibility}"}, ` +
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
        result.errors.push(`${country.id}: ${message}`);
        this.showError();
      }
    }
    this.showSummary(result.imported, result.skipped, result.errors.length);

    result.success = result.errors.length === 0;

    if (!result.success) {
      this.logError('CountryImporter:summary', new Error('Country import failed'), {
        errorCount: result.errors.length,
      });
    }

    return result;
  }
}
