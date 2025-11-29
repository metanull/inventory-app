/* eslint-disable @typescript-eslint/no-explicit-any */
import type { InventoryApiClient } from '../../api/InventoryApiClient.js';
import type { BackwardCompatibilityTracker } from '../../utils/BackwardCompatibilityTracker.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';

/**
 * Helper class for managing Tag entities through the API
 * Handles find-or-create logic with backward compatibility tracking
 *
 * Matches the pattern of SQL importer's TagHelper but uses API calls
 */
export class ApiTagHelper {
  private apiClient: InventoryApiClient;
  private tracker: BackwardCompatibilityTracker;

  constructor(apiClient: InventoryApiClient, tracker: BackwardCompatibilityTracker) {
    this.apiClient = apiClient;
    this.tracker = tracker;
  }

  /**
   * Parse and create tags from separated string
   * IMPORTANT: Fields with colons (e.g., "Warp: wool; Weft: cotton") are STRUCTURED DATA
   * - If colon found: treat as single structured tag (don't split)
   * - Otherwise: split by semicolon (;) primary, comma (,) fallback
   * Tags are language-specific: same content in different languages = different tags
   * @param tagString The tag string to parse
   * @param category The tag category (e.g., 'material', 'dynasty', 'keyword')
   * @param languageId The language ID (3-char ISO 639-3)
   * @returns Array of tag UUIDs
   */
  async findOrCreateList(
    tagString: string,
    category: string,
    languageId: string
  ): Promise<string[]> {
    if (!tagString || tagString.trim() === '') {
      return [];
    }

    // Check if this is structured data (contains colon)
    // Example: "Warp: Light brown wool; Weft: Red wool" should be ONE tag, not split
    const isStructured = tagString.includes(':');

    let tagNames: string[];

    if (isStructured) {
      // Structured data - keep as single tag
      tagNames = [tagString.trim()];
    } else {
      // Simple list - use semicolon as primary separator, comma as fallback
      const separator = tagString.includes(';') ? ';' : ',';
      tagNames = tagString
        .split(separator)
        .map((t) => t.trim())
        .filter((t) => t !== '');
    }

    const tagIds: string[] = [];

    for (const tagName of tagNames) {
      const tagId = await this.findOrCreate(tagName, category, languageId);
      if (tagId) {
        tagIds.push(tagId);
      }
    }

    return tagIds;
  }

  /**
   * Find or create a single Tag
   * @param tagName The tag name
   * @param category The tag category
   * @param languageId The language ID
   * @returns Tag UUID or null if creation fails
   */
  async findOrCreate(
    tagName: string,
    category: string,
    languageId: string
  ): Promise<string | null> {
    // Normalize to lowercase to avoid case-sensitivity issues
    // Original capitalization preserved in description for display
    const normalizedTagName = tagName.toLowerCase();

    // Include table name, category, and language to create unique backward_compatibility
    // Format: mwnf3:tags:{category}:{lang}:{tagName}
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: `tags:${category}:${languageId}`,
      pkValues: [normalizedTagName],
    });

    // Check if already exists in tracker
    let tagId = this.tracker.getUuid(backwardCompat);
    if (tagId) {
      return tagId;
    }

    // Tag doesn't exist, create it
    // Internal_name should be clean tag value only (e.g., "portrait", "leather")
    // ALWAYS lowercase to avoid case-sensitivity issues
    // Original capitalization preserved in description for display
    const createPayload: Record<string, unknown> = {
      internal_name: normalizedTagName,
      category: category,
      language_id: languageId,
      description: tagName, // Keep original capitalization for display
      backward_compatibility: backwardCompat,
    };

    const response = await this.apiClient.tag.tagStore(createPayload as any);
    tagId = response.data.data.id;

    // Register in tracker
    this.tracker.register({
      uuid: tagId,
      backwardCompatibility: backwardCompat,
      entityType: 'item',
      createdAt: new Date(),
    });

    return tagId;
  }

  /**
   * Attach tags to an item using the API
   * @param itemId The item UUID
   * @param tagIds Array of tag UUIDs
   */
  async attachToItem(itemId: string, tagIds: string[]): Promise<void> {
    if (tagIds.length === 0) {
      return;
    }

    // Use the updateTags endpoint to attach tags
    await this.apiClient.item.itemUpdateTags(itemId, {
      attach: tagIds,
    });
  }
}
