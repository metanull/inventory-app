import { BaseSqlImporter, type ImportResult } from '../base/BaseSqlImporter.js';
import type { RowDataPacket } from 'mysql2/promise';

interface LanguageRow extends RowDataPacket {
  id: string;
  internal_name: string;
}

export class LanguageSqlImporter extends BaseSqlImporter {
  getName(): string {
    return 'LanguageSqlImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      this.log('Loading production language data...');

      // Load production language data (already seeded)
      const [languages] = await this.db.execute<LanguageRow[]>(
        'SELECT id, internal_name FROM languages ORDER BY id'
      );

      this.log(`Found ${languages.length} languages in production (already seeded)`);

      for (const lang of languages) {
        // Languages are already seeded - just register them
        const backwardCompat = `production:languages:${lang.id}`;

        if (!this.tracker.has(backwardCompat)) {
          this.tracker.set(backwardCompat, lang.id);
          result.imported++;
        } else {
          result.skipped++;
        }
      }

      this.logSuccess(`Registered ${result.imported} languages, skipped ${result.skipped}`);
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(message);
      this.logError('Failed to import languages', error);
    }

    return result;
  }
}
