/**
 * SH Partner Importer
 *
 * Imports partners from mwnf3_sharing_history database.
 * Uses partner_sh_partners mapping to reuse existing mwnf3 partners when possible.
 *
 * Strategy:
 * 1. For each SH partner, lookup partner_sh_partners.all_partners_id
 * 2. If found, try to find existing mwnf3 partner and reuse its UUID
 * 3. If not found, create new Partner record
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import {
  transformShPartner,
  transformShPartnerTranslation,
  groupShPartnersByKey,
} from '../../domain/transformers/index.js';
import type {
  ShLegacyPartner,
  ShLegacyPartnerName,
  PartnerShPartnerMapping,
} from '../../domain/types/index.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';

export class ShPartnerImporter extends BaseImporter {
  private defaultContextId: string | null = null;
  private partnerMapping: Map<string, string> = new Map(); // sh_partners_id -> all_partners_id

  getName(): string {
    return 'ShPartnerImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      // Get default context ID for partner translations
      this.defaultContextId = this.getDefaultContextId();

      // Load partner_sh_partners mapping
      this.logInfo('Loading partner_sh_partners mapping...');
      const mappings = await this.context.legacyDb.query<PartnerShPartnerMapping>(
        'SELECT all_partners_id, partners_id FROM mwnf3.partner_sh_partners'
      );

      for (const mapping of mappings) {
        this.partnerMapping.set(mapping.partners_id, mapping.all_partners_id);
      }
      this.logInfo(`Loaded ${mappings.length} partner mappings`);

      // Import SH partners
      this.logInfo('Importing Sharing History partners...');
      const partnerResult = await this.importShPartners();
      result.imported += partnerResult.imported;
      result.skipped += partnerResult.skipped;
      result.errors.push(...partnerResult.errors);
      if (partnerResult.warnings) {
        result.warnings = result.warnings || [];
        result.warnings.push(...partnerResult.warnings);
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import SH partners: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importShPartners(): Promise<ImportResult> {
    const result = this.createResult();

    // Query SH partners
    const partners = await this.context.legacyDb.query<ShLegacyPartner>(
      'SELECT * FROM mwnf3_sharing_history.sh_partners ORDER BY partners_id'
    );

    const partnerNames = await this.context.legacyDb.query<ShLegacyPartnerName>(
      'SELECT * FROM mwnf3_sharing_history.sh_partner_names ORDER BY partners_id, lang'
    );

    const grouped = groupShPartnersByKey(partners, partnerNames);
    this.logInfo(`Found ${grouped.length} SH partners`);

    let reusedCount = 0;
    let createdCount = 0;

    for (const group of grouped) {
      try {
        const transformed = transformShPartner(group.partner);
        const shBackwardCompat = transformed.backwardCompatibility;

        // Check if already exists (by SH backward compatibility)
        if (this.entityExists(shBackwardCompat, 'partner')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Check if we can reuse an existing mwnf3 partner
        const allPartnersId = this.partnerMapping.get(group.partner.partners_id);
        let reusedPartnerId: string | null = null;

        if (allPartnersId) {
          // Try to find existing mwnf3 museum or institution partner
          // Format: mwnf3:museums:MUSEUM_ID:COUNTRY or mwnf3:institutions:INSTITUTION_ID:COUNTRY
          // The all_partners_id format is typically like "IT_01" which corresponds to museum/institution IDs

          // Try museum first (most common)
          const museumBackwardCompat = this.findMwnf3PartnerBackwardCompat(allPartnersId);
          if (museumBackwardCompat) {
            reusedPartnerId = this.getEntityUuid(museumBackwardCompat, 'partner');
          }
        }

        // Collect sample
        this.collectSample(
          'sh_partner',
          {
            ...group.partner,
            _all_partners_id: allPartnersId,
            _reused: !!reusedPartnerId,
          } as unknown as Record<string, unknown>,
          'success'
        );

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import SH partner: ${group.partner.partners_id}${reusedPartnerId ? ' (reusing existing)' : ''}`
          );
          this.registerEntity(
            reusedPartnerId || 'sample-partner-sh-' + group.partner.partners_id,
            shBackwardCompat,
            'partner'
          );
          result.imported++;
          if (reusedPartnerId) reusedCount++;
          else createdCount++;
          this.showProgress();
          continue;
        }

        let partnerId: string;

        if (reusedPartnerId) {
          // Reuse existing partner - just register the SH backward_compat as an alias
          partnerId = reusedPartnerId;
          this.registerEntity(partnerId, shBackwardCompat, 'partner');
          reusedCount++;
        } else {
          // Create new partner
          // Get the SH project for this partner if available (based on country pattern)
          const projectId = await this.findShProjectId(group.partner);

          const partnerData = {
            ...transformed.data,
            project_id: projectId,
          };
          partnerId = await this.context.strategy.writePartner(partnerData);
          this.registerEntity(partnerId, shBackwardCompat, 'partner');
          createdCount++;
        }

        // Create translations (always, even if partner was reused)
        for (const translation of group.translations) {
          try {
            const translationData = transformShPartnerTranslation(translation);
            await this.context.strategy.writePartnerTranslation({
              ...translationData.data,
              partner_id: partnerId,
              context_id: this.defaultContextId!,
              backward_compatibility: shBackwardCompat,
            });
          } catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            this.logWarning(
              `Failed to create translation for SH partner ${group.partner.partners_id}:${translation.lang}: ${message}`
            );
          }
        }

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`SH Partner ${group.partner.partners_id}: ${message}`);
        this.logError(`SH Partner ${group.partner.partners_id}`, message);
        this.showError();
      }
    }

    this.logInfo(`  Reused existing partners: ${reusedCount}`);
    this.logInfo(`  Created new partners: ${createdCount}`);

    return result;
  }

  /**
   * Try to find an existing mwnf3 partner by all_partners_id
   * Returns the backward_compatibility string if found, null otherwise
   */
  private findMwnf3PartnerBackwardCompat(allPartnersId: string): string | null {
    // The all_partners_id format is typically like "IT_01" or "IT_01_A"
    // This maps to museum_id or institution_id + country

    // Extract country code (first 2 characters)
    const countryCode = allPartnersId.substring(0, 2).toLowerCase();

    // Try museum format: mwnf3:museums:MUSEUM_ID:COUNTRY
    const museumBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'museums',
      pkValues: [allPartnersId, countryCode],
    });

    if (this.entityExists(museumBackwardCompat, 'partner')) {
      return museumBackwardCompat;
    }

    // Try institution format: mwnf3:institutions:INSTITUTION_ID:COUNTRY
    const institutionBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'institutions',
      pkValues: [allPartnersId, countryCode],
    });

    if (this.entityExists(institutionBackwardCompat, 'partner')) {
      return institutionBackwardCompat;
    }

    return null;
  }

  /**
   * Find the SH project ID for a partner based on available data
   * Returns null if no project can be determined
   */
  private async findShProjectId(_partner: ShLegacyPartner): Promise<string | null> {
    // SH partners don't have a direct project reference
    // We could potentially infer from country or other fields
    // For now, return null (partners are not required to have a project)
    return null;
  }
}
