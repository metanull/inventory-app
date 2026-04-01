/**
 * Explore Region-Location Linker (Story 11.2)
 *
 * Re-parents location collections under their most specific region
 * (highest territory level). For locations with multiple regions,
 * stores additional memberships in extra.additional_regions.
 *
 * Legacy schema:
 * - mwnf3_explore.regionlocations (regionId, locationId)
 * - mwnf3_explore.region (regionId, type) — type = territory_level
 *
 * Steps:
 * 1. Query regionlocations (204 rows)
 * 2. For each location, find region with highest territory_level
 * 3. Update location collection's parent_id to that region
 * 4. Store additional regions in extra.additional_regions
 *
 * Dependencies:
 * - ExploreRegionImporter (regions must exist)
 * - ExploreLocationImporter (locations must exist)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

interface LegacyRegionLocation {
  regionId: number;
  locationId: number;
}

interface LegacyRegionInfo {
  regionId: number;
  type: number | null;
}

export class ExploreRegionLocationLinker extends BaseImporter {
  getName(): string {
    return 'ExploreRegionLocationLinker';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Re-parenting locations under regions...');

      // Fetch region-location associations
      const regionLocations = await this.context.legacyDb.query<LegacyRegionLocation>(
        `SELECT regionId, locationId FROM mwnf3_explore.regionlocations ORDER BY locationId, regionId`
      );
      this.logInfo(`Found ${regionLocations.length} region-location associations`);

      // Fetch region territory levels
      const regionInfos = await this.context.legacyDb.query<LegacyRegionInfo>(
        `SELECT regionId, type FROM mwnf3_explore.region`
      );
      const regionLevelMap = new Map<number, number>();
      for (const r of regionInfos) {
        regionLevelMap.set(r.regionId, r.type ?? 0);
      }

      // Group by location
      const locationRegions = new Map<number, number[]>();
      for (const rl of regionLocations) {
        const list = locationRegions.get(rl.locationId) ?? [];
        list.push(rl.regionId);
        locationRegions.set(rl.locationId, list);
      }

      for (const [locationId, regionIds] of locationRegions) {
        try {
          // Resolve location collection
          const locationBC = `mwnf3_explore:location:${locationId}`;
          const locationCollectionId = await this.getEntityUuidAsync(locationBC, 'collection');
          if (!locationCollectionId) {
            this.logWarning(`Location collection not found: ${locationBC}, skipping`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Find the most specific region (highest territory_level)
          let bestRegionId: number | null = null;
          let bestLevel = -1;
          const additionalRegionIds: number[] = [];

          for (const regionId of regionIds) {
            const level = regionLevelMap.get(regionId) ?? 0;
            if (level > bestLevel) {
              if (bestRegionId !== null) {
                additionalRegionIds.push(bestRegionId);
              }
              bestRegionId = regionId;
              bestLevel = level;
            } else {
              additionalRegionIds.push(regionId);
            }
          }

          if (bestRegionId === null) {
            this.logWarning(`No valid region for location ${locationId}, skipping`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve best region collection
          const regionBC = `mwnf3_explore:region:${bestRegionId}`;
          const regionCollectionId = await this.getEntityUuidAsync(regionBC, 'collection');
          if (!regionCollectionId) {
            this.logWarning(
              `Region collection not found: ${regionBC}, skipping location ${locationId}`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would re-parent location ${locationId} under region ${bestRegionId}`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // Update parent_id
          await this.context.strategy.updateCollectionParentId(
            locationCollectionId,
            regionCollectionId
          );

          // Store additional regions in extra if any
          if (additionalRegionIds.length > 0) {
            // Read existing extra from collection translation (eng)
            const existingExtra = await this.context.strategy.getCollectionTranslationExtra(
              locationCollectionId,
              'eng'
            );
            const extra = existingExtra ?? {};
            extra.additional_regions = additionalRegionIds;

            await this.context.strategy.setCollectionTranslationExtra(
              locationCollectionId,
              'eng',
              JSON.stringify(extra)
            );
          }

          result.imported++;
          this.showProgress();
        } catch (error) {
          result.success = false;
          const errorMessage = error instanceof Error ? error.message : String(error);
          result.errors.push(`Location ${locationId}: ${errorMessage}`);
          this.logError('ExploreRegionLocationLinker', errorMessage, { locationId });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in region-location linking: ${errorMessage}`);
      this.logError('ExploreRegionLocationLinker', errorMessage);
      this.showError();
    }

    return result;
  }
}
