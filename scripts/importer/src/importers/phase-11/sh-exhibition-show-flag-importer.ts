/**
 * Sharing History Exhibition Show-Flag Importer
 *
 * Stamps the legacy `sh_exhibitions.show` publication flag (and related list
 * metadata) into `collection_translations.extra.legacy_exhibition` for every
 * SH exhibition collection — mirroring what
 * Mwnf3ExhibitionTranslationImporter persists for mwnf3 exhibitions.
 *
 * Why: exporters exclude unpublished exhibitions structurally via
 * `collection_translations.extra.legacy_exhibition.show === 'n'`
 * (computeUnpublishedExhibitionSubtree). ShExhibitionImporter reads `show`
 * from the legacy table but never persisted it, so hidden SH exhibitions
 * (e.g. AWE exhibition 2 "Political Context") would leak into a data-package.
 *
 * The flag is MERGED into the existing `extra` of every translation row of
 * the collection (all languages): other keys — bibliography, see-also,
 * quotation, ... — are preserved, and an existing `legacy_exhibition` object
 * is extended rather than replaced.
 *
 * Standalone (`--only sh-exhibition-show-flag`) and idempotent; also safe at
 * the end of a full fresh import.
 *
 * Dependencies:
 * - ShExhibitionImporter (exhibition collections must exist)
 * - ShExhibitionTranslationImporter (translation rows must exist to stamp)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

const SH_SCHEMA = 'mwnf3_sharing_history';

interface ShLegacyExhibitionFlagRow {
  exhibition_id: number;
  project_id: string;
  show: string | null;
  new_status: string | null;
}

export class ShExhibitionShowFlagImporter extends BaseImporter {
  getName(): string {
    return 'ShExhibitionShowFlagImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Stamping legacy show flags on SH exhibition collection translations...');

      const exhibitions = await this.context.legacyDb.query<ShLegacyExhibitionFlagRow>(
        `SELECT exhibition_id, project_id, \`show\`, new_status
         FROM ${SH_SCHEMA}.sh_exhibitions
         ORDER BY project_id, exhibition_id`
      );

      this.logInfo(`Found ${exhibitions.length} SH exhibitions`);

      for (const legacy of exhibitions) {
        try {
          await this.stampExhibition(legacy, result);
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`SH exhibition ${legacy.exhibition_id}: ${message}`);
          this.logError(`SH exhibition ${legacy.exhibition_id}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to stamp SH exhibition show flags: ${message}`);
      this.logError('ShExhibitionShowFlagImporter', message);
      this.showError();
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async stampExhibition(
    legacy: ShLegacyExhibitionFlagRow,
    result: ImportResult
  ): Promise<void> {
    const collectionBackwardCompat = `${SH_SCHEMA}:sh_exhibitions:${legacy.exhibition_id}`;
    const collectionId = await this.getEntityUuidAsync(collectionBackwardCompat, 'collection');

    if (!collectionId) {
      this.logWarning(
        `SH exhibition collection not found (${collectionBackwardCompat}), skipping — run ShExhibitionImporter first`
      );
      result.skipped++;
      this.showSkipped();
      return;
    }

    // Same shape as Mwnf3ExhibitionTranslationImporter's extra.legacy_exhibition.
    const legacyExhibition: Record<string, unknown> = {};
    if (legacy.project_id) legacyExhibition.project_id = legacy.project_id;
    if (legacy.show) legacyExhibition.show = legacy.show;
    if (legacy.new_status) legacyExhibition.new_status = legacy.new_status;

    if (Object.keys(legacyExhibition).length === 0) {
      result.skipped++;
      this.showSkipped();
      return;
    }

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would stamp legacy_exhibition ${JSON.stringify(legacyExhibition)} on ${collectionBackwardCompat}`
      );
      result.imported++;
      this.showProgress();
      return;
    }

    const languageIds = await this.context.strategy.getCollectionTranslationLanguages(collectionId);
    if (languageIds.length === 0) {
      this.logWarning(
        `No translations found for ${collectionBackwardCompat} — run ShExhibitionTranslationImporter first`
      );
      result.skipped++;
      this.showSkipped();
      return;
    }

    let changed = false;
    for (const langId of languageIds) {
      const existing = await this.context.strategy.getCollectionTranslationExtra(
        collectionId,
        langId
      );
      const existingLegacyExhibition =
        existing && typeof existing.legacy_exhibition === 'object' && existing.legacy_exhibition
          ? (existing.legacy_exhibition as Record<string, unknown>)
          : {};

      const mergedLegacyExhibition = { ...existingLegacyExhibition, ...legacyExhibition };

      // Idempotency: skip the write when nothing would change.
      if (JSON.stringify(existingLegacyExhibition) === JSON.stringify(mergedLegacyExhibition)) {
        continue;
      }

      const merged = { ...(existing || {}), legacy_exhibition: mergedLegacyExhibition };
      await this.context.strategy.setCollectionTranslationExtra(
        collectionId,
        langId,
        JSON.stringify(merged)
      );
      changed = true;
    }

    if (changed) {
      this.logInfo(`Stamped legacy_exhibition on ${collectionBackwardCompat}`);
      result.imported++;
      this.showProgress();
    } else {
      result.skipped++;
      this.showSkipped();
    }
  }
}
