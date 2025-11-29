/* eslint-disable @typescript-eslint/no-explicit-any */
import type { InventoryApiClient } from '../../api/InventoryApiClient.js';
import type { BackwardCompatibilityTracker } from '../../utils/BackwardCompatibilityTracker.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';

/**
 * Helper class for managing Author entities through the API
 * Handles find-or-create logic with backward compatibility tracking
 *
 * Matches the pattern of SQL importer's AuthorHelper but uses API calls
 */
export class ApiAuthorHelper {
  private apiClient: InventoryApiClient;
  private tracker: BackwardCompatibilityTracker;

  constructor(apiClient: InventoryApiClient, tracker: BackwardCompatibilityTracker) {
    this.apiClient = apiClient;
    this.tracker = tracker;
  }

  /**
   * Find or create an Author from a name string
   * Authors are language-independent (name is the same across languages)
   * @param authorName The author's name
   * @returns Author UUID or null if creation fails
   */
  async findOrCreate(authorName: string): Promise<string | null> {
    if (!authorName || authorName.trim() === '') {
      return null;
    }

    const trimmedName = authorName.trim();

    // Check tracker first
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'authors',
      pkValues: [trimmedName],
    });

    const existing = this.tracker.getUuid(backwardCompat);
    if (existing) {
      return existing;
    }

    // Search in API
    const foundId = await this.findExistingByBackwardCompat(backwardCompat);
    if (foundId) {
      return foundId;
    }

    // Create new author
    try {
      const response = await (this.apiClient as any).author.authorStore({
        name: trimmedName,
        internal_name: trimmedName,
        backward_compatibility: backwardCompat,
      });

      const authorId = response.data.data.id;

      // Register in tracker
      this.tracker.register({
        uuid: authorId,
        backwardCompatibility: backwardCompat,
        entityType: 'item' as any,
        createdAt: new Date(),
      });

      return authorId;
    } catch (error) {
      // If 422 conflict, try to find it
      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { status?: number } };
        if (axiosError.response?.status === 422) {
          // Try exhaustive search
          const foundId = await this.findExistingByBackwardCompat(backwardCompat, 200);
          if (foundId) {
            return foundId;
          }
        }
      }
      console.error(`Failed to create author: ${trimmedName}`, error);
      return null;
    }
  }

  /**
   * Search for existing author by backward_compatibility
   * @param backwardCompat The backward compatibility value to search for
   * @param maxPages Maximum pages to search (default 100, use 200 for exhaustive retry)
   * @returns Author UUID or null if not found
   */
  private async findExistingByBackwardCompat(
    backwardCompat: string,
    maxPages: number = 100
  ): Promise<string | null> {
    let page = 1;
    const perPage = 100;
    let hasMore = true;

    while (hasMore) {
      const response = await (this.apiClient as any).author.authorIndex(page, perPage, undefined);
      const authors = response.data.data;

      const existing = authors.find((a: any) => a.backward_compatibility === backwardCompat);

      if (existing) {
        this.tracker.register({
          uuid: existing.id,
          backwardCompatibility: backwardCompat,
          entityType: 'item' as any,
          createdAt: new Date(),
        });
        return existing.id;
      }

      hasMore = authors.length === perPage;
      page++;

      if (page > maxPages) {
        break;
      }
    }

    return null;
  }
}
