/**
 * Author Helper
 *
 * Unified helper for managing Author entities.
 * Uses write strategy for persistence.
 */

import type { IWriteStrategy } from '../core/strategy.js';
import type { ITracker } from '../core/tracker.js';
import type { AuthorData } from '../core/types.js';
import { formatBackwardCompatibility } from '../utils/backward-compatibility.js';

export class AuthorHelper {
  private strategy: IWriteStrategy;
  private tracker: ITracker;

  constructor(strategy: IWriteStrategy, tracker: ITracker) {
    this.strategy = strategy;
    this.tracker = tracker;
  }

  /**
   * Find or create an author by name
   */
  async findOrCreate(name: string): Promise<string | null> {
    if (!name || name.trim() === '') {
      return null;
    }

    const trimmedName = name.trim();
    const backwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'authors',
      pkValues: [trimmedName],
    });

    // Check tracker first
    const existing = this.tracker.getUuid(backwardCompat);
    if (existing) {
      return existing;
    }

    // Check database via strategy
    const found = await this.strategy.findByBackwardCompatibility('authors', backwardCompat);
    if (found) {
      return found;
    }

    // Create new author
    const authorData: AuthorData = {
      name: trimmedName,
      internal_name: trimmedName,
      backward_compatibility: backwardCompat,
    };

    try {
      return await this.strategy.writeAuthor(authorData);
    } catch {
      // If creation fails (duplicate), try to find again
      return await this.strategy.findByBackwardCompatibility('authors', backwardCompat);
    }
  }
}
