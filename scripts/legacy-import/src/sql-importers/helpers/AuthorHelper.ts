import { v4 as uuidv4 } from 'uuid';
import type { Connection, RowDataPacket } from 'mysql2/promise';

export class AuthorHelper {
  private db: Connection;
  private tracker: Map<string, string>;
  private now: string;

  constructor(db: Connection, tracker: Map<string, string>, now: string) {
    this.db = db;
    this.tracker = tracker;
    this.now = now;
  }

  async findOrCreate(name: string): Promise<string | null> {
    if (!name || name.trim() === '') {
      return null;
    }

    const trimmed = name.trim();
    const backwardCompat = `mwnf3:authors:${trimmed}`;

    // Check tracker
    if (this.tracker.has(backwardCompat)) {
      return this.tracker.get(backwardCompat)!;
    }

    // Check database
    const [existing] = await this.db.execute<RowDataPacket[]>(
      'SELECT id FROM authors WHERE backward_compatibility = ?',
      [backwardCompat]
    );

    if (existing.length > 0 && existing[0]) {
      const id = existing[0].id as string;
      this.tracker.set(backwardCompat, id);
      return id;
    }

    // Create new
    const authorId = uuidv4();
    try {
      await this.db.execute(
        'INSERT INTO authors (id, name, internal_name, backward_compatibility, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)',
        [authorId, trimmed, trimmed, backwardCompat, this.now, this.now]
      );
      this.tracker.set(backwardCompat, authorId);
      return authorId;
    } catch {
      // Duplicate - try to find again
      const [retry] = await this.db.execute<RowDataPacket[]>(
        'SELECT id FROM authors WHERE backward_compatibility = ?',
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
}
