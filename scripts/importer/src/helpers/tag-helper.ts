/**
 * Tag Helper
 *
 * Unified helper for managing Tag entities.
 * Uses write strategy for persistence.
 */

import type { IWriteStrategy } from '../core/strategy.js';
import type { ITracker } from '../core/tracker.js';
import type { TagData } from '../core/types.js';
import { formatBackwardCompatibility } from '../utils/backward-compatibility.js';

export class TagHelper {
  private strategy: IWriteStrategy;
  private tracker: ITracker;

  constructor(strategy: IWriteStrategy, tracker: ITracker) {
    this.strategy = strategy;
    this.tracker = tracker;
  }

  /**
   * Find or create a list of tags from a tag string
   * Handles structured data and simple lists
   */
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

  /**
   * Find or create a single tag
   */
  async findOrCreate(
    name: string,
    category: string,
    languageId: string
  ): Promise<string | null> {
    const normalized = name.toLowerCase();
    const backwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: `tags:${category}:${languageId}`,
      pkValues: [normalized],
    });

    // Check tracker first
    const existing = this.tracker.getUuid(backwardCompat);
    if (existing) {
      return existing;
    }

    // Check database via strategy
    const found = await this.strategy.findByBackwardCompatibility('tags', backwardCompat);
    if (found) {
      return found;
    }

    // Create new tag
    const tagData: TagData = {
      internal_name: normalized,
      category: category,
      language_id: languageId,
      description: name, // Keep original capitalization for display
      backward_compatibility: backwardCompat,
    };

    try {
      return await this.strategy.writeTag(tagData);
    } catch {
      // If creation fails (duplicate), try to find again
      return await this.strategy.findByBackwardCompatibility('tags', backwardCompat);
    }
  }

  /**
   * Attach tags to an item
   */
  async attachToItem(itemId: string, tagIds: string[]): Promise<void> {
    if (tagIds.length === 0) return;
    await this.strategy.attachTagsToItem(itemId, tagIds);
  }
}
