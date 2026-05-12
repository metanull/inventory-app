/**
 * Explore Monument-Theme Link Importer (Story 13.3)
 *
 * Imports exploremonumentsthemes (1,706 rows) → collection_item pivot
 * linking thematic cycle Collection → monument Item.
 * The locationId in the PK is redundant (structurally implicit) and not stored.
 *
 * BC: mwnf3_explore:monument_theme:{cycleId}:{monumentId}
 *
 * Dependencies:
 * - ExploreThematicCycleImporter (thematic cycles must exist)
 * - ExploreMonumentImporter (monuments must exist)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { ExploreMonumentResolver } from './explore-monument-resolver.js';

interface LegacyMonumentTheme {
  cycleId: number;
  monumentId: number;
  locationId: number;
}

export class ExploreMonumentThemeLinkImporter extends BaseImporter {
  private monumentResolver!: ExploreMonumentResolver;

  getName(): string {
    return 'ExploreMonumentThemeLinkImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing Explore monument-theme links...');
      this.monumentResolver = new ExploreMonumentResolver({
        legacyDb: this.context.legacyDb,
        tracker: this.context.tracker,
        getEntityUuid: (backwardCompatibility, entityType) =>
          this.getEntityUuidAsync(backwardCompatibility, entityType),
      });

      const links = await this.context.legacyDb.query<LegacyMonumentTheme>(
        `SELECT themeId AS cycleId, monumentId, locationId FROM mwnf3_explore.exploremonumentsthemes ORDER BY themeId, monumentId`
      );
      this.logInfo(`Found ${links.length} monument-theme links`);

      for (const link of links) {
        try {
          // Resolve thematic cycle collection
          const cycleBC = `mwnf3_explore:thematiccycle:${link.cycleId}`;
          const collectionId = await this.getEntityUuidAsync(cycleBC, 'collection');
          if (!collectionId) {
            this.logWarning(`Thematic cycle not found: ${cycleBC}, skipping link`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve monument item
          const monumentResolution = await this.monumentResolver.resolve(link.monumentId);
          if (monumentResolution.mode === 'resolvedCandidates') {
            for (const candidate of monumentResolution.resolvedCandidates ?? []) {
              if (!this.isDryRun && !this.isSampleOnlyMode) {
                const linkBC2 = `mwnf3_explore:monument_theme:${link.cycleId}:${link.monumentId}:${candidate.source}`;
                await this.context.strategy.writeCollectionItem({
                  collection_id: collectionId,
                  item_id: candidate.itemId,
                  backward_compatibility: linkBC2,
                });
              }
            }
            result.imported++;
            this.showProgress();
            continue;
          }
          if (!monumentResolution.itemId || !monumentResolution.itemBackwardCompatibility) {
            this.logWarning(
              `${monumentResolution.message ?? `Explore monument mwnf3_explore:monument:${link.monumentId} did not resolve to an item`}, skipping link`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would link monument ${link.monumentId} to cycle ${link.cycleId}`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          const linkBC = `mwnf3_explore:monument_theme:${link.cycleId}:${link.monumentId}`;
          await this.context.strategy.writeCollectionItem({
            collection_id: collectionId,
            item_id: monumentResolution.itemId,
            backward_compatibility: linkBC,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          // Duplicate pivot entries are expected — don't treat as errors
          if (message.includes('Duplicate')) {
            this.logSkip(
              `Duplicate monument-theme link ${link.cycleId}/${link.monumentId}, skipping`
            );
            result.skipped++;
          } else {
            this.logWarning(
              `Failed monument-theme link ${link.cycleId}/${link.monumentId}: ${message}`
            );
          }
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in monument-theme link import: ${errorMessage}`);
      this.logError('ExploreMonumentThemeLinkImporter', errorMessage);
      this.showError();
    }

    return result;
  }
}
