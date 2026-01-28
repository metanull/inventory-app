/**
 * SH Object Importer
 *
 * Imports objects (items) from mwnf3_sharing_history database.
 * Key differences from mwnf3:
 * - PK: (project_id, country, number) - no partner in PK
 * - Partner linked via FK `partners_id`
 * - pd_country stored in extra JSON as `country_id_present_days`
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import {
  groupShObjectsByPK,
  transformShObject,
  transformShObjectTranslation,
  formatShBackwardCompatibility,
} from '../../domain/transformers/index.js';
import type {
  ShLegacyObject,
  ShLegacyObjectText,
  ShObjectGroup,
} from '../../domain/types/index.js';

export class ShObjectImporter extends BaseImporter {
  getName(): string {
    return 'ShObjectImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing Sharing History objects...');

      // Query SH objects
      const objects = await this.context.legacyDb.query<ShLegacyObject>(
        'SELECT * FROM mwnf3_sharing_history.sh_objects ORDER BY project_id, country, number'
      );

      const objectTexts = await this.context.legacyDb.query<ShLegacyObjectText>(
        'SELECT * FROM mwnf3_sharing_history.sh_objects_texts ORDER BY project_id, country, number, lang'
      );

      if (objects.length === 0) {
        this.logInfo('No SH objects found');
        return result;
      }

      // Group objects with their translations
      const objectGroups = groupShObjectsByPK(objects, objectTexts);
      this.logInfo(
        `Found ${objectGroups.length} SH objects (${objectTexts.length} translation rows)`
      );

      // Get default language ID
      const defaultLanguageId = this.getDefaultLanguageId();

      // Import each object group
      for (const group of objectGroups) {
        try {
          const imported = await this.importObject(group, defaultLanguageId, result);
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
            'sh_objects',
            group.project_id,
            group.country,
            group.number
          );
          result.errors.push(`${backwardCompat}: ${message}`);
          this.logError(`SH Object ${backwardCompat}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query SH objects: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importObject(
    group: ShObjectGroup,
    defaultLanguageId: string,
    _result: ImportResult
  ): Promise<boolean> {
    const transformed = transformShObject(group, defaultLanguageId);

    // Log warning if translation in default language is missing
    if (transformed.warning) {
      this.logWarning(transformed.warning);
    }

    // Check if already imported
    if (this.entityExists(transformed.backwardCompatibility, 'item')) {
      return false;
    }

    // Collect sample
    const sampleTranslation = group.translations[0];
    if (sampleTranslation) {
      this.collectSample(
        'sh_object',
        {
          ...group,
          _sample_translation: sampleTranslation,
        } as unknown as Record<string, unknown>,
        'success'
      );
    }

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import SH object: ${group.project_id}:${group.country}:${group.number}`
      );
      this.registerEntity(
        `sample-item-sh-${group.project_id}-${group.country}-${group.number}`,
        transformed.backwardCompatibility,
        'item'
      );
      return true;
    }

    // Resolve dependencies

    // Context (from SH project)
    const contextBackwardCompat = formatShBackwardCompatibility('sh_projects', group.project_id);
    const contextId = this.getEntityUuid(contextBackwardCompat, 'context');
    if (!contextId) {
      throw new Error(
        `SH Project context not found: ${contextBackwardCompat}. Object ${transformed.backwardCompatibility} cannot be imported without its project.`
      );
    }

    // Collection (same backward_compat as context)
    const collectionId = this.getEntityUuid(contextBackwardCompat, 'collection');
    if (!collectionId) {
      throw new Error(
        `SH Collection not found: ${contextBackwardCompat}. Object ${transformed.backwardCompatibility} cannot be imported without its collection.`
      );
    }

    // Project
    const projectId = this.getEntityUuid(contextBackwardCompat, 'project');

    // Partner (optional - use SH partner if available)
    let partnerId: string | null = null;
    if (group.partners_id) {
      const partnerBackwardCompat = formatShBackwardCompatibility('sh_partners', group.partners_id);
      partnerId = this.getEntityUuid(partnerBackwardCompat, 'partner');
      if (!partnerId) {
        this.logWarning(
          `SH Partner not found: ${partnerBackwardCompat} for object ${transformed.backwardCompatibility}`
        );
      }
    }

    // Create Item
    const itemData = {
      ...transformed.data,
      collection_id: collectionId,
      partner_id: partnerId,
      project_id: projectId || null,
    };

    const itemId = await this.context.strategy.writeItem(itemData);
    this.registerEntity(itemId, transformed.backwardCompatibility, 'item');

    // Create translations
    for (const translation of group.translations) {
      try {
        const translationResult = transformShObjectTranslation(translation);
        if (!translationResult) {
          this.logWarning(
            `Skipping translation for SH object ${transformed.backwardCompatibility}:${translation.lang} - missing required fields`
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
          `Failed to create translation for SH object ${transformed.backwardCompatibility}:${translation.lang}: ${message}`
        );
      }
    }

    return true;
  }
}
