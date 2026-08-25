/**
 * THG Gallery Native Project Importer
 *
 * Attaches the items of a gallery's native mwnf3 project to its gallery /
 * exhibition collection.
 *
 * Legacy visibility of a DXA gallery is a UNION, expressed in dxa-api as an SQL
 * OR predicate:
 *
 *   items whose mwnf3 project = thg_gallery.mwnf3_project_id
 *   OR items listed in the thg_gallery_* membership link tables
 *
 * The link-table branch is imported by the ThgGallery{Mwnf3,Sh,Travel,Explore}*
 * importers. This importer covers the native-project branch, which otherwise
 * leaves items visible on the legacy site missing from the imported collection
 * (53 objects on gallery 9/DCA, plus galleries 52, 55 and 56).
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.thg_gallery (gallery_id, mwnf3_project_id)
 * - mwnf3.objects   (project_id, country, museum_id, number)   — one row per language
 * - mwnf3.monuments (project_id, country, institution_id, number) — one row per language
 *
 * New schema:
 * - collection_item pivot (collection_id, item_id) via attachItemsToCollection
 *
 * Item backward_compatibility formats:
 * - mwnf3:objects:{project}:{country}:{museum}:{number}
 * - mwnf3:monuments:{project}:{country}:{institution}:{number}
 *
 * Dependencies:
 * - ThgGalleryImporter (gallery collections)
 * - ObjectImporter / MonumentImporter (the items themselves)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

interface LegacyGalleryProject {
  gallery_id: number;
  mwnf3_project_id: string | null;
}

interface LegacyNativeObject {
  project_id: string;
  country: string;
  museum_id: string;
  number: number;
}

interface LegacyNativeMonument {
  project_id: string;
  country: string;
  institution_id: string;
  number: number;
}

export class ThgGalleryNativeProjectImporter extends BaseImporter {
  getName(): string {
    return 'ThgGalleryNativeProjectImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Attaching native mwnf3 project items to THG gallery collections...');

      const galleries = await this.context.legacyDb.query<LegacyGalleryProject>(
        `SELECT gallery_id, mwnf3_project_id
         FROM mwnf3_thematic_gallery.thg_gallery
         WHERE mwnf3_project_id IS NOT NULL AND mwnf3_project_id != ''
         ORDER BY gallery_id`
      );

      this.logInfo(`Found ${galleries.length} galleries with a native mwnf3 project`);

      let skippedNoItem = 0;
      let skippedNoCollection = 0;

      for (const gallery of galleries) {
        const project = gallery.mwnf3_project_id!;

        try {
          const galleryBackwardCompat = `mwnf3_thematic_gallery:thg_gallery:${gallery.gallery_id}`;
          const collectionId = await this.getEntityUuidAsync(galleryBackwardCompat, 'collection');
          if (!collectionId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Gallery ${gallery.gallery_id}: Collection not found (${galleryBackwardCompat})`
            );
            skippedNoCollection++;
            continue;
          }

          const itemBackwardCompats = await this.loadNativeItemKeys(project);
          this.logInfo(
            `Gallery ${gallery.gallery_id} (${project}): ${itemBackwardCompats.length} native items`
          );

          const itemIds: string[] = [];
          const seen = new Set<string>();

          for (const itemBackwardCompat of itemBackwardCompats) {
            const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');
            if (!itemId) {
              result.warnings = result.warnings || [];
              result.warnings.push(
                `Gallery ${gallery.gallery_id}: native item not found (${itemBackwardCompat})`
              );
              skippedNoItem++;
              continue;
            }

            // attachItemsToCollection is idempotent per pair, but de-duplicate
            // within the batch so the same item is never sent twice.
            if (seen.has(itemId)) {
              continue;
            }
            seen.add(itemId);
            itemIds.push(itemId);

            this.collectSample(
              'thg_gallery_native_project_item',
              {
                gallery_id: gallery.gallery_id,
                mwnf3_project_id: project,
                resolved_item_backward_compat: itemBackwardCompat,
              },
              'success'
            );

            result.imported++;
            this.showProgress();
          }

          if (itemIds.length > 0 && !this.isDryRun && !this.isSampleOnlyMode) {
            await this.context.strategy.attachItemsToCollection(collectionId, itemIds);
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Gallery ${gallery.gallery_id} (${project}): ${message}`);
          this.logError(`Gallery ${gallery.gallery_id} native project ${project}`, message);
          this.showError();
        }
      }

      if (skippedNoItem > 0) {
        this.logInfo(`Skipped ${skippedNoItem} native items not found in tracker/database`);
      }
      if (skippedNoCollection > 0) {
        this.logInfo(`Skipped ${skippedNoCollection} galleries with missing collection`);
      }

      this.showSummary(
        result.imported,
        result.skipped + skippedNoItem + skippedNoCollection,
        result.errors.length
      );
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgGalleryNativeProjectImporter', message);
    }

    return result;
  }

  /**
   * Backward-compatibility keys of every object and monument belonging to a
   * legacy mwnf3 project.
   *
   * mwnf3.objects and mwnf3.monuments hold one row per language, so the
   * identity columns are selected DISTINCT — counting rows instead would
   * inflate the item count several-fold.
   */
  private async loadNativeItemKeys(project: string): Promise<string[]> {
    const objects = await this.context.legacyDb.query<LegacyNativeObject>(
      `SELECT DISTINCT project_id, country, museum_id, number
       FROM mwnf3.objects
       WHERE project_id = ?
       ORDER BY country, museum_id, number`,
      [project]
    );

    const monuments = await this.context.legacyDb.query<LegacyNativeMonument>(
      `SELECT DISTINCT project_id, country, institution_id, number
       FROM mwnf3.monuments
       WHERE project_id = ?
       ORDER BY country, institution_id, number`,
      [project]
    );

    return [
      ...objects.map(
        (row) => `mwnf3:objects:${row.project_id}:${row.country}:${row.museum_id}:${row.number}`
      ),
      ...monuments.map(
        (row) =>
          `mwnf3:monuments:${row.project_id}:${row.country}:${row.institution_id}:${row.number}`
      ),
    ];
  }
}
