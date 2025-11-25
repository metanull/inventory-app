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
    // Tags are language-specific, so we use the language from first translation
    const tagIds: string[] = [];
    const languageId = mapLanguageCode(firstTranslation.lang);

    if (firstTranslation.dynasty) {
      const dynastyTags = await this.findOrCreateTags(firstTranslation.dynasty, 'dynasty', languageId);
      tagIds.push(...dynastyTags);
    }

    if (firstTranslation.keywords) {
      const keywordTags = await this.findOrCreateTags(firstTranslation.keywords, 'keyword', languageId);
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

    // DATA QUALITY: Truncate fields that exceed database limits (255 chars)
    let alternateName = monument.name2 || null;
    let type = monument.typeof || null;
    
    if (alternateName && alternateName.length > 255) {
      const warning = `${monument.project_id}:${monument.institution_id}:${monument.number}:${monument.lang} - alternate_name truncated (${alternateName.length} → 255 chars)`;
      this.logWarning(`DATA QUALITY: Monument translation ${warning}`, {
        monument_key: `${monument.project_id}:${monument.institution_id}:${monument.number}`,
        language: monument.lang,
        field: 'alternate_name',
        original_length: alternateName.length,
        truncated_value: alternateName.substring(0, 252) + '...',
      });
      result.warnings?.push(warning);
      alternateName = alternateName.substring(0, 252) + '...';
    }
    
    if (type && type.length > 255) {
      const warning = `${monument.project_id}:${monument.institution_id}:${monument.number}:${monument.lang} - type truncated (${type.length} → 255 chars)`;
      this.logWarning(`DATA QUALITY: Monument translation ${warning}`, {
        monument_key: `${monument.project_id}:${monument.institution_id}:${monument.number}`,
        language: monument.lang,
        field: 'type',
        original_length: type.length,
        truncated_value: type.substring(0, 252) + '...',
      });
      result.warnings?.push(warning);
      type = type.substring(0, 252) + '...';
    }

    await this.context.apiClient.itemTranslation.itemTranslationStore({
      item_id: itemId,
      language_id: languageId,
      context_id: contextId,
      name: name,
      description: description,
      alternate_name: alternateName,
      type: type,
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
   * Parse and create tags from separated string
   * IMPORTANT: Fields with colons (e.g., "madrasa; cerámica: decoración floral") are STRUCTURED DATA
   * - If colon found: treat as single structured tag (don't split)
   * - Otherwise: split by semicolon (;) primary, comma (,) fallback
   * Tags are language-specific: same content in different languages = different tags
   * Returns array of tag UUIDs
   */
  private async findOrCreateTags(tagString: string, category: string, languageId: string): Promise<string[]> {
    if (!tagString || tagString.trim() === '') {
      return [];
    }

    // Check if this is structured data (contains colon)
    // Example: "cerámica: decoración floral; decoración geométrica" should be ONE tag, not split
    const isStructured = tagString.includes(':');
    
    let tagNames: string[];
    
    if (isStructured) {
      // Structured data - keep as single tag
      tagNames = [tagString.trim()];
    } else {
      // Simple list - use semicolon as primary separator, comma as fallback
      const separator = tagString.includes(';') ? ';' : ',';
      tagNames = tagString
        .split(separator)
        .map((t) => t.trim())
        .filter((t) => t !== '');
    }

    const tagIds: string[] = [];

    for (const tagName of tagNames) {
      // Normalize to lowercase to avoid case-sensitivity issues
      // Original capitalization preserved in description for display
      const normalizedTagName = tagName.toLowerCase();
      
      // Include table name, category, and language to create unique backward_compatibility
      // Format: mwnf3:tags:{category}:{lang}:{tagName}
      const backwardCompat = BackwardCompatibilityFormatter.format({
        schema: 'mwnf3',
        table: `tags:${category}:${languageId}`,
        pkValues: [normalizedTagName],
      });

      // Check if already exists in tracker
      let tagId = this.context.tracker.getUuid(backwardCompat);

      if (!tagId && !this.context.dryRun) {
        // Lookup existing tag first to avoid unnecessary create attempts and warnings
        tagId = await this.findExistingTagByBackwardCompat(backwardCompat);
        
        if (tagId) {
          // Register in tracker for fast future lookups
          this.context.tracker.register({
            uuid: tagId,
            backwardCompatibility: backwardCompat,
            entityType: 'item',
            createdAt: new Date(),
          });
        } else {
          // Tag doesn't exist, create it
          try {
            // Internal_name should be clean tag value only (e.g., "portrait", "limestone")
            // ALWAYS lowercase to avoid case-sensitivity issues
            // Original capitalization preserved in description for display
            const createPayload: any = {
              internal_name: normalizedTagName,
              category: category,
              language_id: languageId,
              description: tagName, // Keep original capitalization for display
              backward_compatibility: backwardCompat,
            };

            const response = await this.context.apiClient.tag.tagStore(createPayload);
            tagId = response.data.data.id;

            // Register in tracker (use 'item' as valid entityType)
            this.context.tracker.register({
              uuid: tagId,
              backwardCompatibility: backwardCompat,
              entityType: 'item',
              createdAt: new Date(),
            });
          } catch (error) {
            // Both 422 and 500 with unique constraint error mean tag already exists
            // Our lookup missed it (pagination issue or backward_compatibility mismatch)
            if (error && typeof error === 'object' && 'response' in error) {
              const axiosError = error as { response?: { status?: number; data?: any } };
              const is422 = axiosError.response?.status === 422;
              const is500Duplicate = 
                axiosError.response?.status === 500 && 
                axiosError.response?.data?.message?.includes('Duplicate entry') &&
                axiosError.response?.data?.message?.includes('tags_name_category_lang_unique');
              
              if (is422 || is500Duplicate) {
                // Tag exists - do exhaustive search by backward_compatibility
                tagId = await this.findExistingTagByBackwardCompat(backwardCompat, 200);
                
                if (!tagId) {
                  // Still not found by backward_compatibility - try searching by actual fields
                  tagId = await this.findExistingTagByFields(normalizedTagName, category, languageId);
                }
                
                if (tagId) {
                  // Found it! Register for future
                  this.context.tracker.register({
                    uuid: tagId,
                    backwardCompatibility: backwardCompat,
                    entityType: 'item',
                    createdAt: new Date(),
                  });
                } else {
                  // Still can't find it - log warning but continue
                  this.logWarning(`Tag exists (${is500Duplicate ? '500 duplicate' : '422 conflict'}) but cannot be found: ${category}:${languageId}:${tagName}`);
                }
              } else {
                // Other error - log it
                this.logError(`Failed to create tag: ${category}:${tagName}`, error);
              }
            } else {
              this.logError(`Failed to create tag: ${category}:${tagName}`, error);
            }
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
   * Search for existing tag by the actual unique constraint fields
   * Used as fallback when backward_compatibility search fails
   * @param internalName The tag name (e.g., "marble", "limestone")
   * @param category The tag category
   * @param languageId The language ID
   */
  private async findExistingTagByFields(
    internalName: string,
    category: string,
    languageId: string
  ): Promise<string | null> {
    let page = 1;
    const perPage = 100;
    let hasMore = true;

    while (hasMore) {
      const response = await this.context.apiClient.tag.tagIndex(page, perPage, undefined);
      const tags = response.data.data;

      // Search by the actual unique constraint fields (case-insensitive)
      const existing = tags.find(
        (t) =>
          t.internal_name.toLowerCase() === internalName.toLowerCase() &&
          t.category === category &&
          t.language_id === languageId
      );

      if (existing) {
        return existing.id;
      }

      hasMore = tags.length === perPage;
      page++;

      // Limit search to prevent infinite loops
      if (page > 200) {
        break;
      }
    }

    return null;
  }

  /**
   * Search for existing tag by backward_compatibility across all pages
   * Only searches exact match since backward_compatibility now includes category
   * @param backwardCompat The backward_compatibility value to search for
   * @param maxPages Maximum pages to search (default 100, use 200 for exhaustive retry)
   */
  private async findExistingTagByBackwardCompat(backwardCompat: string, maxPages: number = 100): Promise<string | null> {
    let page = 1;
    const perPage = 100;
    let hasMore = true;

    while (hasMore) {
      const response = await this.context.apiClient.tag.tagIndex(page, perPage, undefined);
      const tags = response.data.data;

      // Look for exact backward_compatibility match
      const existing = tags.find((t) => t.backward_compatibility === backwardCompat);

      if (existing) {
        // Register in tracker with the backward_compat
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
      if (page > maxPages) {
        if (maxPages >= 200) {
          this.logWarning(`Exhaustive search failed after ${maxPages} pages for: ${backwardCompat}`);
        }
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
