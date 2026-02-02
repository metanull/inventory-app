/**
 * THG Gallery Context Importer
 *
 * Creates Context records for each thg_gallery entry.
 * Each gallery/exhibition gets its own context to enable contextual translations.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.thg_gallery (gallery_id, name, project_id)
 * - mwnf3_thematic_gallery.thg_projects (project_id) - determines gallery vs exhibition
 *
 * New schema:
 * - contexts (id, internal_name, backward_compatibility, is_default)
 *
 * Context naming: {gallery|exhibition}_{name_slug}
 * Backward compatibility: mwnf3_thematic_gallery:thg_gallery:{gallery_id}
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
}

/**
 * Legacy thg_projects structure (for determining gallery vs exhibition type)
 */
interface LegacyThgProject {
  project_id: string;
}

export class ThgGalleryContextImporter extends BaseImporter {
  private exhibitionProjectIds: Set<string> = new Set();

  getName(): string {
    return 'ThgGalleryContextImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Loading project types to determine gallery vs exhibition...');

      // Load exhibition project IDs (project_id = 'EXH')
      // Note: THG = Gallery, EXH = Exhibition
      const projects = await this.context.legacyDb.query<LegacyThgProject>(
        "SELECT project_id FROM mwnf3_thematic_gallery.thg_projects WHERE project_id = 'EXH'"
      );

      for (const project of projects) {
        this.exhibitionProjectIds.add(project.project_id);
      }

      this.logInfo(`Found ${this.exhibitionProjectIds.size} exhibition project types`);
      this.logInfo('Importing contexts for thematic galleries...');

      // Query galleries from legacy database (excluding null parent_gallery_id check since they're all null)
      const galleries = await this.context.legacyDb.query<LegacyThgGallery>(
        'SELECT gallery_id, project_id, name, link FROM mwnf3_thematic_gallery.thg_gallery ORDER BY gallery_id'
      );

      this.logInfo(`Found ${galleries.length} galleries to create contexts for`);

      for (const legacy of galleries) {
        try {
          const backwardCompat = `mwnf3_thematic_gallery:thg_gallery:${legacy.gallery_id}`;

          // Check if already exists (use async for database fallback)
          if (await this.entityExistsAsync(backwardCompat, 'context')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Determine type based on project_id
          const isExhibition = legacy.project_id
            ? this.exhibitionProjectIds.has(legacy.project_id)
            : false;
          const typePrefix = isExhibition ? 'exhibition' : 'gallery';

          // Create internal name from link or name (slugified)
          const slug = this.slugify(legacy.link || legacy.name);
          const internalName = `${typePrefix}_${slug}`;

          // Collect sample
          this.collectSample(
            'thg_gallery_context',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create context: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'context');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write context using strategy
          const contextId = await this.context.strategy.writeContext({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            is_default: false,
          });

          this.registerEntity(contextId, backwardCompat, 'context');

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Gallery ${legacy.gallery_id}: ${message}`);
          this.logError(`Gallery ${legacy.gallery_id}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgGalleryContextImporter', message);
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
