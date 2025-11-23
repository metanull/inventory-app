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
    const tagIds: string[] = [];

    if (firstTranslation.materials) {
      const materialTags = await this.findOrCreateTags(firstTranslation.materials, 'material', result);
      tagIds.push(...materialTags);
    }

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

    // Create and attach artist if present (using first translation)
    if (firstTranslation.artist) {
      const artistId = await this.findOrCreateArtist(firstTranslation, result);
      if (artistId) {
        // Add artist as a special tag for now (until API endpoint exists)
        await this.attachTags(itemId, [artistId]);
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

    await this.context.apiClient.itemTranslation.itemTranslationStore({
      item_id: itemId,
      language_id: languageId,
      context_id: contextId,
      name: name,
      description: description,
      alternate_name: obj.name2 || null,
      type: obj.typeof || null,
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
   */
  private async findOrCreateArtist(obj: LegacyObject, result: ImportResult): Promise<string | null> {
    if (!obj.artist || obj.artist.trim() === '') {
      return null;
    }

    const cleanName = obj.artist.trim();
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'artists',
      pkValues: [cleanName],
    });

    // Check if already exists in tracker
    const existingId = this.context.tracker.getUuid(backwardCompat);
    if (existingId) {
      return existingId;
    }

    if (this.context.dryRun) {
      return null;
    }

    // Create artist as Tag with special naming convention
    const artistData = {
      place_of_birth: obj.birthplace || null,
      place_of_death: obj.deathplace || null,
      date_of_birth: obj.birthdate || null,
      date_of_death: obj.deathdate || null,
      period_of_activity: obj.period_activity || null,
    };

    try {
      const response = await this.context.apiClient.tag.tagStore({
        internal_name: `artist:${cleanName}`,
        description: JSON.stringify(artistData),
        backward_compatibility: backwardCompat,
      });

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
      // Handle 422 conflict - tag with this internal_name already exists
      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { status?: number } };
        if (axiosError.response?.status === 422) {
          // Try to find existing tag with same backward_compatibility
          const existingArtistId = await this.findExistingTagByBackwardCompat(backwardCompat);
          if (existingArtistId) {
            const warning = `Artist '${cleanName}' - Duplicate internal_name resolved`;
            this.logWarning(`DATA QUALITY: ${warning}`, {
              artist: cleanName,
              issue: 'Duplicate internal_name',
              resolution: 'Found existing tag by backward_compatibility',
            });
            result.warnings?.push(warning);
            return existingArtistId;
          }
        }
      }
      // Log but don't fail the whole import
      this.logError(`Failed to create artist tag: ${cleanName}`, error);
      return null;
    }
  }

  /**
   * Parse and create tags from semicolon-separated string
   * Returns array of tag UUIDs
   */
  private async findOrCreateTags(tagString: string, category: string, result: ImportResult): Promise<string[]> {
    if (!tagString || tagString.trim() === '') {
      return [];
    }

    // Split by semicolon and clean
    const tagNames = tagString
      .split(';')
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
              // Try to find existing tag with same backward_compatibility
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
