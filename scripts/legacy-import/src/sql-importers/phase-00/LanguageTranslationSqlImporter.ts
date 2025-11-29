import { BaseSqlImporter, type ImportResult } from '../base/BaseSqlImporter.js';
import { v4 as uuidv4 } from 'uuid';
import type { Connection, RowDataPacket } from 'mysql2/promise';
import type { LegacyDatabase } from '../../database/LegacyDatabase.js';
import { mapLanguageCode } from '../../utils/CodeMappings.js';
import type { SampleCollector } from '../../utils/SampleCollector.js';

interface LegacyLanguageName {
  lang_id: string;
  lang: string;
  name: string;
}

/**
 * Imports language translations from mwnf3.langnames
 * Maps to language_translations table
 */
export class LanguageTranslationSqlImporter extends BaseSqlImporter {
  private legacyDb: LegacyDatabase;

  constructor(
    db: Connection,
    tracker: Map<string, string>,
    legacyDb: LegacyDatabase,
    sampleCollector?: SampleCollector
  ) {
    super(db, tracker, sampleCollector);
    this.legacyDb = legacyDb;
  }

  getName(): string {
    return 'LanguageTranslationSqlImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      this.log('Importing language translations from mwnf3.langnames...');

      // Query language translations
      const languageNames = await this.legacyDb.query<LegacyLanguageName>(
        'SELECT * FROM mwnf3.langnames ORDER BY lang_id, lang'
      );

      this.log(`Found ${languageNames.length} language translations`);

      let processed = 0;
      for (const langName of languageNames) {
        try {
          const success = await this.importLanguageTranslation(langName);
          if (success) {
            result.imported++;
          } else {
            result.skipped++;
          }
          processed++;
          this.showProgress(processed, languageNames.length);
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${langName.lang_id}:${langName.lang}: ${message}`);
          this.logError(
            `Failed to import language translation ${langName.lang_id}:${langName.lang}`,
            error
          );
        }
      }

      console.log('');
      this.logSuccess(`Imported ${result.imported}, skipped ${result.skipped}`);
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(message);
      this.logError('Failed to import language translations', error);
    }

    return result;
  }

  private async importLanguageTranslation(langName: LegacyLanguageName): Promise<boolean> {
    // Map legacy 2-char codes to ISO 639-3
    let languageId: string;
    let displayLanguageId: string;

    try {
      languageId = mapLanguageCode(langName.lang_id);
      displayLanguageId = mapLanguageCode(langName.lang);
    } catch {
      // Unknown language code in mapping - skip
      this.log(
        `Skipping language translation - unknown code: ${langName.lang_id} or ${langName.lang}`
      );
      return false;
    }

    // Check if both languages exist in the database
    const [langExists] = await this.db.execute('SELECT id FROM languages WHERE id = ?', [
      languageId,
    ]);
    const [displayLangExists] = await this.db.execute('SELECT id FROM languages WHERE id = ?', [
      displayLanguageId,
    ]);

    if ((langExists as RowDataPacket[]).length === 0) {
      this.log(
        `Skipping language translation - language not found: ${languageId} (${langName.lang_id})`
      );
      return false;
    }
    if ((displayLangExists as RowDataPacket[]).length === 0) {
      this.log(
        `Skipping language translation - display language not found: ${displayLanguageId} (${langName.lang})`
      );
      return false;
    }

    const backwardCompat = this.formatBackwardCompat('mwnf3', 'langnames', [
      langName.lang_id,
      langName.lang,
    ]);

    // Check if already exists
    if (await this.exists('language_translations', backwardCompat)) {
      return false;
    }

    // Insert language translation
    const translationId = uuidv4();
    await this.db.execute(
      `INSERT INTO language_translations (id, language_id, display_language_id, name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        translationId,
        languageId,
        displayLanguageId,
        langName.name,
        backwardCompat,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(backwardCompat, translationId);
    return true;
  }
}
