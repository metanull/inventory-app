import { BaseSqlImporter, type ImportResult } from '../base/BaseSqlImporter.js';
import { readFileSync } from 'fs';
import { join } from 'path';

interface CountryData {
  id: string;
  internal_name: string;
  backward_compatibility: string;
}

export class CountrySqlImporter extends BaseSqlImporter {
  getName(): string {
    return 'CountrySqlImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      this.log('Loading production country data from JSON...');

      // Load countries from production JSON file (same as API importer and Laravel seeder)
      // Path from scripts/legacy-import to database/seeders/data
      const countriesPath = join(process.cwd(), '../../database/seeders/data/countries.json');
      const fileContent = readFileSync(countriesPath, 'utf-8');
      const countries = JSON.parse(fileContent) as CountryData[];

      this.log(`Found ${countries.length} countries to import`);

      for (const country of countries) {
        try {
          const backwardCompat = country.backward_compatibility;

          // Check if already exists
          if (await this.exists('countries', backwardCompat)) {
            result.skipped++;
            this.tracker.set(backwardCompat, country.id);
            continue;
          }

          // Insert country
          await this.db.execute(
            `INSERT INTO countries (id, internal_name, backward_compatibility, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?)`,
            [country.id, country.internal_name, backwardCompat, this.now, this.now]
          );

          this.tracker.set(backwardCompat, country.id);
          result.imported++;
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${country.id}: ${message}`);
          this.logError(`Failed to import country ${country.id}`, error);
        }
      }

      this.logSuccess(`Imported ${result.imported}, skipped ${result.skipped}`);
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(message);
      this.logError('Failed to import countries', error);
    }

    return result;
  }
}
