import { BaseSqlImporter, type ImportResult } from '../base/BaseSqlImporter.js';
import type { RowDataPacket } from 'mysql2/promise';

interface CountryRow extends RowDataPacket {
  id: string;
  internal_name: string;
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
      this.log('Loading production country data...');

      // Load production country data (already seeded)
      const [countries] = await this.db.execute<CountryRow[]>(
        'SELECT id, internal_name FROM countries ORDER BY id'
      );

      this.log(`Found ${countries.length} countries in production (already seeded)`);

      for (const country of countries) {
        // Countries are already seeded - just register them
        const backwardCompat = `production:countries:${country.id}`;

        if (!this.tracker.has(backwardCompat)) {
          this.tracker.set(backwardCompat, country.id);
          result.imported++;
        } else {
          result.skipped++;
        }
      }

      this.logSuccess(`Registered ${result.imported} countries, skipped ${result.skipped}`);
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(message);
      this.logError('Failed to import countries', error);
    }

    return result;
  }
}
