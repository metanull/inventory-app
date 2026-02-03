/**
 * Partner Monument Linker
 *
 * Links partners (museums) to their monument locations.
 * This must run after all monuments from all legacy sources have been imported.
 *
 * The link is established via the mon_* fields in legacy museums:
 * - mon_project_id, mon_country_id, mon_institution_id, mon_monument_id, mon_lang_id
 *
 * These map to a monument in the new system, and the partner's monument_item_id
 * is updated to reference that Item (monument).
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';

/**
 * Represents a museum with monument location reference
 */
interface MuseumWithMonumentRef {
  museum_id: string;
  country: string;
  name: string;
  mon_project_id: string;
  mon_country_id: string;
  mon_institution_id: string;
  mon_monument_id: number;
  mon_lang_id: string;
}

export class PartnerMonumentLinker extends BaseImporter {
  getName(): string {
    return 'PartnerMonumentLinker';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Linking partners to monument locations...');

      // Query museums that have monument references
      const museumsWithMonuments = await this.context.legacyDb.query<MuseumWithMonumentRef>(`
        SELECT 
          museum_id, country, name,
          mon_project_id, mon_country_id, mon_institution_id, mon_monument_id, mon_lang_id
        FROM mwnf3.museums 
        WHERE mon_project_id IS NOT NULL 
          AND mon_country_id IS NOT NULL 
          AND mon_institution_id IS NOT NULL 
          AND mon_monument_id IS NOT NULL 
          AND mon_lang_id IS NOT NULL
        ORDER BY museum_id, country
      `);

      if (museumsWithMonuments.length === 0) {
        this.logInfo('No museums with monument references found');
        return result;
      }

      this.logInfo(`Found ${museumsWithMonuments.length} museums with monument location references`);

      let linkedCount = 0;
      let notFoundPartnerCount = 0;
      let notFoundMonumentCount = 0;

      for (const museum of museumsWithMonuments) {
        try {
          const linked = await this.linkMuseumToMonument(museum);
          if (linked === 'linked') {
            linkedCount++;
            result.imported++;
            this.showProgress();
          } else if (linked === 'partner-not-found') {
            notFoundPartnerCount++;
            result.skipped++;
            this.showSkipped();
          } else if (linked === 'monument-not-found') {
            notFoundMonumentCount++;
            result.skipped++;
            this.showSkipped();
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          const backwardCompat = formatBackwardCompatibility({
            schema: 'mwnf3',
            table: 'museums',
            pkValues: [museum.museum_id, museum.country],
          });
          result.errors.push(`${backwardCompat}: ${message}`);
          this.logError(`Museum ${museum.museum_id}:${museum.country}`, message);
          this.showError();
        }
      }

      this.logInfo(`  Successfully linked: ${linkedCount}`);
      this.logInfo(`  Partner not found: ${notFoundPartnerCount}`);
      this.logInfo(`  Monument not found: ${notFoundMonumentCount}`);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to link partners to monuments: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  /**
   * Link a single museum to its monument location
   * @returns 'linked' | 'partner-not-found' | 'monument-not-found'
   */
  private async linkMuseumToMonument(
    museum: MuseumWithMonumentRef
  ): Promise<'linked' | 'partner-not-found' | 'monument-not-found'> {
    // Build backward compatibility key for the partner
    const partnerBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'museums',
      pkValues: [museum.museum_id, museum.country],
    });

    // Find the partner UUID
    const partnerId = this.getEntityUuid(partnerBackwardCompat, 'partner');
    if (!partnerId) {
      this.logWarning(
        `Partner not found for museum ${museum.museum_id}:${museum.country} (${partnerBackwardCompat})`
      );
      return 'partner-not-found';
    }

    // Build backward compatibility key for the monument
    // Monument PK: (project_id, country, institution_id, number, lang)
    const monumentBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'monuments',
      pkValues: [
        museum.mon_project_id,
        museum.mon_country_id,
        museum.mon_institution_id,
        String(museum.mon_monument_id),
        museum.mon_lang_id,
      ],
    });

    // Find the monument Item UUID
    const monumentItemId = this.getEntityUuid(monumentBackwardCompat, 'item');
    if (!monumentItemId) {
      this.logWarning(
        `Monument not found for museum ${museum.museum_id}:${museum.country}: ${monumentBackwardCompat}`
      );
      return 'monument-not-found';
    }

    // Log the link (dry-run or sample mode)
    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would link partner ${partnerBackwardCompat} -> monument ${monumentBackwardCompat}`
      );
      return 'linked';
    }

    // Perform the actual update
    await this.context.strategy.updatePartnerMonumentItemId(partnerId, monumentItemId);

    return 'linked';
  }
}
