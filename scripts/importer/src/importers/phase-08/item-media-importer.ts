/**
 * Item Media Importer
 *
 * Imports audio/video URLs attached to items from legacy databases.
 *
 * Creates:
 * - 21 ItemMedia from mwnf3.objects_video (type=video)
 * - 1 ItemMedia from mwnf3.monuments_video (type=video)
 * - 2 ItemMedia from mwnf3_sharing_history.sh_objects_video_audio (type from column)
 *
 * Source tables:
 * - mwnf3.objects_video (21 rows)
 * - mwnf3.monuments_video (1 row)
 * - mwnf3_sharing_history.sh_objects_video_audio (2 rows)
 *
 * Skipped (0 rows): objects_audio, monuments_audio, sh_monuments_video_audio
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import {
  transformObjectVideo,
  transformShObjectVideoAudio,
} from '../../domain/transformers/index.js';
import type { LegacyObjectVideo, ShLegacyObjectVideoAudio } from '../../domain/types/index.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';

export class ItemMediaImporter extends BaseImporter {
  getName(): string {
    return 'ItemMediaImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      // Step 1: mwnf3.objects_video → ItemMedia (video)
      this.logInfo('Importing mwnf3 object videos...');
      const objResult = await this.importObjectVideos();
      result.imported += objResult.imported;
      result.skipped += objResult.skipped;
      result.errors.push(...objResult.errors);

      // Step 2: mwnf3.monuments_video → ItemMedia (video)
      this.logInfo('Importing mwnf3 monument videos...');
      const monResult = await this.importMonumentVideos();
      result.imported += monResult.imported;
      result.skipped += monResult.skipped;
      result.errors.push(...monResult.errors);

      // Step 3: sh.sh_objects_video_audio → ItemMedia
      this.logInfo('Importing SH object video/audio...');
      const shResult = await this.importShObjectVideoAudio();
      result.imported += shResult.imported;
      result.skipped += shResult.skipped;
      result.errors.push(...shResult.errors);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import item media: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  // ===========================================================================
  // Step 1: mwnf3.objects_video
  // ===========================================================================

  private async importObjectVideos(): Promise<ImportResult> {
    const result = this.createResult();

    const rows = await this.context.legacyDb.query<LegacyObjectVideo>(
      `SELECT video_id, project_id, country, museum_id, number, lang,
              video_title, video_description, video_url
       FROM mwnf3.objects_video
       ORDER BY project_id, country, museum_id, number, lang, video_id`
    );
    this.logInfo(`  Found ${rows.length} object video rows`);

    for (const row of rows) {
      try {
        const transformed = transformObjectVideo(row, 'objects');

        // Resolve parent item
        const itemBC = formatBackwardCompatibility({
          schema: 'mwnf3',
          table: 'objects',
          pkValues: [row.project_id, row.country, row.museum_id, row.number],
        });
        const itemId = await this.getEntityUuidAsync(itemBC, 'item');
        if (!itemId) {
          this.logWarning(`Skipping object video: parent item not found for BC=${itemBC}`);
          result.skipped++;
          continue;
        }

        // Resolve language
        const languageId = await this.getLanguageIdByLegacyCodeAsync(row.lang);
        if (!languageId) {
          this.logWarning(
            `Skipping object video: unknown language '${row.lang}' for BC=${transformed.data.backward_compatibility}`
          );
          result.skipped++;
          continue;
        }

        await this.context.strategy.writeItemMedia({
          ...transformed.data,
          item_id: itemId,
          language_id: languageId,
        });

        result.imported++;
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const ctx = `objects_video:${row.project_id}:${row.country}:${row.museum_id}:${row.number}:${row.lang}:${row.video_id}`;
        this.logError(ctx, message);
        result.errors.push(`Failed to import object video ${ctx}: ${message}`);
      }
    }

    return result;
  }

  // ===========================================================================
  // Step 2: mwnf3.monuments_video
  // ===========================================================================

  private async importMonumentVideos(): Promise<ImportResult> {
    const result = this.createResult();

    const rows = await this.context.legacyDb.query<LegacyObjectVideo>(
      `SELECT video_id, project_id, country, museum_id, number, lang,
              video_title, video_description, video_url
       FROM mwnf3.monuments_video
       ORDER BY project_id, country, museum_id, number, lang, video_id`
    );
    this.logInfo(`  Found ${rows.length} monument video rows`);

    for (const row of rows) {
      try {
        const transformed = transformObjectVideo(row, 'monuments');

        // Resolve parent item (monuments use same composite key pattern)
        const itemBC = formatBackwardCompatibility({
          schema: 'mwnf3',
          table: 'monuments',
          pkValues: [row.project_id, row.country, row.museum_id, row.number],
        });
        const itemId = await this.getEntityUuidAsync(itemBC, 'item');
        if (!itemId) {
          this.logWarning(`Skipping monument video: parent item not found for BC=${itemBC}`);
          result.skipped++;
          continue;
        }

        const languageId = await this.getLanguageIdByLegacyCodeAsync(row.lang);
        if (!languageId) {
          this.logWarning(
            `Skipping monument video: unknown language '${row.lang}' for BC=${transformed.data.backward_compatibility}`
          );
          result.skipped++;
          continue;
        }

        await this.context.strategy.writeItemMedia({
          ...transformed.data,
          item_id: itemId,
          language_id: languageId,
        });

        result.imported++;
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const ctx = `monuments_video:${row.project_id}:${row.country}:${row.museum_id}:${row.number}:${row.lang}:${row.video_id}`;
        this.logError(ctx, message);
        result.errors.push(`Failed to import monument video ${ctx}: ${message}`);
      }
    }

    return result;
  }

  // ===========================================================================
  // Step 3: sh.sh_objects_video_audio
  // ===========================================================================

  private async importShObjectVideoAudio(): Promise<ImportResult> {
    const result = this.createResult();

    const rows = await this.context.legacyDb.query<ShLegacyObjectVideoAudio>(
      `SELECT id, project_id, country, number, type, path, title
       FROM mwnf3_sharing_history.sh_objects_video_audio
       ORDER BY id`
    );
    this.logInfo(`  Found ${rows.length} SH object video/audio rows`);

    for (const row of rows) {
      try {
        const transformed = transformShObjectVideoAudio(row);

        // Resolve parent item — SH objects use project_id:country:number
        const itemBC = `mwnf3_sharing_history:sh_objects:${row.project_id}:${row.country}:${row.number}`;
        const itemId = await this.getEntityUuidAsync(itemBC, 'item');
        if (!itemId) {
          this.logWarning(`Skipping SH video/audio: parent item not found for BC=${itemBC}`);
          result.skipped++;
          continue;
        }

        await this.context.strategy.writeItemMedia({
          ...transformed.data,
          item_id: itemId,
        });

        result.imported++;
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const ctx = `sh_objects_video_audio:${row.id}`;
        this.logError(ctx, message);
        result.errors.push(`Failed to import SH video/audio ${ctx}: ${message}`);
      }
    }

    return result;
  }
}
