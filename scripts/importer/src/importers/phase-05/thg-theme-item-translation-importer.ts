/**
 * THG Theme Item Translation Importer
 *
 * Imports theme_item_i18n entries as ItemTranslation records with contextual descriptions.
 * These are contextual descriptions specific to how an item appears within a gallery.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.theme_item_i18n (gallery_id, theme_id, item_id, language_id, contextual_description)
 *
 * New schema:
 * - item_translations (id, item_id, language_id, context_id, description, backward_compatibility)
 *
 * Context: Uses the gallery's context (created by ThgGalleryContextImporter)
 * This creates item translations specific to the gallery context.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';

/**
 * Legacy theme_item_i18n structure
 */
interface LegacyThemeItemI18n {
  gallery_id: number;
  theme_id: number;
  item_id: number;
  language_id: string; // 2-letter code
  contextual_description: string | null;
}

/**
 * Legacy theme_item structure for item resolution
 */
interface LegacyThemeItem {
  gallery_id: number;
  theme_id: number;
  item_id: number;
  // mwnf3 object references
  mwnf3_object_project_id: string | null;
  mwnf3_object_country_id: string | null;
  mwnf3_object_partner_id: string | null;
  mwnf3_object_item_id: number | null;
  // mwnf3 monument references
  mwnf3_monument_project_id: string | null;
  mwnf3_monument_country_id: string | null;
  mwnf3_monument_partner_id: string | null;
  mwnf3_monument_item_id: number | null;
  // mwnf3 monument detail references
  mwnf3_monument_detail_project_id: string | null;
  mwnf3_monument_detail_country_id: string | null;
  mwnf3_monument_detail_partner_id: string | null;
  mwnf3_monument_detail_item_id: number | null;
  mwnf3_monument_detail_detail_id: number | null;
}

export class ThgThemeItemTranslationImporter extends BaseImporter {
  // Cache theme_item data for item resolution
  private themeItemCache: Map<string, LegacyThemeItem> = new Map();

  getName(): string {
    return 'ThgThemeItemTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Loading theme_item data for item resolution...');

      // Load theme_item data to resolve item references
      const themeItems = await this.context.legacyDb.query<LegacyThemeItem>(
        `SELECT gallery_id, theme_id, item_id,
                mwnf3_object_project_id, mwnf3_object_country_id, mwnf3_object_partner_id, mwnf3_object_item_id,
                mwnf3_monument_project_id, mwnf3_monument_country_id, mwnf3_monument_partner_id, mwnf3_monument_item_id,
                mwnf3_monument_detail_project_id, mwnf3_monument_detail_country_id, mwnf3_monument_detail_partner_id,
                mwnf3_monument_detail_item_id, mwnf3_monument_detail_detail_id
         FROM mwnf3_thematic_gallery.theme_item`
      );

      for (const item of themeItems) {
        const key = `${item.gallery_id}.${item.theme_id}.${item.item_id}`;
        this.themeItemCache.set(key, item);
      }

      this.logInfo(`Loaded ${this.themeItemCache.size} theme_item records`);
      this.logInfo('Importing contextual item translations from theme_item_i18n...');

      // Query translations from legacy database
      const translations = await this.context.legacyDb.query<LegacyThemeItemI18n>(
        `SELECT gallery_id, theme_id, item_id, language_id, contextual_description
         FROM mwnf3_thematic_gallery.theme_item_i18n
         WHERE contextual_description IS NOT NULL AND contextual_description != ''
         ORDER BY gallery_id, theme_id, item_id, language_id`
      );

      this.logInfo(`Found ${translations.length} contextual item translations to import`);

      for (const legacy of translations) {
        try {
          // Map 2-letter to 3-letter language code
          const languageId = mapLanguageCode(legacy.language_id);
          if (!languageId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme item ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}: Unknown language code '${legacy.language_id}'`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Check if language exists in tracker
          if (!this.entityExists(languageId, 'language')) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme item ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}: Language '${languageId}' not found in tracker`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get theme_item for item resolution
          const themeItemKey = `${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}`;
          const themeItem = this.themeItemCache.get(themeItemKey);
          if (!themeItem) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme item ${themeItemKey}: theme_item record not found`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve the item reference
          const itemBackwardCompat = this.resolveItemReference(themeItem);
          if (!itemBackwardCompat) {
            // Not an mwnf3 item - skip
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the item ID from tracker
          const itemId = this.getEntityUuid(itemBackwardCompat, 'item');
          if (!itemId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme item ${themeItemKey}: Item not found (${itemBackwardCompat})`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the context ID for this gallery
          const galleryBackwardCompat = `thg_gallery.${legacy.gallery_id}`;
          const contextId = this.getEntityUuid(galleryBackwardCompat, 'context');
          if (!contextId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme item ${themeItemKey}: Context not found for gallery ${legacy.gallery_id}`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const backwardCompat = `thg_theme_item_i18n.${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}.${legacy.language_id}`;

          // Collect sample
          this.collectSample(
            'thg_theme_item_translation',
            {
              ...legacy,
              resolved_item_backward_compat: itemBackwardCompat,
            } as unknown as Record<string, unknown>,
            'success',
            undefined,
            languageId
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create contextual item translation: ${backwardCompat}`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write item translation using strategy
          // Note: This creates a context-specific translation for the item
          await this.context.strategy.writeItemTranslation({
            item_id: itemId,
            language_id: languageId,
            context_id: contextId,
            backward_compatibility: backwardCompat,
            name: '', // Name is not provided in contextual descriptions
            description: legacy.contextual_description || '',
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Theme item ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id} (${legacy.language_id}): ${message}`
          );
          this.logError(
            `Theme item ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id} (${legacy.language_id})`,
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
      this.logError('ThgThemeItemTranslationImporter', error);
    }

    return result;
  }

  /**
   * Resolve item reference from theme_item to backward_compatibility string
   * Returns null if not an mwnf3 item
   */
  private resolveItemReference(legacy: LegacyThemeItem): string | null {
    // Check mwnf3_object reference
    if (
      legacy.mwnf3_object_project_id &&
      legacy.mwnf3_object_country_id &&
      legacy.mwnf3_object_partner_id &&
      legacy.mwnf3_object_item_id !== null
    ) {
      return `mwnf3_object.${legacy.mwnf3_object_project_id}.${legacy.mwnf3_object_country_id}.${legacy.mwnf3_object_partner_id}.${legacy.mwnf3_object_item_id}`;
    }

    // Check mwnf3_monument reference
    if (
      legacy.mwnf3_monument_project_id &&
      legacy.mwnf3_monument_country_id &&
      legacy.mwnf3_monument_partner_id &&
      legacy.mwnf3_monument_item_id !== null
    ) {
      return `mwnf3_monument.${legacy.mwnf3_monument_project_id}.${legacy.mwnf3_monument_country_id}.${legacy.mwnf3_monument_partner_id}.${legacy.mwnf3_monument_item_id}`;
    }

    // Check mwnf3_monument_detail reference
    if (
      legacy.mwnf3_monument_detail_project_id &&
      legacy.mwnf3_monument_detail_country_id &&
      legacy.mwnf3_monument_detail_partner_id &&
      legacy.mwnf3_monument_detail_item_id !== null &&
      legacy.mwnf3_monument_detail_detail_id !== null
    ) {
      return `mwnf3_monument_detail.${legacy.mwnf3_monument_detail_project_id}.${legacy.mwnf3_monument_detail_country_id}.${legacy.mwnf3_monument_detail_partner_id}.${legacy.mwnf3_monument_detail_item_id}.${legacy.mwnf3_monument_detail_detail_id}`;
    }

    // Not an mwnf3 item
    return null;
  }
}
