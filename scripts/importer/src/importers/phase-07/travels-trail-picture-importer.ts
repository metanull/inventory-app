/**
 * Travels Trail Picture Importer
 *
 * Imports pictures from mwnf3.tr_trails_pictures as CollectionImages.
 *
 * Legacy schema:
 * - mwnf3.tr_trails_pictures (lang, country, project_id, trail_id, image_number, path, thumb, caption, photographer, copyright, lastupdate, type)
 *   - PK: (project_id, country, trail_id, image_number, type, lang)
 *   - Types: cover, map, title, or empty
 *
 * New schema:
 * - collection_images (collection_id, path, original_name, mime_type, size, alt_text, display_order)
 * - Picture type stored via picture_type field or extra metadata
 *
 * Dependencies:
 * - TravelsTrailImporter (must run first to create trail collections)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult, CollectionImageData } from '../../core/types.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';
import path from 'path';

/**
 * Legacy trail picture structure
 */
interface LegacyTrailPicture {
  lang: string;
  country: string;
  project_id: string;
  trail_id: number;
  image_number: number;
  path: string;
  thumb: string | null;
  caption: string;
  photographer: string;
  copyright: string;
  lastupdate: string | null;
  type: string; // cover, map, title, or empty
}

/**
 * Grouped picture (unique by non-lang keys)
 */
interface PictureGroup {
  project_id: string;
  country: string;
  trail_id: number;
  image_number: number;
  type: string;
  path: string;
  translations: LegacyTrailPicture[];
}

export class TravelsTrailPictureImporter extends BaseImporter {
  getName(): string {
    return 'TravelsTrailPictureImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing travel trail pictures...');

      // Query all trail pictures
      const pictures = await this.context.legacyDb.query<LegacyTrailPicture>(
        `SELECT lang, country, project_id, trail_id, image_number, path, thumb, caption, photographer, copyright, lastupdate, type
         FROM mwnf3.tr_trails_pictures
         ORDER BY project_id, country, trail_id, type, image_number, lang`
      );

      if (pictures.length === 0) {
        this.logInfo('No trail pictures found');
        return result;
      }

      // Group pictures by non-lang keys
      const groups = this.groupPictures(pictures);
      this.logInfo(`Found ${groups.length} unique pictures (${pictures.length} language rows)`);

      for (const group of groups) {
        try {
          const imported = await this.importPicture(group, result);
          if (imported) {
            result.imported++;
            this.showProgress();
          } else {
            result.skipped++;
            this.showSkipped();
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          const backwardCompat = this.getBackwardCompatibility(group);
          result.errors.push(`${backwardCompat}: ${message}`);
          this.logError(`Trail Picture ${backwardCompat}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query trail pictures: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private groupPictures(pictures: LegacyTrailPicture[]): PictureGroup[] {
    const groups = new Map<string, PictureGroup>();

    for (const pic of pictures) {
      const key = `${pic.project_id}:${pic.country}:${pic.trail_id}:${pic.type}:${pic.image_number}`;

      if (!groups.has(key)) {
        groups.set(key, {
          project_id: pic.project_id,
          country: pic.country,
          trail_id: pic.trail_id,
          image_number: pic.image_number,
          type: pic.type,
          path: pic.path,
          translations: [],
        });
      }

      groups.get(key)!.translations.push(pic);
    }

    return Array.from(groups.values());
  }

  private getBackwardCompatibility(group: PictureGroup): string {
    return `mwnf3_travels:trail_picture:${group.project_id}:${group.country}:${group.trail_id}:${group.type || '_'}:${group.image_number}`;
  }

  private async importPicture(group: PictureGroup, _result: ImportResult): Promise<boolean> {
    const backwardCompat = this.getBackwardCompatibility(group);

    // Check if already imported using path as unique identifier
    const imageKey = group.path.toLowerCase();
    if (this.entityExists(imageKey, 'image')) {
      return false;
    }

    // Find parent trail collection
    const trailBackwardCompat = `mwnf3_travels:trail:${group.project_id}:${group.country}:${group.trail_id}`;
    const collectionId = await this.getEntityUuidAsync(trailBackwardCompat, 'collection');
    if (!collectionId) {
      throw new Error(`Parent trail collection not found: ${trailBackwardCompat}`);
    }

    // Collect sample
    this.collectSample(
      'trail_picture',
      group.translations[0] as unknown as Record<string, unknown>,
      'success'
    );

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import trail picture: ${backwardCompat}`
      );
      this.registerEntity(`sample-${backwardCompat}`, imageKey, 'image');
      return true;
    }

    // Calculate display_order for this collection
    const displayOrderKey = `trail_picture_order:${collectionId}`;
    const currentOrder = this.context.tracker.getMetadata(displayOrderKey);
    const displayOrder = currentOrder ? parseInt(currentOrder, 10) + 1 : 1;
    this.context.tracker.setMetadata(displayOrderKey, String(displayOrder));

    // Get best caption (prefer English)
    const englishTranslation = group.translations.find((t) => t.lang === 'en');
    const primaryTranslation = englishTranslation || group.translations[0]!;
    const caption = primaryTranslation.caption
      ? convertHtmlToMarkdown(primaryTranslation.caption)
      : '';

    // Build alt_text with type and metadata
    const altParts: string[] = [];
    if (group.type) {
      altParts.push(`[${group.type}]`);
    }
    if (caption) {
      altParts.push(caption);
    }
    if (primaryTranslation.photographer) {
      altParts.push(`Photo: ${primaryTranslation.photographer}`);
    }
    if (primaryTranslation.copyright) {
      altParts.push(`© ${primaryTranslation.copyright}`);
    }

    const mimeType = this.getMimeType(group.path);
    const originalName = path.basename(group.path);

    // Create CollectionImage
    const imageData: CollectionImageData = {
      collection_id: collectionId,
      path: group.path,
      original_name: originalName,
      mime_type: mimeType,
      size: 1, // Placeholder size
      alt_text: altParts.join(' | ') || group.path,
      display_order: displayOrder,
    };

    await this.context.strategy.writeCollectionImage(imageData);

    // Register in tracker
    this.registerEntity(backwardCompat, imageKey, 'image');

    return true;
  }

  private getMimeType(filePath: string): string {
    const ext = path.extname(filePath).toLowerCase();
    const mimeTypes: Record<string, string> = {
      '.jpg': 'image/jpeg',
      '.jpeg': 'image/jpeg',
      '.png': 'image/png',
      '.gif': 'image/gif',
      '.webp': 'image/webp',
      '.svg': 'image/svg+xml',
    };
    return mimeTypes[ext] || 'image/jpeg';
  }
}
