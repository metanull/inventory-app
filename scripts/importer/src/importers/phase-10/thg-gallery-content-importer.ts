/**
 * THG Gallery Content Importer
 *
 * Imports exhibition-specific content for thematic galleries:
 * - exhibition_logo → CollectionImage
 * - exhibition_related_content → CollectionMedia (type depends on content type)
 *
 * Legacy schema (mwnf3_thematic_gallery):
 * - exhibition_logo (logo_id, gallery_id, path, display_order)
 * - exhibition_related_content (content_id, gallery_id, type, url, display_order)
 * - exhibition_related_content_i18n (content_id, lang, title, description)
 *
 * New schema:
 * - collection_images (via writeCollectionImage)
 * - collection_media (via writeCollectionMedia, types: audio | video | document)
 *
 * Backward compatibility keys:
 * - Logo image path:     used as tracker key (lowercase path)
 * - Related content:     mwnf3_thematic_gallery:exhibition_related_content:{content_id}:{lang}
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import path from 'path';

/**
 * Logo row from exhibition_logo
 */
interface LegacyExhibitionLogo {
  logo_id: number;
  gallery_id: number;
  path: string;
  display_order: number | null;
}

/**
 * Related content row
 */
interface LegacyExhibitionRelatedContent {
  content_id: number;
  gallery_id: number;
  type: string | null;
  url: string;
  display_order: number | null;
}

/**
 * Related content translation row
 */
interface LegacyExhibitionRelatedContentI18n {
  content_id: number;
  lang: string;
  title: string | null;
  description: string | null;
}

const IMAGE_MIME_TYPES: Record<string, string> = {
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.png': 'image/png',
  '.gif': 'image/gif',
  '.webp': 'image/webp',
};

function guessMimeType(filePath: string): string {
  const ext = path.extname(filePath).toLowerCase();
  return IMAGE_MIME_TYPES[ext] ?? 'application/octet-stream';
}

/**
 * Map legacy media type hint to CollectionMedia type.
 * Falls back to 'document' for unknown or PDF types.
 */
function mapContentType(legacyType: string | null): 'audio' | 'video' | 'document' {
  if (!legacyType) return 'document';
  const t = legacyType.toLowerCase();
  if (t === 'audio' || t === 'mp3' || t === 'ogg') return 'audio';
  if (t === 'video' || t === 'mp4' || t === 'youtube' || t === 'vimeo') return 'video';
  return 'document';
}

export class ThgGalleryContentImporter extends BaseImporter {
  getName(): string {
    return 'ThgGalleryContentImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      // Step 1: Import exhibition logos as collection images
      this.logInfo('Importing exhibition logos as collection images...');
      const logoResult = await this.importExhibitionLogos();
      result.imported += logoResult.imported;
      result.skipped += logoResult.skipped;
      result.errors.push(...logoResult.errors);

      // Step 2: Import exhibition related content as collection media
      this.logInfo('Importing exhibition related content as collection media...');
      const contentResult = await this.importExhibitionRelatedContent();
      result.imported += contentResult.imported;
      result.skipped += contentResult.skipped;
      result.errors.push(...contentResult.errors);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgGalleryContentImporter', message);
    }

    result.success = result.errors.length === 0;
    return result;
  }

  // ===========================================================================
  // Step 1: Exhibition Logos → CollectionImage
  // ===========================================================================

  private async importExhibitionLogos(): Promise<ImportResult> {
    const result = this.createResult();

    let logoRows: LegacyExhibitionLogo[];
    try {
      logoRows = await this.context.legacyDb.query<LegacyExhibitionLogo>(
        `SELECT logo_id, gallery_id, path, display_order
         FROM mwnf3_thematic_gallery.exhibition_logo
         ORDER BY gallery_id, display_order IS NULL, display_order, logo_id`
      );
    } catch (queryError) {
      const message = queryError instanceof Error ? queryError.message : String(queryError);
      if (message.includes("doesn't exist") || message.includes('Table')) {
        this.logInfo(`⚠️ exhibition_logo table not available: ${message}`);
        result.warnings = result.warnings || [];
        result.warnings.push(`exhibition_logo table not available: ${message}`);
        result.success = true;
        return result;
      }
      throw queryError;
    }

    this.logInfo(`Found ${logoRows.length} exhibition logo rows`);

    for (const logo of logoRows) {
      try {
        if (!logo.path) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const galleryBC = `mwnf3_thematic_gallery:thg_gallery:${logo.gallery_id}`;
        const collectionId = await this.getEntityUuidAsync(galleryBC, 'collection');
        if (!collectionId) {
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Exhibition logo ${logo.logo_id}: gallery collection not found (${galleryBC}), skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const trackerKey = logo.path.toLowerCase();

        // Skip if already tracked (dedup by path)
        const existingId = await this.getEntityUuidAsync(trackerKey, 'image');
        if (existingId) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const mimeType = guessMimeType(logo.path);
        const originalName = path.basename(logo.path);

        this.collectSample(
          'exhibition_logo',
          logo as unknown as Record<string, unknown>,
          'success'
        );

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create exhibition logo image: ${logo.path} → collection ${collectionId}`
          );
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.writeCollectionImage({
          collection_id: collectionId,
          path: logo.path,
          original_name: originalName,
          mime_type: mimeType,
          size: 0,
          alt_text: null,
          display_order: logo.display_order ?? 0,
        });

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Exhibition logo ${logo.logo_id}: ${message}`);
        this.logError(`Exhibition logo ${logo.logo_id}`, message);
        this.showError();
      }
    }

    return result;
  }

  // ===========================================================================
  // Step 2: Exhibition Related Content → CollectionMedia
  // ===========================================================================

  private async importExhibitionRelatedContent(): Promise<ImportResult> {
    const result = this.createResult();

    let contentRows: LegacyExhibitionRelatedContent[];
    try {
      contentRows = await this.context.legacyDb.query<LegacyExhibitionRelatedContent>(
        `SELECT content_id, gallery_id, type, url, display_order
         FROM mwnf3_thematic_gallery.exhibition_related_content
         ORDER BY gallery_id, display_order IS NULL, display_order, content_id`
      );
    } catch (queryError) {
      const message = queryError instanceof Error ? queryError.message : String(queryError);
      if (message.includes("doesn't exist") || message.includes('Table')) {
        this.logInfo(`⚠️ exhibition_related_content table not available: ${message}`);
        result.warnings = result.warnings || [];
        result.warnings.push(`exhibition_related_content table not available: ${message}`);
        result.success = true;
        return result;
      }
      throw queryError;
    }

    let i18nRows: LegacyExhibitionRelatedContentI18n[];
    try {
      i18nRows = await this.context.legacyDb.query<LegacyExhibitionRelatedContentI18n>(
        `SELECT content_id, lang, title, description
         FROM mwnf3_thematic_gallery.exhibition_related_content_i18n
         ORDER BY content_id, lang`
      );
    } catch {
      i18nRows = [];
      result.warnings = result.warnings || [];
      result.warnings.push(
        'exhibition_related_content_i18n table not available; content will have no titles'
      );
    }

    this.logInfo(
      `Found ${contentRows.length} related content rows, ${i18nRows.length} translations`
    );

    // Index translations by content_id
    const translationsByContentId = new Map<number, LegacyExhibitionRelatedContentI18n[]>();
    for (const i18n of i18nRows) {
      const existing = translationsByContentId.get(i18n.content_id) ?? [];
      existing.push(i18n);
      translationsByContentId.set(i18n.content_id, existing);
    }

    for (const content of contentRows) {
      try {
        if (!content.url) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const galleryBC = `mwnf3_thematic_gallery:thg_gallery:${content.gallery_id}`;
        const collectionId = await this.getEntityUuidAsync(galleryBC, 'collection');
        if (!collectionId) {
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Related content ${content.content_id}: gallery collection not found (${galleryBC}), skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const mediaType = mapContentType(content.type);
        const translations = translationsByContentId.get(content.content_id) ?? [];

        // Create one media entry per translation language (or one without language if no i18n)
        if (translations.length === 0) {
          const bc = `mwnf3_thematic_gallery:exhibition_related_content:${content.content_id}`;
          if (await this.entityExistsAsync(bc, 'collection_media')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          this.collectSample(
            'exhibition_related_content',
            content as unknown as Record<string, unknown>,
            'success'
          );

          if (!this.isDryRun && !this.isSampleOnlyMode) {
            await this.context.strategy.writeCollectionMedia({
              collection_id: collectionId,
              language_id: null,
              type: mediaType,
              title: `Related content ${content.content_id}`,
              description: null,
              url: content.url,
              display_order: content.display_order ?? 0,
              backward_compatibility: bc,
            });
          } else {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create related content media: ${content.url} (${mediaType})`
            );
          }

          result.imported++;
          this.showProgress();
          continue;
        }

        for (const i18n of translations) {
          try {
            if (!i18n.lang) {
              result.warnings = result.warnings || [];
              result.warnings.push(
                `Related content ${content.content_id}: translation row has no language value (table: exhibition_related_content_i18n, pk: content_id=${content.content_id}), skipping`
              );
              continue;
            }

            const languageId = await this.getLanguageIdByLegacyCodeAsync(i18n.lang);
            if (!languageId) {
              result.warnings = result.warnings || [];
              result.warnings.push(
                `Related content ${content.content_id}: unknown language '${i18n.lang}', skipping`
              );
              continue;
            }

            const bc = `mwnf3_thematic_gallery:exhibition_related_content:${content.content_id}:${i18n.lang}`;
            if (await this.entityExistsAsync(bc, 'collection_media')) {
              result.skipped++;
              this.showSkipped();
              continue;
            }

            this.collectSample(
              'exhibition_related_content_i18n',
              { ...content, ...i18n } as unknown as Record<string, unknown>,
              'success',
              undefined,
              languageId
            );

            if (!this.isDryRun && !this.isSampleOnlyMode) {
              await this.context.strategy.writeCollectionMedia({
                collection_id: collectionId,
                language_id: languageId,
                type: mediaType,
                title: i18n.title || `Related content ${content.content_id}`,
                description: i18n.description ?? null,
                url: content.url,
                display_order: content.display_order ?? 0,
                backward_compatibility: bc,
              });
            } else {
              this.logInfo(
                `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create related content media (${i18n.lang}): ${content.url} (${mediaType})`
              );
            }

            result.imported++;
            this.showProgress();
          } catch (transError) {
            const message = transError instanceof Error ? transError.message : String(transError);
            result.errors.push(
              `Related content ${content.content_id} (${i18n.lang}): ${message}`
            );
            this.logError(`Related content ${content.content_id} (${i18n.lang})`, message);
            this.showError();
          }
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Related content ${content.content_id}: ${message}`);
        this.logError(`Related content ${content.content_id}`, message);
        this.showError();
      }
    }

    return result;
  }
}
