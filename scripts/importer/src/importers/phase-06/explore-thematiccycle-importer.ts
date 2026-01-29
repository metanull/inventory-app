/**
 * Explore Thematic Cycle Importer
 *
 * Creates Collection records for each thematic cycle from the Explore database.
 * Thematic cycles are top-level groupings like "IHM - Islamic Heritage of the Mediterranean".
 *
 * Legacy schema:
 * - mwnf3_explore.thematiccycle (cycleId, cycleLabel, cycleDescription, status, geoCoordinates, zoom, path, order)
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, backward_compatibility, ...)
 *
 * Mapping:
 * - cycleId → backward_compatibility (mwnf3_explore:thematiccycle:{cycleId})
 * - cycleLabel → internal_name (slugified)
 * - geoCoordinates → latitude, longitude (parsed from "lat,lon" format)
 * - zoom → map_zoom
 * - type = 'theme'
 * - parent_id = explore_by_theme root collection
 *
 * Dependencies:
 * - ExploreContextImporter (must run first to create the Explore context)
 * - ExploreRootCollectionsImporter (must run first to create root collections)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

/**
 * Convert a string to a URL-safe slug
 */
function slugify(text: string): string {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '')
    .substring(0, 100);
}

/**
 * Legacy thematiccycle structure
 */
interface LegacyThematicCycle {
  cycleId: number;
  cycleLabel: string;
  cycleDescription: string;
  status: string;
  geoCoordinates: string | null;
  zoom: number | null;
  path: string | null;
  order: number | null;
}

/**
 * Parse legacy geoCoordinates format (e.g., "25,10" or "40.178873,-8.063965")
 * Returns [latitude, longitude] or [null, null] if invalid
 */
function parseGeoCoordinates(coords: string | null): [number | null, number | null] {
  if (!coords || !coords.trim()) {
    return [null, null];
  }
  // Clean whitespace and tabs
  const cleaned = coords.replace(/\s+/g, '').trim();
  const parts = cleaned.split(',');
  if (parts.length !== 2) {
    return [null, null];
  }
  const lat = parseFloat(parts[0]);
  const lon = parseFloat(parts[1]);
  if (isNaN(lat) || isNaN(lon)) {
    return [null, null];
  }
  return [lat, lon];
}

export class ExploreThematicCycleImporter extends BaseImporter {
  private exploreContextId: string | null = null;
  private exploreByThemeId: string | null = null;
  private defaultLanguageId: string = 'eng';

  getName(): string {
    return 'ExploreThematicCycleImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Looking up Explore context and root collection...');

      // Get the Explore context ID
      const exploreContextBackwardCompat = 'mwnf3_explore:context';
      this.exploreContextId = await this.getEntityUuidAsync(exploreContextBackwardCompat, 'context');

      if (!this.exploreContextId) {
        throw new Error(
          `Explore context not found (${exploreContextBackwardCompat}). Run ExploreContextImporter first.`
        );
      }

      // Get the "Explore by Theme" root collection
      const exploreByThemeBackwardCompat = 'mwnf3_explore:root:explore_by_theme';
      this.exploreByThemeId = await this.getEntityUuidAsync(
        exploreByThemeBackwardCompat,
        'collection'
      );

      if (!this.exploreByThemeId) {
        throw new Error(
          `Explore by Theme collection not found (${exploreByThemeBackwardCompat}). Run ExploreRootCollectionsImporter first.`
        );
      }

      this.logInfo(`Found Explore context: ${this.exploreContextId}`);
      this.logInfo(`Found Explore by Theme: ${this.exploreByThemeId}`);
      this.logInfo('Importing thematic cycles...');

      // Query thematic cycles from legacy database (only enabled ones: status = 'e')
      const cycles = await this.context.legacyDb.query<LegacyThematicCycle>(
        `SELECT cycleId, cycleLabel, cycleDescription, status, geoCoordinates, zoom, path, \`order\`
         FROM mwnf3_explore.thematiccycle 
         WHERE status = 'e' AND cycleLabel != ''
         ORDER BY \`order\`, cycleId`
      );

      this.logInfo(`Found ${cycles.length} enabled thematic cycles to import`);

      for (const legacy of cycles) {
        try {
          const backwardCompat = `mwnf3_explore:thematiccycle:${legacy.cycleId}`;

          // Check if already exists
          if (await this.entityExistsAsync(backwardCompat, 'collection')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Parse coordinates
          const [latitude, longitude] = parseGeoCoordinates(legacy.geoCoordinates);

          // Create internal name from cycleLabel
          const internalName = `theme_${slugify(legacy.cycleLabel)}`;

          // Collect sample
          this.collectSample(
            'thematiccycle',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create collection: ${internalName} (${backwardCompat})`
            );
            this.registerEntity('', backwardCompat, 'collection');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Write collection using strategy
          const collectionId = await this.context.strategy.writeCollection({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            context_id: this.exploreContextId,
            language_id: this.defaultLanguageId,
            parent_id: this.exploreByThemeId,
            type: 'theme',
            latitude,
            longitude,
            map_zoom: legacy.zoom,
            country_id: null,
          });

          this.registerEntity(collectionId, backwardCompat, 'collection');

          // Create translation for the collection
          const translationBackwardCompat = `${backwardCompat}:translation:${this.defaultLanguageId}`;

          await this.context.strategy.writeCollectionTranslation({
            collection_id: collectionId,
            language_id: this.defaultLanguageId,
            context_id: this.exploreContextId!,
            backward_compatibility: translationBackwardCompat,
            title: legacy.cycleDescription || legacy.cycleLabel,
            description: legacy.cycleDescription || '',
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          result.success = false;
          const errorMessage = error instanceof Error ? error.message : String(error);
          result.errors.push(`Error importing thematiccycle ${legacy.cycleId}: ${errorMessage}`);
          this.logError('ExploreThematicCycleImporter', error, { cycleId: legacy.cycleId });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in thematic cycle import: ${errorMessage}`);
      this.logError('ExploreThematicCycleImporter', error);
      this.showError();
    }

    return result;
  }
}
