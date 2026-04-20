/**
 * Explore Monument Importer
 *
 * Creates Item records for each monument from the Explore database.
 * Monuments are placed in their respective location collections.
 *
 * Legacy schema:
 * - mwnf3_explore.exploremonument (monumentId, locationId, title, geoCoordinates, zoom, special_monument, related_monument)
 *
 * New schema:
 * - items (id, internal_name, backward_compatibility, latitude, longitude, map_zoom, ...)
 * - collection_item (collection_id, item_id) - linking items to location collections
 *
 * Mapping:
 * - monumentId → backward_compatibility (mwnf3_explore:monument:{monumentId})
 * - exploremonumentext.name → internal_name (default language first, then first named translation)
 * - geoCoordinates → latitude, longitude
 * - zoom → map_zoom
 * - locationId → collection link (via collection_item pivot)
 *
 * Dependencies:
 * - ExploreContextImporter
 * - ExploreLocationImporter (parent location collections must exist)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import {
  transformExploreMonument,
  type ExploreLegacyMonument,
  type ExploreMonumentNameTranslation,
} from '../../domain/transformers/explore-monument-transformer.js';

export class ExploreMonumentImporter extends BaseImporter {
  private exploreContextId: string | null = null;
  private locationCollectionCache: Map<number, string | null> = new Map();

  getName(): string {
    return 'ExploreMonumentImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Looking up Explore context...');

      // Get the Explore context ID
      const exploreContextBackwardCompat = 'mwnf3_explore:context';
      this.exploreContextId = await this.getEntityUuidAsync(
        exploreContextBackwardCompat,
        'context'
      );

      if (!this.exploreContextId) {
        throw new Error(
          `Explore context not found (${exploreContextBackwardCompat}). Run ExploreContextImporter first.`
        );
      }

      this.logInfo(`Found Explore context: ${this.exploreContextId}`);
      this.logInfo('Importing Explore monuments...');
      const defaultLanguageId = await this.getDefaultLanguageIdAsync();

      // Query monuments from legacy database
      const monuments = await this.context.legacyDb.query<ExploreLegacyMonument>(
        `SELECT monumentId, locationId, title, geoCoordinates, zoom, special_monument, related_monument
         FROM mwnf3_explore.exploremonument 
         ORDER BY locationId, monumentId`
      );

      const monumentTranslations = await this.context.legacyDb.query<
        ExploreMonumentNameTranslation & { monumentId: number }
      >(
        `SELECT monumentId, langId, name
         FROM mwnf3_explore.exploremonumentext
         WHERE name IS NOT NULL AND name != ''
         ORDER BY monumentId, langId`
      );

      const translationsByMonumentId = new Map<number, ExploreMonumentNameTranslation[]>();
      for (const translation of monumentTranslations) {
        const existingTranslations = translationsByMonumentId.get(translation.monumentId);
        if (existingTranslations) {
          existingTranslations.push({
            langId: translation.langId,
            name: translation.name,
          });
          continue;
        }

        translationsByMonumentId.set(translation.monumentId, [
          {
            langId: translation.langId,
            name: translation.name,
          },
        ]);
      }

      this.logInfo(`Found ${monuments.length} monuments to import`);

      for (const legacy of monuments) {
        try {
          const backwardCompat = `mwnf3_explore:monument:${legacy.monumentId}`;

          // Check if already exists
          if (await this.entityExistsAsync(backwardCompat, 'item')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const translations = translationsByMonumentId.get(legacy.monumentId);
          if (!translations) {
            throw new Error(
              `Explore monument ${backwardCompat} missing translation rows required for internal_name selection`
            );
          }

          const transformed = transformExploreMonument(legacy, translations, defaultLanguageId);
          if (transformed.warning) {
            this.logWarning(transformed.warning);
          }

          // Collect sample
          this.collectSample(
            'explore_monument',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create item: ${transformed.data.internal_name} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'item');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write item using strategy
          const itemId = await this.context.strategy.writeItem({
            ...transformed.data,
            collection_id: null,
            partner_id: null,
            project_id: null,
          });

          this.registerEntity(itemId, backwardCompat, 'item');

          // Link item to location collection if available
          if (transformed.locationId) {
            const collectionId = await this.getLocationCollectionId(transformed.locationId);
            if (collectionId) {
              await this.context.strategy.writeCollectionItem({
                collection_id: collectionId,
                item_id: itemId,
                backward_compatibility: `${backwardCompat}:collection_link:${transformed.locationId}`,
                display_order: null,
              });
            }
          }

          result.imported++;
          this.showProgress();
        } catch (error) {
          result.success = false;
          const errorMessage = error instanceof Error ? error.message : String(error);
          result.errors.push(`Error importing monument ${legacy.monumentId}: ${errorMessage}`);
          this.logError('ExploreMonumentImporter', errorMessage, { monumentId: legacy.monumentId });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in monument import: ${errorMessage}`);
      this.logError('ExploreMonumentImporter', errorMessage);
      this.showError();
    }

    return result;
  }

  /**
   * Get location collection ID from cache or lookup
   */
  private async getLocationCollectionId(locationId: number): Promise<string | null> {
    if (this.locationCollectionCache.has(locationId)) {
      return this.locationCollectionCache.get(locationId) ?? null;
    }

    const backwardCompat = `mwnf3_explore:location:${locationId}`;
    const collectionId = await this.getEntityUuidAsync(backwardCompat, 'collection');

    this.locationCollectionCache.set(locationId, collectionId);

    return collectionId;
  }
}
