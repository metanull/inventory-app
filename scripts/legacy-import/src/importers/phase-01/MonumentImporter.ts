import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';

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
    };

    try {
      this.log('Importing monuments...');

      // Query all monuments (denormalized - multiple rows per monument)
      const limitClause = this.context.limit > 0 ? ` LIMIT ${this.context.limit * 10}` : '';
      const monuments = await this.context.legacyDb.query<LegacyMonument>(
        `SELECT * FROM mwnf3.monuments ORDER BY project_id, country, institution_id, number${limitClause}`
      );

      if (monuments.length === 0) {
        this.log('No monuments found');
        return result;
      }

      // Group monuments by non-lang PK columns
      const monumentGroups = this.groupMonumentsByPK(monuments);
      this.log(
        `Found ${monumentGroups.length} unique monuments (${monuments.length} language rows)`
      );

      // Limit number of unique monuments if limit specified
      const limitedGroups =
        this.context.limit > 0 ? monumentGroups.slice(0, this.context.limit) : monumentGroups;

      // Import each monument group
      for (const group of limitedGroups) {
        try {
          const imported = await this.importMonument(group);
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
              `Error importing ${group.project_id}:${group.institution_id}:${group.number}: ${message}`
            );
            this.log(`Response: ${JSON.stringify(axiosError.response?.data)}`);
          }
          result.errors.push(
            `${group.project_id}:${group.institution_id}:${group.number}: ${message}`
          );
          this.showError();
        }
      }
      console.log(''); // New line after progress dots
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

  private async importMonument(group: MonumentGroup): Promise<boolean> {
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
      this.log(
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
      throw new Error(`Context not found for project ${group.project_id}`);
    }

    // Resolve context → collection (root collection for this project)
    const collectionBackwardCompat = `${contextBackwardCompat}:collection`;
    const collectionId = this.context.tracker.getUuid(collectionBackwardCompat);

    if (!collectionId) {
      throw new Error(`Collection not found for project ${group.project_id}`);
    }

    // Resolve institution_id → partner_id
    const partnerBackwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'institutions',
      pkValues: [group.institution_id, group.country],
    });
    const partnerId = this.context.tracker.getUuid(partnerBackwardCompat);

    if (!partnerId) {
      throw new Error(`Partner not found for institution ${group.institution_id}:${group.country}`);
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
      await this.importTranslation(itemId, contextId, translation);
    }

    // Parse and create tags from dynasty and keywords (only from first translation to avoid duplicates)
    const tagIds: string[] = [];

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

    return true;
  }

  private async importTranslation(itemId: string, contextId: string, monument: LegacyMonument) {
    // Map legacy ISO 639-1 to ISO 639-3
    const languageId = this.mapLanguageCode(monument.lang);

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

    await this.context.apiClient.itemTranslation.itemTranslationStore({
      item_id: itemId,
      language_id: languageId,
      context_id: contextId,
      name: monument.name || '',
      description: monument.description || '',
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
