/**
 * Dynasty Importer
 *
 * Imports dynasties and their translations from legacy database.
 * Creates Dynasty entities with translations and Item↔Dynasty links.
 *
 * Source tables:
 * - mwnf3.dynasties (57 rows) → Dynasty records
 * - mwnf3.dynasty_texts (251 rows) → DynastyTranslation records
 * - mwnf3.objects_dynasties (2,286 rows) → item_dynasty pivot
 * - mwnf3.monuments_dynasties (469 rows) → item_dynasty pivot
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { transformDynasty, transformDynastyTranslation } from '../../domain/transformers/index.js';
import type {
  LegacyDynasty,
  LegacyDynastyText,
  LegacyObjectDynasty,
  LegacyMonumentDynasty,
} from '../../domain/types/index.js';

export class DynastyImporter extends BaseImporter {
  getName(): string {
    return 'DynastyImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      // Import dynasties and translations
      this.logInfo('Importing dynasties...');
      const dynastyResult = await this.importDynasties();
      result.imported += dynastyResult.imported;
      result.skipped += dynastyResult.skipped;
      result.errors.push(...dynastyResult.errors);

      // Import item-dynasty links
      this.logInfo('Importing item-dynasty links...');
      const linkResult = await this.importItemDynastyLinks();
      result.imported += linkResult.imported;
      result.skipped += linkResult.skipped;
      result.errors.push(...linkResult.errors);

      this.showSummary(
        result.imported,
        result.skipped,
        result.errors.length,
        result.warnings?.length
      );
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import dynasties: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importDynasties(): Promise<ImportResult> {
    const result = this.createResult();

    // Query legacy dynasties
    const dynasties = await this.context.legacyDb.query<LegacyDynasty>(
      'SELECT * FROM mwnf3.dynasties ORDER BY dynasty_id'
    );

    // Query legacy dynasty texts
    const dynastyTexts = await this.context.legacyDb.query<LegacyDynastyText>(
      'SELECT * FROM mwnf3.dynasty_texts ORDER BY dynasty_id, lang_id'
    );

    // Group texts by dynasty_id
    const textsByDynastyId = new Map<number, LegacyDynastyText[]>();
    for (const text of dynastyTexts) {
      const existing = textsByDynastyId.get(text.dynasty_id) || [];
      existing.push(text);
      textsByDynastyId.set(text.dynasty_id, existing);
    }

    this.logInfo(`Found ${dynasties.length} dynasties with ${dynastyTexts.length} translations`);

    for (const legacy of dynasties) {
      try {
        const transformed = transformDynasty(legacy);

        // Check if already exists
        if (await this.entityExistsAsync(transformed.backwardCompatibility, 'dynasty')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Collect sample
        this.collectSample('dynasty', legacy as unknown as Record<string, unknown>, 'success');

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import dynasty: ${legacy.dynasty_id}`
          );
          this.registerEntity(
            'sample-dynasty-' + legacy.dynasty_id,
            transformed.backwardCompatibility,
            'dynasty'
          );
          result.imported++;
          this.showProgress();
          continue;
        }

        // Create dynasty
        const dynastyId = await this.context.strategy.writeDynasty(transformed.data);
        this.registerEntity(dynastyId, transformed.backwardCompatibility, 'dynasty');

        // Create translations
        const texts = textsByDynastyId.get(legacy.dynasty_id) || [];
        for (const text of texts) {
          try {
            const languageId = await this.getLanguageIdByLegacyCodeAsync(text.lang_id);
            if (!languageId) {
              this.logWarning(
                `Unknown language code '${text.lang_id}' for dynasty ${legacy.dynasty_id}, skipping translation`
              );
              continue;
            }

            const translationData = transformDynastyTranslation(text);
            await this.context.strategy.writeDynastyTranslation({
              ...translationData.data,
              dynasty_id: dynastyId,
              language_id: languageId,
            });
          } catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            const warning = `Failed to create translation for dynasty ${legacy.dynasty_id}:${text.lang_id}: ${message}`;
            this.logWarning(warning);
            result.warnings!.push(warning);
          }
        }

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Dynasty ${legacy.dynasty_id}: ${message}`);
        this.logError(`Dynasty ${legacy.dynasty_id}`, message);
        this.showError();
      }
    }

    return result;
  }

  private async importItemDynastyLinks(): Promise<ImportResult> {
    const result = this.createResult();

    // Query legacy object-dynasty links
    const objectDynasties = await this.context.legacyDb.query<LegacyObjectDynasty>(
      'SELECT * FROM mwnf3.objects_dynasties ORDER BY dynasty_id'
    );

    // Query legacy monument-dynasty links
    const monumentDynasties = await this.context.legacyDb.query<LegacyMonumentDynasty>(
      'SELECT * FROM mwnf3.monuments_dynasties ORDER BY dynasty_id'
    );

    this.logInfo(
      `Found ${objectDynasties.length} object-dynasty links and ${monumentDynasties.length} monument-dynasty links`
    );

    // Process object-dynasty links
    for (const link of objectDynasties) {
      try {
        const dynastyBackwardCompat = `mwnf3:dynasties:${link.dynasty_id}`;
        const itemBackwardCompat = `mwnf3:objects:${link.project_id}:${link.country}:${link.museum_id}:${link.number}`;

        const dynastyId = await this.getEntityUuidAsync(dynastyBackwardCompat, 'dynasty');
        const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');

        if (!dynastyId) {
          this.logWarning(`Dynasty not found for backward_compatibility: ${dynastyBackwardCompat}`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        if (!itemId) {
          this.logWarning(`Item not found for backward_compatibility: ${itemBackwardCompat}`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        if (this.isDryRun || this.isSampleOnlyMode) {
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.writeItemDynasty({
          item_id: itemId,
          dynasty_id: dynastyId,
        });

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Object-dynasty link ${link.dynasty_id}: ${message}`);
        this.showError();
      }
    }

    // Process monument-dynasty links
    for (const link of monumentDynasties) {
      try {
        const dynastyBackwardCompat = `mwnf3:dynasties:${link.dynasty_id}`;
        const itemBackwardCompat = `mwnf3:monuments:${link.project_id}:${link.country}:${link.institution_id}:${link.number}`;

        const dynastyId = await this.getEntityUuidAsync(dynastyBackwardCompat, 'dynasty');
        const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');

        if (!dynastyId) {
          this.logWarning(`Dynasty not found for backward_compatibility: ${dynastyBackwardCompat}`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        if (!itemId) {
          this.logWarning(`Item not found for backward_compatibility: ${itemBackwardCompat}`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        if (this.isDryRun || this.isSampleOnlyMode) {
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.writeItemDynasty({
          item_id: itemId,
          dynasty_id: dynastyId,
        });

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Monument-dynasty link ${link.dynasty_id}: ${message}`);
        this.showError();
      }
    }

    return result;
  }
}
