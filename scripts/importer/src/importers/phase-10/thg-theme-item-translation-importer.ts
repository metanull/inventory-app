/**
 * THG Theme Item Translation Importer
 *
 * Stores theme_item_i18n contextual descriptions on the collection_item pivot extra field,
 * grouped by language. Does NOT write item_translations — contextual text describes the
 * item within the specific gallery theme context, not the item itself.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.theme_item_i18n (gallery_id, theme_id, item_id, language_id, contextual_description)
 *
 * New schema:
 * - collection_item.extra (JSON) — merged field:
 *     { "contextual_descriptions": { "eng": "...", "fra": "..." } }
 *
 * Context: Uses the theme collection (ThgThemeImporter BC) for the collection_item lookup.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy theme_item_i18n structure
 */
interface LegacyThemeItemI18n {
  gallery_id: number;
  theme_id: number;
  item_id: number;
  language_id: string; // 2-letter code
  contextual_description: string | null;
}

/**
 * Legacy theme_item structure for item resolution
 */
interface LegacyThemeItem {
  gallery_id: number;
  theme_id: number;
  item_id: number;
  // mwnf3 object references
  mwnf3_object_project_id: string | null;
  mwnf3_object_country_id: string | null;
  mwnf3_object_partner_id: string | null;
  mwnf3_object_item_id: number | null;
  // mwnf3 monument references
  mwnf3_monument_project_id: string | null;
  mwnf3_monument_country_id: string | null;
  mwnf3_monument_partner_id: string | null;
  mwnf3_monument_item_id: number | null;
  // mwnf3 monument detail references
  mwnf3_monument_detail_project_id: string | null;
  mwnf3_monument_detail_country_id: string | null;
  mwnf3_monument_detail_partner_id: string | null;
  mwnf3_monument_detail_item_id: number | null;
  mwnf3_monument_detail_detail_id: number | null;
}

export class ThgThemeItemTranslationImporter extends BaseImporter {
  // Cache theme_item data for item resolution
  private themeItemCache: Map<string, LegacyThemeItem> = new Map();

  getName(): string {
    return 'ThgThemeItemTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Loading theme_item data for item resolution...');

      // Load theme_item data to resolve item references
      try {
        const themeItems = await this.context.legacyDb.query<LegacyThemeItem>(
          `SELECT gallery_id, theme_id, item_id,
                  mwnf3_object_project_id, mwnf3_object_country_id, mwnf3_object_partner_id, mwnf3_object_item_id,
                  mwnf3_monument_project_id, mwnf3_monument_country_id, mwnf3_monument_partner_id, mwnf3_monument_item_id,
                  mwnf3_monument_detail_project_id, mwnf3_monument_detail_country_id, mwnf3_monument_detail_partner_id,
                  mwnf3_monument_detail_item_id, mwnf3_monument_detail_detail_id
           FROM mwnf3_thematic_gallery.theme_item`
        );

        for (const item of themeItems) {
          const key = `${item.gallery_id}.${item.theme_id}.${item.item_id}`;
          this.themeItemCache.set(key, item);
        }

        this.logInfo(`Loaded ${this.themeItemCache.size} theme_item records`);
      } catch (queryError) {
        const message = queryError instanceof Error ? queryError.message : String(queryError);
        if (message.includes("doesn't exist") || message.includes('Unknown column')) {
          this.logInfo(`⚠️ Skipping: Legacy theme_item table not available (${message})`);
          result.warnings = result.warnings || [];
          result.warnings.push(`Legacy theme_item table not available: ${message}`);
          return result;
        }
        throw queryError;
      }

      this.logInfo(
        'Importing contextual item descriptions from theme_item_i18n into collection_item.extra...'
      );

      // Query translations from legacy database
      const translations = await this.context.legacyDb.query<LegacyThemeItemI18n>(
        `SELECT gallery_id, theme_id, item_id, language_id, contextual_description
         FROM mwnf3_thematic_gallery.theme_item_i18n
         WHERE contextual_description IS NOT NULL AND contextual_description != ''
         ORDER BY gallery_id, theme_id, item_id, language_id`
      );

      this.logInfo(`Found ${translations.length} contextual item descriptions to import`);

      // Group by (gallery_id, theme_id, item_id) to process all languages at once
      type GroupKey = string;
      const grouped = new Map<GroupKey, LegacyThemeItemI18n[]>();
      for (const row of translations) {
        const key = `${row.gallery_id}.${row.theme_id}.${row.item_id}`;
        const existing = grouped.get(key);
        if (existing) {
          existing.push(row);
        } else {
          grouped.set(key, [row]);
        }
      }

      for (const [groupKey, rows] of grouped) {
        const first = rows[0];
        try {
          const themeItem = this.themeItemCache.get(groupKey);
          if (!themeItem) {
            result.warnings = result.warnings || [];
            result.warnings.push(`Theme item ${groupKey}: theme_item record not found, skipping`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve the theme collection BC
          const themeBC = `mwnf3_thematic_gallery:theme:${first.gallery_id}:${first.theme_id}`;
          const collectionId = await this.getEntityUuidAsync(themeBC, 'collection');
          if (!collectionId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme item ${groupKey}: theme collection not found (${themeBC}), skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve the item reference
          const itemBackwardCompat = this.resolveItemReference(themeItem);
          if (!itemBackwardCompat) {
            // Not an mwnf3 item — skip silently (SH/Explore items may not need this)
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');
          if (!itemId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Theme item ${groupKey}: item not found (${itemBackwardCompat}), skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Build the contextual_descriptions map and source_bc_by_language map keyed by ISO-3 language ID
          const contextualDescriptions: Record<string, string> = {};
          const sourceBcByLanguage: Record<string, string> = {};
          let validLanguageCount = 0;
          for (const row of rows) {
            if (!row.language_id) {
              result.warnings = result.warnings || [];
              result.warnings.push(
                `Theme item ${groupKey}: translation row has no language value (table: theme_item_i18n, pk: gallery_id=${row.gallery_id}, theme_id=${row.theme_id}, item_id=${row.item_id}, language_id=${row.language_id}), skipping language`
              );
              continue;
            }
            const languageId = await this.getLanguageIdByLegacyCodeAsync(row.language_id);
            if (!languageId) {
              result.warnings = result.warnings || [];
              result.warnings.push(
                `Theme item ${groupKey}: unknown language '${row.language_id}', skipping language`
              );
              continue;
            }
            if (row.contextual_description) {
              contextualDescriptions[languageId] = row.contextual_description;
              sourceBcByLanguage[languageId] = `mwnf3_thematic_gallery:theme_item_i18n:${row.gallery_id}:${row.theme_id}:${row.item_id}:${row.language_id}`;
              validLanguageCount++;
            }
          }

          if (validLanguageCount === 0) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          this.collectSample(
            'thg_theme_item_translation',
            {
              ...first,
              resolved_item_backward_compat: itemBackwardCompat,
              resolved_collection_id: collectionId,
            } as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would update collection_item.extra: collection=${collectionId} item=${itemId} (${validLanguageCount} languages)`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // Read existing extra and merge contextual_descriptions and source_bc_by_language into it
          const existingExtra =
            await this.context.strategy.getCollectionItemExtra(collectionId, itemId);
          const mergedExtra: Record<string, unknown> = existingExtra ?? {};
          mergedExtra.contextual_descriptions = contextualDescriptions;
          mergedExtra.source_bc_by_language = sourceBcByLanguage;

          await this.context.strategy.setCollectionItemExtra(
            collectionId,
            itemId,
            JSON.stringify(mergedExtra)
          );

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Theme item ${groupKey}: ${message}`);
          this.logError(`Theme item ${groupKey}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgThemeItemTranslationImporter', message);
    }

    return result;
  }

  /**
   * Resolve item reference from theme_item to backward_compatibility string
   * Returns null if not an mwnf3 item
   */
  private resolveItemReference(legacy: LegacyThemeItem): string | null {
    // Check mwnf3_object reference
    if (
      legacy.mwnf3_object_project_id &&
      legacy.mwnf3_object_country_id &&
      legacy.mwnf3_object_partner_id &&
      legacy.mwnf3_object_item_id !== null
    ) {
      return `mwnf3:objects:${legacy.mwnf3_object_project_id}:${legacy.mwnf3_object_country_id}:${legacy.mwnf3_object_partner_id}:${legacy.mwnf3_object_item_id}`;
    }

    // Check mwnf3_monument reference
    if (
      legacy.mwnf3_monument_project_id &&
      legacy.mwnf3_monument_country_id &&
      legacy.mwnf3_monument_partner_id &&
      legacy.mwnf3_monument_item_id !== null
    ) {
      return `mwnf3:monuments:${legacy.mwnf3_monument_project_id}:${legacy.mwnf3_monument_country_id}:${legacy.mwnf3_monument_partner_id}:${legacy.mwnf3_monument_item_id}`;
    }

    // Check mwnf3_monument_detail reference
    if (
      legacy.mwnf3_monument_detail_project_id &&
      legacy.mwnf3_monument_detail_country_id &&
      legacy.mwnf3_monument_detail_partner_id &&
      legacy.mwnf3_monument_detail_item_id !== null &&
      legacy.mwnf3_monument_detail_detail_id !== null
    ) {
      return `mwnf3:monument_details:${legacy.mwnf3_monument_detail_project_id}:${legacy.mwnf3_monument_detail_country_id}:${legacy.mwnf3_monument_detail_partner_id}:${legacy.mwnf3_monument_detail_item_id}:${legacy.mwnf3_monument_detail_detail_id}`;
    }

    // Not an mwnf3 item
    return null;
  }
}

