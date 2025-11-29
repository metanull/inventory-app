/* eslint-disable @typescript-eslint/no-explicit-any */
import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';
import { mapLanguageCode } from '../../utils/CodeMappings.js';
import { convertHtmlToMarkdown } from '../../utils/HtmlToMarkdownConverter.js';

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

    // Collect sample for testing - success case (one record per object group)
    const sampleTranslation = group.translations[0];
    if (sampleTranslation) {
      this.collectSample(
        'object',
        sampleTranslation as unknown as Record<string, unknown>,
        'success',
        undefined,
        mapLanguageCode(sampleTranslation.lang)
      );
    }

    if (this.context.dryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import object: ${group.project_id}:${group.museum_id}:${group.number}`
      );

      if (this.isSampleOnlyMode) {
        // Register fake item ID for dependencies
        const fakeItemId = `sample-item-${group.project_id}-${group.museum_id}-${group.number}`;
        this.context.tracker.register({
          uuid: fakeItemId,
          backwardCompatibility: backwardCompat,
          entityType: 'item',
          createdAt: new Date(),
        });
      }
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
      owner_reference: firstTranslation.inventory_id || null,
      mwnf_reference: firstTranslation.working_number || null,
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

    // Get EPM context ID for cross-project translations (matching SQL importer)
    const epmContextBackwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'projects',
      pkValues: ['EPM'],
    });
    const epmContextId = this.context.tracker.getUuid(epmContextBackwardCompat);

    // Create translations for each language (matching SQL importer logic)
    for (const translation of group.translations) {
      // For EPM: only use description2 as description
      if (translation.project_id === 'EPM') {
        if (translation.description2 && translation.description2.trim()) {
          await this.importTranslation(itemId, contextId, translation, result, 'description2');
        }
      }
      // For all other projects:
      else {
        // Create translation in own context using description (if populated)
        if (translation.description && translation.description.trim()) {
          await this.importTranslation(itemId, contextId, translation, result, 'description');
        }

        // If description2 exists and EPM context exists, create EPM translation
        if (translation.description2 && translation.description2.trim() && epmContextId) {
          await this.importTranslation(itemId, epmContextId, translation, result, 'description2');
        }
      }
    }

    // Parse and create tags from materials, dynasty, and keywords (only from first translation to avoid duplicates)
    // Tags are language-specific, so we use the language from first translation
    const tagIds: string[] = [];
    const languageId = mapLanguageCode(firstTranslation.lang);

    if (firstTranslation.materials) {
      const materialTags = await this.findOrCreateTags(
        firstTranslation.materials,
        'material',
        languageId
      );
      tagIds.push(...materialTags);
    }

    if (firstTranslation.dynasty) {
      const dynastyTags = await this.findOrCreateTags(
        firstTranslation.dynasty,
        'dynasty',
        languageId
      );
      tagIds.push(...dynastyTags);
    }

    if (firstTranslation.keywords) {
      const keywordTags = await this.findOrCreateTags(
        firstTranslation.keywords,
        'keyword',
        languageId
      );
      tagIds.push(...keywordTags);
    }

    // Attach all tags to item
    if (tagIds.length > 0) {
      await this.attachTags(itemId, tagIds);
    }

    // Create and attach artist(s) if present (using first translation)
    if (firstTranslation.artist) {
      const artistIds = await this.findOrCreateArtists(
        firstTranslation.artist,
        firstTranslation.birthplace,
        firstTranslation.deathplace,
        firstTranslation.birthdate,
        firstTranslation.deathdate,
        firstTranslation.period_activity
      );
      if (artistIds.length > 0) {
        // Attach artists to item using artist_item pivot table
        await this.attachArtists(itemId, artistIds);
      }
    }

    return true;
  }

  private async importTranslation(
    itemId: string,
    contextId: string,
    obj: LegacyObject,
    result: ImportResult,
    descriptionField: 'description' | 'description2' = 'description'
  ): Promise<void> {
    // Map legacy ISO 639-1 to ISO 639-3
    const languageId = mapLanguageCode(obj.lang);

    // Determine which description to use based on descriptionField parameter
    const sourceDescription =
      descriptionField === 'description2' ? obj.description2 : obj.description;

    // Skip if the selected description field is empty
    if (!sourceDescription || !sourceDescription.trim()) {
      return;
    }

    // Create/find authors and get their IDs
    const authorId = obj.preparedby
      ? await this.findOrCreateAuthor(obj.preparedby, languageId)
      : null;
    const textCopyEditorId = obj.copyeditedby
      ? await this.findOrCreateAuthor(obj.copyeditedby, languageId)
      : null;
    const translatorId = obj.translationby
      ? await this.findOrCreateAuthor(obj.translationby, languageId)
      : null;
    const translationCopyEditorId = obj.translationcopyeditedby
      ? await this.findOrCreateAuthor(obj.translationcopyeditedby, languageId)
      : null;

    // Build extra field ONLY for fields that don't have dedicated columns
    // NOTE: description2 is now handled properly as separate translations, not in extra
    const extraData: Record<string, unknown> = {};

    // Only add to extra if the value is not null/empty
    if (obj.workshop) extraData.workshop = obj.workshop;
    if ('copyright' in obj && obj.copyright) extraData.copyright = obj.copyright as string;
    if ('binding_desc' in obj && obj.binding_desc)
      extraData.binding_desc = obj.binding_desc as string;

    // Extra field should be object or null (API will handle JSON encoding)
    const extraField = Object.keys(extraData).length > 0 ? extraData : null;

    // Use name and description from source
    const name = obj.name?.trim() || null;
    const description = sourceDescription.trim();

    // Validate required fields
    if (!name) {
      const warning = `${obj.project_id}:${obj.museum_id}:${obj.number}:${obj.lang} - Missing required 'name' field`;
      this.logWarning(`DATA QUALITY: Object translation ${warning}`, {
        object_key: `${obj.project_id}:${obj.museum_id}:${obj.number}`,
        language: obj.lang,
        issue: 'Missing name',
      });
      result.warnings?.push(warning);
      // Skip this translation - name is required
      return;
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

      // Collect sample for testing - long alternate_name
      this.collectSample(
        'object',
        obj as unknown as Record<string, unknown>,
        'edge',
        'long_alternate_name',
        languageId
      );
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

      // Collect sample for testing - long type field
      this.collectSample(
        'object',
        obj as unknown as Record<string, unknown>,
        'edge',
        'long_type',
        languageId
      );
    }

    // Convert ALL HTML fields to Markdown (matching SQL importer)
    const nameMarkdown = convertHtmlToMarkdown(name || '');
    const alternateNameMarkdown = alternateName ? convertHtmlToMarkdown(alternateName) : null;
    const descriptionMarkdown = convertHtmlToMarkdown(description);
    const bibliographyMarkdown = obj.bibliography ? convertHtmlToMarkdown(obj.bibliography) : null;
    const typeMarkdown = type ? convertHtmlToMarkdown(type) : null;
    const holderMarkdown = obj.holding_museum ? convertHtmlToMarkdown(obj.holding_museum) : null;
    const ownerMarkdown = obj.current_owner ? convertHtmlToMarkdown(obj.current_owner) : null;
    const initialOwnerMarkdown = obj.original_owner
      ? convertHtmlToMarkdown(obj.original_owner)
      : null;
    const datesMarkdown = obj.date_description ? convertHtmlToMarkdown(obj.date_description) : null;
    const dimensionsMarkdown = obj.dimensions ? convertHtmlToMarkdown(obj.dimensions) : null;
    const placeOfProductionMarkdown = obj.production_place
      ? convertHtmlToMarkdown(obj.production_place)
      : null;
    const methodForDatationMarkdown = obj.datationmethod
      ? convertHtmlToMarkdown(obj.datationmethod)
      : null;
    const methodForProvenanceMarkdown = obj.provenancemethod
      ? convertHtmlToMarkdown(obj.provenancemethod)
      : null;
    const obtentionMarkdown = obj.obtentionmethod
      ? convertHtmlToMarkdown(obj.obtentionmethod)
      : null;

    // Convert location parts individually before joining (matching SQL importer)
    const locationParts = [obj.location, obj.province]
      .filter(Boolean)
      .map((part) => convertHtmlToMarkdown(part));
    const locationMarkdown = locationParts.length > 0 ? locationParts.join(', ') : null;

    await this.context.apiClient.itemTranslation.itemTranslationStore({
      item_id: itemId,
      language_id: languageId,
      context_id: contextId,
      name: nameMarkdown,
      description: descriptionMarkdown,
      alternate_name: alternateNameMarkdown,
      type: typeMarkdown,
      holder: holderMarkdown,
      owner: ownerMarkdown,
      initial_owner: initialOwnerMarkdown,
      dates: datesMarkdown,
      location: locationMarkdown,
      dimensions: dimensionsMarkdown,
      place_of_production: placeOfProductionMarkdown,
      method_for_datation: methodForDatationMarkdown,
      method_for_provenance: methodForProvenanceMarkdown,
      obtention: obtentionMarkdown,
      bibliography: bibliographyMarkdown,
      author_id: authorId,
      text_copy_editor_id: textCopyEditorId,
      translator_id: translatorId,
      translation_copy_editor_id: translationCopyEditorId,
      extra: extraField ? JSON.stringify(extraField) : null,
    });
  }

  /**
   * Find or create Artist records from the artist field
   * Artists field can contain multiple artists separated by semicolons
   * Returns array of artist UUIDs
   */
  private async findOrCreateArtists(
    artistField: string | null,
    birthplace: string | null | undefined,
    deathplace: string | null | undefined,
    birthdate: string | null | undefined,
    deathdate: string | null | undefined,
    periodActivity: string | null | undefined
  ): Promise<string[]> {
    if (!artistField || artistField.trim() === '') {
      return [];
    }

    // Split by semicolon to handle multiple artists
    const artistNames = artistField
      .split(';')
      .map((name) => name.trim())
      .filter((name) => name !== '');

    const artistIds: string[] = [];

    for (const artistName of artistNames) {
      const artistId = await this.findOrCreateSingleArtist(
        artistName,
        birthplace,
        deathplace,
        birthdate,
        deathdate,
        periodActivity
      );
      if (artistId) {
        artistIds.push(artistId);
      }
    }

    return artistIds;
  }

  /**
   * Find or create a single Artist record
   */
  private async findOrCreateSingleArtist(
    artistName: string,
    birthplace: string | null | undefined,
    deathplace: string | null | undefined,
    birthdate: string | null | undefined,
    deathdate: string | null | undefined,
    periodActivity: string | null | undefined
  ): Promise<string | null> {
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'artists',
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

    // Create new artist
    const response = await (this.context.apiClient as any).artist.artistStore({
      name: artistName,
      internal_name: artistName,
      place_of_birth: birthplace || null,
      place_of_death: deathplace || null,
      date_of_birth: birthdate || null,
      date_of_death: deathdate || null,
      period_of_activity: periodActivity || null,
      backward_compatibility: backwardCompat,
    });

    const artistId = response.data.data.id;

    // Register in tracker
    this.context.tracker.register({
      uuid: artistId,
      backwardCompatibility: backwardCompat,
      entityType: 'item' as any,
      createdAt: new Date(),
    });

    return artistId;
  }

  /**
   * Attach artists to an item using the artist_item pivot table
   */
  private async attachArtists(itemId: string, artistIds: string[]): Promise<void> {
    if (artistIds.length === 0 || this.context.dryRun) {
      return;
    }

    // Use the updateArtists endpoint to attach artists

    await (this.context.apiClient.item as any).itemUpdateArtists(itemId, {
      attach: artistIds,
    });
  }

  /**
   * Parse and create tags from separated string
   * IMPORTANT: Fields with colons (e.g., "Warp: wool; Weft: cotton") are STRUCTURED DATA
   * - If colon found: treat as single structured tag (don't split)
   * - Otherwise: split by semicolon (;) primary, comma (,) fallback
   * Tags are language-specific: same content in different languages = different tags
   * Returns array of tag UUIDs
   */
  private async findOrCreateTags(
    tagString: string,
    category: string,
    languageId: string
  ): Promise<string[]> {
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
            const createPayload: Record<string, unknown> = {
              internal_name: normalizedTagName,
              category: category,
              language_id: languageId,
              description: tagName, // Keep original capitalization for display
              backward_compatibility: backwardCompat,
            };

            const response = await this.context.apiClient.tag.tagStore(createPayload as any);
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
              const axiosError = error as { response?: { status?: number; data?: unknown } };
              const is422 = axiosError.response?.status === 422;
              const is500Duplicate =
                axiosError.response?.status === 500 &&
                (axiosError.response?.data as any)?.message?.includes('Duplicate entry') &&
                (axiosError.response?.data as any)?.message?.includes(
                  'tags_name_category_lang_unique'
                );

              if (is422 || is500Duplicate) {
                // Tag exists - do exhaustive search by backward_compatibility
                tagId = await this.findExistingTagByBackwardCompat(backwardCompat, 200);

                if (!tagId) {
                  // Still not found by backward_compatibility - try searching by actual fields
                  tagId = await this.findExistingTagByFields(
                    normalizedTagName,
                    category,
                    languageId
                  );
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
                  this.logWarning(
                    `Tag exists (${is500Duplicate ? '500 duplicate' : '422 conflict'}) but cannot be found: ${category}:${languageId}:${tagName}`
                  );
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
  private async findExistingTagByBackwardCompat(
    backwardCompat: string,
    maxPages: number = 100
  ): Promise<string | null> {
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
          this.logWarning(
            `Exhaustive search failed after ${maxPages} pages for: ${backwardCompat}`
          );
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

  /**
   * Find or create an Author from a name string
   * Authors are language-independent (name is the same across languages)
   */
  private async findOrCreateAuthor(
    authorName: string,
    _languageId: string
  ): Promise<string | null> {
    if (!authorName || authorName.trim() === '') {
      return null;
    }

    const trimmedName = authorName.trim();

    // Check tracker first
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'authors',
      pkValues: [trimmedName],
    });

    const existing = this.context.tracker.getUuid(backwardCompat);
    if (existing) {
      return existing;
    }

    // Create new author
    const response = await (this.context.apiClient as any).author.authorStore({
      name: trimmedName,
      internal_name: trimmedName,
      backward_compatibility: backwardCompat,
    });

    const authorId = response.data.data.id;

    // Register in tracker
    this.context.tracker.register({
      uuid: authorId,
      backwardCompatibility: backwardCompat,
      entityType: 'item' as any,
      createdAt: new Date(),
    });

    return authorId;
  }
}
