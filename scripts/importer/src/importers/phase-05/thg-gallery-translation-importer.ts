/**
 * THG Gallery Translation Importer
 *
 * Imports exhibition_i18n entries as CollectionTranslation records.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.exhibition_i18n (gallery_id, language_id, title, subtitle, heading, about)
 *
 * New schema:
 * - collection_translations (id, collection_id, language_id, context_id, title, description, backward_compatibility)
 *
 * Note: Combines subtitle and heading into description field.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';

/**
 * Legacy exhibition_i18n structure
 */
interface LegacyExhibitionI18n {
  gallery_id: number;
  language_id: string; // 2-letter code
  title: string | null;
  subtitle: string | null;
  heading: string | null;
  about: string | null;
  enabled: 'Y' | 'N';
}

export class ThgGalleryTranslationImporter extends BaseImporter {
  getName(): string {
    return 'ThgGalleryTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing gallery translations from exhibition_i18n...');

      // Query translations from legacy database
      const translations = await this.context.legacyDb.query<LegacyExhibitionI18n>(
        `SELECT gallery_id, language_id, title, subtitle, heading, about, enabled
         FROM mwnf3_thematic_gallery.exhibition_i18n
         ORDER BY gallery_id, language_id`
      );

      this.logInfo(`Found ${translations.length} gallery translations to import`);

      for (const legacy of translations) {
        try {
          // Map 2-letter to 3-letter language code
          const languageId = mapLanguageCode(legacy.language_id);
          if (!languageId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: Unknown language code '${legacy.language_id}'`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Check if language exists in tracker
          if (!this.entityExists(languageId, 'language')) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: Language '${languageId}' not found in tracker`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const galleryBackwardCompat = `thg_gallery.${legacy.gallery_id}`;
          const backwardCompat = `thg_exhibition_i18n.${legacy.gallery_id}.${legacy.language_id}`;

          // Check if already exists
          // Note: We can't directly check collection_translation by backward_compatibility easily
          // So we'll rely on the import process to handle duplicates

          // Get the collection ID
          const collectionId = this.getEntityUuid(galleryBackwardCompat, 'collection');
          if (!collectionId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: Collection not found. Run ThgGalleryImporter first.`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the context ID (same as the gallery's context)
          const contextId = this.getEntityUuid(galleryBackwardCompat, 'context');
          if (!contextId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${legacy.gallery_id}: Context not found. Run ThgGalleryContextImporter first.`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Build title (use title or fallback)
          const title = legacy.title || `Gallery ${legacy.gallery_id}`;

          // Build description from subtitle, heading, and about
          const descriptionParts: string[] = [];
          if (legacy.subtitle) {
            descriptionParts.push(legacy.subtitle);
          }
          if (legacy.heading) {
            descriptionParts.push(legacy.heading);
          }
          if (legacy.about) {
            descriptionParts.push(legacy.about);
          }
          const description = descriptionParts.join('\n\n') || null;

          // Collect sample
          this.collectSample(
            'thg_gallery_translation',
            legacy as unknown as Record<string, unknown>,
            'success',
            undefined,
            languageId
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create collection translation: ${title} (${backwardCompat})`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write collection translation using strategy
          await this.context.strategy.writeCollectionTranslation({
            collection_id: collectionId,
            language_id: languageId,
            context_id: contextId,
            title: title,
            description: description,
            backward_compatibility: backwardCompat,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Gallery ${legacy.gallery_id} (${legacy.language_id}): ${message}`
          );
          this.logError(`Gallery ${legacy.gallery_id} (${legacy.language_id})`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgGalleryTranslationImporter', error);
    }

    return result;
  }
}
