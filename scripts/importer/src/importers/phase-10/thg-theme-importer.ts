/**
 * THG Theme Importer
 *
 * Imports theme entries as Theme records linked to Collections.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.theme (gallery_id, theme_id, parent_theme_id, display_order)
 *
 * New schema:
 * - themes (id, collection_id, parent_id, display_order, internal_name, backward_compatibility)
 *
 * Backward compatibility: mwnf3_thematic_gallery:theme:{gallery_id}:{theme_id}
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
      this.logInfo('Importing themes from thematic galleries...');

      // Query themes from legacy database
      // First pass: Import themes without parents (parent_theme_id IS NULL)
      // Second pass: Import themes with parents (after parent themes exist)
      const themes = await this.context.legacyDb.query<LegacyTheme>(
        `SELECT gallery_id, theme_id, parent_theme_id, display_order
         FROM mwnf3_thematic_gallery.theme
         ORDER BY gallery_id, parent_theme_id IS NOT NULL, display_order, theme_id`
      );

      this.logInfo(`Found ${themes.length} themes to import`);

      // Two-pass import: first themes without parents, then themes with parents
      for (const legacy of themes) {
        try {
          const backwardCompat = `mwnf3_thematic_gallery:theme:${legacy.gallery_id}:${legacy.theme_id}`;

          // Check if already exists (use async for database fallback)
          if (await this.entityExistsAsync(backwardCompat, 'theme')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the collection ID for this gallery (use async for database fallback)
          const galleryBackwardCompat = `mwnf3_thematic_gallery:thg_gallery:${legacy.gallery_id}`;
          const collectionId = await this.getEntityUuidAsync(galleryBackwardCompat, 'collection');
          if (!collectionId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme ${legacy.gallery_id}.${legacy.theme_id}: Collection not found. Run ThgGalleryImporter first.`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve parent theme ID if exists (use async for database fallback)
          let parentId: string | null = null;
          if (legacy.parent_theme_id !== null) {
            const parentBackwardCompat = `mwnf3_thematic_gallery:theme:${legacy.gallery_id}:${legacy.parent_theme_id}`;
            parentId = await this.getEntityUuidAsync(parentBackwardCompat, 'theme');
            if (!parentId) {
              // Parent theme not yet imported - this should be rare with sorted query
              result.warnings = result.warnings || [];
              result.warnings.push(
                `Theme ${legacy.gallery_id}.${legacy.theme_id}: Parent theme ${legacy.parent_theme_id} not found`
              );
              result.skipped++;
              this.showSkipped();
              continue;
            }
          }

          // Create internal name
          const internalName = `theme_${legacy.gallery_id}_${legacy.theme_id}`;

          // Collect sample
          this.collectSample('thg_theme', legacy as unknown as Record<string, unknown>, 'success');

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create theme: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'theme');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write theme using strategy
          const themeId = await this.context.strategy.writeTheme({
            collection_id: collectionId,
            parent_id: parentId,
            display_order: legacy.display_order,
            internal_name: internalName,
            backward_compatibility: backwardCompat,
          });

          this.registerEntity(themeId, backwardCompat, 'theme');

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
