/**
 * MWNF3 Exhibition Translation Importer
 *
 * Pivots EAV *_fields tables into CollectionTranslation records for:
 * - Exhibition-level: exhibition_fields → CollectionTranslation
 * - Theme-level: exhibition_theme_fields → CollectionTranslation
 * - Page-level: exhibition_page_fields → CollectionTranslation
 * - Artintro-level: artintro_fields, artintro_theme_fields (no page_fields — artintro pages
 *   use artintro_page_fields)
 * - Artintro page-level: artintro_page_fields → CollectionTranslation
 *
 * EAV pattern: (entity_id, lang_id CHAR(2), field VARCHAR(255), value TEXT)
 * Key fields:
 * - Exhibition: exh_title, exh_description, exh_credits
 * - Theme: theme_title, theme_subtitle
 * - Page: page_title, page_text, page_quote
 * - Artintro: art_title, art_subtitle, art_description, art_credits, art_intro_header, art_intro_text
 *
 * Dependencies:
 * - Mwnf3ExhibitionImporter (must run first to create hierarchy collections)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import type { Mwnf3LegacyEavField } from '../../domain/types/index.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';

const MWNF3_SCHEMA = 'mwnf3';

/**
 * Pivoted EAV row: all fields for a given (entity_id, lang_id) combination.
 */
interface PivotedFields {
  [field: string]: string;
}

export class Mwnf3ExhibitionTranslationImporter extends BaseImporter {
  // Cache: exhibition_id → project_id
  private exhibitionProjectMap: Map<number, string> | null = null;
  // Cache: theme_id → exhibition_id
  private themeExhibitionMap: Map<number, number> | null = null;

  getName(): string {
    return 'Mwnf3ExhibitionTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing mwnf3 exhibition translations (EAV pivot)...');

      // ========================================================================
      // Pass 1: Exhibition translations
      // ========================================================================
      await this.importExhibitionTranslations(result);

      // ========================================================================
      // Pass 2: Theme translations
      // ========================================================================
      await this.importThemeTranslations(result);

      // ========================================================================
      // Pass 3: Page translations
      // ========================================================================
      await this.importPageTranslations(result);

      // ========================================================================
      // Pass 4: Artintro translations
      // ========================================================================
      await this.importArtintroTranslations(result);

      // ========================================================================
      // Pass 5: Artintro theme translations
      // ========================================================================
      await this.importArtintroThemeTranslations(result);

      // ========================================================================
      // Pass 6: Artintro page translations
      // ========================================================================
      await this.importArtintroPageTranslations(result);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('Mwnf3ExhibitionTranslationImporter', message);
    }

    return result;
  }

  // --------------------------------------------------------------------------
  // Exhibition translations (exhibition_fields EAV)
  // --------------------------------------------------------------------------
  private async importExhibitionTranslations(result: ImportResult): Promise<void> {
    const eavRows = await this.context.legacyDb.query<Mwnf3LegacyEavField>(
      `SELECT exhibition_id AS entity_id, lang_id, field, value
       FROM ${MWNF3_SCHEMA}.exhibition_fields
       WHERE value IS NOT NULL AND value != ''
       ORDER BY exhibition_id, lang_id, field`
    );

    const pivoted = this.pivotEavRows(eavRows);
    this.logInfo(`Found ${eavRows.length} exhibition EAV rows → ${pivoted.size} translations`);

    for (const [key, fields] of pivoted.entries()) {
      try {
        const [entityIdStr, legacyLang] = key.split(':');
        const entityId = parseInt(entityIdStr!, 10);
        const backwardCompat = `${MWNF3_SCHEMA}:exhibition_fields:${entityId}:${legacyLang}`;

        if (await this.entityExistsAsync(backwardCompat, 'collection_translation')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const languageId = await this.getLanguageIdByLegacyCodeAsync(legacyLang!);
        if (!languageId) {
          this.logWarning(`Exhibition ${entityId}: Unknown language '${legacyLang}', skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const collectionBackwardCompat = `${MWNF3_SCHEMA}:exhibitions:${entityId}`;
        const collectionId = await this.getEntityUuidAsync(collectionBackwardCompat, 'collection');
        if (!collectionId) {
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Exhibition translation ${entityId}/${legacyLang}: Collection not found`
          );
          this.logWarning(
            `Exhibition translation ${entityId}/${legacyLang}: Collection not found, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const contextId = await this.resolveContextForExhibition(entityId);
        if (!contextId) {
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Exhibition translation ${entityId}/${legacyLang}: Context not found`
          );
          this.logWarning(
            `Exhibition translation ${entityId}/${legacyLang}: Context not found, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const title = fields['exh_title'] || `Exhibition ${entityId}`;
        const description = fields['exh_description']
          ? convertHtmlToMarkdown(fields['exh_description'])
          : null;

        // Collect remaining fields into extra
        const extra: Record<string, string> = {};
        if (fields['exh_credits']) extra.credits = convertHtmlToMarkdown(fields['exh_credits']);
        const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

        if (this.isDryRun || this.isSampleOnlyMode) {
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.writeCollectionTranslation({
          collection_id: collectionId,
          language_id: languageId,
          context_id: contextId,
          title: convertHtmlToMarkdown(title),
          description,
          extra: extraJson,
          backward_compatibility: backwardCompat,
        });

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Exhibition translation ${key}: ${message}`);
        this.logError(`Exhibition translation ${key}`, message);
        this.showError();
      }
    }
  }

  // --------------------------------------------------------------------------
  // Theme translations (exhibition_theme_fields EAV)
  // --------------------------------------------------------------------------
  private async importThemeTranslations(result: ImportResult): Promise<void> {
    const eavRows = await this.context.legacyDb.query<Mwnf3LegacyEavField>(
      `SELECT theme_id AS entity_id, lang_id, field, value
       FROM ${MWNF3_SCHEMA}.exhibition_theme_fields
       WHERE value IS NOT NULL AND value != ''
       ORDER BY theme_id, lang_id, field`
    );

    const pivoted = this.pivotEavRows(eavRows);
    this.logInfo(`Found ${eavRows.length} theme EAV rows → ${pivoted.size} translations`);

    for (const [key, fields] of pivoted.entries()) {
      try {
        const [entityIdStr, legacyLang] = key.split(':');
        const entityId = parseInt(entityIdStr!, 10);
        const backwardCompat = `${MWNF3_SCHEMA}:exhibition_theme_fields:${entityId}:${legacyLang}`;

        if (await this.entityExistsAsync(backwardCompat, 'collection_translation')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const languageId = await this.getLanguageIdByLegacyCodeAsync(legacyLang!);
        if (!languageId) {
          this.logWarning(`Theme ${entityId}: Unknown language '${legacyLang}', skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const collectionBackwardCompat = `${MWNF3_SCHEMA}:exhibition_themes:${entityId}`;
        const collectionId = await this.getEntityUuidAsync(collectionBackwardCompat, 'collection');
        if (!collectionId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Theme translation ${entityId}/${legacyLang}: Collection not found`);
          this.logWarning(
            `Theme translation ${entityId}/${legacyLang}: Collection not found, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const contextId = await this.resolveContextForTheme(entityId);
        if (!contextId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Theme translation ${entityId}/${legacyLang}: Context not found`);
          this.logWarning(
            `Theme translation ${entityId}/${legacyLang}: Context not found, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const title = fields['theme_title'] || `Theme ${entityId}`;
        const extra: Record<string, string> = {};
        if (fields['theme_subtitle'])
          extra.subtitle = convertHtmlToMarkdown(fields['theme_subtitle']);
        const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

        if (this.isDryRun || this.isSampleOnlyMode) {
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.writeCollectionTranslation({
          collection_id: collectionId,
          language_id: languageId,
          context_id: contextId,
          title: convertHtmlToMarkdown(title),
          description: null,
          extra: extraJson,
          backward_compatibility: backwardCompat,
        });

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Theme translation ${key}: ${message}`);
        this.logError(`Theme translation ${key}`, message);
        this.showError();
      }
    }
  }

  // --------------------------------------------------------------------------
  // Page translations (exhibition_page_fields EAV)
  // --------------------------------------------------------------------------
  private async importPageTranslations(result: ImportResult): Promise<void> {
    const eavRows = await this.context.legacyDb.query<Mwnf3LegacyEavField>(
      `SELECT page_id AS entity_id, lang_id, field, value
       FROM ${MWNF3_SCHEMA}.exhibition_page_fields
       WHERE value IS NOT NULL AND value != ''
       ORDER BY page_id, lang_id, field`
    );

    const pivoted = this.pivotEavRows(eavRows);
    this.logInfo(`Found ${eavRows.length} page EAV rows → ${pivoted.size} translations`);

    for (const [key, fields] of pivoted.entries()) {
      try {
        const [entityIdStr, legacyLang] = key.split(':');
        const entityId = parseInt(entityIdStr!, 10);
        const backwardCompat = `${MWNF3_SCHEMA}:exhibition_page_fields:${entityId}:${legacyLang}`;

        if (await this.entityExistsAsync(backwardCompat, 'collection_translation')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const languageId = await this.getLanguageIdByLegacyCodeAsync(legacyLang!);
        if (!languageId) {
          this.logWarning(`Page ${entityId}: Unknown language '${legacyLang}', skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const collectionBackwardCompat = `${MWNF3_SCHEMA}:exhibition_pages:${entityId}`;
        const collectionId = await this.getEntityUuidAsync(collectionBackwardCompat, 'collection');
        if (!collectionId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Page translation ${entityId}/${legacyLang}: Collection not found`);
          this.logWarning(
            `Page translation ${entityId}/${legacyLang}: Collection not found, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Resolve context via theme → exhibition chain
        const contextId = await this.resolveContextForPage(entityId);
        if (!contextId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Page translation ${entityId}/${legacyLang}: Context not found`);
          this.logWarning(
            `Page translation ${entityId}/${legacyLang}: Context not found, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const title = fields['page_title'] || `Page ${entityId}`;
        const description = fields['page_text'] ? convertHtmlToMarkdown(fields['page_text']) : null;
        const quote = fields['page_quote'] ? convertHtmlToMarkdown(fields['page_quote']) : null;

        if (this.isDryRun || this.isSampleOnlyMode) {
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.writeCollectionTranslation({
          collection_id: collectionId,
          language_id: languageId,
          context_id: contextId,
          title: convertHtmlToMarkdown(title),
          description,
          quote,
          backward_compatibility: backwardCompat,
        });

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Page translation ${key}: ${message}`);
        this.logError(`Page translation ${key}`, message);
        this.showError();
      }
    }
  }

  // --------------------------------------------------------------------------
  // Artintro translations (artintro_fields EAV)
  // --------------------------------------------------------------------------
  private async importArtintroTranslations(result: ImportResult): Promise<void> {
    const eavRows = await this.context.legacyDb.query<Mwnf3LegacyEavField>(
      `SELECT artintro_id AS entity_id, lang_id, field, value
       FROM ${MWNF3_SCHEMA}.artintro_fields
       WHERE value IS NOT NULL AND value != ''
       ORDER BY artintro_id, lang_id, field`
    );

    const pivoted = this.pivotEavRows(eavRows);
    this.logInfo(`Found ${eavRows.length} artintro EAV rows → ${pivoted.size} translations`);

    for (const [key, fields] of pivoted.entries()) {
      try {
        const [entityIdStr, legacyLang] = key.split(':');
        const entityId = parseInt(entityIdStr!, 10);
        const backwardCompat = `${MWNF3_SCHEMA}:artintro_fields:${entityId}:${legacyLang}`;

        if (await this.entityExistsAsync(backwardCompat, 'collection_translation')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const languageId = await this.getLanguageIdByLegacyCodeAsync(legacyLang!);
        if (!languageId) {
          this.logWarning(`Artintro ${entityId}: Unknown language '${legacyLang}', skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const collectionBackwardCompat = `${MWNF3_SCHEMA}:artintros:${entityId}`;
        const collectionId = await this.getEntityUuidAsync(collectionBackwardCompat, 'collection');
        if (!collectionId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Artintro translation ${key}: Collection not found`);
          this.logWarning(`Artintro translation ${key}: Collection not found, skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const contextId = await this.getEntityUuidAsync(`${MWNF3_SCHEMA}:projects:ISL`, 'context');
        if (!contextId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Artintro translation ${key}: ISL context not found`);
          this.logWarning(`Artintro translation ${key}: ISL context not found, skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const title = fields['art_title'] || `Art Introduction ${entityId}`;
        const descParts: string[] = [];
        if (fields['art_description'])
          descParts.push(convertHtmlToMarkdown(fields['art_description']));
        if (fields['art_intro_text'])
          descParts.push(convertHtmlToMarkdown(fields['art_intro_text']));
        const description = descParts.length > 0 ? descParts.join('\n\n') : null;

        const extra: Record<string, string> = {};
        if (fields['art_subtitle']) extra.subtitle = convertHtmlToMarkdown(fields['art_subtitle']);
        if (fields['art_credits']) extra.credits = convertHtmlToMarkdown(fields['art_credits']);
        if (fields['art_intro_header'])
          extra.intro_header = convertHtmlToMarkdown(fields['art_intro_header']);
        const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

        if (this.isDryRun || this.isSampleOnlyMode) {
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.writeCollectionTranslation({
          collection_id: collectionId,
          language_id: languageId,
          context_id: contextId,
          title: convertHtmlToMarkdown(title),
          description,
          extra: extraJson,
          backward_compatibility: backwardCompat,
        });

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Artintro translation ${key}: ${message}`);
        this.logError(`Artintro translation ${key}`, message);
        this.showError();
      }
    }
  }

  // --------------------------------------------------------------------------
  // Artintro theme translations (artintro_theme_fields EAV)
  // --------------------------------------------------------------------------
  private async importArtintroThemeTranslations(result: ImportResult): Promise<void> {
    const eavRows = await this.context.legacyDb.query<Mwnf3LegacyEavField>(
      `SELECT theme_id AS entity_id, lang_id, field, value
       FROM ${MWNF3_SCHEMA}.artintro_theme_fields
       WHERE value IS NOT NULL AND value != ''
       ORDER BY theme_id, lang_id, field`
    );

    const pivoted = this.pivotEavRows(eavRows);
    this.logInfo(`Found ${eavRows.length} artintro theme EAV rows → ${pivoted.size} translations`);

    for (const [key, fields] of pivoted.entries()) {
      try {
        const [entityIdStr, legacyLang] = key.split(':');
        const entityId = parseInt(entityIdStr!, 10);
        const backwardCompat = `${MWNF3_SCHEMA}:artintro_theme_fields:${entityId}:${legacyLang}`;

        if (await this.entityExistsAsync(backwardCompat, 'collection_translation')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const languageId = await this.getLanguageIdByLegacyCodeAsync(legacyLang!);
        if (!languageId) {
          this.logWarning(`Artintro theme ${entityId}: Unknown language '${legacyLang}', skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const collectionBackwardCompat = `${MWNF3_SCHEMA}:artintro_themes:${entityId}`;
        const collectionId = await this.getEntityUuidAsync(collectionBackwardCompat, 'collection');
        if (!collectionId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Artintro theme translation ${key}: Collection not found`);
          this.logWarning(`Artintro theme translation ${key}: Collection not found, skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const contextId = await this.getEntityUuidAsync(`${MWNF3_SCHEMA}:projects:ISL`, 'context');
        if (!contextId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Artintro theme translation ${key}: ISL context not found`);
          this.logWarning(`Artintro theme translation ${key}: ISL context not found, skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const title = fields['theme_title'] || `Artintro Theme ${entityId}`;
        const extra: Record<string, string> = {};
        if (fields['theme_subtitle'])
          extra.subtitle = convertHtmlToMarkdown(fields['theme_subtitle']);
        const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

        if (this.isDryRun || this.isSampleOnlyMode) {
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.writeCollectionTranslation({
          collection_id: collectionId,
          language_id: languageId,
          context_id: contextId,
          title: convertHtmlToMarkdown(title),
          description: null,
          extra: extraJson,
          backward_compatibility: backwardCompat,
        });

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Artintro theme translation ${key}: ${message}`);
        this.logError(`Artintro theme translation ${key}`, message);
        this.showError();
      }
    }
  }

  // --------------------------------------------------------------------------
  // Artintro page translations (artintro_page_fields EAV)
  // --------------------------------------------------------------------------
  private async importArtintroPageTranslations(result: ImportResult): Promise<void> {
    const eavRows = await this.context.legacyDb.query<Mwnf3LegacyEavField>(
      `SELECT page_id AS entity_id, lang_id, field, value
       FROM ${MWNF3_SCHEMA}.artintro_page_fields
       WHERE value IS NOT NULL AND value != ''
       ORDER BY page_id, lang_id, field`
    );

    const pivoted = this.pivotEavRows(eavRows);
    this.logInfo(`Found ${eavRows.length} artintro page EAV rows → ${pivoted.size} translations`);

    for (const [key, fields] of pivoted.entries()) {
      try {
        const [entityIdStr, legacyLang] = key.split(':');
        const entityId = parseInt(entityIdStr!, 10);
        const backwardCompat = `${MWNF3_SCHEMA}:artintro_page_fields:${entityId}:${legacyLang}`;

        if (await this.entityExistsAsync(backwardCompat, 'collection_translation')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const languageId = await this.getLanguageIdByLegacyCodeAsync(legacyLang!);
        if (!languageId) {
          this.logWarning(`Artintro page ${entityId}: Unknown language '${legacyLang}', skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const collectionBackwardCompat = `${MWNF3_SCHEMA}:artintro_pages:${entityId}`;
        const collectionId = await this.getEntityUuidAsync(collectionBackwardCompat, 'collection');
        if (!collectionId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Artintro page translation ${key}: Collection not found`);
          this.logWarning(`Artintro page translation ${key}: Collection not found, skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const contextId = await this.getEntityUuidAsync(`${MWNF3_SCHEMA}:projects:ISL`, 'context');
        if (!contextId) {
          result.warnings = result.warnings || [];
          result.warnings.push(`Artintro page translation ${key}: ISL context not found`);
          this.logWarning(`Artintro page translation ${key}: ISL context not found, skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const title = fields['page_title'] || `Artintro Page ${entityId}`;
        const description = fields['page_text'] ? convertHtmlToMarkdown(fields['page_text']) : null;
        const quote = fields['page_quote'] ? convertHtmlToMarkdown(fields['page_quote']) : null;

        if (this.isDryRun || this.isSampleOnlyMode) {
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.writeCollectionTranslation({
          collection_id: collectionId,
          language_id: languageId,
          context_id: contextId,
          title: convertHtmlToMarkdown(title),
          description,
          quote,
          backward_compatibility: backwardCompat,
        });

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Artintro page translation ${key}: ${message}`);
        this.logError(`Artintro page translation ${key}`, message);
        this.showError();
      }
    }
  }

  // ==========================================================================
  // Shared helpers
  // ==========================================================================

  /**
   * Pivot EAV rows into grouped (entity_id:lang_id) → { field: value } map.
   */
  private pivotEavRows(rows: Mwnf3LegacyEavField[]): Map<string, PivotedFields> {
    const map = new Map<string, PivotedFields>();
    for (const row of rows) {
      const key = `${row.entity_id}:${row.lang_id}`;
      if (!map.has(key)) {
        map.set(key, {});
      }
      map.get(key)![row.field] = row.value;
    }
    return map;
  }

  private async resolveContextForExhibition(exhibitionId: number): Promise<string | null> {
    if (!this.exhibitionProjectMap) {
      const rows = await this.context.legacyDb.query<{ exhibition_id: number; project_id: string }>(
        `SELECT exhibition_id, project_id FROM ${MWNF3_SCHEMA}.exhibitions`
      );
      this.exhibitionProjectMap = new Map(rows.map((r) => [r.exhibition_id, r.project_id]));
    }

    const projectId = this.exhibitionProjectMap.get(exhibitionId);
    if (!projectId) return null;

    return this.getEntityUuidAsync(
      `${MWNF3_SCHEMA}:projects:${projectId.toUpperCase()}`,
      'context'
    );
  }

  private async resolveContextForTheme(themeId: number): Promise<string | null> {
    if (!this.themeExhibitionMap) {
      const rows = await this.context.legacyDb.query<{ theme_id: number; exhibition_id: number }>(
        `SELECT theme_id, exhibition_id FROM ${MWNF3_SCHEMA}.exhibition_themes`
      );
      this.themeExhibitionMap = new Map(rows.map((r) => [r.theme_id, r.exhibition_id]));
    }

    const exhibitionId = this.themeExhibitionMap.get(themeId);
    if (exhibitionId === undefined) return null;

    return this.resolveContextForExhibition(exhibitionId);
  }

  // page → theme → exhibition chain
  private pageThemeMap: Map<number, number> | null = null;

  private async resolveContextForPage(pageId: number): Promise<string | null> {
    if (!this.pageThemeMap) {
      const rows = await this.context.legacyDb.query<{ page_id: number; theme_id: number }>(
        `SELECT page_id, theme_id FROM ${MWNF3_SCHEMA}.exhibition_pages`
      );
      this.pageThemeMap = new Map(rows.map((r) => [r.page_id, r.theme_id]));
    }

    const themeId = this.pageThemeMap.get(pageId);
    if (themeId === undefined) return null;

    return this.resolveContextForTheme(themeId);
  }
}
