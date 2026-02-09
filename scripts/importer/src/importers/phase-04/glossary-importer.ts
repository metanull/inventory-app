/**
 * Glossary Importer
 *
 * Imports glossary (word) entries from the legacy database.
 * Each word can have multiple spellings and definitions in different languages.
 *
 * Legacy schema:
 * - mwnf3.glossary (word_id, name)
 * - mwnf3.gl_spellings (spelling_id, word_id, lang_id, spelling)
 * - mwnf3.gl_definitions (word_id, lang_id, definition)
 *
 * New schema:
 * - glossaries (id, internal_name, backward_compatibility)
 * - glossary_spellings (id, glossary_id, language_id, spelling)
 * - glossary_translations (id, glossary_id, language_id, definition)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';

/**
 * Legacy glossary word structure
 */
interface LegacyGlossaryWord {
  word_id: number;
  name: string | null;
}

export class GlossaryImporter extends BaseImporter {
  getName(): string {
    return 'GlossaryImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing glossary words from legacy database...');

      // Query words from legacy database
      const words = await this.context.legacyDb.query<LegacyGlossaryWord>(
        'SELECT word_id, name FROM mwnf3.glossary ORDER BY word_id'
      );

      this.logInfo(`Found ${words.length} glossary words to import`);

      for (const legacy of words) {
        try {
          // Use standard backward compatibility format: database:table:pk
          const backwardCompat = `mwnf3:glossary:${legacy.word_id}`;

          // Check if already exists
          if (this.entityExists(backwardCompat, 'glossary')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Use name as internal_name, or generate one if null
          const internalName = legacy.name || `word_${legacy.word_id}`;

          // Collect sample
          this.collectSample('glossary', legacy as unknown as Record<string, unknown>, 'success');

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import glossary word: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'glossary');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write glossary using strategy
          const glossaryId = await this.context.strategy.writeGlossary({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
          });

          this.registerEntity(glossaryId, backwardCompat, 'glossary');

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Word ${legacy.word_id}: ${message}`);
          this.logError(`Glossary word ${legacy.word_id}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import glossary words: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}

/**
 * Glossary Translation Importer
 *
 * Imports glossary definitions from the legacy database.
 * Must run after GlossaryImporter.
 */
export class GlossaryTranslationImporter extends BaseImporter {
  getName(): string {
    return 'GlossaryTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing glossary definitions from legacy database...');

      // Query definitions from legacy database
      interface LegacyGlossaryDefinition {
        word_id: number;
        lang_id: string;
        definition: string | null;
      }

      const definitions = await this.context.legacyDb.query<LegacyGlossaryDefinition>(
        'SELECT word_id, lang_id, definition FROM mwnf3.gl_definitions ORDER BY word_id, lang_id'
      );

      this.logInfo(`Found ${definitions.length} glossary definitions to import`);

      for (const legacy of definitions) {
        try {
          // Skip if no definition
          if (!legacy.definition) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Find the glossary word by backward_compatibility using tracker
          const wordBackwardCompat = `mwnf3:glossary:${legacy.word_id}`;
          const glossaryId = this.getEntityUuid(wordBackwardCompat, 'glossary');

          if (!glossaryId) {
            result.errors.push(`Glossary word not found for definition: word_id=${legacy.word_id}`);
            this.showError();
            continue;
          }

          // Map legacy language code (2-char) to new language ID (3-char)
          let languageId: string;
          try {
            languageId = mapLanguageCode(legacy.lang_id);
          } catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(
              `Language mapping failed for glossary definition: lang_id=${legacy.lang_id}, word_id=${legacy.word_id} - ${message}`
            );
            this.showError();
            continue;
          }

          // Collect sample
          this.collectSample(
            'glossary_translation',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import glossary definition: word_id=${legacy.word_id}, lang=${legacy.lang_id}`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write glossary translation using strategy
          await this.context.strategy.writeGlossaryTranslation({
            glossary_id: glossaryId,
            language_id: languageId,
            definition: legacy.definition,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Definition word_id=${legacy.word_id}, lang=${legacy.lang_id}: ${message}`
          );
          this.logError(
            `Glossary definition word_id=${legacy.word_id}, lang=${legacy.lang_id}`,
            message
          );
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import glossary definitions: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}

/**
 * Glossary Spelling Importer
 *
 * Imports glossary spellings from the legacy database.
 * Must run after GlossaryImporter.
 *
 * Note: The legacy system allowed users to abuse spellings as "synonyms",
 * effectively storing different words as alternate spellings.
 * We import all spellings as-is; cleanup/synonym detection can be done later.
 */
export class GlossarySpellingImporter extends BaseImporter {
  getName(): string {
    return 'GlossarySpellingImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing glossary spellings from legacy database...');

      // Query spellings from legacy database
      interface LegacyGlossarySpelling {
        spelling_id: number;
        word_id: number;
        lang_id: string;
        spelling: string;
      }

      const spellings = await this.context.legacyDb.query<LegacyGlossarySpelling>(
        'SELECT spelling_id, word_id, lang_id, spelling FROM mwnf3.gl_spellings ORDER BY word_id, lang_id, spelling_id'
      );

      this.logInfo(`Found ${spellings.length} glossary spellings to import`);

      for (const legacy of spellings) {
        try {
          // Skip if no spelling text
          if (!legacy.spelling || legacy.spelling.trim() === '') {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Find the glossary word by backward_compatibility using tracker
          const wordBackwardCompat = `mwnf3:glossary:${legacy.word_id}`;
          const glossaryId = this.getEntityUuid(wordBackwardCompat, 'glossary');

          if (!glossaryId) {
            result.errors.push(
              `Glossary word not found for spelling: word_id=${legacy.word_id}, spelling_id=${legacy.spelling_id}`
            );
            this.showError();
            continue;
          }

          // Map legacy language code (2-char) to new language ID (3-char)
          let languageId: string;
          try {
            languageId = mapLanguageCode(legacy.lang_id);
          } catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(
              `Language mapping failed for glossary spelling: lang_id=${legacy.lang_id}, word_id=${legacy.word_id}, spelling_id=${legacy.spelling_id} - ${message}`
            );
            this.showError();
            continue;
          }

          // Collect sample
          this.collectSample(
            'glossary_spelling',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import glossary spelling: ${legacy.spelling} (word_id=${legacy.word_id}, lang=${legacy.lang_id})`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write glossary spelling using strategy
          await this.context.strategy.writeGlossarySpelling({
            glossary_id: glossaryId,
            language_id: languageId,
            spelling: legacy.spelling,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Spelling spelling_id=${legacy.spelling_id}, word_id=${legacy.word_id}: ${message}`
          );
          this.logError(
            `Glossary spelling spelling_id=${legacy.spelling_id}, word_id=${legacy.word_id}`,
            message
          );
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import glossary spellings: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}
