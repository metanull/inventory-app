/**
 * Collection Media Importer
 *
 * Imports audio/video URLs attached to THG theme collections.
 *
 * Creates:
 * - 2 CollectionMedia from exhibition_audio + theme_audio (type=audio)
 * - 16 CollectionMedia from exhibition_video + theme_video (type=video)
 * Total: 18 CollectionMedia (one per theme assignment, duplicated per theme)
 *
 * Source tables (mwnf3_thematic_gallery):
 * - exhibition_audio (5 rows) — gallery-level audio definitions
 * - exhibition_video (12 rows) — gallery-level video definitions
 * - theme_audio (2 rows) — assigns audio to themes
 * - theme_video (16 rows) — assigns video to themes
 *
 * Must run AFTER phase-10 (THG theme collections exist).
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { transformThgThemeMedia } from '../../domain/transformers/index.js';
import type { ThgLegacyExhibitionMedia, ThgLegacyThemeMedia } from '../../domain/types/index.js';

export class CollectionMediaImporter extends BaseImporter {
  getName(): string {
    return 'CollectionMediaImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      // Step 1: Audio — join exhibition_audio + theme_audio
      this.logInfo('Importing THG theme audio...');
      const audioResult = await this.importThemeMedia('audio');
      result.imported += audioResult.imported;
      result.skipped += audioResult.skipped;
      result.errors.push(...audioResult.errors);

      // Step 2: Video — join exhibition_video + theme_video
      this.logInfo('Importing THG theme video...');
      const videoResult = await this.importThemeMedia('video');
      result.imported += videoResult.imported;
      result.skipped += videoResult.skipped;
      result.errors.push(...videoResult.errors);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import collection media: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  // ===========================================================================
  // Import theme audio or video
  // ===========================================================================

  private async importThemeMedia(mediaType: 'audio' | 'video'): Promise<ImportResult> {
    const result = this.createResult();

    const idColumn = mediaType === 'audio' ? 'audio_id' : 'video_id';
    const exhibitionTable = mediaType === 'audio' ? 'exhibition_audio' : 'exhibition_video';
    const themeTable = mediaType === 'audio' ? 'theme_audio' : 'theme_video';

    // Join exhibition-level media with theme assignments
    const rows = await this.context.legacyDb.query<ThgLegacyExhibitionMedia & ThgLegacyThemeMedia>(
      `SELECT e.${idColumn} AS media_id, e.gallery_id, e.lang, e.title,
              e.description, e.url,
              t.theme_id, t.overview_page, t.sort_order
       FROM mwnf3_thematic_gallery.${exhibitionTable} e
       JOIN mwnf3_thematic_gallery.${themeTable} t
         ON e.${idColumn} = t.${idColumn} AND e.gallery_id = t.gallery_id
       ORDER BY t.gallery_id, t.theme_id, t.sort_order`
    );
    this.logInfo(`  Found ${rows.length} theme ${mediaType} assignments`);

    for (const row of rows) {
      try {
        const exhibition: ThgLegacyExhibitionMedia = {
          media_id: row.media_id,
          gallery_id: row.gallery_id,
          lang: row.lang,
          title: row.title,
          description: row.description,
          url: row.url,
        };

        const transformed = transformThgThemeMedia(
          exhibition,
          row.theme_id,
          row.sort_order,
          row.overview_page,
          mediaType
        );

        // Resolve collection: theme_id=0 means gallery-level, otherwise theme-level
        let collectionId: string | null;
        if (row.theme_id === 0) {
          const galleryBC = `mwnf3_thematic_gallery:thg_gallery:${row.gallery_id}`;
          collectionId = await this.getEntityUuidAsync(galleryBC, 'collection');
          if (!collectionId) {
            this.logWarning(
              `Skipping gallery-level ${mediaType}: gallery collection not found for BC=${galleryBC} (theme_id=0)`
            );
            result.skipped++;
            continue;
          }
        } else {
          const themeBC = `mwnf3_thematic_gallery:thg_theme:${row.gallery_id}:${row.theme_id}`;
          collectionId = await this.getEntityUuidAsync(themeBC, 'collection');
          if (!collectionId) {
            this.logWarning(
              `Skipping theme ${mediaType}: theme collection not found for BC=${themeBC}`
            );
            result.skipped++;
            continue;
          }
        }

        // Resolve language
        const languageId = await this.getLanguageIdByLegacyCodeAsync(row.lang);
        if (!languageId) {
          this.logWarning(
            `Skipping theme ${mediaType}: unknown language '${row.lang}' for gallery=${row.gallery_id} theme=${row.theme_id}`
          );
          result.skipped++;
          continue;
        }

        await this.context.strategy.writeCollectionMedia({
          ...transformed.data,
          collection_id: collectionId,
          language_id: languageId,
        });

        result.imported++;
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const ctx = `theme_${mediaType}:${row.media_id}:${row.gallery_id}:${row.theme_id}`;
        this.logError(ctx, message);
        result.errors.push(`Failed to import theme ${mediaType} ${ctx}: ${message}`);
      }
    }

    return result;
  }
}
