/**
 * SQL Write Strategy
 *
 * Implements IWriteStrategy using direct SQL queries.
 * This is the fast-path for bulk imports.
 */

import { v4 as uuidv4 } from 'uuid';
import type { Connection, RowDataPacket } from 'mysql2/promise';
import type { IWriteStrategy } from '../core/strategy.js';
import type {
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
} from '../core/types.js';
import type { ITracker } from '../core/tracker.js';

export class SqlWriteStrategy implements IWriteStrategy {
  private db: Connection;
  private tracker: ITracker;
  private now: string;

  constructor(db: Connection, tracker: ITracker) {
    this.db = db;
    this.tracker = tracker;
    this.now = new Date().toISOString().slice(0, 19).replace('T', ' ');
  }

  // =========================================================================
  // Reference Data
  // =========================================================================

  async writeLanguage(data: LanguageData): Promise<string> {
    await this.db.execute(
      `INSERT INTO languages (id, internal_name, is_default, is_enabled, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        data.id,
        data.internal_name,
        data.is_default ? 1 : 0,
        data.is_enabled ? 1 : 0,
        data.backward_compatibility,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(data.backward_compatibility, data.id);
    return data.id;
  }

  async writeLanguageTranslation(data: LanguageTranslationData): Promise<void> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO language_translations (id, language_id, target_language_id, name, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [id, data.language_id, data.target_language_id, data.name, this.now, this.now]
    );
  }

  async writeCountry(data: CountryData): Promise<string> {
    await this.db.execute(
      `INSERT INTO countries (id, internal_name, is_default, is_enabled, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        data.id,
        data.internal_name,
        data.is_default ? 1 : 0,
        data.is_enabled ? 1 : 0,
        data.backward_compatibility,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(data.backward_compatibility, data.id);
    return data.id;
  }

  async writeCountryTranslation(data: CountryTranslationData): Promise<void> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO country_translations (id, country_id, language_id, name, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [id, data.country_id, data.language_id, data.name, this.now, this.now]
    );
  }

  // =========================================================================
  // Core Entities
  // =========================================================================

  async writeContext(data: ContextData): Promise<string> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO contexts (id, internal_name, is_default, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [id, data.internal_name, data.is_default ? 1 : 0, data.backward_compatibility, this.now, this.now]
    );

    this.tracker.set(data.backward_compatibility, id);
    return id;
  }

  async writeContextTranslation(data: ContextTranslationData): Promise<void> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO context_translations (id, context_id, language_id, name, description, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [id, data.context_id, data.language_id, data.name, data.description, this.now, this.now]
    );
  }

  async writeCollection(data: CollectionData): Promise<string> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO collections (id, context_id, language_id, parent_id, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [id, data.context_id, data.language_id, data.parent_id, data.internal_name, data.backward_compatibility, this.now, this.now]
    );

    this.tracker.set(data.backward_compatibility, id);
    return id;
  }

  async writeCollectionTranslation(data: CollectionTranslationData): Promise<void> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO collection_translations (id, collection_id, language_id, context_id, title, description, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [id, data.collection_id, data.language_id, data.context_id, data.title, data.description, this.now, this.now]
    );
  }

  async writeProject(data: ProjectData): Promise<string> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO projects (id, context_id, internal_name, start_date, end_date, is_launched, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        data.context_id,
        data.internal_name,
        data.start_date,
        data.end_date,
        data.is_launched ? 1 : 0,
        data.backward_compatibility,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(data.backward_compatibility, id);
    return id;
  }

  async writeProjectTranslation(data: ProjectTranslationData): Promise<void> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO project_translations (id, project_id, language_id, context_id, name, description, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [id, data.project_id, data.language_id, data.context_id, data.name, data.description, this.now, this.now]
    );
  }

  // =========================================================================
  // Partners
  // =========================================================================

  async writePartner(data: PartnerData): Promise<string> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO partners (id, type, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [id, data.type, data.internal_name, data.backward_compatibility, this.now, this.now]
    );

    this.tracker.set(data.backward_compatibility, id);
    return id;
  }

  async writePartnerTranslation(data: PartnerTranslationData): Promise<void> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO partner_translations (id, partner_id, language_id, context_id, name, description, city_display, contact_website, contact_phone, contact_email_general, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        data.partner_id,
        data.language_id,
        data.context_id,
        data.name,
        data.description,
        data.city_display,
        data.contact_website,
        data.contact_phone,
        data.contact_email_general,
        data.extra,
        this.now,
        this.now,
      ]
    );
  }

  // =========================================================================
  // Items
  // =========================================================================

  async writeItem(data: ItemData): Promise<string> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO items (id, partner_id, collection_id, internal_name, type, country_id, project_id, owner_reference, mwnf_reference, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        data.partner_id,
        data.collection_id,
        data.internal_name,
        data.type,
        data.country_id,
        data.project_id,
        data.owner_reference,
        data.mwnf_reference,
        data.backward_compatibility,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(data.backward_compatibility, id);
    return id;
  }

  async writeItemTranslation(data: ItemTranslationData): Promise<void> {
    const id = uuidv4();
    await this.db.execute(
      `INSERT INTO item_translations (id, item_id, language_id, context_id, name, alternate_name, description, type, holder, owner, initial_owner, dates, location, dimensions, place_of_production, method_for_datation, method_for_provenance, obtention, bibliography, author_id, text_copy_editor_id, translator_id, translation_copy_editor_id, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        id,
        data.item_id,
        data.language_id,
        data.context_id,
        data.name,
        data.alternate_name,
        data.description,
        data.type,
        data.holder,
        data.owner,
        data.initial_owner,
        data.dates,
        data.location,
        data.dimensions,
        data.place_of_production,
        data.method_for_datation,
        data.method_for_provenance,
        data.obtention,
        data.bibliography,
        data.author_id,
        data.text_copy_editor_id,
        data.translator_id,
        data.translation_copy_editor_id,
        data.extra,
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

  // =========================================================================
  // Supporting Entities
  // =========================================================================

  async writeTag(data: TagData): Promise<string> {
    const id = uuidv4();
    try {
      await this.db.execute(
        `INSERT INTO tags (id, internal_name, category, language_id, description, backward_compatibility, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
        [id, data.internal_name, data.category, data.language_id, data.description, data.backward_compatibility, this.now, this.now]
      );
      this.tracker.set(data.backward_compatibility, id);
      return id;
    } catch (error) {
      // Duplicate entry - try to find existing record
      // This is expected when the same tag is imported multiple times
      const existing = await this.findByBackwardCompatibility('tags', data.backward_compatibility);
      if (existing) {
        return existing;
      }
      // If we can't find it after the error, re-throw with context
      const message = error instanceof Error ? error.message : String(error);
      throw new Error(`Failed to create or find tag: ${data.backward_compatibility}. Original error: ${message}`);
    }
  }

  async writeAuthor(data: AuthorData): Promise<string> {
    const id = uuidv4();
    try {
      await this.db.execute(
        `INSERT INTO authors (id, name, internal_name, backward_compatibility, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?)`,
        [id, data.name, data.internal_name, data.backward_compatibility, this.now, this.now]
      );
      this.tracker.set(data.backward_compatibility, id);
      return id;
    } catch (error) {
      // Duplicate entry - try to find existing record
      // This is expected when the same author is imported multiple times
      const existing = await this.findByBackwardCompatibility('authors', data.backward_compatibility);
      if (existing) {
        return existing;
      }
      // If we can't find it after the error, re-throw with context
      const message = error instanceof Error ? error.message : String(error);
      throw new Error(`Failed to create or find author: ${data.backward_compatibility}. Original error: ${message}`);
    }
  }

  async writeArtist(data: ArtistData): Promise<string> {
    const id = uuidv4();
    try {
      await this.db.execute(
        `INSERT INTO artists (id, name, internal_name, place_of_birth, place_of_death, date_of_birth, date_of_death, period_of_activity, backward_compatibility, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          id,
          data.name,
          data.internal_name,
          data.place_of_birth,
          data.place_of_death,
          data.date_of_birth,
          data.date_of_death,
          data.period_of_activity,
          data.backward_compatibility,
          this.now,
          this.now,
        ]
      );
      this.tracker.set(data.backward_compatibility, id);
      return id;
    } catch (error) {
      // Duplicate entry - try to find existing record
      // This is expected when the same artist is imported multiple times
      const existing = await this.findByBackwardCompatibility('artists', data.backward_compatibility);
      if (existing) {
        return existing;
      }
      // If we can't find it after the error, re-throw with context
      const message = error instanceof Error ? error.message : String(error);
      throw new Error(`Failed to create or find artist: ${data.backward_compatibility}. Original error: ${message}`);
    }
  }

  // =========================================================================
  // Lookup Methods
  // =========================================================================

  async exists(table: string, backwardCompatibility: string): Promise<boolean> {
    // Check tracker first
    if (this.tracker.exists(backwardCompatibility)) {
      return true;
    }

    const [rows] = await this.db.execute<RowDataPacket[]>(
      `SELECT id FROM ${table} WHERE backward_compatibility = ?`,
      [backwardCompatibility]
    );
    return rows.length > 0;
  }

  async findByBackwardCompatibility(table: string, backwardCompatibility: string): Promise<string | null> {
    // Check tracker first
    const cached = this.tracker.getUuid(backwardCompatibility);
    if (cached) {
      return cached;
    }

    const [rows] = await this.db.execute<RowDataPacket[]>(
      `SELECT id FROM ${table} WHERE backward_compatibility = ?`,
      [backwardCompatibility]
    );

    if (rows.length > 0 && rows[0]) {
      const id = rows[0].id as string;
      this.tracker.set(backwardCompatibility, id);
      return id;
    }

    return null;
  }
}
