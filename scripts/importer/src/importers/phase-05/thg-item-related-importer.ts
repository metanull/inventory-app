/**
 * THG Item Related Importer
 *
 * Imports theme_item_related entries as ItemItemLink records.
 * Creates links between items within a gallery context.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.theme_item_related (gallery_id, theme_id, item_id, related_item_id)
 *
 * New schema:
 * - item_item_links (id, source_id, target_id, context_id)
 *
 * Context: Uses the gallery's context (created by ThgGalleryContextImporter)
 * Only processes mwnf3-resolvable items.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Legacy theme_item_related structure
 */
interface LegacyThemeItemRelated {
  gallery_id: number;
  theme_id: number;
  item_id: number;
  related_item_id: number;
}

/**
 * Legacy theme_item structure for item resolution
 */
interface LegacyThemeItem {
  gallery_id: number;
  theme_id: number;
  item_id: number;
  // mwnf3 object references
  mwnf3_object_project_id: string | null;
  mwnf3_object_country_id: string | null;
  mwnf3_object_partner_id: string | null;
  mwnf3_object_item_id: number | null;
  // mwnf3 monument references
  mwnf3_monument_project_id: string | null;
  mwnf3_monument_country_id: string | null;
  mwnf3_monument_partner_id: string | null;
  mwnf3_monument_item_id: number | null;
  // mwnf3 monument detail references
  mwnf3_monument_detail_project_id: string | null;
  mwnf3_monument_detail_country_id: string | null;
  mwnf3_monument_detail_partner_id: string | null;
  mwnf3_monument_detail_item_id: number | null;
  mwnf3_monument_detail_detail_id: number | null;
}

export class ThgItemRelatedImporter extends BaseImporter {
  // Cache theme_item data for item resolution
  private themeItemCache: Map<string, LegacyThemeItem> = new Map();

  getName(): string {
    return 'ThgItemRelatedImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Loading theme_item data for item resolution...');

      // Load theme_item data to resolve item references
      // Note: The legacy schema may not have this table - handle gracefully
      try {
        const themeItems = await this.context.legacyDb.query<LegacyThemeItem>(
          `SELECT gallery_id, theme_id, item_id,
                  mwnf3_object_project_id, mwnf3_object_country_id, mwnf3_object_partner_id, mwnf3_object_item_id,
                  mwnf3_monument_project_id, mwnf3_monument_country_id, mwnf3_monument_partner_id, mwnf3_monument_item_id,
                  mwnf3_monument_detail_project_id, mwnf3_monument_detail_country_id, mwnf3_monument_detail_partner_id,
                  mwnf3_monument_detail_item_id, mwnf3_monument_detail_detail_id
           FROM mwnf3_thematic_gallery.theme_item`
        );

        for (const item of themeItems) {
          const key = `${item.gallery_id}.${item.theme_id}.${item.item_id}`;
          this.themeItemCache.set(key, item);
        }

        this.logInfo(`Loaded ${this.themeItemCache.size} theme_item records`);
      } catch (queryError) {
        const message = queryError instanceof Error ? queryError.message : String(queryError);
        if (message.includes("doesn't exist") || message.includes('Unknown column')) {
          this.logInfo(
            `⚠️ Skipping: Legacy theme_item table not available (${message})`
          );
          result.warnings = result.warnings || [];
          result.warnings.push(`Legacy theme_item table not available: ${message}`);
          return result;
        }
        throw queryError;
      }

      this.logInfo('Importing item-item links from theme_item_related...');

      // Query related items from legacy database
      const relatedItems = await this.context.legacyDb.query<LegacyThemeItemRelated>(
        `SELECT gallery_id, theme_id, item_id, related_item_id
         FROM mwnf3_thematic_gallery.theme_item_related
         ORDER BY gallery_id, theme_id, item_id, related_item_id`
      );

      this.logInfo(`Found ${relatedItems.length} item-item relations to import`);

      for (const legacy of relatedItems) {
        try {
          // Get theme_item for source item resolution
          const sourceKey = `${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}`;
          const sourceThemeItem = this.themeItemCache.get(sourceKey);
          if (!sourceThemeItem) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Item relation ${sourceKey}: source theme_item not found`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get theme_item for target (related) item resolution
          const targetKey = `${legacy.gallery_id}.${legacy.theme_id}.${legacy.related_item_id}`;
          const targetThemeItem = this.themeItemCache.get(targetKey);
          if (!targetThemeItem) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Item relation ${sourceKey}: target theme_item not found (related_item_id=${legacy.related_item_id})`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve source item reference
          const sourceBackwardCompat = this.resolveItemReference(sourceThemeItem);
          if (!sourceBackwardCompat) {
            // Not an mwnf3 item - skip
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve target item reference
          const targetBackwardCompat = this.resolveItemReference(targetThemeItem);
          if (!targetBackwardCompat) {
            // Not an mwnf3 item - skip
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the source item ID from tracker or database (items are from earlier phases)
          const sourceId = await this.getEntityUuidAsync(sourceBackwardCompat, 'item');
          if (!sourceId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Item relation ${sourceKey}: Source item not found (${sourceBackwardCompat})`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the target item ID from tracker or database (items are from earlier phases)
          const targetId = await this.getEntityUuidAsync(targetBackwardCompat, 'item');
          if (!targetId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Item relation ${sourceKey}: Target item not found (${targetBackwardCompat})`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Get the context ID for this gallery (Phase 05 internal, but use async for consistency)
          const galleryBackwardCompat = `mwnf3_thematic_gallery:thg_gallery:${legacy.gallery_id}`;
          const contextId = await this.getEntityUuidAsync(galleryBackwardCompat, 'context');
          if (!contextId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Item relation ${sourceKey}: Context not found for gallery ${legacy.gallery_id}`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Create backward compatibility key for the link
          const backwardCompat = `mwnf3_thematic_gallery:theme_item_related:${legacy.gallery_id}:${legacy.theme_id}:${legacy.item_id}:${legacy.related_item_id}`;

          // Check if already exists (use async for database fallback)
          if (await this.entityExistsAsync(backwardCompat, 'item_item_link')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Collect sample
          this.collectSample(
            'thg_item_related',
            {
              ...legacy,
              source_backward_compat: sourceBackwardCompat,
              target_backward_compat: targetBackwardCompat,
            } as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create item link: ${backwardCompat}`
            );
            this.registerEntity('', backwardCompat, 'item_item_link');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write item-item link using strategy
          const linkId = await this.context.strategy.writeItemItemLink({
            source_id: sourceId,
            target_id: targetId,
            context_id: contextId,
            backward_compatibility: backwardCompat,
          });

          this.registerEntity(linkId, backwardCompat, 'item_item_link');

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Item relation ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}->${legacy.related_item_id}: ${message}`
          );
          this.logError(
            `Item relation ${legacy.gallery_id}.${legacy.theme_id}.${legacy.item_id}->${legacy.related_item_id}`,
            error
          );
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgItemRelatedImporter', error);
    }

    return result;
  }

  /**
   * Resolve item reference from theme_item to backward_compatibility string
   * Returns null if not an mwnf3 item
   */
  private resolveItemReference(legacy: LegacyThemeItem): string | null {
    // Check mwnf3_object reference
    // Format: mwnf3:objects:PROJECT:COUNTRY:MUSEUM:NUMBER (matching object-transformer.ts)
    if (
      legacy.mwnf3_object_project_id &&
      legacy.mwnf3_object_country_id &&
      legacy.mwnf3_object_partner_id &&
      legacy.mwnf3_object_item_id !== null
    ) {
      return `mwnf3:objects:${legacy.mwnf3_object_project_id}:${legacy.mwnf3_object_country_id}:${legacy.mwnf3_object_partner_id}:${legacy.mwnf3_object_item_id}`;
    }

    // Check mwnf3_monument reference
    // Format: mwnf3:monuments:PROJECT:COUNTRY:INSTITUTION:NUMBER (matching monument-transformer.ts)
    if (
      legacy.mwnf3_monument_project_id &&
      legacy.mwnf3_monument_country_id &&
      legacy.mwnf3_monument_partner_id &&
      legacy.mwnf3_monument_item_id !== null
    ) {
      return `mwnf3:monuments:${legacy.mwnf3_monument_project_id}:${legacy.mwnf3_monument_country_id}:${legacy.mwnf3_monument_partner_id}:${legacy.mwnf3_monument_item_id}`;
    }

    // Check mwnf3_monument_detail reference
    // Format: mwnf3:monument_details:PROJECT:COUNTRY:INSTITUTION:MONUMENT:DETAIL (matching monument-detail-transformer.ts)
    if (
      legacy.mwnf3_monument_detail_project_id &&
      legacy.mwnf3_monument_detail_country_id &&
      legacy.mwnf3_monument_detail_partner_id &&
      legacy.mwnf3_monument_detail_item_id !== null &&
      legacy.mwnf3_monument_detail_detail_id !== null
    ) {
      return `mwnf3:monument_details:${legacy.mwnf3_monument_detail_project_id}:${legacy.mwnf3_monument_detail_country_id}:${legacy.mwnf3_monument_detail_partner_id}:${legacy.mwnf3_monument_detail_item_id}:${legacy.mwnf3_monument_detail_detail_id}`;
    }

    // Not an mwnf3 item
    return null;
  }
}
