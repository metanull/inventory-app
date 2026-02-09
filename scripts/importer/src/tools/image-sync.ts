/**
 * Image Synchronization Tool
 *
 * Synchronizes legacy images to new storage by:
 * 1. Finding ItemImage and PartnerImage records with size=1 (legacy placeholder)
 * 2. Copying or symlinking the actual image file from legacy storage
 * 3. Updating the database record with correct path, size, and metadata
 *
 * This is a standalone tool that only connects to the new database.
 */

import type { Connection } from 'mysql2/promise';
import {
  getFileExtension,
  getLegacyImagePath,
  getNewImagePath,
  copyFile,
  symlinkFile,
  getFileSize,
  fileExists,
  ensureDirectory,
} from '../utils/image-sync.js';

interface ImageRecord {
  id: string;
  path: string;
  size: number;
  original_name: string | null;
  alt_text: string | null;
  mime_type: string;
}

export interface ImageSyncOptions {
  useSymlink: boolean;
  legacyImagesRoot: string;
  newImagesRoot: string;
  dryRun?: boolean;
}

export interface ImageSyncResult {
  success: boolean;
  imported: number;
  skipped: number;
  errors: string[];
}

export interface ImageSyncLogger {
  info(message: string): void;
  error(context: string, error: unknown): void;
}

export class ImageSyncTool {
  private db: Connection;
  private options: ImageSyncOptions;
  private logger: ImageSyncLogger;

  constructor(db: Connection, options: ImageSyncOptions, logger: ImageSyncLogger) {
    this.db = db;
    this.options = options;
    this.logger = logger;
  }

  async run(): Promise<ImageSyncResult> {
    const result: ImageSyncResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      this.logger.info('Starting image synchronization...');
      this.logger.info(`Mode: ${this.options.useSymlink ? 'SYMLINK' : 'COPY'}`);
      this.logger.info(`Legacy images root: ${this.options.legacyImagesRoot}`);
      this.logger.info(`New images root: ${this.options.newImagesRoot}`);

      // Ensure new images directory exists
      await ensureDirectory(this.options.newImagesRoot);

      // Sync ItemImages
      this.logger.info('\n=== Syncing ItemImages ===');
      const itemImageResults = await this.syncItemImages();
      result.imported += itemImageResults.synced;
      result.skipped += itemImageResults.skipped;
      result.errors.push(...itemImageResults.errors);

      // Sync PartnerImages
      this.logger.info('\n=== Syncing PartnerImages ===');
      const partnerImageResults = await this.syncPartnerImages();
      result.imported += partnerImageResults.synced;
      result.skipped += partnerImageResults.skipped;
      result.errors.push(...partnerImageResults.errors);

      // Sync CollectionImages
      this.logger.info('\n=== Syncing CollectionImages ===');
      const collectionImageResults = await this.syncCollectionImages();
      result.imported += collectionImageResults.synced;
      result.skipped += collectionImageResults.skipped;
      result.errors.push(...collectionImageResults.errors);

      this.logger.info(
        `\nSummary: ${result.imported} synced, ${result.skipped} skipped, ${result.errors.length} errors`
      );
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Image sync failed: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async syncItemImages(): Promise<{
    synced: number;
    skipped: number;
    errors: string[];
  }> {
    const errors: string[] = [];
    let synced = 0;
    let skipped = 0;

    // Query ItemImages with size=1
    const images = await this.queryImages('item_images');
    this.logger.info(`Found ${images.length} ItemImages to sync`);

    for (const image of images) {
      try {
        const result = await this.syncImage(image, 'item_images');
        if (result) {
          synced++;
          process.stdout.write('.');
        } else {
          skipped++;
          process.stdout.write('⊘');
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        errors.push(`ItemImage ${image.id}: ${message}`);
        this.logger.error(`ItemImage ${image.id}`, message);
        process.stdout.write('✗');
      }
    }
    process.stdout.write('\n');

    return { synced, skipped, errors };
  }

  private async syncPartnerImages(): Promise<{
    synced: number;
    skipped: number;
    errors: string[];
  }> {
    const errors: string[] = [];
    let synced = 0;
    let skipped = 0;

    // Query PartnerImages with size=1
    const images = await this.queryImages('partner_images');
    this.logger.info(`Found ${images.length} PartnerImages to sync`);

    for (const image of images) {
      try {
        const result = await this.syncImage(image, 'partner_images');
        if (result) {
          synced++;
          process.stdout.write('.');
        } else {
          skipped++;
          process.stdout.write('⊘');
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        errors.push(`PartnerImage ${image.id}: ${message}`);
        this.logger.error(`PartnerImage ${image.id}`, message);
        process.stdout.write('✗');
      }
    }
    process.stdout.write('\n');

    return { synced, skipped, errors };
  }

  private async syncCollectionImages(): Promise<{
    synced: number;
    skipped: number;
    errors: string[];
  }> {
    const errors: string[] = [];
    let synced = 0;
    let skipped = 0;

    // Query CollectionImages with size=1
    const images = await this.queryImages('collection_images');
    this.logger.info(`Found ${images.length} CollectionImages to sync`);

    for (const image of images) {
      try {
        const result = await this.syncImage(image, 'collection_images');
        if (result) {
          synced++;
          process.stdout.write('.');
        } else {
          skipped++;
          process.stdout.write('⊘');
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        errors.push(`CollectionImage ${image.id}: ${message}`);
        this.logger.error(`CollectionImage ${image.id}`, message);
        process.stdout.write('✗');
      }
    }
    process.stdout.write('\n');

    return { synced, skipped, errors };
  }

  private async queryImages(tableName: string): Promise<ImageRecord[]> {
    const query = `
      SELECT id, path, size, original_name, alt_text, mime_type
      FROM ${tableName}
      WHERE size = 1
      ORDER BY id
    `;

    const [rows] = await this.db.execute(query);
    return rows as ImageRecord[];
  }

  private async syncImage(image: ImageRecord, tableName: string): Promise<boolean> {
    if (this.options.dryRun) {
      this.logger.info(`[DRY-RUN] Would sync ${tableName}.${image.id}: ${image.path}`);
      return true;
    }

    // Get legacy image path
    const legacyPath = getLegacyImagePath(this.options.legacyImagesRoot, image.path);

    // Check if legacy image exists
    if (!(await fileExists(legacyPath))) {
      throw new Error(`Legacy image not found: ${legacyPath}`);
    }

    // Get file extension
    const extension = getFileExtension(image.path);
    if (!extension) {
      throw new Error(`Cannot determine file extension from path: ${image.path}`);
    }

    // Build new path
    const newPath = getNewImagePath(this.options.newImagesRoot, image.id, extension);

    // Copy or symlink the file
    if (this.options.useSymlink) {
      await symlinkFile(legacyPath, newPath);
    } else {
      await copyFile(legacyPath, newPath);
    }

    // Get actual file size
    const actualSize = await getFileSize(newPath);

    // Update database record with just the filename (no leading slash, no directory)
    const filename = `${image.id}${extension}`;
    await this.updateImageRecord(tableName, image.id, {
      path: filename,
      size: actualSize,
      original_name: image.path,
      alt_text: null,
    });

    return true;
  }

  private async updateImageRecord(
    tableName: string,
    id: string,
    updates: {
      path: string;
      size: number;
      original_name: string;
      alt_text: null;
    }
  ): Promise<void> {
    const query = `
      UPDATE ${tableName}
      SET path = ?,
          size = ?,
          original_name = ?,
          alt_text = ?,
          updated_at = NOW()
      WHERE id = ?
    `;

    await this.db.execute(query, [
      updates.path,
      updates.size,
      updates.original_name,
      updates.alt_text,
      id,
    ]);
  }
}
