/**
 * Explore Filter Importer (Story 14.1)
 *
 * Imports Explore filters as Tags and filter-monument links as item_tag pivot rows.
 *
 * 1. Import 30 filters → Tag: internal_name=lowercase name, category='filter', language_id='eng'
 *    BC: mwnf3_explore:filter:{filter_id}
 * 2. Import 4,992 filters_explore_monuments → item_tag
 *
 * Skip: filter_types (1 row, implicit in category)
 *       explorethemes / explorethemestranslated / locationsthemes (all empty)
 *
 * Dependencies:
 * - ExploreMonumentImporter (monument items must exist)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

interface LegacyFilter {
  filterId: string;
  name: string;
  filtertype: string | null;
}

interface LegacyFilterMonument {
  filterId: string;
  monumentId: number;
}

export class ExploreFilterImporter extends BaseImporter {
  getName(): string {
    return 'ExploreFilterImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing Explore filters...');

      // 1. Import filters → Tags
      const filters = await this.context.legacyDb.query<LegacyFilter>(
        `SELECT filter_id AS filterId, filter_name AS name, type_id AS filtertype FROM mwnf3_explore.filters ORDER BY filter_id`
      );
      this.logInfo(`Found ${filters.length} filters to import`);

      const filterTagMap = new Map<string, string>(); // filterId → tag UUID

      for (const filter of filters) {
        try {
          const backwardCompat = `mwnf3_explore:filter:${filter.filterId}`;

          // Check if already exists
          const existingId = await this.getEntityUuidAsync(backwardCompat, 'tag');
          if (existingId) {
            filterTagMap.set(filter.filterId, existingId);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const internalName = filter.name.toLowerCase().trim();

          this.collectSample(
            'explore_filter',
            filter as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create tag: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'tag');
            result.imported++;
            this.showProgress();
            continue;
          }

          const tagId = await this.context.strategy.writeTag({
            internal_name: internalName,
            category: 'filter',
            language_id: 'eng',
            description: filter.name,
            backward_compatibility: backwardCompat,
          });

          this.registerEntity(tagId, backwardCompat, 'tag');
          filterTagMap.set(filter.filterId, tagId);

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Filter ${filter.filterId}: ${message}`);
          this.logError('ExploreFilterImporter', message, { filterId: filter.filterId });
          this.showError();
        }
      }

      // 2. Import filter-monument links → item_tag
      this.logInfo('Importing filter-monument links...');
      const links = await this.context.legacyDb.query<LegacyFilterMonument>(
        `SELECT filter_id AS filterId, monumentId FROM mwnf3_explore.filters_explore_monuments ORDER BY filter_id, monumentId`
      );
      this.logInfo(`Found ${links.length} filter-monument links`);

      for (const link of links) {
        try {
          // Resolve tag
          let tagId = filterTagMap.get(link.filterId);
          if (!tagId) {
            const filterBC = `mwnf3_explore:filter:${link.filterId}`;
            tagId = (await this.getEntityUuidAsync(filterBC, 'tag')) ?? undefined;
            if (tagId) {
              filterTagMap.set(link.filterId, tagId);
            }
          }
          if (!tagId) {
            this.logWarning(`Filter tag not found for filterId ${link.filterId}, skipping link`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve monument item
          const monumentBC = `mwnf3_explore:monument:${link.monumentId}`;
          const itemId = await this.getEntityUuidAsync(monumentBC, 'item');
          if (!itemId) {
            this.logWarning(`Monument item not found: ${monumentBC}, skipping link`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          if (this.isDryRun || this.isSampleOnlyMode) {
            result.imported++;
            this.showProgress();
            continue;
          }

          await this.context.strategy.attachTagsToItem(itemId, [tagId]);
          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          if (message.includes('Duplicate')) {
            this.logSkip(
              `Duplicate filter-monument link ${link.filterId}/${link.monumentId}, skipping`
            );
            result.skipped++;
          } else {
            this.logWarning(
              `Failed filter-monument link ${link.filterId}/${link.monumentId}: ${message}`
            );
          }
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in filter import: ${errorMessage}`);
      this.logError('ExploreFilterImporter', errorMessage);
      this.showError();
    }

    return result;
  }
}
