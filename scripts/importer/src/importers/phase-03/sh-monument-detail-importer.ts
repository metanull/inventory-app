/**
 * SH Monument Detail Importer
 *
 * Imports monument details (child items of monuments) from mwnf3_sharing_history database.
 * Monument details are Items with type='monument_detail' and parent_id pointing to their monument.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult, ItemData } from '../../core/types.js';
import {
  groupShMonumentDetailsByPK,
  transformShMonumentDetail,
  transformShMonumentDetailTranslation,
  formatShBackwardCompatibility,
} from '../../domain/transformers/index.js';
import type {
  ShLegacyMonumentDetail,
  ShLegacyMonumentDetailText,
  ShMonumentDetailGroup,
} from '../../domain/types/index.js';

export class ShMonumentDetailImporter extends BaseImporter {
  getName(): string {
    return 'ShMonumentDetailImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing Sharing History monument details...');

      // Query SH monument details
      const details = await this.context.legacyDb.query<ShLegacyMonumentDetail>(
        'SELECT * FROM mwnf3_sharing_history.sh_monument_details ORDER BY project_id, country, number, detail_id'
      );

      const detailTexts = await this.context.legacyDb.query<ShLegacyMonumentDetailText>(
        'SELECT * FROM mwnf3_sharing_history.sh_monument_detail_texts ORDER BY project_id, country, number, detail_id, lang'
      );

      if (details.length === 0) {
        this.logInfo('No SH monument details found');
        return result;
      }

      // Group details with their translations
      const detailGroups = groupShMonumentDetailsByPK(details, detailTexts);
      this.logInfo(
        `Found ${detailGroups.length} SH monument details (${detailTexts.length} translation rows)`
      );

      // Get default language ID and context
      const defaultLanguageId = this.getDefaultLanguageId();
      const defaultContextId = this.getDefaultContextId();

      // Import each detail group
      for (const group of detailGroups) {
        try {
          const imported = await this.importMonumentDetail(
            group,
            defaultLanguageId,
            defaultContextId,
            result
          );
          if (imported) {
            result.imported++;
            this.showProgress();
          } else {
            result.skipped++;
            this.showSkipped();
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          const backwardCompat = formatShBackwardCompatibility(
            'sh_monument_details',
            group.project_id,
            group.country,
            group.number,
            group.detail_id
          );
          result.errors.push(`${backwardCompat}: ${message}`);
          this.logError(`SH Monument Detail ${backwardCompat}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query SH monument details: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importMonumentDetail(
    group: ShMonumentDetailGroup,
    defaultLanguageId: string,
    defaultContextId: string,
    _result: ImportResult
  ): Promise<boolean> {
    const transformed = transformShMonumentDetail(group, defaultLanguageId);

    // Log warning if translation in default language is missing
    if (transformed.warning) {
      this.logWarning(transformed.warning);
    }

    // Check if already imported
    if (this.entityExists(transformed.backwardCompatibility, 'item')) {
      return false;
    }

    // Resolve parent monument
    const parentId = this.getEntityUuid(transformed.parentBackwardCompatibility, 'item');
    if (!parentId) {
      throw new Error(
        `Parent monument not found: ${transformed.parentBackwardCompatibility}. Detail ${transformed.backwardCompatibility} cannot be imported without its parent.`
      );
    }

    // Collect sample
    const sampleTranslation = group.translations[0];
    if (sampleTranslation) {
      this.collectSample(
        'sh_monument_detail',
        {
          ...group,
          _sample_translation: sampleTranslation,
        } as unknown as Record<string, unknown>,
        'success'
      );
    }

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import SH monument detail: ${group.project_id}:${group.country}:${group.number}:${group.detail_id}`
      );
      this.registerEntity(
        `sample-item-sh-detail-${group.project_id}-${group.country}-${group.number}-${group.detail_id}`,
        transformed.backwardCompatibility,
        'item'
      );
      return true;
    }

    // Resolve additional dependencies

    // Context (from SH project) - for translations
    const contextBackwardCompat = formatShBackwardCompatibility('sh_projects', group.project_id);
    const contextId = this.getEntityUuid(contextBackwardCompat, 'context') || defaultContextId;

    // Collection (same backward_compat as context)
    const collectionId = this.getEntityUuid(contextBackwardCompat, 'collection');
    if (!collectionId) {
      throw new Error(`SH Collection not found for project ${group.project_id}`);
    }

    // Project
    const projectId = this.getEntityUuid(contextBackwardCompat, 'project');

    // Create Item (monument detail)
    const itemData: ItemData = {
      ...transformed.data,
      parent_id: parentId,
      collection_id: collectionId,
      partner_id: null, // Details inherit partner from parent
      project_id: projectId || null,
    };

    const itemId = await this.context.strategy.writeItem(itemData);
    this.registerEntity(itemId, transformed.backwardCompatibility, 'item');

    // Create translations
    for (const translation of group.translations) {
      try {
        const translationResult = transformShMonumentDetailTranslation(translation);
        if (!translationResult) {
          this.logWarning(
            `Skipping translation for SH monument detail ${transformed.backwardCompatibility}:${translation.lang} - missing required fields`
          );
          continue;
        }
        await this.context.strategy.writeItemTranslation({
          ...translationResult.data,
          item_id: itemId,
          context_id: contextId,
          backward_compatibility: transformed.backwardCompatibility,
        });
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        this.logWarning(
          `Failed to create translation for SH monument detail ${transformed.backwardCompatibility}:${translation.lang}: ${message}`
        );
      }
    }

    return true;
  }
}
