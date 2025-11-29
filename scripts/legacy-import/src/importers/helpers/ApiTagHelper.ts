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

    // Lookup existing tag first to avoid unnecessary create attempts and warnings
    tagId = await this.findExistingByBackwardCompat(backwardCompat);

    if (tagId) {
      // Register in tracker for fast future lookups
      this.tracker.register({
        uuid: tagId,
        backwardCompatibility: backwardCompat,
        entityType: 'item',
        createdAt: new Date(),
      });
      return tagId;
    }

    // Tag doesn't exist, create it
    try {
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
    } catch (error) {
      // Both 422 and 500 with unique constraint error mean tag already exists
      // Our lookup missed it (pagination issue or backward_compatibility mismatch)
      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { status?: number; data?: unknown } };
        const is422 = axiosError.response?.status === 422;
        const is500Duplicate =
          axiosError.response?.status === 500 &&
          (axiosError.response?.data as any)?.message?.includes('Duplicate entry') &&
          (axiosError.response?.data as any)?.message?.includes('tags_name_category_lang_unique');

        if (is422 || is500Duplicate) {
          // Tag exists - do exhaustive search by backward_compatibility
          tagId = await this.findExistingByBackwardCompat(backwardCompat, 200);

          if (!tagId) {
            // Still not found by backward_compatibility - try searching by actual fields
            tagId = await this.findExistingByFields(normalizedTagName, category, languageId);
          }

          if (tagId) {
            // Found it! Register for future
            this.tracker.register({
              uuid: tagId,
              backwardCompatibility: backwardCompat,
              entityType: 'item',
              createdAt: new Date(),
            });
            return tagId;
          } else {
            // Still can't find it - log warning but continue
            console.warn(
              `Tag exists (${is500Duplicate ? '500 duplicate' : '422 conflict'}) but cannot be found: ${category}:${languageId}:${tagName}`
            );
          }
        } else {
          // Other error - log it
          console.error(`Failed to create tag: ${category}:${tagName}`, error);
        }
      } else {
        console.error(`Failed to create tag: ${category}:${tagName}`, error);
      }
    }

    return null;
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

  /**
   * Search for existing tag by backward_compatibility
   * @param backwardCompat The backward compatibility value to search for
   * @param maxPages Maximum pages to search (default 100, use 200 for exhaustive retry)
   * @returns Tag UUID or null if not found
   */
  private async findExistingByBackwardCompat(
    backwardCompat: string,
    maxPages: number = 100
  ): Promise<string | null> {
    let page = 1;
    const perPage = 100;
    let hasMore = true;

    while (hasMore) {
      const response = await this.apiClient.tag.tagIndex(page, perPage, undefined);
      const tags = response.data.data;

      const existing = tags.find((t) => t.backward_compatibility === backwardCompat);

      if (existing) {
        this.tracker.register({
          uuid: existing.id,
          backwardCompatibility: backwardCompat,
          entityType: 'item',
          createdAt: new Date(),
        });
        return existing.id;
      }

      hasMore = tags.length === perPage;
      page++;

      if (page > maxPages) {
        if (maxPages >= 200) {
          console.warn(`Exhaustive search failed after ${maxPages} pages for: ${backwardCompat}`);
        }
        break;
      }
    }

    return null;
  }

  /**
   * Search for existing tag by the actual unique constraint fields
   * Used as fallback when backward_compatibility search fails
   * @param internalName The tag name (e.g., "portrait", "leather")
   * @param category The tag category
   * @param languageId The language ID
   * @returns Tag UUID or null if not found
   */
  private async findExistingByFields(
    internalName: string,
    category: string,
    languageId: string
  ): Promise<string | null> {
    let page = 1;
    const perPage = 100;
    let hasMore = true;

    // Normalize for case-insensitive comparison (MariaDB unique constraint is case-insensitive)
    const normalizedName = internalName.toLowerCase();
    const normalizedCategory = category?.toLowerCase();
    const normalizedLanguage = languageId?.toLowerCase();

    while (hasMore) {
      const response = await this.apiClient.tag.tagIndex(page, perPage, undefined);
      const tags = response.data.data;

      // Search by the actual unique constraint fields (case-insensitive)
      const existing = tags.find(
        (t) =>
          t.internal_name?.toLowerCase() === normalizedName &&
          t.category?.toLowerCase() === normalizedCategory &&
          t.language_id?.toLowerCase() === normalizedLanguage
      );

      if (existing) {
        return existing.id;
      }

      hasMore = tags.length === perPage;
      page++;

      // Limit search to prevent infinite loops
      if (page > 200) {
        break;
      }
    }

    return null;
  }
}
