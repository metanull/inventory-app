import { BaseSqlImporter, type ImportResult } from '../base/BaseSqlImporter.js';
import { readFileSync } from 'fs';
import { join } from 'path';

interface LanguageData {
  id: string;
  internal_name: string;
  backward_compatibility: string;
  is_default: boolean;
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
      this.log('Loading production language data from JSON...');

      // Load languages from production JSON file (same as API importer and Laravel seeder)
      // Path from scripts/legacy-import/src/sql-importers/phase-00 to database/seeders/data
      const languagesPath = join(process.cwd(), '../../database/seeders/data/languages.json');
      const fileContent = readFileSync(languagesPath, 'utf-8');
      const languages = JSON.parse(fileContent) as LanguageData[];

      this.log(`Found ${languages.length} languages to import`);

      for (const language of languages) {
        try {
          const backwardCompat = language.backward_compatibility;

          // Check if already exists
          if (await this.exists('languages', backwardCompat)) {
            result.skipped++;
            this.tracker.set(backwardCompat, language.id);
            continue;
          }

          // Insert language
          await this.db.execute(
            `INSERT INTO languages (id, internal_name, backward_compatibility, is_default, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?)`,
            [
              language.id,
              language.internal_name,
              backwardCompat,
              language.is_default ? 1 : 0,
              this.now,
              this.now,
            ]
          );

          this.tracker.set(backwardCompat, language.id);
          result.imported++;
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${language.id}: ${message}`);
          this.logError(`Failed to import language ${language.id}`, error);
        }
      }

      this.logSuccess(`Imported ${result.imported}, skipped ${result.skipped}`);
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(message);
      this.logError('Failed to import languages', error);
    }

    return result;
  }
}
