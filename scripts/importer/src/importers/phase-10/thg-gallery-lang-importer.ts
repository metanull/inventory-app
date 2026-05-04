/**
 * THG Gallery Lang Importer
 *
 * Imports thg_gallery_lang entries as CollectionTranslation records.
 * This covers base gallery/exhibition translations shared by both galleries and exhibitions.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.thg_gallery_lang (gallery_id, lang, title, ...)
 *
 * New schema:
 * - collection_translations (id, collection_id, language_id, context_id, title, description, backward_compatibility)
 *
 * This importer runs for ALL thg_gallery rows (both galleries and exhibitions).
 * Exhibition-specific extra data from exhibition_i18n is handled by ThgExhibitionTranslationImporter.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy thg_gallery_lang structure
 * PK: (gallery_id, lang)
 */
interface LegacyThgGalleryLang {
  gallery_id: number;
  lang: string; // 2-char language code
  title: string | null;
  subtitle: string | null;
  description: string | null;
}

export class ThgGalleryLangImporter extends BaseImporter {
  getName(): string {
    return 'ThgGalleryLangImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing gallery base translations from thg_gallery_lang...');

      // Query base gallery translations
      const rows = await this.context.legacyDb.query<LegacyThgGalleryLang>(
        `SELECT gallery_id, lang, title, subtitle, description
         FROM mwnf3_thematic_gallery.thg_gallery_lang
         ORDER BY gallery_id, lang`
      );

      this.logInfo(`Found ${rows.length} gallery lang rows to import`);

      for (const legacy of rows) {
        try {
          if (!legacy.lang) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: translation row has no language value (table: thg_gallery_lang, pk: gallery_id=${legacy.gallery_id}, lang=${legacy.lang}), skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const languageId = await this.getLanguageIdByLegacyCodeAsync(legacy.lang);
          if (!languageId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: unknown language '${legacy.lang}' in thg_gallery_lang`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const galleryBackwardCompat = `mwnf3_thematic_gallery:thg_gallery:${legacy.gallery_id}`;
          const backwardCompat = `mwnf3_thematic_gallery:thg_gallery_lang:${legacy.gallery_id}:${legacy.lang}`;

          if (await this.entityExistsAsync(backwardCompat, 'collection_translation')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const collectionId = await this.getEntityUuidAsync(galleryBackwardCompat, 'collection');
          if (!collectionId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: collection not found — run ThgGalleryImporter first`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const contextId = await this.getEntityUuidAsync(galleryBackwardCompat, 'context');
          if (!contextId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: context not found — run ThgGalleryContextImporter first`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const title = legacy.title || `Gallery ${legacy.gallery_id}`;

          const descriptionParts: string[] = [];
          if (legacy.subtitle) descriptionParts.push(legacy.subtitle);
          if (legacy.description) descriptionParts.push(legacy.description);
          const description = descriptionParts.join('\n\n') || null;

          this.collectSample(
            'thg_gallery_lang',
            legacy as unknown as Record<string, unknown>,
            'success',
            undefined,
            languageId
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create gallery lang translation: ${title} (${backwardCompat})`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          await this.context.strategy.writeCollectionTranslation({
            collection_id: collectionId,
            language_id: languageId,
            context_id: contextId,
            title,
            description,
            backward_compatibility: backwardCompat,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Gallery ${legacy.gallery_id} (${legacy.lang}): ${message}`
          );
          this.logError(`Gallery ${legacy.gallery_id} (${legacy.lang})`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgGalleryLangImporter', message);
    }

    return result;
  }
}
