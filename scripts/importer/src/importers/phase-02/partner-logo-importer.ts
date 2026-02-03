/**
 * Partner Logo Importer
 *
 * Imports logos from mwnf3.museums and mwnf3.institutions.
 *
 * Legacy fields:
 * - museums: logo, logo1, logo2, logo3
 * - institutions: logo, logo1, logo2
 *
 * Strategy:
 * - Each non-null logo path becomes a PartnerLogo record
 * - logo_type is derived from the field name (logo -> primary, logo1 -> secondary, etc.)
 * - Display order is based on the logo field ordinal
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult, PartnerLogoData } from '../../core/types.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import path from 'path';

interface MuseumLogo {
  museum_id: string;
  country: string;
  name: string;
  logo: string | null;
  logo1: string | null;
  logo2: string | null;
  logo3: string | null;
}

interface InstitutionLogo {
  institution_id: string;
  country: string;
  name: string;
  logo: string | null;
  logo1: string | null;
  logo2: string | null;
}

export class PartnerLogoImporter extends BaseImporter {
  getName(): string {
    return 'PartnerLogoImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing partner logos...');

      // Import museum logos
      const museumResult = await this.importMuseumLogos();
      result.imported += museumResult.imported;
      result.skipped += museumResult.skipped;
      result.errors.push(...museumResult.errors);

      // Import institution logos
      const institutionResult = await this.importInstitutionLogos();
      result.imported += institutionResult.imported;
      result.skipped += institutionResult.skipped;
      result.errors.push(...institutionResult.errors);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import partner logos: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importMuseumLogos(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing museum logos...');

      const museums = await this.context.legacyDb.query<MuseumLogo>(`
        SELECT museum_id, country, name, logo, logo1, logo2, logo3
        FROM mwnf3.museums
        WHERE logo IS NOT NULL OR logo1 IS NOT NULL OR logo2 IS NOT NULL OR logo3 IS NOT NULL
        ORDER BY museum_id, country
      `);

      if (museums.length === 0) {
        this.logInfo('No museums with logos found');
        return result;
      }

      this.logInfo(`Found ${museums.length} museums with logos`);

      // Count total logos
      let totalLogos = 0;
      for (const museum of museums) {
        if (museum.logo) totalLogos++;
        if (museum.logo1) totalLogos++;
        if (museum.logo2) totalLogos++;
        if (museum.logo3) totalLogos++;
      }
      this.logInfo(`Total museum logos to import: ${totalLogos}`);

      // Import each museum's logos
      for (const museum of museums) {
        try {
          const partnerBackwardCompat = formatBackwardCompatibility({
            schema: 'mwnf3',
            table: 'museums',
            pkValues: [museum.museum_id, museum.country],
          });

          // Find Partner UUID
          const partnerId = this.getEntityUuid(partnerBackwardCompat, 'partner');
          if (!partnerId) {
            this.logWarning(`Partner not found for museum ${museum.museum_id}:${museum.country}`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Import each logo field
          const logoFields: Array<{ path: string | null; type: string; order: number }> = [
            { path: museum.logo, type: 'primary', order: 1 },
            { path: museum.logo1, type: 'secondary', order: 2 },
            { path: museum.logo2, type: 'tertiary', order: 3 },
            { path: museum.logo3, type: 'quaternary', order: 4 },
          ];

          for (const logoField of logoFields) {
            if (!logoField.path) continue;

            try {
              const imported = await this.importLogo(
                partnerId,
                logoField.path,
                logoField.type,
                logoField.order,
                partnerBackwardCompat,
                museum.name
              );
              if (imported) {
                result.imported++;
                this.showProgress();
              } else {
                result.skipped++;
                this.showSkipped();
              }
            } catch (error) {
              const message = error instanceof Error ? error.message : String(error);
              result.errors.push(`${partnerBackwardCompat} (${logoField.type}): ${message}`);
              this.logError(`Museum logo ${partnerBackwardCompat}`, message);
              this.showError();
            }
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
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query museum logos: ${message}`);
    }

    return result;
  }

  private async importInstitutionLogos(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing institution logos...');

      const institutions = await this.context.legacyDb.query<InstitutionLogo>(`
        SELECT institution_id, country, name, logo, logo1, logo2
        FROM mwnf3.institutions
        WHERE logo IS NOT NULL OR logo1 IS NOT NULL OR logo2 IS NOT NULL
        ORDER BY institution_id, country
      `);

      if (institutions.length === 0) {
        this.logInfo('No institutions with logos found');
        return result;
      }

      this.logInfo(`Found ${institutions.length} institutions with logos`);

      // Count total logos
      let totalLogos = 0;
      for (const institution of institutions) {
        if (institution.logo) totalLogos++;
        if (institution.logo1) totalLogos++;
        if (institution.logo2) totalLogos++;
      }
      this.logInfo(`Total institution logos to import: ${totalLogos}`);

      // Import each institution's logos
      for (const institution of institutions) {
        try {
          const partnerBackwardCompat = formatBackwardCompatibility({
            schema: 'mwnf3',
            table: 'institutions',
            pkValues: [institution.institution_id, institution.country],
          });

          // Find Partner UUID
          const partnerId = this.getEntityUuid(partnerBackwardCompat, 'partner');
          if (!partnerId) {
            this.logWarning(
              `Partner not found for institution ${institution.institution_id}:${institution.country}`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Import each logo field
          const logoFields: Array<{ path: string | null; type: string; order: number }> = [
            { path: institution.logo, type: 'primary', order: 1 },
            { path: institution.logo1, type: 'secondary', order: 2 },
            { path: institution.logo2, type: 'tertiary', order: 3 },
          ];

          for (const logoField of logoFields) {
            if (!logoField.path) continue;

            try {
              const imported = await this.importLogo(
                partnerId,
                logoField.path,
                logoField.type,
                logoField.order,
                partnerBackwardCompat,
                institution.name
              );
              if (imported) {
                result.imported++;
                this.showProgress();
              } else {
                result.skipped++;
                this.showSkipped();
              }
            } catch (error) {
              const message = error instanceof Error ? error.message : String(error);
              result.errors.push(`${partnerBackwardCompat} (${logoField.type}): ${message}`);
              this.logError(`Institution logo ${partnerBackwardCompat}`, message);
              this.showError();
            }
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          const backwardCompat = formatBackwardCompatibility({
            schema: 'mwnf3',
            table: 'institutions',
            pkValues: [institution.institution_id, institution.country],
          });
          result.errors.push(`${backwardCompat}: ${message}`);
          this.logError(
            `Institution ${institution.institution_id}:${institution.country}`,
            message
          );
          this.showError();
        }
      }
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query institution logos: ${message}`);
    }

    return result;
  }

  /**
   * Import a single logo
   */
  private async importLogo(
    partnerId: string,
    logoPath: string,
    logoType: string,
    displayOrder: number,
    partnerBackwardCompat: string,
    partnerName: string
  ): Promise<boolean> {
    // Use prefixed key to avoid collision with partner images
    const logoKey = `logo:${logoPath.toLowerCase()}`;

    // Check if already imported
    if (this.entityExists(logoKey, 'image')) {
      return false;
    }

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import logo for ${partnerBackwardCompat}: ${logoPath}`
      );
      this.registerEntity(`sample-logo-${partnerBackwardCompat}-${logoType}`, logoKey, 'image');
      return true;
    }

    // Extract metadata
    const mimeType = this.getMimeType(logoPath);
    const originalName = path.basename(logoPath);

    // Build alt text
    const altText = `${partnerName} - ${logoType} logo`;

    // Create PartnerLogo
    const logoData: PartnerLogoData = {
      id: undefined,
      partner_id: partnerId,
      path: logoPath,
      original_name: originalName,
      mime_type: mimeType,
      size: 0, // Size unknown from legacy data
      logo_type: logoType,
      alt_text: altText.length > 500 ? altText.substring(0, 497) + '...' : altText,
      display_order: displayOrder,
    };

    const logoId = await this.context.strategy.writePartnerLogo(logoData);

    // Track using prefixed key
    this.registerEntity(logoId, logoKey, 'image');

    return true;
  }

  /**
   * Get MIME type from file path
   */
  private getMimeType(filePath: string): string {
    const ext = path.extname(filePath).toLowerCase();
    const mimeTypes: Record<string, string> = {
      '.jpg': 'image/jpeg',
      '.jpeg': 'image/jpeg',
      '.png': 'image/png',
      '.gif': 'image/gif',
      '.svg': 'image/svg+xml',
      '.webp': 'image/webp',
      '.ico': 'image/x-icon',
      '.bmp': 'image/bmp',
    };
    return mimeTypes[ext] || 'application/octet-stream';
  }
}
