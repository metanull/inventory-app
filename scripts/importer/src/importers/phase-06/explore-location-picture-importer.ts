/**
 * Explore Location Picture Importer
 *
 * Imports pictures from mwnf3_explore.locations_pictures as CollectionImages.
 * Locations are collections in Explore, so their pictures go to collection_images.
 *
 * Legacy schema:
 * - mwnf3_explore.locations_pictures (locationId, lang, image_number, path, thumb, caption, photographer, copyright, lastupdate, type)
 *   - PK: (locationId, lang, image_number, type)
 *
 * New schema:
 * - collection_images (collection_id, path, original_name, mime_type, size, alt_text, display_order)
 *
 * Dependencies:
 * - ExploreLocationImporter (must run first to create location collections)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { CollectionImageData, ImportResult } from '../../core/types.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';
import path from 'path';

/**
 * Legacy location picture structure
 */
interface LegacyLocationPicture {
  locationId: number;
  lang: string;
  image_number: number;
  path: string;
  thumb: string | null;
  caption: string | null;
  photographer: string | null;
  copyright: string | null;
  lastupdate: string | null;
  type: string;
}

/**
 * Grouped picture (unique by non-lang keys)
 */
interface PictureGroup {
  locationId: number;
  image_number: number;
  type: string;
  path: string;
  translations: LegacyLocationPicture[];
}

export class ExploreLocationPictureImporter extends BaseImporter {
  getName(): string {
    return 'ExploreLocationPictureImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing explore location pictures...');

      // Query all location pictures
      const pictures = await this.context.legacyDb.query<LegacyLocationPicture>(
        `SELECT locationId, lang, image_number, path, thumb, caption, photographer, copyright, lastupdate, type
         FROM mwnf3_explore.locations_pictures
         ORDER BY locationId, type, image_number, lang`
      );

      if (pictures.length === 0) {
        this.logInfo('No location pictures found');
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
          this.logError(`Location Picture ${backwardCompat}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query location pictures: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private groupPictures(pictures: LegacyLocationPicture[]): PictureGroup[] {
    const groups = new Map<string, PictureGroup>();

    for (const pic of pictures) {
      const key = `${pic.locationId}:${pic.type || '_'}:${pic.image_number}`;

      if (!groups.has(key)) {
        groups.set(key, {
          locationId: pic.locationId,
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
    return `mwnf3_explore:location_picture:${group.locationId}:${group.type || '_'}:${group.image_number}`;
  }

  private async importPicture(group: PictureGroup, _result: ImportResult): Promise<boolean> {
    const backwardCompat = this.getBackwardCompatibility(group);

    // Check if already imported using path as unique identifier
    const imageKey = group.path.toLowerCase();
    if (this.entityExists(imageKey, 'image')) {
      return false;
    }

    // Find parent location collection
    const locationBackwardCompat = `mwnf3_explore:location:${group.locationId}`;
    const collectionId = await this.getEntityUuidAsync(locationBackwardCompat, 'collection');
    if (!collectionId) {
      throw new Error(`Parent location collection not found: ${locationBackwardCompat}`);
    }

    // Collect sample
    this.collectSample(
      'explore_location_picture',
      group.translations[0] as unknown as Record<string, unknown>,
      'success'
    );

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import location picture: ${backwardCompat}`
      );
      this.registerEntity(`sample-${backwardCompat}`, imageKey, 'image');
      return true;
    }

    // Calculate display_order for this collection
    const displayOrderKey = `explore_location_picture_order:${collectionId}`;
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
