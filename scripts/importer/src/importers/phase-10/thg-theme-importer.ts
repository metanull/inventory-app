/**
 * THG Theme Importer
 *
 * Imports theme entries as child Collection records (type='theme').
 * This replaces the separate Theme model with Collections for consistency
 * with Explore and Travels importers.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.theme (gallery_id, theme_id, parent_theme_id, display_order)
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, backward_compatibility)
 *   - type = 'theme' for all themes (hierarchy determined by parent_id)
 *   - parent_id = gallery collection for root themes, parent theme collection for subthemes
 *
 * Backward compatibility: mwnf3_thematic_gallery:theme:{gallery_id}:{theme_id}
 *
 * Dependencies:
 * - ThgGalleryImporter (must run first to create gallery collections)
 * - ThgGalleryContextImporter (for context reference)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy theme structure
 */
interface LegacyTheme {
  gallery_id: number;
  theme_id: number;
  parent_theme_id: number | null;
  display_order: number;
}

export class ThgThemeImporter extends BaseImporter {
  getName(): string {
    return 'ThgThemeImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing themes as child collections...');

      // Get default language ID
      const defaultLanguageId = await this.getDefaultLanguageIdAsync();

      // Query themes from legacy database
      // First pass: Import themes without parents (parent_theme_id IS NULL)
      // Second pass: Import themes with parents (after parent themes exist)
      const themes = await this.context.legacyDb.query<LegacyTheme>(
        `SELECT gallery_id, theme_id, parent_theme_id, display_order
         FROM mwnf3_thematic_gallery.theme
         ORDER BY gallery_id, parent_theme_id IS NOT NULL, display_order, theme_id`
      );

      this.logInfo(`Found ${themes.length} themes to import as collections`);

      // Two-pass import: first themes without parents, then themes with parents
      for (const legacy of themes) {
        try {
          const backwardCompat = `mwnf3_thematic_gallery:theme:${legacy.gallery_id}:${legacy.theme_id}`;

          // Check if already exists as collection (use async for database fallback)
          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the gallery collection ID (use async for database fallback)
          const galleryBackwardCompat = `mwnf3_thematic_gallery:thg_gallery:${legacy.gallery_id}`;
          const galleryCollectionId = await this.getEntityUuidAsync(
            galleryBackwardCompat,
            'collection'
          );
          if (!galleryCollectionId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme ${legacy.gallery_id}.${legacy.theme_id}: Gallery collection not found. Run ThgGalleryImporter first.`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the gallery's context ID (use async for database fallback)
          const contextId = await this.getEntityUuidAsync(galleryBackwardCompat, 'context');
          if (!contextId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme ${legacy.gallery_id}.${legacy.theme_id}: Gallery context not found. Run ThgGalleryContextImporter first.`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Determine parent_id based on whether this is a root theme or subtheme
          // All themes use type='theme', hierarchy is determined by parent_id
          let parentId: string;

          if (legacy.parent_theme_id === null) {
            // Root theme - parent is the gallery collection
            parentId = galleryCollectionId;
          } else {
            // Subtheme - parent is the parent theme collection
            const parentBackwardCompat = `mwnf3_thematic_gallery:theme:${legacy.gallery_id}:${legacy.parent_theme_id}`;
            const parentThemeId = await this.getEntityUuidAsync(parentBackwardCompat, 'collection');
            if (!parentThemeId) {
              // Parent theme not yet imported - this should be rare with sorted query
              result.warnings = result.warnings || [];
              result.warnings.push(
                `Theme ${legacy.gallery_id}.${legacy.theme_id}: Parent theme ${legacy.parent_theme_id} not found`
              );
              result.skipped++;
              this.showSkipped();
              continue;
            }
            parentId = parentThemeId;
          }

          // Create internal name (use 'theme' for all, subthemes distinguished by parent_id)
          const internalName = `theme_${legacy.gallery_id}_${legacy.theme_id}`;

          // Collect sample
          this.collectSample(
            'thg_theme_collection',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create theme collection: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'collection');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write theme as collection using strategy
          const collectionId = await this.context.strategy.writeCollection({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            context_id: contextId,
            language_id: defaultLanguageId,
            parent_id: parentId,
            type: 'theme',
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Theme ${legacy.gallery_id}.${legacy.theme_id}: ${message}`);
          this.logError(`Theme ${legacy.gallery_id}.${legacy.theme_id}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgThemeImporter', message);
    }

    return result;
  }
}
