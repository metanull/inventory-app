import { BaseSqlImporter, type ImportResult } from '../base/BaseSqlImporter.js';
import { v4 as uuidv4 } from 'uuid';
import type { Connection } from 'mysql2/promise';
import type { LegacyDatabase } from '../../database/LegacyDatabase.js';
import { mapLanguageCode } from '../../utils/CodeMappings.js';
import { convertHtmlToMarkdown } from '../../utils/HtmlToMarkdownConverter.js';
import { AuthorHelper } from '../helpers/AuthorHelper.js';
import { TagHelper } from '../helpers/TagHelper.js';
import type { SampleCollector } from '../../utils/SampleCollector.js';
import type { LogWriter } from '../utils/LogWriter.js';

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
  date_description?: string;
  datationmethod?: string;
  description?: string;
  bibliography?: string;
  preparedby?: string;
  copyeditedby?: string;
  translationby?: string;
  translationcopyeditedby?: string;
  dynasty?: string;
  keywords?: string;
  phone?: string;
  fax?: string;
  email?: string;
  institution?: string;
  patrons?: string;
  architects?: string;
  history?: string;
  external_sources?: string;
  description2?: string;
  copyright?: string;
}

export class MonumentSqlImporter extends BaseSqlImporter {
  private legacyDb: LegacyDatabase;
  private authorHelper: AuthorHelper;
  private tagHelper: TagHelper;

  constructor(
    db: Connection,
    tracker: Map<string, string>,
    legacyDb: LegacyDatabase,
    sampleCollector?: SampleCollector,
    logger?: LogWriter
  ) {
    super(db, tracker, sampleCollector, logger);
    this.legacyDb = legacyDb;
    this.authorHelper = new AuthorHelper(db, tracker, this.now);
    this.tagHelper = new TagHelper(db, tracker, this.now);
  }

  getName(): string {
    return 'MonumentSqlImporter';
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

      const monuments = await this.legacyDb.query<LegacyMonument>(
        'SELECT * FROM mwnf3.monuments ORDER BY project_id, country, institution_id, number, lang'
      );

      const grouped = this.groupByMonument(monuments);
      this.log(`Found ${grouped.length} unique monuments (${monuments.length} translations)`);

      let processed = 0;
      for (const group of grouped) {
        try {
          const success = await this.importMonument(group);
          if (success) {
            result.imported++;
          } else {
            result.skipped++;
          }
          processed++;
          if (processed % 100 === 0) {
            this.showProgress(processed, grouped.length);
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${group.key}: ${message}`);
        }
      }

      console.log('');
      this.logSuccess(`Imported ${result.imported}, skipped ${result.skipped}`);
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(message);
      this.logError('Failed to import monuments', error);
    }

    return result;
  }

  private groupByMonument(monuments: LegacyMonument[]): Array<{
    key: string;
    translations: LegacyMonument[];
  }> {
    const map = new Map<string, LegacyMonument[]>();
    for (const monument of monuments) {
      const key = `${monument.project_id}:${monument.country}:${monument.institution_id}:${monument.number}`;
      if (!map.has(key)) {
        map.set(key, []);
      }
      map.get(key)!.push(monument);
    }
    return Array.from(map.entries()).map(([key, translations]) => ({ key, translations }));
  }

  private async importMonument(group: {
    key: string;
    translations: LegacyMonument[];
  }): Promise<boolean> {
    const first = group.translations[0];
    if (!first) return false;

    const backwardCompat = this.formatBackwardCompat('mwnf3', 'monuments', [
      first.project_id,
      first.country,
      first.institution_id,
      first.number,
    ]);

    if (await this.exists('items', backwardCompat)) {
      return false;
    }

    // Resolve dependencies
    const contextId = await this.findByBackwardCompat(
      'contexts',
      this.formatBackwardCompat('mwnf3', 'projects', [first.project_id])
    );
    if (!contextId) return false;

    const collectionId = await this.findByBackwardCompat(
      'collections',
      this.formatBackwardCompat('mwnf3', 'projects', [first.project_id]) + ':collection'
    );
    if (!collectionId) return false;

    const partnerId = await this.findByBackwardCompat(
      'partners',
      this.formatBackwardCompat('mwnf3', 'institutions', [first.institution_id, first.country])
    );
    if (!partnerId) return false;

    // Create Item - use English name for internal_name
    const itemId = uuidv4();
    const englishTranslation = group.translations.find((t) => t.lang === 'en') || first;
    const internalName = englishTranslation.name
      ? convertHtmlToMarkdown(englishTranslation.name)
      : first.working_number || first.number;

    // Map country code to 3-char ISO
    const { mapCountryCode } = await import('../../utils/CodeMappings.js');
    const countryId = mapCountryCode(first.country);

    // Get project_id from projects table (linked to context)
    const projectId = await this.findByBackwardCompat(
      'projects',
      this.formatBackwardCompat('mwnf3', 'projects', [first.project_id]) + ':project'
    );
    if (!projectId) return false;

    await this.db.execute(
      `INSERT INTO items (id, partner_id, collection_id, internal_name, type, country_id, project_id, mwnf_reference, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, 'monument', ?, ?, ?, ?, ?, ?)`,
      [
        itemId,
        partnerId,
        collectionId,
        internalName,
        countryId,
        projectId,
        first.working_number,
        backwardCompat,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(backwardCompat, itemId);

    // Get EPM context ID for cross-project translations
    const epmContextId = await this.findByBackwardCompat(
      'contexts',
      this.formatBackwardCompat('mwnf3', 'projects', ['EPM'])
    );

    // Create translations
    for (const monument of group.translations) {
      // For EPM: only use description2 as description (monuments don't have EPM but keep consistent)
      if (monument.project_id === 'EPM') {
        await this.importTranslation(itemId, contextId, monument, 'description2');
      }
      // For all other projects:
      else {
        // Create translation in own context using description (if populated)
        if (monument.description && monument.description.trim()) {
          await this.importTranslation(itemId, contextId, monument, 'description');
        }

        // If description2 exists and EPM context exists, create EPM translation
        if (monument.description2 && monument.description2.trim() && epmContextId) {
          await this.importTranslation(itemId, epmContextId, monument, 'description2');
        }
      }
    }

    // Create tags
    await this.createTags(itemId, first);

    return true;
  }

  private async importTranslation(
    itemId: string,
    contextId: string,
    monument: LegacyMonument,
    descriptionField: 'description' | 'description2'
  ): Promise<void> {
    const languageId = mapLanguageCode(monument.lang);
    const name = monument.name?.trim();
    if (!name) return;

    // Determine which description to use based on descriptionField parameter
    const sourceDescription =
      descriptionField === 'description2' ? monument.description2 : monument.description;

    // Skip if the selected description field is empty
    if (!sourceDescription || !sourceDescription.trim()) {
      return;
    }

    // Create authors
    const authorId = monument.preparedby
      ? await this.authorHelper.findOrCreate(monument.preparedby)
      : null;
    const textCopyEditorId = monument.copyeditedby
      ? await this.authorHelper.findOrCreate(monument.copyeditedby)
      : null;
    const translatorId = monument.translationby
      ? await this.authorHelper.findOrCreate(monument.translationby)
      : null;
    const translationCopyEditorId = monument.translationcopyeditedby
      ? await this.authorHelper.findOrCreate(monument.translationcopyeditedby)
      : null;

    // Build monument key for logging
    const monumentKey = `${monument.project_id}:${monument.country}:${monument.institution_id}:${monument.number}`;

    // Convert HTML to Markdown for all text fields
    const nameMarkdown = convertHtmlToMarkdown(name);
    let alternateNameMarkdown = monument.name2 ? convertHtmlToMarkdown(monument.name2) : null;
    const descriptionMarkdown = convertHtmlToMarkdown(sourceDescription);
    const typeMarkdown = monument.typeof ? convertHtmlToMarkdown(monument.typeof) : null;
    const datesMarkdown = monument.date_description
      ? convertHtmlToMarkdown(monument.date_description)
      : null;
    const methodForDatationMarkdown = monument.datationmethod
      ? convertHtmlToMarkdown(monument.datationmethod)
      : null;
    const bibliographyMarkdown = monument.bibliography
      ? convertHtmlToMarkdown(monument.bibliography)
      : null;

    // Convert location (composed from multiple fields)
    const locationParts = [monument.location, monument.province, monument.address]
      .filter(Boolean)
      .map((part) => convertHtmlToMarkdown(part));
    const locationMarkdown = locationParts.length > 0 ? locationParts.join(', ') : null;

    // Build extra field for monument-specific fields
    const extra: Record<string, string> = {};
    if (monument.phone) extra.phone = monument.phone;
    if (monument.fax) extra.fax = monument.fax;
    if (monument.email) extra.email = monument.email;
    if (monument.institution) extra.institution = monument.institution;
    if (monument.patrons) extra.patrons = monument.patrons;
    if (monument.architects) extra.architects = monument.architects;
    if (monument.history) extra.history = monument.history;
    if (monument.external_sources) extra.external_sources = monument.external_sources;
    if (monument.copyright) extra.copyright = monument.copyright;
    const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

    // Truncate fields that exceed database limits (VARCHAR(255))
    if (alternateNameMarkdown && alternateNameMarkdown.length > 255) {
      this.log(
        `WARNING: Truncating alternate_name (${alternateNameMarkdown.length} → 255 chars) for ${monumentKey}:${monument.lang}`
      );
      alternateNameMarkdown = alternateNameMarkdown.substring(0, 252) + '...';
    }

    let typeValue = typeMarkdown;
    if (typeValue && typeValue.length > 255) {
      this.log(
        `WARNING: Truncating type (${typeValue.length} → 255 chars) for ${monumentKey}:${monument.lang}`
      );
      typeValue = typeValue.substring(0, 252) + '...';
    }

    const translationId = uuidv4();
    await this.db.execute(
      `INSERT INTO item_translations (id, item_id, language_id, context_id, name, alternate_name, description, type, dates, location, method_for_datation, bibliography, author_id, text_copy_editor_id, translator_id, translation_copy_editor_id, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        translationId,
        itemId,
        languageId,
        contextId,
        nameMarkdown,
        alternateNameMarkdown,
        descriptionMarkdown,
        typeValue,
        datesMarkdown,
        locationMarkdown,
        methodForDatationMarkdown,
        bibliographyMarkdown,
        authorId,
        textCopyEditorId,
        translatorId,
        translationCopyEditorId,
        extraJson,
        this.now,
        this.now,
      ]
    );
  }

  private async createTags(itemId: string, monument: LegacyMonument): Promise<void> {
    const languageId = mapLanguageCode(monument.lang);
    const tagIds: string[] = [];

    if (monument.dynasty) {
      tagIds.push(
        ...(await this.tagHelper.findOrCreateList(monument.dynasty, 'dynasty', languageId))
      );
    }
    if (monument.keywords) {
      tagIds.push(
        ...(await this.tagHelper.findOrCreateList(monument.keywords, 'keyword', languageId))
      );
    }

    await this.tagHelper.attachToItem(itemId, tagIds);
  }
}
