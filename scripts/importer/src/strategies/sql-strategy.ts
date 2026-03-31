/**
 * SQL Write Strategy
 *
 * Implements IWriteStrategy using direct SQL queries.
 * This is the fast-path for bulk imports.
 *
 * IMPORTANT: All string fields are automatically sanitized by converting
 * HTML to Markdown before being written to the database. This ensures
 * legacy HTML content is properly converted regardless of which importer
 * is used. See sanitizeAllStrings() in utils/html-to-markdown.ts.
 */

import { v4 as uuidv4 } from 'uuid';
import type { RowDataPacket } from 'mysql2/promise';
import type { IWriteStrategy } from '../core/strategy.js';
import type {
  EntityType,
  LanguageData,
  LanguageTranslationData,
  CountryData,
  CountryTranslationData,
  ContextData,
  ContextTranslationData,
  CollectionData,
  CollectionTranslationData,
  ProjectData,
  ProjectTranslationData,
  PartnerData,
  PartnerTranslationData,
  ItemData,
  ItemTranslationData,
  TagData,
  AuthorData,
  AuthorTranslationData,
  ArtistData,
  ItemImageData,
  PartnerImageData,
  PartnerLogoData,
  CollectionImageData,
  GlossaryData,
  GlossaryTranslationData,
  GlossarySpellingData,
  ItemItemLinkData,
  ItemItemLinkTranslationData,
  CollectionItemData,
  DynastyData,
  DynastyTranslationData,
  ItemDynastyData,
  TimelineData,
  TimelineEventData,
  TimelineEventTranslationData,
  TimelineEventItemData,
  TimelineEventImageData,
  ItemMediaData,
  CollectionMediaData,
  ItemDocumentData,
  ContributorData,
  ContributorTranslationData,
  ContributorImageData,
} from '../core/types.js';
import { sanitizeAllStrings } from '../utils/html-to-markdown.js';

const tableEntityMap: Record<string, EntityType> = {
  languages: 'language',
  countries: 'country',
  contexts: 'context',
  collections: 'collection',
  projects: 'project',
  partners: 'partner',
  items: 'item',
  tags: 'tag',
  authors: 'author',
  author_translations: 'author_translation',
  artists: 'artist',
  language_translations: 'language_translation',
  country_translations: 'country_translation',
  glossaries: 'glossary',
  glossary_translations: 'glossary_translation',
  glossary_spellings: 'glossary_spelling',
  item_item_links: 'item_item_link',
  item_item_link_translations: 'item_item_link_translation',
  dynasties: 'dynasty',
  dynasty_translations: 'dynasty_translation',
  timelines: 'timeline',
  timeline_events: 'timeline_event',
  timeline_event_translations: 'timeline_event_translation',
  item_media: 'item_media',
  collection_media: 'collection_media',
  item_documents: 'item_document',
  contributors: 'contributor',
  contributor_translations: 'contributor_translation',
};

function mapTableToEntityType(table: string): EntityType | null {
  return tableEntityMap[table] ?? null;
}
import type { ITracker } from '../core/tracker.js';

// Type for resilient connection wrapper
type DatabaseConnection = {
  execute<
    T extends
      | RowDataPacket[]
      | RowDataPacket[][]
      | import('mysql2').OkPacket
      | import('mysql2').OkPacket[]
      | import('mysql2').ResultSetHeader,
  >(
    sql: string,
    values?: unknown
  ): Promise<[T, import('mysql2').FieldPacket[]]>;
  end(): Promise<void>;
};

export class SqlWriteStrategy implements IWriteStrategy {
  private db: DatabaseConnection;
  private tracker: ITracker;
  private now: string;

  constructor(db: DatabaseConnection, tracker: ITracker) {
    this.db = db;
    this.tracker = tracker;
    this.now = new Date().toISOString().slice(0, 19).replace('T', ' ');
  }

  // =========================================================================
  // Reference Data
  // =========================================================================

  async writeLanguage(data: LanguageData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    // Note: Match the old importer which uses: id, internal_name, backward_compatibility, is_default
    // The languages table does NOT have an is_enabled column
    await this.db.execute(
      `INSERT INTO languages (id, internal_name, backward_compatibility, is_default, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [
        sanitized.id,
        sanitized.internal_name,
        sanitized.backward_compatibility,
        sanitized.is_default ? 1 : 0,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(sanitized.backward_compatibility, sanitized.id, 'language');
    return sanitized.id;
  }

  async writeLanguageTranslation(data: LanguageTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO language_translations (id, language_id, display_language_id, name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.language_id,
        sanitized.display_language_id,
        sanitized.name,
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );
  }

  async writeCountry(data: CountryData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    // Note: Match the old importer which uses: id, internal_name, backward_compatibility
    // The countries table does NOT have is_default or is_enabled columns
    await this.db.execute(
      `INSERT INTO countries (id, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?)`,
      [sanitized.id, sanitized.internal_name, sanitized.backward_compatibility, this.now, this.now]
    );

    this.tracker.set(sanitized.backward_compatibility, sanitized.id, 'country');
    return sanitized.id;
  }

  async writeCountryTranslation(data: CountryTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO country_translations (id, country_id, language_id, name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.country_id,
        sanitized.language_id,
        sanitized.name,
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );
  }

  // =========================================================================
  // Core Entities
  // =========================================================================

  async writeContext(data: ContextData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO contexts (id, internal_name, is_default, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.internal_name,
        sanitized.is_default ? 1 : 0,
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(sanitized.backward_compatibility, id, 'context');
    return id;
  }

  async writeContextTranslation(_data: ContextTranslationData): Promise<void> {
    // No-op: context_translations table does not exist in current schema
    // The old importer does not create context translations
  }

  async writeCollection(data: CollectionData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO collections (id, context_id, language_id, parent_id, type, display_order, internal_name, backward_compatibility, latitude, longitude, map_zoom, country_id, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.context_id,
        sanitized.language_id,
        sanitized.parent_id ?? null,
        sanitized.type ?? 'collection',
        data.display_order ?? null,
        sanitized.internal_name,
        sanitized.backward_compatibility,
        data.latitude ?? null,
        data.longitude ?? null,
        data.map_zoom ?? null,
        sanitized.country_id ?? null,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(sanitized.backward_compatibility, id, 'collection');
    return id;
  }

  async writeCollectionTranslation(data: CollectionTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    const extra = data.extra ?? null;
    await this.db.execute(
      `INSERT INTO collection_translations (id, collection_id, language_id, context_id, title, description, quote, extra, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.collection_id,
        sanitized.language_id,
        sanitized.context_id,
        sanitized.title,
        sanitized.description ?? null,
        sanitized.quote ?? null,
        extra,
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );
  }

  async writeCollectionItem(data: CollectionItemData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const displayOrder = data.display_order ?? null;
    const extra = data.extra ? JSON.stringify(data.extra) : null;
    await this.db.execute(
      `INSERT INTO collection_item (collection_id, item_id, display_order, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [sanitized.collection_id, sanitized.item_id, displayOrder, extra, this.now, this.now]
    );
  }

  async writeProject(data: ProjectData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO projects (id, internal_name, context_id, language_id, launch_date, is_launched, is_enabled, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.internal_name,
        sanitized.context_id,
        sanitized.language_id,
        sanitized.launch_date,
        sanitized.is_launched ? 1 : 0,
        sanitized.is_enabled !== false ? 1 : 0, // Default to true
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(sanitized.backward_compatibility, id, 'project');
    return id;
  }

  async writeProjectTranslation(_data: ProjectTranslationData): Promise<void> {
    // No-op: project_translations table does not exist in current schema
    // The old importer does not create project translations
  }

  async deleteProjectsWithoutItems(
    dryRun = false
  ): Promise<
    Array<{ id: string; backward_compatibility: string | null; internal_name: string | null }>
  > {
    // Start transaction to ensure a consistent snapshot + atomic deletes
    await this.db.execute('START TRANSACTION');
    try {
      const [rows] = await this.db.execute<import('mysql2').RowDataPacket[]>(
        `SELECT p.id, p.backward_compatibility, p.internal_name
         FROM projects p
         LEFT JOIN items i ON i.project_id = p.id
         WHERE i.id IS NULL`
      );

      const projects = (rows as RowDataPacket[]).map((r) => ({
        id: String(r.id),
        backward_compatibility: r.backward_compatibility ?? null,
        internal_name: r.internal_name ?? null,
      }));

      if (!dryRun && projects.length > 0) {
        for (const p of projects) {
          await this.db.execute(`DELETE FROM projects WHERE id = ?`, [p.id]);
        }
      }

      await this.db.execute('COMMIT');
      return projects;
    } catch (err) {
      await this.db.execute('ROLLBACK');
      throw err;
    }
  }

  // =========================================================================
  // Partners
  // ========================================================================="},{

  async writePartner(data: PartnerData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO partners (id, type, internal_name, backward_compatibility, country_id, latitude, longitude, map_zoom, project_id, monument_item_id, visible, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.type,
        sanitized.internal_name,
        sanitized.backward_compatibility,
        sanitized.country_id ?? null,
        sanitized.latitude ?? null,
        sanitized.longitude ?? null,
        sanitized.map_zoom ?? 16, // default
        sanitized.project_id ?? null,
        sanitized.monument_item_id ?? null,
        sanitized.visible ?? false, // default
        this.now,
        this.now,
      ]
    );

    this.tracker.set(sanitized.backward_compatibility, id, 'partner');
    return id;
  }

  async writePartnerTranslation(data: PartnerTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO partner_translations (id, partner_id, language_id, context_id, name, description, city_display, contact_website, contact_phone, contact_email_general, extra, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.partner_id,
        sanitized.language_id,
        sanitized.context_id,
        sanitized.name,
        sanitized.description,
        sanitized.city_display,
        sanitized.contact_website,
        sanitized.contact_phone,
        sanitized.contact_email_general,
        sanitized.extra,
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );
  }

  async updatePartnerMonumentItemId(partnerId: string, monumentItemId: string): Promise<void> {
    await this.db.execute(`UPDATE partners SET monument_item_id = ?, updated_at = ? WHERE id = ?`, [
      monumentItemId,
      this.now,
      partnerId,
    ]);
  }

  // =========================================================================
  // Items
  // =========================================================================

  async writeItem(data: ItemData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO items (id, partner_id, collection_id, parent_id, internal_name, type, country_id, project_id, owner_reference, mwnf_reference, start_date, end_date, display_order, latitude, longitude, map_zoom, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.partner_id,
        sanitized.collection_id,
        sanitized.parent_id ?? null,
        sanitized.internal_name,
        sanitized.type,
        sanitized.country_id,
        sanitized.project_id,
        sanitized.owner_reference,
        sanitized.mwnf_reference,
        data.start_date ?? null,
        data.end_date ?? null,
        data.display_order ?? null,
        data.latitude ?? null,
        data.longitude ?? null,
        data.map_zoom ?? null,
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(sanitized.backward_compatibility, id, 'item');
    return id;
  }

  async writeItemTranslation(data: ItemTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    // Convert undefined values to null for SQL compatibility
    const safeNull = (val: string | null | undefined): string | null => val ?? null;
    await this.db.execute(
      `INSERT INTO item_translations (id, item_id, language_id, context_id, name, alternate_name, description, type, holder, owner, initial_owner, dates, location, dimensions, place_of_production, method_for_datation, method_for_provenance, obtention, bibliography, author_id, text_copy_editor_id, translator_id, translation_copy_editor_id, extra, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.item_id,
        sanitized.language_id,
        sanitized.context_id,
        sanitized.name,
        safeNull(sanitized.alternate_name),
        sanitized.description,
        safeNull(sanitized.type),
        safeNull(sanitized.holder),
        safeNull(sanitized.owner),
        safeNull(sanitized.initial_owner),
        safeNull(sanitized.dates),
        safeNull(sanitized.location),
        safeNull(sanitized.dimensions),
        safeNull(sanitized.place_of_production),
        safeNull(sanitized.method_for_datation),
        safeNull(sanitized.method_for_provenance),
        safeNull(sanitized.obtention),
        safeNull(sanitized.bibliography),
        safeNull(sanitized.author_id),
        safeNull(sanitized.text_copy_editor_id),
        safeNull(sanitized.translator_id),
        safeNull(sanitized.translation_copy_editor_id),
        safeNull(sanitized.extra),
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );
  }

  async attachTagsToItem(itemId: string, tagIds: string[]): Promise<void> {
    for (const tagId of tagIds) {
      try {
        await this.db.execute(
          `INSERT IGNORE INTO item_tag (item_id, tag_id, created_at, updated_at)
           VALUES (?, ?, ?, ?)`,
          [itemId, tagId, this.now, this.now]
        );
      } catch {
        // Ignore duplicates
      }
    }
  }

  async attachArtistsToItem(itemId: string, artistIds: string[]): Promise<void> {
    for (const artistId of artistIds) {
      try {
        await this.db.execute(
          `INSERT IGNORE INTO artist_item (item_id, artist_id, created_at, updated_at)
           VALUES (?, ?, ?, ?)`,
          [itemId, artistId, this.now, this.now]
        );
      } catch {
        // Ignore duplicates
      }
    }
  }

  async attachItemsToCollection(collectionId: string, itemIds: string[]): Promise<void> {
    for (const itemId of itemIds) {
      try {
        await this.db.execute(
          `INSERT IGNORE INTO collection_item (collection_id, item_id, created_at, updated_at)
           VALUES (?, ?, ?, ?)`,
          [collectionId, itemId, this.now, this.now]
        );
      } catch {
        // Ignore duplicates
      }
    }
  }

  async attachPartnersToCollection(
    collectionId: string,
    partnerIds: string[],
    collectionType: string = 'project'
  ): Promise<void> {
    for (const partnerId of partnerIds) {
      try {
        await this.db.execute(
          `INSERT IGNORE INTO collection_partner (collection_id, collection_type, partner_id, created_at, updated_at)
           VALUES (?, ?, ?, ?, ?)`,
          [collectionId, collectionType, partnerId, this.now, this.now]
        );
      } catch {
        // Ignore duplicates - this is expected when multiple items share the same partner
      }
    }
  }

  async attachPartnerToCollectionWithLevel(
    collectionId: string,
    partnerId: string,
    collectionType: string,
    level: string
  ): Promise<void> {
    try {
      await this.db.execute(
        `INSERT INTO collection_partner (collection_id, collection_type, partner_id, level, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE level = VALUES(level)`,
        [collectionId, collectionType, partnerId, level, this.now, this.now]
      );
    } catch (error) {
      // Log and rethrow non-duplicate errors
      const message = error instanceof Error ? error.message : String(error);
      if (!message.includes('Duplicate')) {
        throw error;
      }
    }
  }

  // =========================================================================
  // Supporting Entities
  // =========================================================================

  async writeTag(data: TagData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    try {
      await this.db.execute(
        `INSERT INTO tags (id, internal_name, category, language_id, description, backward_compatibility, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          id,
          sanitized.internal_name,
          sanitized.category,
          sanitized.language_id,
          sanitized.description,
          sanitized.backward_compatibility,
          this.now,
          this.now,
        ]
      );
      this.tracker.set(sanitized.backward_compatibility, id, 'tag');
      return id;
    } catch (error) {
      // Duplicate entry - try to find existing record
      // This is expected when the same tag is imported multiple times
      const existing = await this.findByBackwardCompatibility(
        'tags',
        sanitized.backward_compatibility
      );
      if (existing) {
        return existing;
      }
      // If we can't find it after the error, re-throw with context
      const message = error instanceof Error ? error.message : String(error);
      throw new Error(
        `Failed to create or find tag: ${sanitized.backward_compatibility}. Original error: ${message}`
      );
    }
  }

  async writeAuthor(data: AuthorData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    const bc = sanitized.backward_compatibility || null;
    try {
      await this.db.execute(
        `INSERT INTO authors (id, name, firstname, lastname, givenname, originalname, internal_name, backward_compatibility, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          id,
          sanitized.name,
          sanitized.firstname ?? null,
          sanitized.lastname ?? null,
          sanitized.givenname ?? null,
          sanitized.originalname ?? null,
          sanitized.internal_name,
          bc,
          this.now,
          this.now,
        ]
      );
      if (bc) {
        this.tracker.set(bc, id, 'author');
      }
      return id;
    } catch (error) {
      // Duplicate entry - try to find existing record by BC
      if (bc) {
        const existing = await this.findByBackwardCompatibility('authors', bc);
        if (existing) {
          return existing;
        }
        // BC lookup failed — the existing record may have been created without BC
        // (e.g., by AuthorHelper from free-text fields). Find by name and adopt.
        const byName = await this.findAuthorByName(sanitized.name);
        if (byName) {
          // Update the existing record with the proper BC so future lookups work
          await this.db.execute(
            'UPDATE authors SET backward_compatibility = ? WHERE id = ?',
            [bc, byName]
          );
          this.tracker.set(bc, byName, 'author');
          return byName;
        }
      }
      // If we can't find it after the error, re-throw with context
      const message = error instanceof Error ? error.message : String(error);
      throw new Error(
        `Failed to create or find author: ${bc || sanitized.name}. Original error: ${message}`
      );
    }
  }

  async findAuthorByName(name: string): Promise<string | null> {
    const [rows] = await this.db.execute<RowDataPacket[]>(
      'SELECT id FROM authors WHERE name = ? LIMIT 1',
      [name]
    );
    return rows.length > 0 ? (rows[0].id as string) : null;
  }

  async writeAuthorTranslation(data: AuthorTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO author_translations (id, author_id, language_id, context_id, curriculum, backward_compatibility, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.author_id,
        sanitized.language_id,
        sanitized.context_id,
        sanitized.curriculum ?? null,
        sanitized.backward_compatibility ?? null,
        sanitized.extra ?? null,
        this.now,
        this.now,
      ]
    );
  }

  async writeArtist(data: ArtistData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    try {
      await this.db.execute(
        `INSERT INTO artists (id, name, internal_name, place_of_birth, place_of_death, date_of_birth, date_of_death, period_of_activity, backward_compatibility, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          id,
          sanitized.name,
          sanitized.internal_name,
          sanitized.place_of_birth,
          sanitized.place_of_death,
          sanitized.date_of_birth,
          sanitized.date_of_death,
          sanitized.period_of_activity,
          sanitized.backward_compatibility,
          this.now,
          this.now,
        ]
      );
      this.tracker.set(sanitized.backward_compatibility, id, 'artist');
      return id;
    } catch (error) {
      // Duplicate entry - try to find existing record
      // This is expected when the same artist is imported multiple times
      const existing = await this.findByBackwardCompatibility(
        'artists',
        sanitized.backward_compatibility
      );
      if (existing) {
        return existing;
      }
      // If we can't find it after the error, re-throw with context
      const message = error instanceof Error ? error.message : String(error);
      throw new Error(
        `Failed to create or find artist: ${sanitized.backward_compatibility}. Original error: ${message}`
      );
    }
  }

  async writeItemImage(data: ItemImageData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = sanitized.id || uuidv4();
    await this.db.execute(
      `INSERT INTO item_images (id, item_id, path, original_name, mime_type, size, alt_text, display_order, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.item_id,
        sanitized.path,
        sanitized.original_name,
        sanitized.mime_type,
        sanitized.size,
        sanitized.alt_text,
        sanitized.display_order,
        this.now,
        this.now,
      ]
    );
    // Track using lowercase path as unique identifier
    this.tracker.set(sanitized.path.toLowerCase(), id, 'image');
    return id;
  }

  async writePartnerImage(data: PartnerImageData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = sanitized.id || uuidv4();
    await this.db.execute(
      `INSERT INTO partner_images (id, partner_id, path, original_name, mime_type, size, alt_text, display_order, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.partner_id,
        sanitized.path,
        sanitized.original_name,
        sanitized.mime_type,
        sanitized.size,
        sanitized.alt_text,
        sanitized.display_order,
        sanitized.extra ?? null,
        this.now,
        this.now,
      ]
    );
    // Track using lowercase path as unique identifier
    this.tracker.set(sanitized.path.toLowerCase(), id, 'image');
    return id;
  }

  async writePartnerLogo(data: PartnerLogoData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = sanitized.id || uuidv4();
    await this.db.execute(
      `INSERT INTO partner_logos (id, partner_id, path, original_name, mime_type, size, logo_type, alt_text, display_order, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.partner_id,
        sanitized.path,
        sanitized.original_name,
        sanitized.mime_type,
        sanitized.size,
        sanitized.logo_type ?? 'primary',
        sanitized.alt_text,
        sanitized.display_order,
        this.now,
        this.now,
      ]
    );
    // Track using lowercase path as unique identifier (prefixed to avoid collision with images)
    this.tracker.set(`logo:${sanitized.path.toLowerCase()}`, id, 'image');
    return id;
  }

  async writeCollectionImage(data: CollectionImageData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = sanitized.id || uuidv4();
    await this.db.execute(
      `INSERT INTO collection_images (id, collection_id, path, original_name, mime_type, size, alt_text, display_order, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.collection_id,
        sanitized.path,
        sanitized.original_name,
        sanitized.mime_type,
        sanitized.size,
        sanitized.alt_text,
        sanitized.display_order,
        this.now,
        this.now,
      ]
    );
    // Track using lowercase path as unique identifier
    this.tracker.set(sanitized.path.toLowerCase(), id, 'image');
    return id;
  }

  // =========================================================================
  // Glossary
  // =========================================================================

  async writeGlossary(data: GlossaryData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO glossaries (id, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?)`,
      [id, sanitized.internal_name, sanitized.backward_compatibility, this.now, this.now]
    );

    this.tracker.set(sanitized.backward_compatibility, id, 'glossary');
    return id;
  }

  async writeGlossaryTranslation(data: GlossaryTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO glossary_translations (id, glossary_id, language_id, definition, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [id, sanitized.glossary_id, sanitized.language_id, sanitized.definition, this.now, this.now]
    );
  }

  async writeGlossarySpelling(data: GlossarySpellingData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO glossary_spellings (id, glossary_id, language_id, spelling, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [id, sanitized.glossary_id, sanitized.language_id, sanitized.spelling, this.now, this.now]
    );
    return id;
  }

  // =========================================================================
  // Item Links
  // =========================================================================

  async writeItemItemLink(data: ItemItemLinkData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    const backwardCompat = sanitized.backward_compatibility ?? null;
    await this.db.execute(
      `INSERT INTO item_item_links (id, source_id, target_id, context_id, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.source_id,
        sanitized.target_id,
        sanitized.context_id,
        backwardCompat,
        this.now,
        this.now,
      ]
    );

    // Track with provided backward_compatibility if available, otherwise use composite key
    if (backwardCompat) {
      this.tracker.set(backwardCompat, id, 'item_item_link');
    }
    return id;
  }

  async writeItemItemLinkTranslation(data: ItemItemLinkTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO item_item_link_translations (id, item_item_link_id, language_id, description, reciprocal_description, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.item_item_link_id,
        sanitized.language_id,
        sanitized.description ?? null,
        sanitized.reciprocal_description ?? null,
        sanitized.backward_compatibility ?? null,
        this.now,
        this.now,
      ]
    );
  }

  // =========================================================================
  // Dynasties
  // =========================================================================

  async writeDynasty(data: DynastyData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO dynasties (id, from_ah, to_ah, from_ad, to_ad, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        data.from_ah ?? null,
        data.to_ah ?? null,
        data.from_ad ?? null,
        data.to_ad ?? null,
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(sanitized.backward_compatibility, id, 'dynasty');
    return id;
  }

  async writeDynastyTranslation(data: DynastyTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO dynasty_translations (id, dynasty_id, language_id, name, also_known_as, area, history, date_description_ah, date_description_ad, backward_compatibility, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.dynasty_id,
        sanitized.language_id,
        sanitized.name ?? null,
        sanitized.also_known_as ?? null,
        sanitized.area ?? null,
        sanitized.history ?? null,
        sanitized.date_description_ah ?? null,
        sanitized.date_description_ad ?? null,
        sanitized.backward_compatibility ?? null,
        sanitized.extra ?? null,
        this.now,
        this.now,
      ]
    );
  }

  async writeItemDynasty(data: ItemDynastyData): Promise<void> {
    await this.db.execute(
      `INSERT IGNORE INTO item_dynasty (item_id, dynasty_id, created_at, updated_at)
       VALUES (?, ?, ?, ?)`,
      [data.item_id, data.dynasty_id, this.now, this.now]
    );
  }

  // =========================================================================
  // Timelines
  // =========================================================================

  async writeTimeline(data: TimelineData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO timelines (id, internal_name, country_id, collection_id, backward_compatibility, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.internal_name,
        sanitized.country_id,
        sanitized.collection_id ?? null,
        sanitized.backward_compatibility,
        sanitized.extra ?? null,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(sanitized.backward_compatibility, id, 'timeline');
    return id;
  }

  async writeTimelineEvent(data: TimelineEventData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO timeline_events (id, timeline_id, internal_name, year_from, year_to, year_from_ah, year_to_ah, date_from, date_to, display_order, backward_compatibility, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.timeline_id,
        sanitized.internal_name,
        data.year_from,
        data.year_to,
        data.year_from_ah ?? null,
        data.year_to_ah ?? null,
        data.date_from ?? null,
        data.date_to ?? null,
        data.display_order,
        sanitized.backward_compatibility,
        sanitized.extra ?? null,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(sanitized.backward_compatibility, id, 'timeline_event');
    return id;
  }

  async writeTimelineEventTranslation(data: TimelineEventTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO timeline_event_translations (id, timeline_event_id, language_id, name, description, date_from_description, date_to_description, date_from_ah_description, backward_compatibility, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.timeline_event_id,
        sanitized.language_id,
        sanitized.name ?? null,
        sanitized.description ?? null,
        sanitized.date_from_description ?? null,
        sanitized.date_to_description ?? null,
        sanitized.date_from_ah_description ?? null,
        sanitized.backward_compatibility ?? null,
        sanitized.extra ?? null,
        this.now,
        this.now,
      ]
    );
  }

  async writeTimelineEventItem(data: TimelineEventItemData): Promise<void> {
    await this.db.execute(
      `INSERT IGNORE INTO timeline_event_item (timeline_event_id, item_id, display_order, backward_compatibility, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        data.timeline_event_id,
        data.item_id,
        data.display_order,
        data.backward_compatibility ?? null,
        data.extra ?? null,
        this.now,
        this.now,
      ]
    );
  }

  async writeTimelineEventImage(data: TimelineEventImageData): Promise<string> {
    const id = data.id || uuidv4();
    await this.db.execute(
      `INSERT INTO timeline_event_images (id, timeline_event_id, path, original_name, mime_type, size, alt_text, display_order, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        data.timeline_event_id,
        data.path,
        data.original_name,
        data.mime_type,
        data.size,
        data.alt_text ?? null,
        data.display_order,
        this.now,
        this.now,
      ]
    );
    return id;
  }

  async updateTimelineExtra(timelineId: string, extra: string): Promise<void> {
    await this.db.execute(
      `UPDATE timelines SET extra = ?, updated_at = ? WHERE id = ?`,
      [extra, this.now, timelineId]
    );
  }

  // =========================================================================
  // Media & Documents
  // =========================================================================

  async writeItemMedia(data: ItemMediaData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO item_media (id, item_id, language_id, type, title, description,
                               url, display_order, extra, backward_compatibility,
                               created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.item_id,
        sanitized.language_id ?? null,
        sanitized.type,
        sanitized.title,
        sanitized.description ?? null,
        sanitized.url,
        data.display_order,
        sanitized.extra ?? null,
        sanitized.backward_compatibility ?? null,
        this.now,
        this.now,
      ]
    );
    if (sanitized.backward_compatibility) {
      this.tracker.set(sanitized.backward_compatibility, id, 'item_media');
    }
    return id;
  }

  async writeCollectionMedia(data: CollectionMediaData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO collection_media (id, collection_id, language_id, type, title, description,
                                     url, display_order, extra, backward_compatibility,
                                     created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.collection_id,
        sanitized.language_id ?? null,
        sanitized.type,
        sanitized.title,
        sanitized.description ?? null,
        sanitized.url,
        data.display_order,
        sanitized.extra ?? null,
        sanitized.backward_compatibility ?? null,
        this.now,
        this.now,
      ]
    );
    if (sanitized.backward_compatibility) {
      this.tracker.set(sanitized.backward_compatibility, id, 'collection_media');
    }
    return id;
  }

  async writeItemDocument(data: ItemDocumentData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO item_documents (id, item_id, language_id, path, original_name,
                                   mime_type, size, title, display_order, extra,
                                   backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.item_id,
        sanitized.language_id ?? null,
        sanitized.path,
        sanitized.original_name,
        sanitized.mime_type,
        data.size,
        sanitized.title ?? null,
        data.display_order,
        sanitized.extra ?? null,
        sanitized.backward_compatibility ?? null,
        this.now,
        this.now,
      ]
    );
    if (sanitized.backward_compatibility) {
      this.tracker.set(sanitized.backward_compatibility, id, 'item_document');
    }
    return id;
  }

  async updateItemTranslationAuthorFk(
    itemId: string,
    languageId: string,
    fkColumn: string,
    authorId: string
  ): Promise<void> {
    const allowedColumns = ['author_id', 'text_copy_editor_id', 'translator_id', 'translation_copy_editor_id'];
    if (!allowedColumns.includes(fkColumn)) {
      throw new Error(`Invalid FK column: ${fkColumn}`);
    }
    await this.db.execute(
      `UPDATE item_translations SET ${fkColumn} = ? WHERE item_id = ? AND language_id = ? AND ${fkColumn} IS NULL LIMIT 1`,
      [authorId, itemId, languageId]
    );
  }

  async updateDynastyTranslationAuthorFk(
    dynastyId: string,
    languageId: string,
    fkColumn: string,
    authorId: string
  ): Promise<void> {
    const allowedColumns = ['author_id', 'text_copy_editor_id', 'translator_id', 'translation_copy_editor_id'];
    if (!allowedColumns.includes(fkColumn)) {
      throw new Error(`Invalid FK column: ${fkColumn}`);
    }
    await this.db.execute(
      `UPDATE dynasty_translations SET ${fkColumn} = ? WHERE dynasty_id = ? AND language_id = ? AND ${fkColumn} IS NULL LIMIT 1`,
      [authorId, dynastyId, languageId]
    );
  }

  // =========================================================================
  // Lookup Methods
  // =========================================================================

  /**
   * Tables that do not have a backward_compatibility column.
   * For these tables, DB fallback lookups are skipped — only the in-memory
   * tracker is consulted.
   */
  private static readonly TABLES_WITHOUT_BC = new Set([
    'item_images',
    'partner_images',
    'partner_logos',
    'collection_images',
    'contributor_images',
    'timeline_event_images',
  ]);

  async exists(table: string, backwardCompatibility: string): Promise<boolean> {
    const entityType = mapTableToEntityType(table);
    if (entityType && this.tracker.exists(backwardCompatibility, entityType)) {
      return true;
    }

    // Image tables lack backward_compatibility column — tracker-only lookup
    if (SqlWriteStrategy.TABLES_WITHOUT_BC.has(table)) {
      return false;
    }

    const [rows] = await this.db.execute<RowDataPacket[]>(
      `SELECT id FROM ${table} WHERE backward_compatibility = ?`,
      [backwardCompatibility]
    );
    return rows.length > 0;
  }

  async findByBackwardCompatibility(
    table: string,
    backwardCompatibility: string
  ): Promise<string | null> {
    const entityType = mapTableToEntityType(table);
    // Check tracker first
    const cached = entityType
      ? this.tracker.getUuid(backwardCompatibility, entityType)
      : this.tracker.getUuid(backwardCompatibility);
    if (cached) {
      return cached;
    }

    // Image tables lack backward_compatibility column — tracker-only lookup
    if (SqlWriteStrategy.TABLES_WITHOUT_BC.has(table)) {
      return null;
    }

    const [rows] = await this.db.execute<RowDataPacket[]>(
      `SELECT id FROM ${table} WHERE backward_compatibility = ?`,
      [backwardCompatibility]
    );

    if (rows.length > 0 && rows[0]) {
      const id = rows[0].id as string;
      if (entityType) {
        this.tracker.set(backwardCompatibility, id, entityType);
      }
      return id;
    }

    return null;
  }

  // =========================================================================
  // Contributors
  // =========================================================================

  async writeContributor(data: ContributorData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = sanitized.id || uuidv4();
    await this.db.execute(
      `INSERT INTO contributors (id, collection_id, category, display_order, visible, backward_compatibility, internal_name, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.collection_id,
        sanitized.category,
        data.display_order,
        data.visible ? 1 : 0,
        sanitized.backward_compatibility ?? null,
        sanitized.internal_name,
        this.now,
        this.now,
      ]
    );
    if (sanitized.backward_compatibility) {
      this.tracker.set(sanitized.backward_compatibility, id, 'contributor');
    }
    return id;
  }

  async writeContributorTranslation(data: ContributorTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = sanitized.id || uuidv4();
    await this.db.execute(
      `INSERT INTO contributor_translations (id, contributor_id, language_id, context_id, name, description, link, alt_text, extra, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.contributor_id,
        sanitized.language_id,
        sanitized.context_id,
        sanitized.name ?? null,
        sanitized.description ?? null,
        sanitized.link ?? null,
        sanitized.alt_text ?? null,
        sanitized.extra ?? null,
        sanitized.backward_compatibility ?? null,
        this.now,
        this.now,
      ]
    );
  }

  async writeContributorImage(data: ContributorImageData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = sanitized.id || uuidv4();
    await this.db.execute(
      `INSERT INTO contributor_images (id, contributor_id, path, original_name, mime_type, size, alt_text, display_order, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.contributor_id,
        sanitized.path,
        sanitized.original_name,
        sanitized.mime_type,
        sanitized.size,
        sanitized.alt_text,
        sanitized.display_order,
        this.now,
        this.now,
      ]
    );
    // Track using lowercase path as unique identifier
    this.tracker.set(sanitized.path.toLowerCase(), id, 'image');
    return id;
  }

  // =========================================================================
  // Extra JSON Read-Modify-Write
  // =========================================================================

  async getCollectionTranslationExtra(
    collectionId: string,
    languageId: string
  ): Promise<Record<string, unknown> | null> {
    const [rows] = await this.db.execute<RowDataPacket[]>(
      `SELECT extra FROM collection_translations WHERE collection_id = ? AND language_id = ? LIMIT 1`,
      [collectionId, languageId]
    );
    if (rows.length === 0 || !rows[0]?.extra) return null;
    const raw = rows[0].extra;
    return typeof raw === 'string' ? JSON.parse(raw) : raw;
  }

  async setCollectionTranslationExtra(
    collectionId: string,
    languageId: string,
    extra: string
  ): Promise<void> {
    await this.db.execute(
      `UPDATE collection_translations SET extra = ?, updated_at = ? WHERE collection_id = ? AND language_id = ?`,
      [extra, this.now, collectionId, languageId]
    );
  }

  async getItemTranslationExtra(
    itemId: string,
    languageId: string
  ): Promise<Record<string, unknown> | null> {
    const [rows] = await this.db.execute<RowDataPacket[]>(
      `SELECT extra FROM item_translations WHERE item_id = ? AND language_id = ? LIMIT 1`,
      [itemId, languageId]
    );
    if (rows.length === 0 || !rows[0]?.extra) return null;
    const raw = rows[0].extra;
    return typeof raw === 'string' ? JSON.parse(raw) : raw;
  }

  async setItemTranslationExtra(
    itemId: string,
    languageId: string,
    extra: string
  ): Promise<void> {
    await this.db.execute(
      `UPDATE item_translations SET extra = ?, updated_at = ? WHERE item_id = ? AND language_id = ?`,
      [extra, this.now, itemId, languageId]
    );
  }

  async attachTagsToCollectionImage(collectionImageId: string, tagIds: string[]): Promise<void> {
    for (const tagId of tagIds) {
      try {
        await this.db.execute(
          `INSERT IGNORE INTO collection_image_tag (collection_image_id, tag_id, created_at, updated_at)
           VALUES (?, ?, ?, ?)`,
          [collectionImageId, tagId, this.now, this.now]
        );
      } catch {
        // Ignore duplicates
      }
    }
  }

  async getCollectionTranslationLanguages(collectionId: string): Promise<string[]> {
    const [rows] = await this.db.execute<RowDataPacket[]>(
      `SELECT DISTINCT language_id FROM collection_translations WHERE collection_id = ?`,
      [collectionId]
    );
    return rows.map((r) => r.language_id as string);
  }

  async getItemTranslationLanguages(itemId: string): Promise<string[]> {
    const [rows] = await this.db.execute<RowDataPacket[]>(
      `SELECT DISTINCT language_id FROM item_translations WHERE item_id = ?`,
      [itemId]
    );
    return rows.map((r) => r.language_id as string);
  }

  // =========================================================================
  // Update Methods (for post-processing / re-parenting)
  // =========================================================================

  async updateCollectionParentId(collectionId: string, parentId: string): Promise<void> {
    await this.db.execute(
      `UPDATE collections SET parent_id = ?, updated_at = ? WHERE id = ?`,
      [parentId, this.now, collectionId]
    );
  }

  async updateBackwardCompatibility(
    table: string,
    id: string,
    backwardCompatibility: string
  ): Promise<void> {
    await this.db.execute(
      `UPDATE ${table} SET backward_compatibility = ?, updated_at = ? WHERE id = ?`,
      [backwardCompatibility, this.now, id]
    );
  }
}
