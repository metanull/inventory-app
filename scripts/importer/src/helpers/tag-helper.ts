/**
 * Tag Helper
 *
 * Unified helper for managing Tag entities.
 * Uses write strategy for persistence.
 */

import type { IWriteStrategy } from '../core/strategy.js';
import type { ITracker } from '../core/tracker.js';
import type { ILogger } from '../core/base-importer.js';
import type { TagData } from '../core/types.js';
import { formatBackwardCompatibility } from '../utils/backward-compatibility.js';
import { stripHtml } from '../utils/html-to-markdown.js';

export class TagHelper {
  private strategy: IWriteStrategy;
  private tracker: ITracker;
  private logger: ILogger;

  constructor(strategy: IWriteStrategy, tracker: ITracker, logger: ILogger) {
    this.strategy = strategy;
    this.tracker = tracker;
    this.logger = logger;
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

    // Split by semicolon as primary separator, comma as fallback
    const separator = tagString.includes(';') ? ';' : ',';
    const tagNames = tagString
      .split(separator)
      .map((t) => t.trim())
      .filter(Boolean);

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
  async findOrCreate(name: string, category: string, languageId: string): Promise<string | null> {
    // Strip HTML from tag name
    const cleanName = stripHtml(name);
    if (!cleanName) {
      return null;
    }

    const normalized = cleanName.toLowerCase();
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
      description: cleanName, // Keep original capitalization for display (HTML stripped)
      backward_compatibility: backwardCompat,
    };

    try {
      return await this.strategy.writeTag(tagData);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      this.logger.warning(
        `Tag write failed for '${cleanName}' (${category}/${languageId}): ${message}, retrying lookup`
      );
      const retryResult = await this.strategy.findByBackwardCompatibility('tags', backwardCompat);
      if (!retryResult) {
        this.logger.warning(
          `Tag retry lookup also failed for '${cleanName}' (${category}/${languageId}) — entity lost`
        );
      }
      return retryResult;
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
