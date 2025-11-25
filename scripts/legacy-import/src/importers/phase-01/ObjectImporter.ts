import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';
import { mapLanguageCode } from '../../utils/CodeMappings.js';

interface LegacyObject {
  project_id: string;
  country: string;
  museum_id: string;
  number: string;
  lang: string;
  working_number?: string;
  inventory_id?: string;
  name?: string;
  name2?: string;
  typeof?: string;
  holding_museum?: string;
  location?: string;
  province?: string;
  date_description?: string;
  start_date?: string | null;
  end_date?: string | null;
  dynasty?: string;
  current_owner?: string;
  original_owner?: string;
  provenance?: string;
  dimensions?: string;
  materials?: string;
  artist?: string;
  birthdate?: string;
  birthplace?: string;
  deathdate?: string;
  deathplace?: string;
  period_activity?: string;
  production_place?: string;
  workshop?: string;
  description?: string;
  description2?: string;
  datationmethod?: string;
  provenancemethod?: string;
  obtentionmethod?: string;
  bibliography?: string;
  keywords?: string;
  preparedby?: string;
  copyeditedby?: string;
  translationby?: string;
  translationcopyeditedby?: string;
}

interface ObjectGroup {
  project_id: string;
  country: string;
  museum_id: string;
  number: string;
  translations: LegacyObject[];
}

/**
 * Imports objects from mwnf3.objects
 *
 * CRITICAL: objects table is denormalized with language in PK
 * - PK: project_id, country, museum_id, number, LANG (5 columns)
 * - Multiple rows per object (one per language)
 * - Must group by non-lang columns and create ItemTranslations
 * - backward_compatibility: mwnf3:objects:{proj}:{country}:{museum}:{num} (NO LANG)
 */
export class ObjectImporter extends BaseImporter {
  getName(): string {
    return 'ObjectImporter';
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
      this.logInfo('Importing objects...');

      // Query all objects (denormalized - multiple rows per object)
      const objects = await this.context.legacyDb.query<LegacyObject>(
        `SELECT * FROM mwnf3.objects ORDER BY project_id, country, museum_id, number`
      );

      if (objects.length === 0) {
        this.logInfo('No objects found');
        return result;
      }

      // Group objects by non-lang PK columns
      const objectGroups = this.groupObjectsByPK(objects);
      this.logInfo(`Found ${objectGroups.length} unique objects (${objects.length} language rows)`);

      const limitedGroups = objectGroups;

      // Import each object group
      for (const group of limitedGroups) {
        try {
          const imported = await this.importObject(group, result);
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
              `ObjectImporter:${group.project_id}:${group.museum_id}:${group.number}`,
              error instanceof Error ? error : new Error(message),
              { responseData: axiosError.response?.data }
            );
          }
          result.errors.push(`${group.project_id}:${group.museum_id}:${group.number}: ${message}`);
          this.showError();
        }
      }
      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query objects: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  /**
   * Group denormalized object rows by non-lang PK columns
   */
  private groupObjectsByPK(objects: LegacyObject[]): ObjectGroup[] {
    const groups = new Map<string, ObjectGroup>();

    for (const obj of objects) {
      const key = `${obj.project_id}:${obj.country}:${obj.museum_id}:${obj.number}`;

      if (!groups.has(key)) {
        groups.set(key, {
          project_id: obj.project_id,
          country: obj.country,
          museum_id: obj.museum_id,
          number: obj.number,
          translations: [],
        });
      }

      groups.get(key)!.translations.push(obj);
    }

    return Array.from(groups.values());
  }

  private async importObject(group: ObjectGroup, result: ImportResult): Promise<boolean> {
    // Format backward_compatibility (NO LANG)
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'objects',
      pkValues: [group.project_id, group.country, group.museum_id, group.number],
    });

    // Check if already imported
    if (this.context.tracker.exists(backwardCompat)) {
      return false;
    }

    if (this.context.dryRun) {
      this.logInfo(
        `[DRY-RUN] Would import object: ${group.project_id}:${group.museum_id}:${group.number}`
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
        `Skipping object ${group.project_id}:${group.museum_id}:${group.number} - project not found`,
        { project_id: group.project_id, museum_id: group.museum_id, number: group.number }
      );
      return false;
    }

    // Resolve context → collection (root collection for this project)
    const collectionBackwardCompat = `${contextBackwardCompat}:collection`;
    const collectionId = this.context.tracker.getUuid(collectionBackwardCompat);

    if (!collectionId) {
      this.logWarning(
        `Skipping object ${group.project_id}:${group.museum_id}:${group.number} - collection not found`,
        { project_id: group.project_id }
      );
      return false;
    }

    // Resolve museum_id → partner_id
    const partnerBackwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'museums',
      pkValues: [group.museum_id, group.country],
    });
    const partnerId = this.context.tracker.getUuid(partnerBackwardCompat);

    if (!partnerId) {
      this.logWarning(
        `Skipping object ${group.project_id}:${group.museum_id}:${group.number} - museum not found`,
        { museum_id: group.museum_id, country: group.country }
      );
      return false;
    }

    // Use first translation for base data
    const firstTranslation = group.translations[0];
    if (!firstTranslation) {
      throw new Error('No translations found for object');
    }

    // Create Item
    const itemResponse = await this.context.apiClient.item.itemStore({
      internal_name:
        firstTranslation.inventory_id || firstTranslation.working_number || group.number,
      type: 'object',
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

    // Parse and create tags from materials, dynasty, and keywords (only from first translation to avoid duplicates)
    // Tags are language-specific, so we use the language from first translation
    const tagIds: string[] = [];
    const languageId = mapLanguageCode(firstTranslation.lang);

    if (firstTranslation.materials) {
      const materialTags = await this.findOrCreateTags(firstTranslation.materials, 'material', languageId);
      tagIds.push(...materialTags);
    }

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

    // Create and attach artist(s) if present (using first translation)
    if (firstTranslation.artist) {
      const artistIds = await this.findOrCreateArtist(firstTranslation, languageId);
      if (artistIds.length > 0) {
        // Add artist as a special tag for now (until API endpoint exists)
        await this.attachTags(itemId, artistIds);
      }
    }

    return true;
  }

  private async importTranslation(
    itemId: string,
    contextId: string,
    obj: LegacyObject,
    result: ImportResult
  ): Promise<void> {
    // Map legacy ISO 639-1 to ISO 639-3
    const languageId = mapLanguageCode(obj.lang);

    // TODO: Resolve author IDs when API endpoints are available
    // For now, author names are stored as text in extra field
    const authorId = null; // await this.findOrCreateAuthor(obj.preparedby);
    const textCopyEditorId = null; // await this.findOrCreateAuthor(obj.copyeditedby);
    const translatorId = null; // await this.findOrCreateAuthor(obj.translationby);
    const translationCopyEditorId = null; // await this.findOrCreateAuthor(obj.translationcopyeditedby);

    // Combine location and province
    const locationFull = [obj.location, obj.province].filter(Boolean).join(', ') || null;

    // Store author names in extra field for now
    const extraData = {
      preparedby: obj.preparedby || null,
      copyeditedby: obj.copyeditedby || null,
      translationby: obj.translationby || null,
      translationcopyeditedby: obj.translationcopyeditedby || null,
      workshop: obj.workshop || null,
      provenance: obj.provenance || null,
    };

    // DATA QUALITY: Check for missing required fields
    let name = obj.name?.trim() || '';
    let description = obj.description?.trim() || '';
    
    if (!name) {
      const warning = `${obj.project_id}:${obj.museum_id}:${obj.number}:${obj.lang} - Missing 'name', using fallback`;
      this.logWarning(`DATA QUALITY: Object translation ${warning}`, {
        object_key: `${obj.project_id}:${obj.museum_id}:${obj.number}`,
        language: obj.lang,
        issue: 'Missing name',
        fallback_used: obj.working_number || obj.inventory_id || `Object ${obj.number}`,
      });
      result.warnings?.push(warning);
      name = obj.working_number || obj.inventory_id || `Object ${obj.number}`;
    }
    
    if (!description) {
      const warning = `${obj.project_id}:${obj.museum_id}:${obj.number}:${obj.lang} - Missing 'description', using fallback`;
      this.logWarning(`DATA QUALITY: Object translation ${warning}`, {
        object_key: `${obj.project_id}:${obj.museum_id}:${obj.number}`,
        language: obj.lang,
        issue: 'Missing description',
        fallback_used: '(No description available)',
      });
      result.warnings?.push(warning);
      description = '(No description available)';
    }

    // DATA QUALITY: Truncate fields that exceed database limits (255 chars)
    let alternateName = obj.name2 || null;
    let type = obj.typeof || null;
    
    if (alternateName && alternateName.length > 255) {
      const warning = `${obj.project_id}:${obj.museum_id}:${obj.number}:${obj.lang} - alternate_name truncated (${alternateName.length} → 255 chars)`;
      this.logWarning(`DATA QUALITY: Object translation ${warning}`, {
        object_key: `${obj.project_id}:${obj.museum_id}:${obj.number}`,
        language: obj.lang,
        field: 'alternate_name',
        original_length: alternateName.length,
        truncated_value: alternateName.substring(0, 252) + '...',
      });
      result.warnings?.push(warning);
      alternateName = alternateName.substring(0, 252) + '...';
    }
    
    if (type && type.length > 255) {
      const warning = `${obj.project_id}:${obj.museum_id}:${obj.number}:${obj.lang} - type truncated (${type.length} → 255 chars)`;
      this.logWarning(`DATA QUALITY: Object translation ${warning}`, {
        object_key: `${obj.project_id}:${obj.museum_id}:${obj.number}`,
        language: obj.lang,
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
      holder: obj.holding_museum || null,
      owner: obj.current_owner || null,
      initial_owner: obj.original_owner || null,
      dates: obj.date_description || null,
      location: locationFull,
      dimensions: obj.dimensions || null,
      place_of_production: obj.production_place || null,
      method_for_datation: obj.datationmethod || null,
      method_for_provenance: obj.provenancemethod || null,
      obtention: obj.obtentionmethod || null,
      bibliography: obj.bibliography || null,
      author_id: authorId,
      text_copy_editor_id: textCopyEditorId,
      translator_id: translatorId,
      translation_copy_editor_id: translationCopyEditorId,
      extra: JSON.stringify(extraData),
    });
  }



  /**
   * TODO: Implement proper Artist API endpoints
   * For now, we'll create artists as Tags with special prefix
   * Artists field can contain multiple artists separated by semicolons
   * Artists are language-specific like other tags
   */
  private async findOrCreateArtist(obj: LegacyObject, languageId: string): Promise<string[]> {
    if (!obj.artist || obj.artist.trim() === '') {
      return [];
    }

    // Split by semicolon to handle multiple artists
    const artistNames = obj.artist
      .split(';')
      .map((name) => name.trim())
      .filter((name) => name !== '');

    const artistIds: string[] = [];

    for (const artistName of artistNames) {
      const artistId = await this.findOrCreateSingleArtist(artistName, obj, languageId);
      if (artistId) {
        artistIds.push(artistId);
      }
    }

    return artistIds;
  }

  /**
   * Find or create a single artist as a Tag
   */
  private async findOrCreateSingleArtist(artistName: string, obj: LegacyObject, languageId: string): Promise<string | null> {
    // Include language to distinguish same artist name in different languages
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: `artists:${languageId}`,
      pkValues: [artistName],
    });

    // Check if already exists in tracker
    const existingId = this.context.tracker.getUuid(backwardCompat);
    if (existingId) {
      return existingId;
    }

    if (this.context.dryRun) {
      return null;
    }

    // Lookup existing artist first to avoid unnecessary warnings
    const existing = await this.findExistingTagByBackwardCompat(backwardCompat);
    if (existing) {
      this.context.tracker.register({
        uuid: existing,
        backwardCompatibility: backwardCompat,
        entityType: 'item',
        createdAt: new Date(),
      });
      return existing;
    }

    // Create artist as Tag with special naming convention
    const artistData = {
      place_of_birth: obj.birthplace || null,
      place_of_death: obj.deathplace || null,
      date_of_birth: obj.birthdate || null,
      date_of_death: obj.deathdate || null,
      period_of_activity: obj.period_activity || null,
    };

    // Normalize to lowercase and limit to 240 chars (DB limit is 255, leave margin)
    const normalizedArtistName = artistName.toLowerCase();
    const internalName = normalizedArtistName.substring(0, 240);

    try {
      // Internal_name should be clean artist name only (lowercase)
      // Category and language_id are separate fields
      // Original capitalization preserved in description
      const createPayload: any = {
        internal_name: internalName,
        category: 'artist',
        language_id: languageId,
        description: JSON.stringify({ ...artistData, original_name: artistName }),
        backward_compatibility: backwardCompat,
      };

      const response = await this.context.apiClient.tag.tagStore(createPayload);

      const artistId = response.data.data.id;
      
      // Register in tracker
      this.context.tracker.register({
        uuid: artistId,
        backwardCompatibility: backwardCompat,
        entityType: 'item',
        createdAt: new Date(),
      });
      
      return artistId;
    } catch (error) {
      // Should rarely happen now that we lookup first
      this.logError(`Failed to create artist tag: ${artistName}`, error);
      return null;
    }
  }

  /**
   * Parse and create tags from separated string
   * IMPORTANT: Fields with colons (e.g., "Warp: wool; Weft: cotton") are STRUCTURED DATA
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
    // Example: "Warp: Light brown wool; Weft: Red wool" should be ONE tag, not split
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
            // Internal_name should be clean tag value only (e.g., "portrait", "leather")
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
   * @param internalName The tag name (e.g., "portrait", "leather")
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

    // Normalize for case-insensitive comparison (MariaDB unique constraint is case-insensitive)
    const normalizedName = internalName.toLowerCase();
    const normalizedCategory = category?.toLowerCase();
    const normalizedLanguage = languageId?.toLowerCase();

    while (hasMore) {
      const response = await this.context.apiClient.tag.tagIndex(page, perPage, undefined);
      const tags = response.data.data;

      // Search by the actual unique constraint fields (case-insensitive)
      const existing = tags.find(
        (t) =>
          t.internal_name?.toLowerCase() === normalizedName &&
          t.category?.toLowerCase() === normalizedCategory &&
          t.language_id?.toLowerCase() === normalizedLanguage
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
   * Search for existing tag by backward_compatibility
   * Searches with pagination for exact match
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

      const existing = tags.find((t) => t.backward_compatibility === backwardCompat);

      if (existing) {
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
