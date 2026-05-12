/**
 * Artist Helper
 *
 * Unified helper for managing Artist entities.
 * Uses write strategy for persistence.
 */

import type { IWriteStrategy } from '../core/strategy.js';
import type { ITracker } from '../core/tracker.js';
import type { ILogger } from '../core/base-importer.js';
import type { ArtistData } from '../core/types.js';
import { formatBackwardCompatibility } from '../utils/backward-compatibility.js';
import { sanitizeAllStrings } from '../utils/html-to-markdown.js';

export class ArtistHelper {
  private strategy: IWriteStrategy;
  private tracker: ITracker;
  private logger: ILogger;

  constructor(strategy: IWriteStrategy, tracker: ITracker, logger: ILogger) {
    this.strategy = strategy;
    this.tracker = tracker;
    this.logger = logger;
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

    const sanitizedName = sanitizeAllStrings({ name: name.trim() }).name.trim().slice(0, 255);
    if (!sanitizedName) {
      return null;
    }
    const backwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'artists',
      pkValues: [sanitizedName],
    });

    // Check tracker first
    const existing = this.tracker.getUuid(backwardCompat, 'artist');
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
      name: sanitizedName,
      internal_name: sanitizedName,
      place_of_birth: birthplace || null,
      place_of_death: deathplace || null,
      date_of_birth: birthdate || null,
      date_of_death: deathdate || null,
      period_of_activity: periodActivity || null,
      backward_compatibility: backwardCompat,
    };

    try {
      return await this.strategy.writeArtist(artistData);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      this.logger.warning(`Artist write failed for '${sanitizedName}': ${message}, retrying lookup`);
      const retryResult = await this.strategy.findByBackwardCompatibility(
        'artists',
        backwardCompat
      );
      if (retryResult) {
        return retryResult;
      }
      const byName = await this.strategy.findArtistByName(sanitizedName);
      if (byName) {
        return byName.id;
      }
      this.logger.warning(`Artist retry lookup also failed for '${sanitizedName}' — entity lost`);
      return null;
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
