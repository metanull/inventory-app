/**
 * Author Helper
 *
 * Finds or creates Author entities from unstructured name strings.
 * Used by object/monument importers where the author is a free-text field
 * with no legacy ID. Authors from structured tables (mwnf3.authors) are
 * imported first by AuthorImporter with proper ID-based backward_compatibility.
 *
 * This helper:
 * 1. Looks up by name — reuses the existing record if found (e.g., one
 *    already created by AuthorImporter from the structured table).
 * 2. Creates a new author WITHOUT backward_compatibility if not found
 *    (no legacy ID is available for free-text author references).
 */

import type { IWriteStrategy } from '../core/strategy.js';
import type { ITracker } from '../core/tracker.js';
import type { ILogger } from '../core/base-importer.js';
import type { AuthorData } from '../core/types.js';

export class AuthorHelper {
  private strategy: IWriteStrategy;
  private logger: ILogger;

  constructor(strategy: IWriteStrategy, _tracker: ITracker, logger: ILogger) {
    this.strategy = strategy;
    this.logger = logger;
  }

  /**
   * Find or create an author by name.
   *
   * Since free-text author references have no legacy ID, these authors
   * do not get a backward_compatibility value. The lookup is purely
   * name-based.
   */
  async findOrCreate(name: string): Promise<string | null> {
    if (!name || name.trim() === '') {
      return null;
    }

    const trimmedName = name.trim();

    // Look up by name — may find an author already created by AuthorImporter
    const existing = await this.strategy.findAuthorByName(trimmedName);
    if (existing) {
      return existing;
    }

    // Create new author without backward_compatibility (no legacy ID available)
    const authorData: AuthorData = {
      name: trimmedName,
      internal_name: trimmedName,
      backward_compatibility: '',
    };

    try {
      return await this.strategy.writeAuthor(authorData);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      this.logger.warning(
        `Author write failed for '${trimmedName}': ${message}, retrying name lookup`
      );
      // Retry: another thread/record may have created the same author concurrently
      const retryResult = await this.strategy.findAuthorByName(trimmedName);
      if (!retryResult) {
        this.logger.warning(`Author retry lookup also failed for '${trimmedName}' — entity lost`);
      }
      return retryResult;
    }
  }
}
