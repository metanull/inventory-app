import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';

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
    };

    try {
      this.log('Importing objects...');

      // Query all objects (denormalized - multiple rows per object)
      const limitClause = this.context.limit > 0 ? ` LIMIT ${this.context.limit * 10}` : '';
      const objects = await this.context.legacyDb.query<LegacyObject>(
        `SELECT * FROM mwnf3.objects ORDER BY project_id, country, museum_id, number${limitClause}`
      );

      if (objects.length === 0) {
        this.log('No objects found');
        return result;
      }

      // Group objects by non-lang PK columns
      const objectGroups = this.groupObjectsByPK(objects);
      this.log(`Found ${objectGroups.length} unique objects (${objects.length} language rows)`);

      // Limit number of unique objects if limit specified
      const limitedGroups =
        this.context.limit > 0 ? objectGroups.slice(0, this.context.limit) : objectGroups;

      // Import each object group
      for (const group of limitedGroups) {
        try {
          const imported = await this.importObject(group);
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
            this.log(
              `Error importing ${group.project_id}:${group.museum_id}:${group.number}: ${message}`
            );
            this.log(`Response: ${JSON.stringify(axiosError.response?.data)}`);
          }
          result.errors.push(`${group.project_id}:${group.museum_id}:${group.number}: ${message}`);
          this.showError();
        }
      }
      console.log(''); // New line after progress dots
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

  private async importObject(group: ObjectGroup): Promise<boolean> {
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
      this.log(
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
      throw new Error(`Context not found for project ${group.project_id}`);
    }

    // Resolve context → collection (root collection for this project)
    const collectionBackwardCompat = `${contextBackwardCompat}:collection`;
    const collectionId = this.context.tracker.getUuid(collectionBackwardCompat);

    if (!collectionId) {
      throw new Error(`Collection not found for project ${group.project_id}`);
    }

    // Resolve museum_id → partner_id
    const partnerBackwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'museums',
      pkValues: [group.museum_id, group.country],
    });
    const partnerId = this.context.tracker.getUuid(partnerBackwardCompat);

    if (!partnerId) {
      throw new Error(`Partner not found for museum ${group.museum_id}:${group.country}`);
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
      await this.importTranslation(itemId, contextId, translation);
    }

    // Parse and create tags from materials, dynasty, and keywords (only from first translation to avoid duplicates)
    const tagIds: string[] = [];

    if (firstTranslation.materials) {
      const materialTags = await this.findOrCreateTags(firstTranslation.materials, 'material');
      tagIds.push(...materialTags);
    }

    if (firstTranslation.dynasty) {
      const dynastyTags = await this.findOrCreateTags(firstTranslation.dynasty, 'dynasty');
      tagIds.push(...dynastyTags);
    }

    if (firstTranslation.keywords) {
      const keywordTags = await this.findOrCreateTags(firstTranslation.keywords, 'keyword');
      tagIds.push(...keywordTags);
    }

    // Attach all tags to item
    if (tagIds.length > 0) {
      await this.attachTags(itemId, tagIds);
    }

    // Create and attach artist if present (using first translation)
    if (firstTranslation.artist) {
      const artistId = await this.findOrCreateArtist(firstTranslation);
      if (artistId) {
        // Add artist as a special tag for now (until API endpoint exists)
        await this.attachTags(itemId, [artistId]);
      }
    }

    return true;
  }

  private async importTranslation(itemId: string, contextId: string, obj: LegacyObject) {
    // Map legacy ISO 639-1 to ISO 639-3
    const languageId = this.mapLanguageCode(obj.lang);

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

    await this.context.apiClient.itemTranslation.itemTranslationStore({
      item_id: itemId,
      language_id: languageId,
      context_id: contextId,
      name: obj.name || '',
      description: obj.description || '',
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

  private mapLanguageCode(legacyCode: string): string {
    const mapping: Record<string, string> = {
      en: 'eng',
      fr: 'fra',
      es: 'spa',
      de: 'deu',
      it: 'ita',
      pt: 'por',
      ar: 'ara',
      tr: 'tur',
    };

    return mapping[legacyCode] || legacyCode;
  }

  /**
   * TODO: Implement proper Artist API endpoints
   * For now, we'll create artists as Tags with special prefix
   */
  private async findOrCreateArtist(obj: LegacyObject): Promise<string | null> {
    if (!obj.artist || obj.artist.trim() === '') {
      return null;
    }

    const cleanName = obj.artist.trim();
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'artists',
      pkValues: [cleanName],
    });

    // Check if already exists
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
      entityType: 'item', // Use 'item' as entityType (valid option)
      createdAt: new Date(),
    });

    return artistId;
  }

  /**
   * Parse and create tags from semicolon-separated string
   * Returns array of tag UUIDs
   */
  private async findOrCreateTags(tagString: string, category: string): Promise<string[]> {
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
      }

      if (tagId) {
        tagIds.push(tagId);
      }
    }

    return tagIds;
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
