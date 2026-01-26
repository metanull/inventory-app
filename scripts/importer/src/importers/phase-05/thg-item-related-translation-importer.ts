/**
 * THG Item Related Translation Importer
 *
 * Imports theme_item_related_i18n entries as ItemItemLinkTranslation records.
 * Provides multilingual descriptions for item-to-item links.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.theme_item_related_i18n (gallery_id, theme_id, item_id, related_item_id, language_id, description)
 *
 * New schema:
 * - item_item_link_translations (id, item_item_link_id, language_id, description, reciprocal_description, backward_compatibility)
 *
 * Note: description describes source → target direction
 * reciprocal_description would describe target → source (if available)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';

/**
 * Legacy theme_item_related_i18n structure
 */
interface LegacyThemeItemRelatedI18n {
  gallery_id: number;
  theme_id: number;
  item_id: number;
  related_item_id: number;
  language_id: string; // 2-letter code
  description: string | null;
}

export class ThgItemRelatedTranslationImporter extends BaseImporter {
  getName(): string {
    return 'ThgItemRelatedTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing item-item link translations from theme_item_related_i18n...');

      // Query translations from legacy database
      const translations = await this.context.legacyDb.query<LegacyThemeItemRelatedI18n>(
        `SELECT gallery_id, theme_id, item_id, related_item_id, language_id, description
         FROM mwnf3_thematic_gallery.theme_item_related_i18n
         WHERE description IS NOT NULL AND description != ''
         ORDER BY gallery_id, theme_id, item_id, related_item_id, language_id`
      );

      this.logInfo(`Found ${translations.length} item link translations to import`);

      for (const legacy of translations) {
        try {
          // Map 2-letter to 3-letter language code
          const languageId = mapLanguageCode(legacy.language_id);
          if (!languageId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Item link ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}->${legacy.related_item_id}: Unknown language code '${legacy.language_id}'`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Check if language exists in tracker or database (languages are from Phase 00)
          if (!(await this.entityExistsAsync(languageId, 'language'))) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Item link ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}->${legacy.related_item_id}: Language '${languageId}' not found`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the item-item link ID (use async for database fallback)
          const linkBackwardCompat = `thg_item_related.${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}.${legacy.related_item_id}`;
          const linkId = await this.getEntityUuidAsync(linkBackwardCompat, 'item_item_link');
          if (!linkId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Item link translation ${linkBackwardCompat}: Link not found. Run ThgItemRelatedImporter first.`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const backwardCompat = `thg_item_related_i18n.${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}.${legacy.related_item_id}.${legacy.language_id}`;

          // Collect sample
          this.collectSample(
            'thg_item_related_translation',
            legacy as unknown as Record<string, unknown>,
            'success',
            undefined,
            languageId
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create item link translation: ${backwardCompat}`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write item-item link translation using strategy
          await this.context.strategy.writeItemItemLinkTranslation({
            item_item_link_id: linkId,
            language_id: languageId,
            description: legacy.description,
            reciprocal_description: null, // Not provided in legacy data
            backward_compatibility: backwardCompat,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Item link translation ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}->${legacy.related_item_id} (${legacy.language_id}): ${message}`
          );
          this.logError(
            `Item link translation ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}->${legacy.related_item_id} (${legacy.language_id})`,
            error
          );
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgItemRelatedTranslationImporter', error);
    }

    return result;
  }
}
