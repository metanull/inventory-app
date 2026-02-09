/**
 * Explore Monument Picture Importer
 *
 * Imports pictures from mwnf3_explore.exploremonument_pictures as ItemImages.
 *
 * Legacy schema:
 * - mwnf3_explore.exploremonument_pictures (monumentId, lang, image_number, path, thumb, caption, photographer, copyright, lastupdate, type)
 *   - PK: (monumentId, lang, type, image_number)
 *
 * New schema:
 * - item_images (item_id, path, original_name, mime_type, size, alt_text, display_order)
 *
 * Dependencies:
 * - ExploreMonumentImporter (must run first to create monument items)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult, ItemImageData } from '../../core/types.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';
import path from 'path';

/**
 * Legacy monument picture structure
 */
interface LegacyMonumentPicture {
  monumentId: number;
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
  monumentId: number;
  image_number: number;
  type: string;
  path: string;
  translations: LegacyMonumentPicture[];
}

export class ExploreMonumentPictureImporter extends BaseImporter {
  getName(): string {
    return 'ExploreMonumentPictureImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing explore monument pictures...');

      // Query all monument pictures
      const pictures = await this.context.legacyDb.query<LegacyMonumentPicture>(
        `SELECT monumentId, lang, image_number, path, thumb, caption, photographer, copyright, lastupdate, type
         FROM mwnf3_explore.exploremonument_pictures
         ORDER BY monumentId, type, image_number, lang`
      );

      if (pictures.length === 0) {
        this.logInfo('No monument pictures found');
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
          this.logError(`Monument Picture ${backwardCompat}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query monument pictures: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private groupPictures(pictures: LegacyMonumentPicture[]): PictureGroup[] {
    const groups = new Map<string, PictureGroup>();

    for (const pic of pictures) {
      const key = `${pic.monumentId}:${pic.type || '_'}:${pic.image_number}`;

      if (!groups.has(key)) {
        groups.set(key, {
          monumentId: pic.monumentId,
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
    return `mwnf3_explore:monument_picture:${group.monumentId}:${group.type || '_'}:${group.image_number}`;
  }

  private async importPicture(group: PictureGroup, _result: ImportResult): Promise<boolean> {
    const backwardCompat = this.getBackwardCompatibility(group);

    // Check if already imported using path as unique identifier
    const imageKey = group.path.toLowerCase();
    if (this.entityExists(imageKey, 'image')) {
      return false;
    }

    // Find parent monument item
    const monumentBackwardCompat = `mwnf3_explore:monument:${group.monumentId}`;
    const itemId = await this.getEntityUuidAsync(monumentBackwardCompat, 'item');
    if (!itemId) {
      throw new Error(`Parent monument item not found: ${monumentBackwardCompat}`);
    }

    // Collect sample
    this.collectSample(
      'explore_monument_picture',
      group.translations[0] as unknown as Record<string, unknown>,
      'success'
    );

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import monument picture: ${backwardCompat}`
      );
      this.registerEntity(`sample-${backwardCompat}`, imageKey, 'image');
      return true;
    }

    // Calculate display_order for this item
    const displayOrderKey = `explore_monument_picture_order:${itemId}`;
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

    // Create ItemImage
    const imageData: ItemImageData = {
      item_id: itemId,
      path: group.path,
      original_name: originalName,
      mime_type: mimeType,
      size: 1, // Placeholder size
      alt_text: altParts.join(' | ') || group.path,
      display_order: displayOrder,
    };

    await this.context.strategy.writeItemImage(imageData);

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
