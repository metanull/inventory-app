import { v4 as uuidv4 } from 'uuid';
import type { Connection, RowDataPacket } from 'mysql2/promise';

export class TagHelper {
  private db: Connection;
  private tracker: Map<string, string>;
  private now: string;

  constructor(db: Connection, tracker: Map<string, string>, now: string) {
    this.db = db;
    this.tracker = tracker;
    this.now = now;
  }

  async findOrCreateList(
    tagString: string,
    category: string,
    languageId: string
  ): Promise<string[]> {
    if (!tagString || tagString.trim() === '') {
      return [];
    }

    // Check if structured data (contains colon)
    const isStructured = tagString.includes(':');
    const separator = isStructured ? null : tagString.includes(';') ? ';' : ',';
    const tagNames = separator
      ? tagString
          .split(separator)
          .map((t) => t.trim())
          .filter(Boolean)
      : [tagString.trim()];

    const tagIds: string[] = [];
    for (const tagName of tagNames) {
      const tagId = await this.findOrCreate(tagName, category, languageId);
      if (tagId) tagIds.push(tagId);
    }
    return tagIds;
  }

  async findOrCreate(name: string, category: string, languageId: string): Promise<string | null> {
    const normalized = name.toLowerCase();
    const backwardCompat = `mwnf3:tags:${category}:${languageId}:${normalized}`;

    // Check tracker
    if (this.tracker.has(backwardCompat)) {
      return this.tracker.get(backwardCompat)!;
    }

    // Check database
    const [existing] = await this.db.execute<RowDataPacket[]>(
      'SELECT id FROM tags WHERE backward_compatibility = ?',
      [backwardCompat]
    );

    if (existing.length > 0 && existing[0]) {
      const id = existing[0].id as string;
      this.tracker.set(backwardCompat, id);
      return id;
    }

    // Create new
    const tagId = uuidv4();
    try {
      await this.db.execute(
        'INSERT INTO tags (id, internal_name, category, language_id, description, backward_compatibility, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [tagId, normalized, category, languageId, name, backwardCompat, this.now, this.now]
      );
      this.tracker.set(backwardCompat, tagId);
      return tagId;
    } catch {
      // Duplicate - try to find again
      const [retry] = await this.db.execute<RowDataPacket[]>(
        'SELECT id FROM tags WHERE backward_compatibility = ?',
        [backwardCompat]
      );
      if (retry.length > 0 && retry[0]) {
        const id = retry[0].id as string;
        this.tracker.set(backwardCompat, id);
        return id;
      }
      return null;
    }
  }

  async attachToItem(itemId: string, tagIds: string[]): Promise<void> {
    for (const tagId of tagIds) {
      try {
        await this.db.execute(
          'INSERT IGNORE INTO item_tag (item_id, tag_id, created_at, updated_at) VALUES (?, ?, ?, ?)',
          [itemId, tagId, this.now, this.now]
        );
      } catch {
        // Ignore duplicates
      }
    }
  }
}
