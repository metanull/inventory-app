/**
 * Monument Importer
 *
 * Imports monuments (items) from legacy database.
 * Similar to ObjectImporter but for monuments.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import {
  groupMonumentsByPK,
  transformMonument,
  transformMonumentTranslation,
  extractMonumentTags,
  planMonumentTranslations,
} from '../../domain/transformers/index.js';
import type { LegacyMonument, MonumentGroup } from '../../domain/types/index.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';
import { TagHelper } from '../../helpers/tag-helper.js';
import { AuthorHelper } from '../../helpers/author-helper.js';

export class MonumentImporter extends BaseImporter {
  private tagHelper!: TagHelper;
  private authorHelper!: AuthorHelper;

  getName(): string {
    return 'MonumentImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    // Initialize helpers
    this.tagHelper = new TagHelper(this.context.strategy, this.context.tracker);
    this.authorHelper = new AuthorHelper(this.context.strategy, this.context.tracker);

    try {
      this.logInfo('Importing monuments...');

      // Query all monuments (denormalized - multiple rows per monument)
      const monuments = await this.context.legacyDb.query<LegacyMonument>(
        'SELECT * FROM mwnf3.monuments ORDER BY project_id, country, institution_id, number'
      );

      if (monuments.length === 0) {
        this.logInfo('No monuments found');
        return result;
      }

      // Group monuments by non-lang PK columns
      const monumentGroups = groupMonumentsByPK(monuments);
      this.logInfo(`Found ${monumentGroups.length} unique monuments (${monuments.length} language rows)`);

      // Check if EPM context exists
      const epmContextBackwardCompat = formatBackwardCompatibility({
        schema: 'mwnf3',
        table: 'projects',
        pkValues: ['EPM'],
      });
      const epmContextId = this.getEntityUuid(epmContextBackwardCompat);
      const hasEpmContext = !!epmContextId;

      // Import each monument group
      for (const group of monumentGroups) {
        try {
          const imported = await this.importMonument(group, hasEpmContext, epmContextId, result);
          if (imported) {
            result.imported++;
            this.showProgress();
          } else {
            result.skipped++;
            this.showSkipped();
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${group.project_id}:${group.institution_id}:${group.number}: ${message}`);
          this.logError(`Monument ${group.project_id}:${group.institution_id}:${group.number}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query monuments: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importMonument(
    group: MonumentGroup,
    hasEpmContext: boolean,
    epmContextId: string | null,
    result: ImportResult
  ): Promise<boolean> {
    const transformed = transformMonument(group);

    // Check if already imported
    if (this.entityExists(transformed.backwardCompatibility)) {
      return false;
    }

    // Collect sample
    const sampleTranslation = group.translations[0];
    if (sampleTranslation) {
      this.collectSample(
        'monument',
        sampleTranslation as unknown as Record<string, unknown>,
        'success',
        undefined,
        mapLanguageCode(sampleTranslation.lang)
      );
    }

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import monument: ${group.project_id}:${group.institution_id}:${group.number}`
      );
      this.registerEntity(
        `sample-item-${group.project_id}-${group.institution_id}-${group.number}`,
        transformed.backwardCompatibility,
        'item'
      );
      return true;
    }

    // Resolve dependencies
    const contextBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'projects',
      pkValues: [group.project_id],
    });
    const contextId = this.getEntityUuid(contextBackwardCompat);
    if (!contextId) {
      this.logWarning(`Skipping monument ${group.project_id}:${group.institution_id}:${group.number} - project not found`);
      return false;
    }

    const collectionBackwardCompat = `${contextBackwardCompat}:collection`;
    const collectionId = this.getEntityUuid(collectionBackwardCompat);
    if (!collectionId) {
      this.logWarning(`Skipping monument ${group.project_id}:${group.institution_id}:${group.number} - collection not found`);
      return false;
    }

    const partnerBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'institutions',
      pkValues: [group.institution_id, group.country],
    });
    const partnerId = this.getEntityUuid(partnerBackwardCompat);
    if (!partnerId) {
      this.logWarning(`Skipping monument ${group.project_id}:${group.institution_id}:${group.number} - institution not found`);
      return false;
    }

    const projectBackwardCompat = `${contextBackwardCompat}:project`;
    const projectId = this.getEntityUuid(projectBackwardCompat);

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
    const translationPlans = planMonumentTranslations(group, hasEpmContext);
    for (const plan of translationPlans) {
      try {
        // Cast to LegacyMonument since planMonumentTranslations works with the type
        const translation = plan.translation as unknown as LegacyMonument;
        const translationResult = transformMonumentTranslation(translation, plan.descriptionField);
        if (!translationResult) continue;

        // Add any warnings to result
        if (translationResult.warnings.length > 0) {
          result.warnings?.push(...translationResult.warnings);
        }

        // Resolve authors
        const authorId = translationResult.authorName
          ? await this.authorHelper.findOrCreate(translationResult.authorName)
          : null;
        const textCopyEditorId = translationResult.textCopyEditorName
          ? await this.authorHelper.findOrCreate(translationResult.textCopyEditorName)
          : null;
        const translatorId = translationResult.translatorName
          ? await this.authorHelper.findOrCreate(translationResult.translatorName)
          : null;
        const translationCopyEditorId = translationResult.translationCopyEditorName
          ? await this.authorHelper.findOrCreate(translationResult.translationCopyEditorName)
          : null;

        // Determine context for translation
        const translationContextId = plan.contextType === 'epm' ? epmContextId! : contextId;

        await this.context.strategy.writeItemTranslation({
          ...translationResult.data,
          item_id: itemId,
          context_id: translationContextId,
          author_id: authorId,
          text_copy_editor_id: textCopyEditorId,
          translator_id: translatorId,
          translation_copy_editor_id: translationCopyEditorId,
        });
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        this.logWarning(`Failed to create translation: ${message}`);
      }
    }

    // Create tags (from first translation only to avoid duplicates)
    const firstTranslation = group.translations[0];
    if (firstTranslation) {
      const extractedTags = extractMonumentTags(firstTranslation);
      const tagIds: string[] = [];

      if (extractedTags.keywords.length > 0) {
        for (const keyword of extractedTags.keywords) {
          const tagId = await this.tagHelper.findOrCreate(keyword, 'keyword', extractedTags.languageId);
          if (tagId) tagIds.push(tagId);
        }
      }

      if (tagIds.length > 0) {
        await this.tagHelper.attachToItem(itemId, tagIds);
      }
    }

    return true;
  }
}
