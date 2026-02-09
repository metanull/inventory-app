/**
 * Travels Monument Importer
 *
 * Creates Item records for each travel monument from the Travels database.
 * Travel monuments are specific items within travel itineraries - they are separate
 * entities from the main mwnf3.monuments table and have their own content.
 *
 * Legacy schema:
 * - mwnf3.tr_monuments (project_id, country, itinerary_id, location_id, number, lang, trail_id, title)
 *   - Composite key: (project_id, country, trail_id, itinerary_id, location_id, number)
 *   - Multiple rows per monument (one per language)
 *   - These are travel-specific monuments, separate from mwnf3.monuments
 *
 * New schema:
 * - items (id, internal_name, type, collection_id, ...)
 * - collection_item (collection_id, item_id) for linking to location collection
 *
 * Mapping:
 * - (project_id, country, trail_id, itinerary_id, location_id, number) → backward_compatibility
 * - title → used to create internal_name (slugified)
 * - type = 'monument'
 * - Linked to parent location collection via collection_item pivot
 *
 * Dependencies:
 * - TravelsContextImporter
 * - TravelsLocationImporter (must run first)
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
 * Legacy travel monument structure
 */
interface LegacyTravelMonument {
  project_id: string;
  country: string;
  itinerary_id: string;
  location_id: string;
  number: string;
  lang: string;
  trail_id: number;
  title: string;
}

/**
 * Grouped monument (unique by non-lang keys)
 */
interface TravelMonumentGroup {
  project_id: string;
  country: string;
  trail_id: number;
  itinerary_id: string;
  location_id: string;
  number: string;
  translations: LegacyTravelMonument[];
}

export class TravelsMonumentImporter extends BaseImporter {
  private travelsContextId: string | null = null;

  getName(): string {
    return 'TravelsMonumentImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Looking up Travels context...');

      // Get the Travels context ID
      const travelsContextBackwardCompat = 'mwnf3_travels:context';
      this.travelsContextId = await this.getEntityUuidAsync(
        travelsContextBackwardCompat,
        'context'
      );

      if (!this.travelsContextId) {
        throw new Error(
          `Travels context not found (${travelsContextBackwardCompat}). Run TravelsContextImporter first.`
        );
      }

      this.logInfo('Importing travel monuments...');

      // Query all travel monuments from legacy database
      const monuments = await this.context.legacyDb.query<LegacyTravelMonument>(
        `SELECT project_id, country, itinerary_id, location_id, number, lang, trail_id, title
         FROM mwnf3.tr_monuments 
         ORDER BY project_id, country, trail_id, itinerary_id, location_id, number, lang`
      );

      this.logInfo(`Found ${monuments.length} travel monument rows`);

      // Group monuments by non-lang keys
      const groups = this.groupMonuments(monuments);
      this.logInfo(`Grouped into ${groups.length} unique monuments`);

      for (const group of groups) {
        try {
          const backwardCompat = `mwnf3_travels:monument:${group.project_id}:${group.country}:${group.trail_id}:${group.itinerary_id}:${group.location_id}:${group.number}`;

          // Check if already exists
          if (await this.entityExistsAsync(backwardCompat, 'item')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Find parent location collection
          const locationBackwardCompat = `mwnf3_travels:location:${group.project_id}:${group.country}:${group.trail_id}:${group.itinerary_id}:${group.location_id}`;
          const locationId = await this.getEntityUuidAsync(locationBackwardCompat, 'collection');

          if (!locationId) {
            this.logWarning(`Parent location not found for monument: ${backwardCompat}`, {
              locationBackwardCompat,
            });
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Find English title for internal name (or first available)
          const englishTranslation = group.translations.find((t) => t.lang === 'en');
          const primaryTranslation = englishTranslation || group.translations[0];

          if (!primaryTranslation) {
            this.logWarning(`No translations found for monument: ${backwardCompat}`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Create internal name
          const internalName = `tr_mon_${group.project_id}_${group.country}_${group.trail_id}_${group.itinerary_id}_${group.location_id}_${group.number}_${slugify(primaryTranslation.title || 'unnamed')}`;

          // Collect sample
          this.collectSample(
            'travel_monument',
            primaryTranslation as unknown as Record<string, unknown>,
            'success',
            `Travel monument ${group.project_id}/${group.country}/${group.trail_id}/${group.itinerary_id}/${group.location_id}/${group.number}`
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create monument: ${internalName}`
            );
            this.registerEntity('', backwardCompat, 'item');
            result.imported++;
            this.showProgress();
            continue;
          }

          // Get country ID
          const countryId = await this.getEntityUuidAsync(group.country, 'country');

          // Write Item
          const itemId = await this.context.strategy.writeItem({
            internal_name: internalName,
            backward_compatibility: backwardCompat,
            type: 'monument',
            partner_id: null, // Travel monuments don't have a specific partner
            collection_id: locationId, // Primary collection is the location
            parent_id: null,
            country_id: countryId || null,
            project_id: null, // No project association
            owner_reference: null,
            mwnf_reference: null,
            latitude: null,
            longitude: null,
            map_zoom: null,
          });

          this.registerEntity(itemId, backwardCompat, 'item');

          // Link to location collection via collection_item pivot
          await this.context.strategy.writeCollectionItem({
            collection_id: locationId,
            item_id: itemId,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          result.success = false;
          const errorMessage = error instanceof Error ? error.message : String(error);
          result.errors.push(
            `Error importing monument ${group.project_id}/${group.country}/${group.trail_id}/${group.itinerary_id}/${group.location_id}/${group.number}: ${errorMessage}`
          );
          this.logError('TravelsMonumentImporter', errorMessage, {
            project_id: group.project_id,
            country: group.country,
            trail_id: group.trail_id,
            itinerary_id: group.itinerary_id,
            location_id: group.location_id,
            number: group.number,
          });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in monument import: ${errorMessage}`);
      this.logError('TravelsMonumentImporter', errorMessage);
      this.showError();
    }

    return result;
  }

  /**
   * Group monuments by their non-language keys
   */
  private groupMonuments(monuments: LegacyTravelMonument[]): TravelMonumentGroup[] {
    const groupMap = new Map<string, TravelMonumentGroup>();

    for (const monument of monuments) {
      const key = `${monument.project_id}:${monument.country}:${monument.trail_id}:${monument.itinerary_id}:${monument.location_id}:${monument.number}`;

      if (!groupMap.has(key)) {
        groupMap.set(key, {
          project_id: monument.project_id,
          country: monument.country,
          trail_id: monument.trail_id,
          itinerary_id: monument.itinerary_id,
          location_id: monument.location_id,
          number: monument.number,
          translations: [],
        });
      }

      groupMap.get(key)!.translations.push(monument);
    }

    return Array.from(groupMap.values());
  }
}
