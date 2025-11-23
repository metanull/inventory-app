import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';
import { mapLanguageCode } from '../../utils/CodeMappings.js';

interface LegacyMonument {
  project_id: string;
  country: string;
  institution_id: string;
  number: string;
  lang: string;
  working_number?: string;
  name?: string;
  name2?: string;
  typeof?: string;
  location?: string;
  province?: string;
  address?: string;
  phone?: string;
  fax?: string;
  email?: string;
  institution?: string;
  date_description?: string;
  start_date?: string | null;
  end_date?: string | null;
  dynasty?: string;
  patrons?: string;
  architects?: string;
  description?: string;
  description2?: string;
  history?: string;
  datationmethod?: string;
  bibliography?: string;
  external_sources?: string;
  keywords?: string;
  preparedby?: string;
  copyeditedby?: string;
  translationby?: string;
  translationcopyeditedby?: string;
}

interface MonumentGroup {
  project_id: string;
  country: string;
  institution_id: string;
  number: string;
  translations: LegacyMonument[];
}

/**
 * Imports monuments from mwnf3.monuments
 *
 * CRITICAL: monuments table is denormalized with language in PK
 * - PK: project_id, country, institution_id, number, LANG (5 columns)
 * - Multiple rows per monument (one per language)
 * - Must group by non-lang columns and create ItemTranslations
 * - backward_compatibility: mwnf3:monuments:{proj}:{country}:{inst}:{num} (NO LANG)
 */
export class MonumentImporter extends BaseImporter {
  getName(): string {
    return 'MonumentImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
      warnings: [],
    };

    try {
      this.logInfo('Importing monuments...');

      // Query all monuments (denormalized - multiple rows per monument)
      const monuments = await this.context.legacyDb.query<LegacyMonument>(
        `SELECT * FROM mwnf3.monuments ORDER BY project_id, country, institution_id, number`
      );

      if (monuments.length === 0) {
        this.logInfo('No monuments found');
        return result;
      }

      // Group monuments by non-lang PK columns
      const monumentGroups = this.groupMonumentsByPK(monuments);
      this.logInfo(
        `Found ${monumentGroups.length} unique monuments (${monuments.length} language rows)`
      );

      const limitedGroups = monumentGroups;

      // Import each monument group
      for (const group of limitedGroups) {
        try {
          const imported = await this.importMonument(group, result);
          if (imported) {
            result.imported++;
            this.showProgress();
          } else {
            result.skipped++;
            this.showSkipped();
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          // Log detailed error info
          if (error && typeof error === 'object' && 'response' in error) {
            const axiosError = error as { response?: { status?: number; data?: unknown } };
            this.logError(
              `MonumentImporter:${group.project_id}:${group.institution_id}:${group.number}`,
              error instanceof Error ? error : new Error(message),
              { responseData: axiosError.response?.data }
            );
          }
          result.errors.push(
            `${group.project_id}:${group.institution_id}:${group.number}: ${message}`
          );
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

  /**
   * Group denormalized monument rows by non-lang PK columns
   */
  private groupMonumentsByPK(monuments: LegacyMonument[]): MonumentGroup[] {
    const groups = new Map<string, MonumentGroup>();

    for (const monument of monuments) {
      const key = `${monument.project_id}:${monument.country}:${monument.institution_id}:${monument.number}`;

      if (!groups.has(key)) {
        groups.set(key, {
          project_id: monument.project_id,
          country: monument.country,
          institution_id: monument.institution_id,
          number: monument.number,
          translations: [],
        });
      }

      groups.get(key)!.translations.push(monument);
    }

    return Array.from(groups.values());
  }

  private async importMonument(group: MonumentGroup, result: ImportResult): Promise<boolean> {
    // Format backward_compatibility (NO LANG)
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'monuments',
      pkValues: [group.project_id, group.country, group.institution_id, group.number],
    });

    // Check if already imported
    if (this.context.tracker.exists(backwardCompat)) {
      return false;
    }

    if (this.context.dryRun) {
      this.logInfo(
        `[DRY-RUN] Would import monument: ${group.project_id}:${group.institution_id}:${group.number}`
      );
      return true;
    }

    // Resolve project_id → context_id
    const contextBackwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'projects',
      pkValues: [group.project_id],
    });
    const contextId = this.context.tracker.getUuid(contextBackwardCompat);

    if (!contextId) {
      this.logWarning(
        `Skipping monument ${group.project_id}:${group.institution_id}:${group.number} - project not found`,
        { project_id: group.project_id, institution_id: group.institution_id, number: group.number }
      );
      return false;
    }

    // Resolve context → collection (root collection for this project)
    const collectionBackwardCompat = `${contextBackwardCompat}:collection`;
    const collectionId = this.context.tracker.getUuid(collectionBackwardCompat);

    if (!collectionId) {
      this.logWarning(
        `Skipping monument ${group.project_id}:${group.institution_id}:${group.number} - collection not found`,
        { project_id: group.project_id }
      );
      return false;
    }

    // Resolve institution_id → partner_id
    const partnerBackwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'institutions',
      pkValues: [group.institution_id, group.country],
    });
    const partnerId = this.context.tracker.getUuid(partnerBackwardCompat);

    if (!partnerId) {
      this.logWarning(
        `Skipping monument ${group.project_id}:${group.institution_id}:${group.number} - institution not found`,
        { institution_id: group.institution_id, country: group.country }
      );
      return false;
    }

    // Use first translation for base data
    const firstTranslation = group.translations[0];
    if (!firstTranslation) {
      throw new Error('No translations found for monument');
    }

    // Create Item
    const itemResponse = await this.context.apiClient.item.itemStore({
      internal_name: firstTranslation.working_number || firstTranslation.name || group.number,
      type: 'monument',
      collection_id: collectionId,
      partner_id: partnerId,
      backward_compatibility: backwardCompat,
    });

    const itemId = itemResponse.data.data.id;

    // Register in tracker
    this.context.tracker.register({
      uuid: itemId,
      backwardCompatibility: backwardCompat,
      entityType: 'item',
      createdAt: new Date(),
    });

    // Create translations for each language
    for (const translation of group.translations) {
      await this.importTranslation(itemId, contextId, translation, result);
    }

    // Parse and create tags from dynasty and keywords (only from first translation to avoid duplicates)
    const tagIds: string[] = [];

    if (firstTranslation.dynasty) {
      const dynastyTags = await this.findOrCreateTags(firstTranslation.dynasty, 'dynasty', result);
      tagIds.push(...dynastyTags);
    }

    if (firstTranslation.keywords) {
      const keywordTags = await this.findOrCreateTags(firstTranslation.keywords, 'keyword', result);
      tagIds.push(...keywordTags);
    }

    // Attach all tags to item
    if (tagIds.length > 0) {
      await this.attachTags(itemId, tagIds);
    }

    return true;
  }

  private async importTranslation(
    itemId: string,
    contextId: string,
    monument: LegacyMonument,
    result: ImportResult
  ): Promise<void> {
    // Map legacy ISO 639-1 to ISO 639-3
    const languageId = mapLanguageCode(monument.lang);

    // TODO: Resolve author IDs when API endpoints are available
    // For now, author names are stored as text in extra field
    const authorId = null;
    const textCopyEditorId = null;
    const translatorId = null;
    const translationCopyEditorId = null;

    // Combine location, province, and address
    const locationParts = [monument.location, monument.province, monument.address].filter(Boolean);
    const locationFull = locationParts.length > 0 ? locationParts.join(', ') : null;

    // Store author names and additional info in extra field
    const extraData = {
      preparedby: monument.preparedby || null,
      copyeditedby: monument.copyeditedby || null,
      translationby: monument.translationby || null,
      translationcopyeditedby: monument.translationcopyeditedby || null,
      phone: monument.phone || null,
      fax: monument.fax || null,
      email: monument.email || null,
      institution: monument.institution || null,
      patrons: monument.patrons || null,
      architects: monument.architects || null,
      history: monument.history || null,
      external_sources: monument.external_sources || null,
    };

    // DATA QUALITY: Handle missing required fields with fallbacks
    let name = monument.name;
    let description = monument.description;

    if (!name || name.trim() === '') {
      const warning = `${monument.project_id}:${monument.institution_id}:${monument.number}:${monument.lang} - Missing 'name', using fallback`;
      this.logWarning(`DATA QUALITY: Monument translation ${warning}`, {
        monument_key: `${monument.project_id}:${monument.institution_id}:${monument.number}`,
        language: monument.lang,
        issue: 'Missing name',
        fallback_used: monument.working_number || `Monument ${monument.number}`,
      });
      result.warnings?.push(warning);
      name = monument.working_number || `Monument ${monument.number}`;
    }

    if (!description || description.trim() === '') {
      const warning = `${monument.project_id}:${monument.institution_id}:${monument.number}:${monument.lang} - Missing 'description', using fallback`;
      this.logWarning(`DATA QUALITY: Monument translation ${warning}`, {
        monument_key: `${monument.project_id}:${monument.institution_id}:${monument.number}`,
        language: monument.lang,
        issue: 'Missing description',
        fallback_used: '(No description available)',
      });
      result.warnings?.push(warning);
      description = '(No description available)';
    }

    await this.context.apiClient.itemTranslation.itemTranslationStore({
      item_id: itemId,
      language_id: languageId,
      context_id: contextId,
      name: name,
      description: description,
      alternate_name: monument.name2 || null,
      type: monument.typeof || null,
      dates: monument.date_description || null,
      location: locationFull,
      method_for_datation: monument.datationmethod || null,
      bibliography: monument.bibliography || null,
      author_id: authorId,
      text_copy_editor_id: textCopyEditorId,
      translator_id: translatorId,
      translation_copy_editor_id: translationCopyEditorId,
      extra: JSON.stringify(extraData),
    });
  }



  /**
   * Parse and create tags from comma-separated string
   * Returns array of tag UUIDs
   */
  private async findOrCreateTags(tagString: string, category: string, result: ImportResult): Promise<string[]> {
    if (!tagString || tagString.trim() === '') {
      return [];
    }

    // Split by comma and clean
    const tagNames = tagString
      .split(',')
      .map((t) => t.trim())
      .filter((t) => t !== '');

    const tagIds: string[] = [];

    for (const tagName of tagNames) {
      const backwardCompat = BackwardCompatibilityFormatter.format({
        schema: 'mwnf3',
        table: 'tags',
        pkValues: [category, tagName],
      });

      // Check if already exists
      let tagId = this.context.tracker.getUuid(backwardCompat);

      if (!tagId && !this.context.dryRun) {
        // Create new tag via API
        try {
          const response = await this.context.apiClient.tag.tagStore({
            internal_name: `${category}:${tagName}`,
            description: `${category} tag`,
            backward_compatibility: backwardCompat,
          });

          tagId = response.data.data.id;

          // Register in tracker (use 'item' as valid entityType)
          this.context.tracker.register({
            uuid: tagId,
            backwardCompatibility: backwardCompat,
            entityType: 'item',
            createdAt: new Date(),
          });
        } catch (error) {
          // Handle 422 conflict - tag already exists with this internal_name
          if (error && typeof error === 'object' && 'response' in error) {
            const axiosError = error as { response?: { status?: number } };
            if (axiosError.response?.status === 422) {
              // Try to find existing tag with same backward_compatibility (case-insensitive)
              tagId = await this.findExistingTagByBackwardCompat(backwardCompat);
              if (tagId) {
                const warning = `Tag '${category}:${tagName}' - Duplicate internal_name resolved`;
                this.logWarning(`DATA QUALITY: ${warning}`, {
                  category,
                  tagName,
                  issue: 'Duplicate internal_name',
                  resolution: 'Found existing tag by backward_compatibility',
                });
                result.warnings?.push(warning);
              }
            }
          }
          if (!tagId) {
            // Log error but continue - tags are not critical
            this.logError(`Failed to create/find tag: ${category}:${tagName}`, error);
          }
        }
      }

      if (tagId) {
        tagIds.push(tagId);
      }
    }

    return tagIds;
  }

  /**
   * Search for existing tag by backward_compatibility across all pages
   * Also checks for case-insensitive matches since internal_name validation may be case-insensitive
   */
  private async findExistingTagByBackwardCompat(backwardCompat: string): Promise<string | null> {
    let page = 1;
    const perPage = 100;
    let hasMore = true;
    
    // Extract category and tagName from backward_compat for case-insensitive search
    // Format: mwnf3:tags:{category}:{tagName}
    const parts = backwardCompat.split(':');
    const category = parts.length >= 3 ? parts[2] : null;
    const tagName = parts.length >= 4 ? parts.slice(3).join(':') : null;

    while (hasMore) {
      const response = await this.context.apiClient.tag.tagIndex(page, perPage, undefined);
      const tags = response.data.data;

      // First try exact match
      let existing = tags.find((t) => t.backward_compatibility === backwardCompat);
      
      // If not found and we have category/tagName, try case-insensitive match
      if (!existing && category && tagName) {
        existing = tags.find((t) => {
          if (!t.backward_compatibility) return false;
          const tParts = t.backward_compatibility.split(':');
          const tCategory = tParts.length >= 3 ? tParts[2] : null;
          const tTagName = tParts.length >= 4 ? tParts.slice(3).join(':') : null;
          
          return (
            tCategory?.toLowerCase() === category.toLowerCase() &&
            tTagName?.toLowerCase() === tagName.toLowerCase()
          );
        });
      }
      
      if (existing) {
        // Register in tracker with the ORIGINAL backward_compat (not the found one)
        // This ensures future lookups with same case work
        this.context.tracker.register({
          uuid: existing.id,
          backwardCompatibility: backwardCompat,
          entityType: 'item',
          createdAt: new Date(),
        });
        return existing.id;
      }

      hasMore = tags.length === perPage;
      page++;

      // Safety limit
      if (page > 100) {
        this.logWarning(`Stopped searching for tag after 100 pages (10,000 records)`);
        break;
      }
    }

    return null;
  }

  /**
   * Attach tags to an item using the API
   */
  private async attachTags(itemId: string, tagIds: string[]): Promise<void> {
    if (tagIds.length === 0 || this.context.dryRun) {
      return;
    }

    // Use the updateTags endpoint to attach tags
    await this.context.apiClient.item.itemUpdateTags(itemId, {
      attach: tagIds,
    });
  }
}
