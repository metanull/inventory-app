import { v4 as uuidv4 } from 'uuid';
import type { Connection, RowDataPacket } from 'mysql2/promise';

export class ArtistHelper {
  private db: Connection;
  private tracker: Map<string, string>;
  private now: string;

  constructor(db: Connection, tracker: Map<string, string>, now: string) {
    this.db = db;
    this.tracker = tracker;
    this.now = now;
  }

  async findOrCreate(
    name: string,
    birthplace?: string | null,
    deathplace?: string | null,
    birthdate?: string | null,
    deathdate?: string | null,
    periodActivity?: string | null
  ): Promise<string | null> {
    if (!name || name.trim() === '') {
      return null;
    }

    const trimmed = name.trim();
    const backwardCompat = `mwnf3:artists:${trimmed}`;

    // Check tracker
    if (this.tracker.has(backwardCompat)) {
      return this.tracker.get(backwardCompat)!;
    }

    // Check database
    const [existing] = await this.db.execute<RowDataPacket[]>(
      'SELECT id FROM artists WHERE backward_compatibility = ?',
      [backwardCompat]
    );

    if (existing.length > 0 && existing[0]) {
      const id = existing[0].id as string;
      this.tracker.set(backwardCompat, id);
      return id;
    }

    // Create new
    const artistId = uuidv4();
    try {
      await this.db.execute(
        'INSERT INTO artists (id, name, internal_name, place_of_birth, place_of_death, date_of_birth, date_of_death, period_of_activity, backward_compatibility, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [
          artistId,
          trimmed,
          trimmed,
          birthplace,
          deathplace,
          birthdate,
          deathdate,
          periodActivity,
          backwardCompat,
          this.now,
          this.now,
        ]
      );
      this.tracker.set(backwardCompat, artistId);
      return artistId;
    } catch {
      // Duplicate - try to find again
      const [retry] = await this.db.execute<RowDataPacket[]>(
        'SELECT id FROM artists WHERE backward_compatibility = ?',
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

  async attachToItem(itemId: string, artistIds: string[]): Promise<void> {
    for (const artistId of artistIds) {
      try {
        await this.db.execute(
          'INSERT IGNORE INTO artist_item (artist_id, item_id, created_at, updated_at) VALUES (?, ?, ?, ?)',
          [artistId, itemId, this.now, this.now]
        );
      } catch {
        // Ignore duplicates
      }
    }
  }
}
