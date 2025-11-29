import { BaseSqlImporter, type ImportResult } from '../base/BaseSqlImporter.js';
import { v4 as uuidv4 } from 'uuid';
import type { Connection } from 'mysql2/promise';
import type { LegacyDatabase } from '../../database/LegacyDatabase.js';
import { mapLanguageCode } from '../../utils/CodeMappings.js';
import { convertHtmlToMarkdown } from '../../utils/HtmlToMarkdownConverter.js';
import { AuthorHelper } from '../helpers/AuthorHelper.js';
import { ArtistHelper } from '../helpers/ArtistHelper.js';
import { TagHelper } from '../helpers/TagHelper.js';

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
  current_owner?: string;
  original_owner?: string;
  dimensions?: string;
  production_place?: string;
  datationmethod?: string;
  provenancemethod?: string;
  obtentionmethod?: string;
  description?: string;
  bibliography?: string;
  preparedby?: string;
  copyeditedby?: string;
  translationby?: string;
  translationcopyeditedby?: string;
  artist?: string;
  birthplace?: string;
  deathplace?: string;
  birthdate?: string;
  deathdate?: string;
  period_activity?: string;
  materials?: string;
  dynasty?: string;
  keywords?: string;
  workshop?: string;
  description2?: string;
  copyright?: string;
  binding_desc?: string;
}

export class ObjectSqlImporter extends BaseSqlImporter {
  private legacyDb: LegacyDatabase;
  private authorHelper: AuthorHelper;
  private artistHelper: ArtistHelper;
  private tagHelper: TagHelper;

  constructor(db: Connection, tracker: Map<string, string>, legacyDb: LegacyDatabase) {
    super(db, tracker);
    this.legacyDb = legacyDb;
    this.authorHelper = new AuthorHelper(db, tracker, this.now);
    this.artistHelper = new ArtistHelper(db, tracker, this.now);
    this.tagHelper = new TagHelper(db, tracker, this.now);
  }

  getName(): string {
    return 'ObjectSqlImporter';
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

      const objects = await this.legacyDb.query<LegacyObject>(
        'SELECT * FROM mwnf3.objects ORDER BY project_id, country, museum_id, number, lang'
      );

      const grouped = this.groupByObject(objects);
      this.log(`Found ${grouped.length} unique objects (${objects.length} translations)`);

      let processed = 0;
      for (const group of grouped) {
        try {
          const success = await this.importObject(group);
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
      this.logError('Failed to import objects', error);
    }

    return result;
  }

  private groupByObject(objects: LegacyObject[]): Array<{
    key: string;
    translations: LegacyObject[];
  }> {
    const map = new Map<string, LegacyObject[]>();
    for (const obj of objects) {
      const key = `${obj.project_id}:${obj.country}:${obj.museum_id}:${obj.number}`;
      if (!map.has(key)) {
        map.set(key, []);
      }
      map.get(key)!.push(obj);
    }
    return Array.from(map.entries()).map(([key, translations]) => ({ key, translations }));
  }

  private async importObject(group: {
    key: string;
    translations: LegacyObject[];
  }): Promise<boolean> {
    const first = group.translations[0];
    if (!first) return false;

    const backwardCompat = this.formatBackwardCompat('mwnf3', 'objects', [
      first.project_id,
      first.country,
      first.museum_id,
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
      this.formatBackwardCompat('mwnf3', 'museums', [first.museum_id, first.country])
    );
    if (!partnerId) return false;

    // Create Item - use English name for internal_name
    const itemId = uuidv4();
    const englishTranslation = group.translations.find((t) => t.lang === 'en') || first;
    const internalName = englishTranslation.name
      ? convertHtmlToMarkdown(englishTranslation.name)
      : first.inventory_id || first.working_number || first.number;
    
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
      `INSERT INTO items (id, partner_id, collection_id, internal_name, type, country_id, project_id, owner_reference, mwnf_reference, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, 'object', ?, ?, ?, ?, ?, ?, ?)`,
      [
        itemId,
        partnerId,
        collectionId,
        internalName,
        countryId,
        projectId,
        first.inventory_id,
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
    for (const obj of group.translations) {
      // For EPM: only use description2 as description
      if (obj.project_id === 'EPM') {
        await this.importTranslation(itemId, contextId, obj, 'description2');
      }
      // For all other projects:
      else {
        // Create translation in own context using description (if populated)
        if (obj.description && obj.description.trim()) {
          await this.importTranslation(itemId, contextId, obj, 'description');
        }
        
        // If description2 exists and EPM context exists, create EPM translation
        if (obj.description2 && obj.description2.trim() && epmContextId) {
          await this.importTranslation(itemId, epmContextId, obj, 'description2');
        }
      }
    }

    // Create tags
    await this.createTags(itemId, first);

    // Create artists
    if (first.artist) {
      await this.createArtists(itemId, first);
    }

    return true;
  }

  private async importTranslation(
    itemId: string,
    contextId: string,
    obj: LegacyObject,
    descriptionField: 'description' | 'description2'
  ): Promise<void> {
    const languageId = mapLanguageCode(obj.lang);
    const name = obj.name?.trim();
    if (!name) return;

    // Determine which description to use based on descriptionField parameter
    const sourceDescription = descriptionField === 'description2' ? obj.description2 : obj.description;
    
    // Skip if the selected description field is empty
    if (!sourceDescription || !sourceDescription.trim()) {
      return;
    }

    // Create authors
    const authorId = obj.preparedby ? await this.authorHelper.findOrCreate(obj.preparedby) : null;
    const textCopyEditorId = obj.copyeditedby
      ? await this.authorHelper.findOrCreate(obj.copyeditedby)
      : null;
    const translatorId = obj.translationby
      ? await this.authorHelper.findOrCreate(obj.translationby)
      : null;
    const translationCopyEditorId = obj.translationcopyeditedby
      ? await this.authorHelper.findOrCreate(obj.translationcopyeditedby)
      : null;

    // Build object key for logging
    const objectKey = `${obj.project_id}:${obj.country}:${obj.museum_id}:${obj.number}`;

    // Convert HTML to Markdown
    const nameMarkdown = convertHtmlToMarkdown(name);
    let alternateNameMarkdown = obj.name2 ? convertHtmlToMarkdown(obj.name2) : null;
    const bibliographyMarkdown = obj.bibliography ? convertHtmlToMarkdown(obj.bibliography) : null;
    const descriptionMarkdown = convertHtmlToMarkdown(sourceDescription);

    // Build extra field
    const extra: Record<string, string> = {};
    if (obj.workshop) extra.workshop = obj.workshop;
    if (obj.copyright) extra.copyright = obj.copyright;
    if (obj.binding_desc) extra.binding_desc = obj.binding_desc;
    const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

    // Truncate fields that exceed database limits (VARCHAR(255))
    if (alternateNameMarkdown && alternateNameMarkdown.length > 255) {
      this.log(`WARNING: Truncating alternate_name (${alternateNameMarkdown.length} → 255 chars) for ${objectKey}:${obj.lang}`);
      alternateNameMarkdown = alternateNameMarkdown.substring(0, 252) + '...';
    }
    
    let typeValue = obj.typeof || null;
    if (typeValue && typeValue.length > 255) {
      this.log(`WARNING: Truncating type (${typeValue.length} → 255 chars) for ${objectKey}:${obj.lang}`);
      typeValue = typeValue.substring(0, 252) + '...';
    }

    const location = [obj.location, obj.province].filter(Boolean).join(', ') || null;

    const translationId = uuidv4();
    await this.db.execute(
      `INSERT INTO item_translations (id, item_id, language_id, context_id, name, alternate_name, description, type, holder, owner, initial_owner, dates, location, dimensions, place_of_production, method_for_datation, method_for_provenance, obtention, bibliography, author_id, text_copy_editor_id, translator_id, translation_copy_editor_id, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        translationId,
        itemId,
        languageId,
        contextId,
        nameMarkdown,
        alternateNameMarkdown,
        descriptionMarkdown,
        typeValue,
        obj.holding_museum,
        obj.current_owner,
        obj.original_owner,
        obj.date_description,
        location,
        obj.dimensions,
        obj.production_place,
        obj.datationmethod,
        obj.provenancemethod,
        obj.obtentionmethod,
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

  private async createTags(itemId: string, obj: LegacyObject): Promise<void> {
    const languageId = mapLanguageCode(obj.lang);
    const tagIds: string[] = [];

    if (obj.materials) {
      tagIds.push(
        ...(await this.tagHelper.findOrCreateList(obj.materials, 'material', languageId))
      );
    }
    if (obj.dynasty) {
      tagIds.push(...(await this.tagHelper.findOrCreateList(obj.dynasty, 'dynasty', languageId)));
    }
    if (obj.keywords) {
      tagIds.push(...(await this.tagHelper.findOrCreateList(obj.keywords, 'keyword', languageId)));
    }

    await this.tagHelper.attachToItem(itemId, tagIds);
  }

  private async createArtists(itemId: string, obj: LegacyObject): Promise<void> {
    if (!obj.artist) return;

    const artistNames = obj.artist
      .split(';')
      .map((n) => n.trim())
      .filter(Boolean);
    const artistIds: string[] = [];

    for (const artistName of artistNames) {
      const artistId = await this.artistHelper.findOrCreate(
        artistName,
        obj.birthplace,
        obj.deathplace,
        obj.birthdate,
        obj.deathdate,
        obj.period_activity
      );
      if (artistId) artistIds.push(artistId);
    }

    await this.artistHelper.attachToItem(itemId, artistIds);
  }
}
