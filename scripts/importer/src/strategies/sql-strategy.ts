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
  ArtistData,
  ItemImageData,
  PartnerImageData,
  PartnerLogoData,
  CollectionImageData,
  GlossaryData,
  GlossaryTranslationData,
  GlossarySpellingData,
  ThemeData,
  ThemeTranslationData,
  ItemItemLinkData,
  ItemItemLinkTranslationData,
  CollectionItemData,
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
  artists: 'artist',
  language_translations: 'language_translation',
  country_translations: 'country_translation',
  glossaries: 'glossary',
  glossary_translations: 'glossary_translation',
  glossary_spellings: 'glossary_spelling',
  themes: 'theme',
  theme_translations: 'theme_translation',
  item_item_links: 'item_item_link',
  item_item_link_translations: 'item_item_link_translation',
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
      `INSERT INTO collections (id, context_id, language_id, parent_id, type, internal_name, backward_compatibility, latitude, longitude, map_zoom, country_id, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.context_id,
        sanitized.language_id,
        sanitized.parent_id ?? null,
        sanitized.type ?? 'collection',
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
    await this.db.execute(
      `INSERT INTO collection_translations (id, collection_id, language_id, context_id, title, description, quote, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.collection_id,
        sanitized.language_id,
        sanitized.context_id,
        sanitized.title,
        sanitized.description ?? null, // Ensure null instead of undefined
        sanitized.quote ?? null, // Optional quote field
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );
  }

  async writeCollectionItem(data: CollectionItemData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    await this.db.execute(
      `INSERT INTO collection_item (collection_id, item_id, created_at, updated_at)
       VALUES (?, ?, ?, ?)`,
      [sanitized.collection_id, sanitized.item_id, this.now, this.now]
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

  // =========================================================================
  // Partners
  // =========================================================================

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
    await this.db.execute(
      `UPDATE partners SET monument_item_id = ?, updated_at = ? WHERE id = ?`,
      [monumentItemId, this.now, partnerId]
    );
  }

  // =========================================================================
  // Items
  // =========================================================================

  async writeItem(data: ItemData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO items (id, partner_id, collection_id, parent_id, internal_name, type, country_id, project_id, owner_reference, mwnf_reference, latitude, longitude, map_zoom, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
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
    try {
      await this.db.execute(
        `INSERT INTO authors (id, name, internal_name, backward_compatibility, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?)`,
        [
          id,
          sanitized.name,
          sanitized.internal_name,
          sanitized.backward_compatibility,
          this.now,
          this.now,
        ]
      );
      this.tracker.set(sanitized.backward_compatibility, id, 'author');
      return id;
    } catch (error) {
      // Duplicate entry - try to find existing record
      // This is expected when the same author is imported multiple times
      const existing = await this.findByBackwardCompatibility(
        'authors',
        sanitized.backward_compatibility
      );
      if (existing) {
        return existing;
      }
      // If we can't find it after the error, re-throw with context
      const message = error instanceof Error ? error.message : String(error);
      throw new Error(
        `Failed to create or find author: ${sanitized.backward_compatibility}. Original error: ${message}`
      );
    }
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
      `INSERT INTO partner_images (id, partner_id, path, original_name, mime_type, size, alt_text, display_order, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.partner_id,
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
  // Themes (Thematic Gallery)
  // =========================================================================

  async writeTheme(data: ThemeData): Promise<string> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO themes (id, collection_id, parent_id, display_order, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.collection_id,
        sanitized.parent_id ?? null,
        sanitized.display_order,
        sanitized.internal_name,
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(sanitized.backward_compatibility, id, 'theme');
    return id;
  }

  async writeThemeTranslation(data: ThemeTranslationData): Promise<void> {
    const sanitized = sanitizeAllStrings(data);
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO theme_translations (id, theme_id, language_id, context_id, title, description, introduction, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        sanitized.theme_id,
        sanitized.language_id,
        sanitized.context_id,
        sanitized.title,
        sanitized.description ?? null,
        sanitized.introduction ?? null,
        sanitized.backward_compatibility,
        this.now,
        this.now,
      ]
    );
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
  // Lookup Methods
  // =========================================================================

  async exists(table: string, backwardCompatibility: string): Promise<boolean> {
    const entityType = mapTableToEntityType(table);
    if (entityType && this.tracker.exists(backwardCompatibility, entityType)) {
      return true;
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
}
