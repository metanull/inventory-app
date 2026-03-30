/**
 * SH Exhibition Importer
 *
 * Imports the 3-level SH exhibition hierarchy as nested Collections:
 * - sh_exhibitions → Collection (type='exhibition'), child of SH project collection
 * - sh_exhibition_themes → Collection (type='theme'), child of exhibition
 * - sh_exhibition_subthemes → Collection (type='subtheme'), child of theme
 *
 * Legacy schema (mwnf3_sharing_history):
 * - sh_exhibitions (exhibition_id, project_id, name, sort, show, geoCoordinates, zoom, ...)
 * - sh_exhibition_themes (theme_id, exhibition_id, name, sort, geoCoordinates, zoom)
 * - sh_exhibition_subthemes (subtheme_id, theme_id, name, sort, geoCoordinates, zoom)
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, display_order, latitude, longitude, map_zoom, ...)
 *
 * Backward compatibility keys:
 * - Exhibition: mwnf3_sharing_history:sh_exhibitions:{exhibition_id}
 * - Theme: mwnf3_sharing_history:sh_exhibition_themes:{theme_id}
 * - Subtheme: mwnf3_sharing_history:sh_exhibition_subthemes:{subtheme_id}
 *
 * Dependencies:
 * - ShProjectImporter (must run first to create SH project collections/contexts)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import type {
  ShLegacyExhibition,
  ShLegacyExhibitionTheme,
  ShLegacyExhibitionSubtheme,
} from '../../domain/types/index.js';

const SH_SCHEMA = 'mwnf3_sharing_history';

export class ShExhibitionImporter extends BaseImporter {
  getName(): string {
    return 'ShExhibitionImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing SH exhibition hierarchy as nested collections...');

      const defaultLanguageId = await this.getDefaultLanguageIdAsync();

      // ========================================================================
      // Pass 1: Import exhibitions as Collections (type='exhibition')
      // ========================================================================
      const exhibitions = await this.context.legacyDb.query<ShLegacyExhibition>(
        `SELECT exhibition_id, project_id, name, sort, show, geoCoordinates, zoom,
                exh_thumb, logo1, url1, logo2, url2, logo3, url3, homeimage, portal_image
         FROM ${SH_SCHEMA}.sh_exhibitions
         ORDER BY sort, exhibition_id`
      );

      this.logInfo(`Found ${exhibitions.length} SH exhibitions`);

      for (const legacy of exhibitions) {
        try {
          const backwardCompat = `${SH_SCHEMA}:sh_exhibitions:${legacy.exhibition_id}`;

          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve the parent: the SH project collection
          const projectBackwardCompat = `${SH_SCHEMA}:sh_projects:${legacy.project_id.toLowerCase()}`;
          const parentCollectionId = await this.getEntityUuidAsync(
            projectBackwardCompat,
            'collection'
          );
          if (!parentCollectionId) {
            result.errors.push(
              `Exhibition ${legacy.exhibition_id}: SH project collection not found (${projectBackwardCompat}). Run ShProjectImporter first.`
            );
            this.logError(
              `Exhibition ${legacy.exhibition_id}`,
              `SH project collection not found (${projectBackwardCompat})`
            );
            this.showError();
            continue;
          }

          // Resolve context from the SH project
          const contextId = await this.getEntityUuidAsync(projectBackwardCompat, 'context');
          if (!contextId) {
            result.errors.push(
              `Exhibition ${legacy.exhibition_id}: SH project context not found (${projectBackwardCompat}). Run ShProjectImporter first.`
            );
            this.logError(
              `Exhibition ${legacy.exhibition_id}`,
              `SH project context not found (${projectBackwardCompat})`
            );
            this.showError();
            continue;
          }

          // Parse geoCoordinates
          const { latitude, longitude } = this.parseGeoCoordinates(legacy.geoCoordinates);

          const internalName = `sh_exhibition_${legacy.exhibition_id}`;

          this.collectSample(
            'sh_exhibition',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create exhibition collection: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'collection');
            result.imported++;
            this.showProgress();
            continue;
          }

          const collectionId = await this.context.strategy.writeCollection({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            context_id: contextId,
            language_id: defaultLanguageId,
            parent_id: parentCollectionId,
            type: 'exhibition',
            display_order: legacy.sort,
            latitude,
            longitude,
            map_zoom: legacy.zoom || null,
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');
          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Exhibition ${legacy.exhibition_id}: ${message}`);
          this.logError(`Exhibition ${legacy.exhibition_id}`, message);
          this.showError();
        }
      }

      this.logInfo(`Exhibitions done: ${result.imported} imported, ${result.skipped} skipped`);

      // ========================================================================
      // Pass 2: Import themes as Collections (type='theme')
      // ========================================================================
      const themes = await this.context.legacyDb.query<ShLegacyExhibitionTheme>(
        `SELECT theme_id, exhibition_id, name, sort, geoCoordinates, zoom
         FROM ${SH_SCHEMA}.sh_exhibition_themes
         ORDER BY exhibition_id, sort, theme_id`
      );

      this.logInfo(`Found ${themes.length} SH exhibition themes`);
      let themesImported = 0;
      let themesSkipped = 0;

      for (const legacy of themes) {
        try {
          const backwardCompat = `${SH_SCHEMA}:sh_exhibition_themes:${legacy.theme_id}`;

          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            themesSkipped++;
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Parent is the exhibition collection
          const exhibitionBackwardCompat = `${SH_SCHEMA}:sh_exhibitions:${legacy.exhibition_id}`;
          const parentId = await this.getEntityUuidAsync(exhibitionBackwardCompat, 'collection');
          if (!parentId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme ${legacy.theme_id}: Exhibition collection not found (${exhibitionBackwardCompat})`
            );
            this.logWarning(
              `Theme ${legacy.theme_id}: Exhibition collection not found (${exhibitionBackwardCompat}), skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve context via the exhibition's SH project
          const contextId = await this.resolveContextForExhibition(legacy.exhibition_id);
          if (!contextId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme ${legacy.theme_id}: Could not resolve context for exhibition ${legacy.exhibition_id}`
            );
            this.logWarning(
              `Theme ${legacy.theme_id}: Could not resolve context for exhibition ${legacy.exhibition_id}, skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const { latitude, longitude } = this.parseGeoCoordinates(legacy.geoCoordinates);
          const internalName = `sh_exhibition_theme_${legacy.theme_id}`;

          this.collectSample(
            'sh_exhibition_theme',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create theme collection: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'collection');
            themesImported++;
            result.imported++;
            this.showProgress();
            continue;
          }

          const collectionId = await this.context.strategy.writeCollection({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            context_id: contextId,
            language_id: defaultLanguageId,
            parent_id: parentId,
            type: 'theme',
            display_order: legacy.sort,
            latitude,
            longitude,
            map_zoom: legacy.zoom || null,
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');
          themesImported++;
          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Theme ${legacy.theme_id}: ${message}`);
          this.logError(`Theme ${legacy.theme_id}`, message);
          this.showError();
        }
      }

      this.logInfo(
        `Themes done: ${themesImported} imported, ${themesSkipped} skipped`
      );

      // ========================================================================
      // Pass 3: Import subthemes as Collections (type='subtheme')
      // ========================================================================
      const subthemes = await this.context.legacyDb.query<ShLegacyExhibitionSubtheme>(
        `SELECT subtheme_id, theme_id, name, sort, geoCoordinates, zoom
         FROM ${SH_SCHEMA}.sh_exhibition_subthemes
         ORDER BY theme_id, sort, subtheme_id`
      );

      this.logInfo(`Found ${subthemes.length} SH exhibition subthemes`);
      let subthemesImported = 0;
      let subthemesSkipped = 0;

      for (const legacy of subthemes) {
        try {
          const backwardCompat = `${SH_SCHEMA}:sh_exhibition_subthemes:${legacy.subtheme_id}`;

          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            subthemesSkipped++;
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Parent is the theme collection
          const themeBackwardCompat = `${SH_SCHEMA}:sh_exhibition_themes:${legacy.theme_id}`;
          const parentId = await this.getEntityUuidAsync(themeBackwardCompat, 'collection');
          if (!parentId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Subtheme ${legacy.subtheme_id}: Theme collection not found (${themeBackwardCompat})`
            );
            this.logWarning(
              `Subtheme ${legacy.subtheme_id}: Theme collection not found (${themeBackwardCompat}), skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve context by walking up: subtheme → theme → exhibition → project
          const contextId = await this.resolveContextForTheme(legacy.theme_id);
          if (!contextId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Subtheme ${legacy.subtheme_id}: Could not resolve context for theme ${legacy.theme_id}`
            );
            this.logWarning(
              `Subtheme ${legacy.subtheme_id}: Could not resolve context for theme ${legacy.theme_id}, skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const { latitude, longitude } = this.parseGeoCoordinates(legacy.geoCoordinates);
          const internalName = `sh_exhibition_subtheme_${legacy.subtheme_id}`;

          this.collectSample(
            'sh_exhibition_subtheme',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create subtheme collection: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'collection');
            subthemesImported++;
            result.imported++;
            this.showProgress();
            continue;
          }

          const collectionId = await this.context.strategy.writeCollection({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            context_id: contextId,
            language_id: defaultLanguageId,
            parent_id: parentId,
            type: 'subtheme',
            display_order: legacy.sort,
            latitude,
            longitude,
            map_zoom: legacy.zoom || null,
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');
          subthemesImported++;
          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Subtheme ${legacy.subtheme_id}: ${message}`);
          this.logError(`Subtheme ${legacy.subtheme_id}`, message);
          this.showError();
        }
      }

      this.logInfo(
        `Subthemes done: ${subthemesImported} imported, ${subthemesSkipped} skipped`
      );

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ShExhibitionImporter', message);
    }

    return result;
  }

  // In-memory cache: exhibition_id → project_id (populated lazily)
  private exhibitionProjectMap: Map<number, string> | null = null;
  // In-memory cache: theme_id → exhibition_id (populated lazily)
  private themeExhibitionMap: Map<number, number> | null = null;

  /**
   * Resolve context ID for a given exhibition_id by looking up its SH project.
   */
  private async resolveContextForExhibition(exhibitionId: number): Promise<string | null> {
    if (!this.exhibitionProjectMap) {
      const rows = await this.context.legacyDb.query<{ exhibition_id: number; project_id: string }>(
        `SELECT exhibition_id, project_id FROM ${SH_SCHEMA}.sh_exhibitions`
      );
      this.exhibitionProjectMap = new Map(rows.map((r) => [r.exhibition_id, r.project_id]));
    }

    const projectId = this.exhibitionProjectMap.get(exhibitionId);
    if (!projectId) return null;

    const projectBackwardCompat = `${SH_SCHEMA}:sh_projects:${projectId.toLowerCase()}`;
    return this.getEntityUuidAsync(projectBackwardCompat, 'context');
  }

  /**
   * Resolve context ID for a given theme_id by walking up to exhibition → project.
   */
  private async resolveContextForTheme(themeId: number): Promise<string | null> {
    if (!this.themeExhibitionMap) {
      const rows = await this.context.legacyDb.query<{ theme_id: number; exhibition_id: number }>(
        `SELECT theme_id, exhibition_id FROM ${SH_SCHEMA}.sh_exhibition_themes`
      );
      this.themeExhibitionMap = new Map(rows.map((r) => [r.theme_id, r.exhibition_id]));
    }

    const exhibitionId = this.themeExhibitionMap.get(themeId);
    if (exhibitionId === undefined) return null;

    return this.resolveContextForExhibition(exhibitionId);
  }

  /**
   * Parse geoCoordinates string (format: "lat,long" or "lat,long,zoom")
   */
  private parseGeoCoordinates(geoCoordinates: string | null): {
    latitude: number | null;
    longitude: number | null;
  } {
    if (!geoCoordinates) {
      return { latitude: null, longitude: null };
    }
    const parts = geoCoordinates.split(',').map((s) => s.trim());
    if (parts.length >= 2) {
      const lat = parseFloat(parts[0]);
      const lon = parseFloat(parts[1]);
      if (!isNaN(lat) && !isNaN(lon)) {
        return { latitude: lat, longitude: lon };
      }
    }
    return { latitude: null, longitude: null };
  }
}
