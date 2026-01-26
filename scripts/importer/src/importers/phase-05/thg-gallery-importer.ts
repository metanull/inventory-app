/**
 * THG Gallery Importer
 *
 * Imports thg_gallery entries as Collection records (type='gallery' or 'exhibition').
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.thg_gallery (gallery_id, project_id, name, etc.)
 *
 * New schema:
 * - collections (id, type, context_id, language_id, internal_name, backward_compatibility)
 *
 * Collection type: 'gallery' (THG project) or 'exhibition' (EXH project)
 * Context: Uses the context created by ThgGalleryContextImporter
 * Backward compatibility: thg_gallery.{gallery_id}
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy thg_gallery structure
 */
interface LegacyThgGallery {
  gallery_id: number;
  project_id: string | null;
  name: string;
  link: string | null;
  sort_order: number;
  status: 'A' | 'H';
}

export class ThgGalleryImporter extends BaseImporter {
  private exhibitionProjectIds: Set<string> = new Set(['EXH']);

  getName(): string {
    return 'ThgGalleryImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing thematic galleries as collections...');

      // Get default language ID (use async for database fallback when starting from later phases)
      const defaultLanguageId = await this.getDefaultLanguageIdAsync();

      // Query galleries from legacy database
      const galleries = await this.context.legacyDb.query<LegacyThgGallery>(
        'SELECT gallery_id, project_id, name, link, sort_order, status FROM mwnf3_thematic_gallery.thg_gallery ORDER BY sort_order, gallery_id'
      );

      this.logInfo(`Found ${galleries.length} galleries to import as collections`);

      for (const legacy of galleries) {
        try {
          const backwardCompat = `thg_gallery.${legacy.gallery_id}`;

          // Check if already exists as collection (use async for database fallback)
          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the corresponding context (created by ThgGalleryContextImporter or already in DB)
          const contextId = await this.getEntityUuidAsync(backwardCompat, 'context');
          if (!contextId) {
            result.errors.push(
              `Gallery ${legacy.gallery_id}: Context not found. Run ThgGalleryContextImporter first.`
            );
            this.showError();
            continue;
          }

          // Determine collection type based on project_id
          const isExhibition = legacy.project_id
            ? this.exhibitionProjectIds.has(legacy.project_id)
            : false;
          const collectionType = isExhibition ? 'exhibition' : 'gallery';

          // Create internal name from link or name (slugified)
          const slug = this.slugify(legacy.link || legacy.name);
          const internalName = `${collectionType}_${slug}`;

          // Collect sample
          this.collectSample(
            'thg_gallery_collection',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create collection: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'collection');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write collection using strategy
          // Note: Collections need context_id, language_id, and optionally parent_id
          const collectionId = await this.context.strategy.writeCollection({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            context_id: contextId,
            language_id: defaultLanguageId,
            parent_id: null, // parent_gallery_id is always null in legacy data
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Gallery ${legacy.gallery_id}: ${message}`);
          this.logError(`Gallery ${legacy.gallery_id}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgGalleryImporter', error);
    }

    return result;
  }

  /**
   * Convert a string to a URL-friendly slug
   */
  private slugify(text: string): string {
    return text
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '_')
      .replace(/^-+|-+$/g, '');
  }
}
