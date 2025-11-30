/**
 * Artist Helper
 *
 * Unified helper for managing Artist entities.
 * Uses write strategy for persistence.
 */

import type { IWriteStrategy } from '../core/strategy.js';
import type { ITracker } from '../core/tracker.js';
import type { ArtistData } from '../core/types.js';
import { formatBackwardCompatibility } from '../utils/backward-compatibility.js';

export class ArtistHelper {
  private strategy: IWriteStrategy;
  private tracker: ITracker;

  constructor(strategy: IWriteStrategy, tracker: ITracker) {
    this.strategy = strategy;
    this.tracker = tracker;
  }

  /**
   * Find or create an artist
   */
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

    const trimmedName = name.trim();
    const backwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'artists',
      pkValues: [trimmedName],
    });

    // Check tracker first
    const existing = this.tracker.getUuid(backwardCompat);
    if (existing) {
      return existing;
    }

    // Check database via strategy
    const found = await this.strategy.findByBackwardCompatibility('artists', backwardCompat);
    if (found) {
      return found;
    }

    // Create new artist
    const artistData: ArtistData = {
      name: trimmedName,
      internal_name: trimmedName,
      place_of_birth: birthplace || null,
      place_of_death: deathplace || null,
      date_of_birth: birthdate || null,
      date_of_death: deathdate || null,
      period_of_activity: periodActivity || null,
      backward_compatibility: backwardCompat,
    };

    try {
      return await this.strategy.writeArtist(artistData);
    } catch {
      // If creation fails (duplicate), try to find again
      return await this.strategy.findByBackwardCompatibility('artists', backwardCompat);
    }
  }

  /**
   * Attach artists to an item
   */
  async attachToItem(itemId: string, artistIds: string[]): Promise<void> {
    if (artistIds.length === 0) return;
    await this.strategy.attachArtistsToItem(itemId, artistIds);
  }
}
