/**
 * Author Helper
 *
 * Unified helper for managing Author entities.
 * Uses write strategy for persistence.
 */

import type { IWriteStrategy } from '../core/strategy.js';
import type { ITracker } from '../core/tracker.js';
import type { ILogger } from '../core/base-importer.js';
import type { AuthorData } from '../core/types.js';
import { formatBackwardCompatibility } from '../utils/backward-compatibility.js';

export class AuthorHelper {
  private strategy: IWriteStrategy;
  private tracker: ITracker;
  private logger: ILogger;

  constructor(strategy: IWriteStrategy, tracker: ITracker, logger: ILogger) {
    this.strategy = strategy;
    this.tracker = tracker;
    this.logger = logger;
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
    const existing = this.tracker.getUuid(backwardCompat, 'author');
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
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      this.logger.warning(`Author write failed for '${trimmedName}': ${message}, retrying lookup`);
      const retryResult = await this.strategy.findByBackwardCompatibility('authors', backwardCompat);
      if (!retryResult) {
        this.logger.warning(`Author retry lookup also failed for '${trimmedName}' — entity lost`);
      }
      return retryResult;
    }
  }
}
