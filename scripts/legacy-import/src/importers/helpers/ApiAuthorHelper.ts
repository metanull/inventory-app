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

    // Create new author
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
  }
}
