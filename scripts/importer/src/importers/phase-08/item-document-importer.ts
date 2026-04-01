/**
 * Item Document Importer
 *
 * Imports uploaded document files (PDFs) attached to items from legacy databases.
 *
 * Creates:
 * - 23 ItemDocument from mwnf3_sharing_history.sh_objects_document
 *   with size=1 placeholder (resolved later by DocumentSyncTool)
 *
 * Source tables:
 * - mwnf3_sharing_history.sh_objects_document (23 rows)
 *
 * Skipped (0 rows): sh_monuments_document
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { transformShObjectDocument } from '../../domain/transformers/index.js';
import type { ShLegacyObjectDocument } from '../../domain/types/index.js';

export class ItemDocumentImporter extends BaseImporter {
  getName(): string {
    return 'ItemDocumentImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing SH object documents...');

      const rows = await this.context.legacyDb.query<ShLegacyObjectDocument>(
        `SELECT project_id, country, number, lang, path, type, img_count
         FROM mwnf3_sharing_history.sh_objects_document
         ORDER BY project_id, country, number, lang, img_count`
      );
      this.logInfo(`  Found ${rows.length} SH object document rows`);

      for (const row of rows) {
        try {
          const transformed = transformShObjectDocument(row);

          // Resolve parent item
          const itemBC = `mwnf3_sharing_history:sh_objects:${row.project_id}:${row.country}:${row.number}`;
          const itemId = await this.getEntityUuidAsync(itemBC, 'item');
          if (!itemId) {
            this.logWarning(`Skipping SH document: parent item not found for BC=${itemBC}`);
            result.skipped++;
            continue;
          }

          // Resolve language
          const languageId = await this.getLanguageIdByLegacyCodeAsync(row.lang);
          if (!languageId) {
            this.logWarning(
              `Skipping SH document: unknown language '${row.lang}' for BC=${transformed.data.backward_compatibility}`
            );
            result.skipped++;
            continue;
          }

          await this.context.strategy.writeItemDocument({
            ...transformed.data,
            item_id: itemId,
            language_id: languageId,
          });

          result.imported++;
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          const ctx = `sh_objects_document:${row.project_id}:${row.country}:${row.number}:${row.lang}:${row.img_count}`;
          this.logError(ctx, message);
          result.errors.push(`Failed to import SH document ${ctx}: ${message}`);
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import item documents: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}
