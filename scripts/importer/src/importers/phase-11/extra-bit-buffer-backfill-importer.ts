/**
 * Extra Bit-Buffer Backfill Importer
 *
 * Standalone, idempotent repair of `collection_translations.extra` rows that
 * hold a serialized Node Buffer (`{"type":"Buffer","data":[1]}`) where a JSON
 * boolean belongs. mysql2 returns MySQL `bit(1)` columns as Buffers, and an
 * importer that stored one without running it through bitToBoolean left that
 * shape in the JSON column.
 *
 * The originating defect was ThgGalleryTranslationImporter writing
 * thg_gallery.has_timeline / has_country_timeline raw. ThgGalleryLangImporter
 * normalises the same two columns but merges into an existing translation row
 * with existing-wins semantics, so it could not repair what ran before it.
 * Both write paths normalise now; this step fixes the rows already imported,
 * which neither importer would revisit (the exhibition path skips rows that
 * already exist, the lang path keeps the existing value).
 *
 * Scope: `collection_translations.extra` is the only place the shape can
 * appear. `thg_gallery.has_timeline` and `has_country_timeline` are the only
 * `bit(1)` columns in the whole legacy schema, and the only two importers that
 * read them both write into that column.
 *
 * Only single-byte Buffers are converted — see normalizeSerializedBitBuffers.
 * The step reads nothing from the legacy database, and is a no-op after a
 * fresh full import.
 *
 * Run standalone with `--only extra-bit-buffer-backfill`.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { normalizeSerializedBitBuffers } from '../../utils/legacy-values.js';

export class ExtraBitBufferBackfillImporter extends BaseImporter {
  getName(): string {
    return 'ExtraBitBufferBackfillImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Normalising serialized bit(1) Buffers in collection_translations.extra...');

      const candidates =
        await this.context.strategy.findCollectionTranslationsWithSerializedBuffers();

      this.logInfo(`Found ${candidates.length} candidate row(s) to inspect`);

      for (const candidate of candidates) {
        try {
          const normalized = normalizeSerializedBitBuffers(candidate.extra);

          // Structural identity: nothing matched the single-byte Buffer shape,
          // so the JSON_SEARCH hit was an unrelated "Buffer" string.
          if (normalized === candidate.extra) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would normalise extra on collection_translation ${candidate.id}`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          await this.context.strategy.setCollectionTranslationExtraById(
            candidate.id,
            JSON.stringify(normalized)
          );

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Collection translation ${candidate.id}: ${message}`);
          this.logError(`Collection translation ${candidate.id}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to normalise serialized bit Buffers: ${message}`);
      this.logError('ExtraBitBufferBackfillImporter', message);
      this.showError();
    }

    result.success = result.errors.length === 0;
    return result;
  }
}
