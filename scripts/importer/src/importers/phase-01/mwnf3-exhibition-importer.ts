/**
 * MWNF3 Exhibition Importer
 *
 * Imports the mwnf3 exhibition hierarchy as nested Collections:
 * - exhibitions (27) → Collection (type='exhibition'), child of project collection
 * - exhibition_themes (~171) → Collection (type='theme'), child of exhibition
 * - exhibition_pages (200+) → Collection (type='theme'), nested child of theme
 *
 * Also imports the artintro hierarchy (identical structure):
 * - artintros (1) → Collection (type='collection'), child of ISL project collection
 * - artintro_themes (10) → Collection (type='theme'), child of artintro
 * - artintro_pages (19) → Collection (type='theme'), nested child of artintro theme
 *
 * Legacy schema: mwnf3
 * Backward compatibility keys:
 * - Exhibition: mwnf3:exhibitions:{exhibition_id}
 * - Theme: mwnf3:exhibition_themes:{theme_id}
 * - Page: mwnf3:exhibition_pages:{page_id}
 * - Artintro: mwnf3:artintros:{artintro_id}
 * - Artintro Theme: mwnf3:artintro_themes:{theme_id}
 * - Artintro Page: mwnf3:artintro_pages:{page_id}
 *
 * Dependencies:
 * - ProjectImporter (must run first to create project collections/contexts)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import type {
  Mwnf3LegacyExhibition,
  Mwnf3LegacyExhibitionTheme,
  Mwnf3LegacyExhibitionPage,
  Mwnf3LegacyArtintro,
  Mwnf3LegacyArtintroTheme,
  Mwnf3LegacyArtintroPage,
} from '../../domain/types/index.js';

const MWNF3_SCHEMA = 'mwnf3';

export class Mwnf3ExhibitionImporter extends BaseImporter {
  // Cache: exhibition_id → project_id
  private exhibitionProjectMap: Map<number, string> | null = null;

  getName(): string {
    return 'Mwnf3ExhibitionImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing mwnf3 exhibition hierarchy as nested collections...');

      const defaultLanguageId = await this.getDefaultLanguageIdAsync();

      // ========================================================================
      // Pass 1: Import exhibitions as Collections (type='exhibition')
      // ========================================================================
      const exhibitions = await this.context.legacyDb.query<Mwnf3LegacyExhibition>(
        `SELECT exhibition_id, project_id, name, n, \`show\`, portal_image, exh_link
         FROM ${MWNF3_SCHEMA}.exhibitions
         ORDER BY n, exhibition_id`
      );

      this.logInfo(`Found ${exhibitions.length} mwnf3 exhibitions`);

      // Build exhibition→project map for context resolution
      this.exhibitionProjectMap = new Map(
        exhibitions.map((e) => [e.exhibition_id, e.project_id])
      );

      for (const legacy of exhibitions) {
        try {
          const backwardCompat = `${MWNF3_SCHEMA}:exhibitions:${legacy.exhibition_id}`;

          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve parent: the project collection
          const projectBackwardCompat = `${MWNF3_SCHEMA}:projects:${legacy.project_id.toUpperCase()}`;
          const parentCollectionId = await this.getEntityUuidAsync(
            projectBackwardCompat,
            'collection'
          );
          if (!parentCollectionId) {
            result.errors.push(
              `Exhibition ${legacy.exhibition_id}: Project collection not found (${projectBackwardCompat})`
            );
            this.logError(
              `Exhibition ${legacy.exhibition_id}`,
              `Project collection not found (${projectBackwardCompat})`
            );
            this.showError();
            continue;
          }

          const contextId = await this.getEntityUuidAsync(projectBackwardCompat, 'context');
          if (!contextId) {
            result.errors.push(
              `Exhibition ${legacy.exhibition_id}: Project context not found (${projectBackwardCompat})`
            );
            this.logError(
              `Exhibition ${legacy.exhibition_id}`,
              `Project context not found (${projectBackwardCompat})`
            );
            this.showError();
            continue;
          }

          const internalName = `mwnf3_exhibition_${legacy.exhibition_id}`;

          // Build extra from metadata
          const extra: Record<string, unknown> = {};
          if (legacy.show) extra.show = legacy.show;
          if (legacy.portal_image) extra.portal_image = legacy.portal_image;
          if (legacy.exh_link) extra.exh_link = legacy.exh_link;

          this.collectSample(
            'mwnf3_exhibition',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create exhibition: ${internalName}`
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
            display_order: legacy.n,
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
      // Pass 2: Import exhibition themes as Collections (type='theme')
      // ========================================================================
      const themes = await this.context.legacyDb.query<Mwnf3LegacyExhibitionTheme>(
        `SELECT theme_id, exhibition_id, name, n
         FROM ${MWNF3_SCHEMA}.exhibition_themes
         ORDER BY exhibition_id, n, theme_id`
      );

      this.logInfo(`Found ${themes.length} exhibition themes`);
      let themesImported = 0;
      let themesSkipped = 0;

      for (const legacy of themes) {
        try {
          const backwardCompat = `${MWNF3_SCHEMA}:exhibition_themes:${legacy.theme_id}`;

          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            themesSkipped++;
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const exhibitionBackwardCompat = `${MWNF3_SCHEMA}:exhibitions:${legacy.exhibition_id}`;
          const parentId = await this.getEntityUuidAsync(exhibitionBackwardCompat, 'collection');
          if (!parentId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme ${legacy.theme_id}: Exhibition not found (${exhibitionBackwardCompat})`
            );
            this.logWarning(
              `Theme ${legacy.theme_id}: Exhibition not found (${exhibitionBackwardCompat}), skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const contextId = await this.resolveContextForExhibition(legacy.exhibition_id);
          if (!contextId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme ${legacy.theme_id}: Could not resolve context for exhibition ${legacy.exhibition_id}`
            );
            this.logWarning(
              `Theme ${legacy.theme_id}: Could not resolve context, skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const internalName = `mwnf3_exhibition_theme_${legacy.theme_id}`;

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.registerEntity('', backwardCompat, 'collection');
            result.imported++;
            themesImported++;
            this.showProgress();
            continue;
          }

          const collectionId = await this.context.strategy.writeCollection({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            context_id: contextId,
            language_id: await this.getDefaultLanguageIdAsync(),
            parent_id: parentId,
            type: 'theme',
            display_order: legacy.n,
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');
          result.imported++;
          themesImported++;
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
      // Pass 3: Import exhibition pages as Collections (type='theme', nested)
      // ========================================================================
      // Build theme→exhibition map for context resolution
      const themeExhibitionMap = new Map(
        themes.map((t) => [t.theme_id, t.exhibition_id])
      );

      const pages = await this.context.legacyDb.query<Mwnf3LegacyExhibitionPage>(
        `SELECT page_id, theme_id, n, remark
         FROM ${MWNF3_SCHEMA}.exhibition_pages
         ORDER BY theme_id, n, page_id`
      );

      this.logInfo(`Found ${pages.length} exhibition pages`);
      let pagesImported = 0;
      let pagesSkipped = 0;

      for (const legacy of pages) {
        try {
          const backwardCompat = `${MWNF3_SCHEMA}:exhibition_pages:${legacy.page_id}`;

          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            pagesSkipped++;
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const themeBackwardCompat = `${MWNF3_SCHEMA}:exhibition_themes:${legacy.theme_id}`;
          const parentId = await this.getEntityUuidAsync(themeBackwardCompat, 'collection');
          if (!parentId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Page ${legacy.page_id}: Theme not found (${themeBackwardCompat})`
            );
            this.logWarning(
              `Page ${legacy.page_id}: Theme not found (${themeBackwardCompat}), skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const exhibitionId = themeExhibitionMap.get(legacy.theme_id);
          const contextId = exhibitionId
            ? await this.resolveContextForExhibition(exhibitionId)
            : null;
          if (!contextId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Page ${legacy.page_id}: Could not resolve context`
            );
            this.logWarning(
              `Page ${legacy.page_id}: Could not resolve context, skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const internalName = `mwnf3_exhibition_page_${legacy.page_id}`;

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.registerEntity('', backwardCompat, 'collection');
            result.imported++;
            pagesImported++;
            this.showProgress();
            continue;
          }

          const collectionId = await this.context.strategy.writeCollection({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            context_id: contextId,
            language_id: await this.getDefaultLanguageIdAsync(),
            parent_id: parentId,
            type: 'theme',
            display_order: legacy.n,
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');
          result.imported++;
          pagesImported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Page ${legacy.page_id}: ${message}`);
          this.logError(`Page ${legacy.page_id}`, message);
          this.showError();
        }
      }

      this.logInfo(
        `Pages done: ${pagesImported} imported, ${pagesSkipped} skipped`
      );

      // ========================================================================
      // Pass 4: Import artintro hierarchy
      // ========================================================================
      await this.importArtintroHierarchy(result);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('Mwnf3ExhibitionImporter', message);
    }

    return result;
  }

  private async importArtintroHierarchy(result: ImportResult): Promise<void> {
    const defaultLanguageId = await this.getDefaultLanguageIdAsync();

    // Artintros
    const artintros = await this.context.legacyDb.query<Mwnf3LegacyArtintro>(
      `SELECT artintro_id, project_id, name
       FROM ${MWNF3_SCHEMA}.artintros
       ORDER BY artintro_id`
    );

    this.logInfo(`Found ${artintros.length} artintros`);

    for (const legacy of artintros) {
      try {
        const backwardCompat = `${MWNF3_SCHEMA}:artintros:${legacy.artintro_id}`;

        if (await this.entityExistsAsync(backwardCompat, 'collection')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const projectBackwardCompat = `${MWNF3_SCHEMA}:projects:${legacy.project_id.toUpperCase()}`;
        const parentCollectionId = await this.getEntityUuidAsync(
          projectBackwardCompat,
          'collection'
        );
        if (!parentCollectionId) {
          result.errors.push(
            `Artintro ${legacy.artintro_id}: Project collection not found (${projectBackwardCompat})`
          );
          this.logError(
            `Artintro ${legacy.artintro_id}`,
            `Project collection not found (${projectBackwardCompat})`
          );
          this.showError();
          continue;
        }

        const contextId = await this.getEntityUuidAsync(projectBackwardCompat, 'context');
        if (!contextId) {
          result.errors.push(
            `Artintro ${legacy.artintro_id}: Project context not found`
          );
          this.logError(`Artintro ${legacy.artintro_id}`, 'Project context not found');
          this.showError();
          continue;
        }

        const internalName = `mwnf3_artintro_${legacy.artintro_id}`;

        if (this.isDryRun || this.isSampleOnlyMode) {
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
          type: 'collection',
          display_order: null,
        });

        this.registerEntity(collectionId, backwardCompat, 'collection');
        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Artintro ${legacy.artintro_id}: ${message}`);
        this.logError(`Artintro ${legacy.artintro_id}`, message);
        this.showError();
      }
    }

    // Artintro themes
    const artThemes = await this.context.legacyDb.query<Mwnf3LegacyArtintroTheme>(
      `SELECT theme_id, artintro_id, name, n
       FROM ${MWNF3_SCHEMA}.artintro_themes
       ORDER BY artintro_id, n, theme_id`
    );

    this.logInfo(`Found ${artThemes.length} artintro themes`);

    for (const legacy of artThemes) {
      try {
        const backwardCompat = `${MWNF3_SCHEMA}:artintro_themes:${legacy.theme_id}`;

        if (await this.entityExistsAsync(backwardCompat, 'collection')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const artintroBackwardCompat = `${MWNF3_SCHEMA}:artintros:${legacy.artintro_id}`;
        const parentId = await this.getEntityUuidAsync(artintroBackwardCompat, 'collection');
        if (!parentId) {
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Artintro theme ${legacy.theme_id}: Artintro not found (${artintroBackwardCompat})`
          );
          this.logWarning(
            `Artintro theme ${legacy.theme_id}: Artintro not found, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Artintro is always ISL project
        const contextId = await this.getEntityUuidAsync(
          `${MWNF3_SCHEMA}:projects:ISL`,
          'context'
        );
        if (!contextId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Artintro theme ${legacy.theme_id}: ISL context not found`);
          this.logWarning(`Artintro theme ${legacy.theme_id}: ISL context not found, skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const internalName = `mwnf3_artintro_theme_${legacy.theme_id}`;

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.registerEntity('', backwardCompat, 'collection');
          result.imported++;
          this.showProgress();
          continue;
        }

        const collectionId = await this.context.strategy.writeCollection({
          internal_name: internalName,
          backward_compatibility: backwardCompat,
          context_id: contextId,
          language_id: await this.getDefaultLanguageIdAsync(),
          parent_id: parentId,
          type: 'theme',
          display_order: legacy.n,
        });

        this.registerEntity(collectionId, backwardCompat, 'collection');
        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Artintro theme ${legacy.theme_id}: ${message}`);
        this.logError(`Artintro theme ${legacy.theme_id}`, message);
        this.showError();
      }
    }

    // Artintro pages
    const artPages = await this.context.legacyDb.query<Mwnf3LegacyArtintroPage>(
      `SELECT page_id, theme_id, n, remark
       FROM ${MWNF3_SCHEMA}.artintro_pages
       ORDER BY theme_id, n, page_id`
    );

    this.logInfo(`Found ${artPages.length} artintro pages`);

    for (const legacy of artPages) {
      try {
        const backwardCompat = `${MWNF3_SCHEMA}:artintro_pages:${legacy.page_id}`;

        if (await this.entityExistsAsync(backwardCompat, 'collection')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const themeBackwardCompat = `${MWNF3_SCHEMA}:artintro_themes:${legacy.theme_id}`;
        const parentId = await this.getEntityUuidAsync(themeBackwardCompat, 'collection');
        if (!parentId) {
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Artintro page ${legacy.page_id}: Theme not found (${themeBackwardCompat})`
          );
          this.logWarning(
            `Artintro page ${legacy.page_id}: Theme not found, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const contextId = await this.getEntityUuidAsync(
          `${MWNF3_SCHEMA}:projects:ISL`,
          'context'
        );
        if (!contextId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Artintro page ${legacy.page_id}: ISL context not found`);
          this.logWarning(`Artintro page ${legacy.page_id}: ISL context not found, skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const internalName = `mwnf3_artintro_page_${legacy.page_id}`;

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.registerEntity('', backwardCompat, 'collection');
          result.imported++;
          this.showProgress();
          continue;
        }

        const collectionId = await this.context.strategy.writeCollection({
          internal_name: internalName,
          backward_compatibility: backwardCompat,
          context_id: contextId,
          language_id: await this.getDefaultLanguageIdAsync(),
          parent_id: parentId,
          type: 'theme',
          display_order: legacy.n,
        });

        this.registerEntity(collectionId, backwardCompat, 'collection');
        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Artintro page ${legacy.page_id}: ${message}`);
        this.logError(`Artintro page ${legacy.page_id}`, message);
        this.showError();
      }
    }
  }

  /**
   * Resolve context ID for a given exhibition_id by looking up its project.
   */
  private async resolveContextForExhibition(exhibitionId: number): Promise<string | null> {
    if (!this.exhibitionProjectMap) {
      const rows = await this.context.legacyDb.query<{ exhibition_id: number; project_id: string }>(
        `SELECT exhibition_id, project_id FROM ${MWNF3_SCHEMA}.exhibitions`
      );
      this.exhibitionProjectMap = new Map(rows.map((r) => [r.exhibition_id, r.project_id]));
    }

    const projectId = this.exhibitionProjectMap.get(exhibitionId);
    if (!projectId) return null;

    const projectBackwardCompat = `${MWNF3_SCHEMA}:projects:${projectId.toUpperCase()}`;
    return this.getEntityUuidAsync(projectBackwardCompat, 'context');
  }
}
