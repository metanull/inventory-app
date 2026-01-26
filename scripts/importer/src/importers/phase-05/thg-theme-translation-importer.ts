/**
 * THG Theme Translation Importer
 *
 * Imports theme_i18n entries as ThemeTranslation records.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.theme_i18n (gallery_id, theme_id, language_id, title, quote, presentation)
 *
 * New schema:
 * - theme_translations (id, theme_id, language_id, context_id, title, description, introduction, backward_compatibility)
 *
 * Mapping:
 * - title → title
 * - quote → introduction
 * - presentation → description
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';

/**
 * Legacy theme_i18n structure
 */
interface LegacyThemeI18n {
  gallery_id: number;
  theme_id: number;
  language_id: string; // 2-letter code
  title: string | null;
  quote: string | null;
  presentation: string | null;
}

export class ThgThemeTranslationImporter extends BaseImporter {
  getName(): string {
    return 'ThgThemeTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing theme translations from theme_i18n...');

      // Query translations from legacy database
      const translations = await this.context.legacyDb.query<LegacyThemeI18n>(
        `SELECT gallery_id, theme_id, language_id, title, quote, presentation
         FROM mwnf3_thematic_gallery.theme_i18n
         ORDER BY gallery_id, theme_id, language_id`
      );

      this.logInfo(`Found ${translations.length} theme translations to import`);

      for (const legacy of translations) {
        try {
          // Map 2-letter to 3-letter language code
          const languageId = mapLanguageCode(legacy.language_id);
          if (!languageId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme ${legacy.gallery_id}.${legacy.theme_id}: Unknown language code '${legacy.language_id}'`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Check if language exists in tracker
          if (!(await this.entityExistsAsync(languageId, 'language'))) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme ${legacy.gallery_id}.${legacy.theme_id}: Language '${languageId}' not found`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const themeBackwardCompat = `thg_theme.${legacy.gallery_id}.${legacy.theme_id}`;
          const backwardCompat = `thg_theme_i18n.${legacy.gallery_id}.${legacy.theme_id}.${legacy.language_id}`;

          // Get the theme ID (use async for database fallback)
          const themeId = await this.getEntityUuidAsync(themeBackwardCompat, 'theme');
          if (!themeId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme ${legacy.gallery_id}.${legacy.theme_id}: Theme not found. Run ThgThemeImporter first.`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the context ID for this gallery (use async for database fallback)
          const galleryBackwardCompat = `thg_gallery.${legacy.gallery_id}`;
          const contextId = await this.getEntityUuidAsync(galleryBackwardCompat, 'context');
          if (!contextId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme ${legacy.gallery_id}.${legacy.theme_id}: Context not found. Run ThgGalleryContextImporter first.`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Build title (use title or fallback)
          const title = legacy.title || `Theme ${legacy.theme_id}`;

          // Collect sample
          this.collectSample(
            'thg_theme_translation',
            legacy as unknown as Record<string, unknown>,
            'success',
            undefined,
            languageId
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create theme translation: ${title} (${backwardCompat})`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write theme translation using strategy
          await this.context.strategy.writeThemeTranslation({
            theme_id: themeId,
            language_id: languageId,
            context_id: contextId,
            title: title,
            description: legacy.presentation ?? null,
            introduction: legacy.quote ?? null,
            backward_compatibility: backwardCompat,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Theme ${legacy.gallery_id}.${legacy.theme_id} (${legacy.language_id}): ${message}`
          );
          this.logError(
            `Theme ${legacy.gallery_id}.${legacy.theme_id} (${legacy.language_id})`,
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
      this.logError('ThgThemeTranslationImporter', error);
    }

    return result;
  }
}
