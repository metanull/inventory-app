/**
 * SH Partner Logo Importer
 *
 * Imports logos from mwnf3_sharing_history.sh_partners.
 *
 * Legacy fields:
 * - sh_partners: logo, logo1, logo2, logo3
 *
 * Strategy:
 * - Each non-null logo path becomes a PartnerLogo record
 * - logo_type is derived from the field name (logo -> primary, logo1 -> secondary, etc.)
 * - Display order is based on the logo field ordinal
 * - Partners are found by SH backward_compatibility key (already imported by ShPartnerImporter)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult, PartnerLogoData } from '../../core/types.js';
import { formatShBackwardCompatibility } from '../../domain/transformers/index.js';
import path from 'path';

const SH_PARTNERS_TABLE = 'sh_partners';

interface ShPartnerLogo {
  partners_id: string;
  name: string;
  logo: string | null;
  logo1: string | null;
  logo2: string | null;
  logo3: string | null;
}

export class ShPartnerLogoImporter extends BaseImporter {
  getName(): string {
    return 'ShPartnerLogoImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing Sharing History partner logos...');

      const partners = await this.context.legacyDb.query<ShPartnerLogo>(`
        SELECT partners_id, name, logo, logo1, logo2, logo3
        FROM mwnf3_sharing_history.sh_partners
        WHERE logo IS NOT NULL OR logo1 IS NOT NULL OR logo2 IS NOT NULL OR logo3 IS NOT NULL
        ORDER BY partners_id
      `);

      if (partners.length === 0) {
        this.logInfo('No SH partners with logos found');
        return result;
      }

      this.logInfo(`Found ${partners.length} SH partners with logos`);

      // Count total logos
      let totalLogos = 0;
      for (const partner of partners) {
        if (partner.logo) totalLogos++;
        if (partner.logo1) totalLogos++;
        if (partner.logo2) totalLogos++;
        if (partner.logo3) totalLogos++;
      }
      this.logInfo(`Total SH partner logos to import: ${totalLogos}`);

      // Import each partner's logos
      for (const partner of partners) {
        try {
          const shBackwardCompat = formatShBackwardCompatibility(
            SH_PARTNERS_TABLE,
            partner.partners_id
          );

          // Find Partner UUID
          const partnerId = this.getEntityUuid(shBackwardCompat, 'partner');
          if (!partnerId) {
            this.logWarning(`Partner not found for SH partner ${partner.partners_id}`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Import each logo field
          const logoFields: Array<{ path: string | null; type: string; order: number }> = [
            { path: partner.logo, type: 'primary', order: 1 },
            { path: partner.logo1, type: 'secondary', order: 2 },
            { path: partner.logo2, type: 'tertiary', order: 3 },
            { path: partner.logo3, type: 'quaternary', order: 4 },
          ];

          for (const logoField of logoFields) {
            if (!logoField.path) continue;

            try {
              const imported = await this.importLogo(
                partnerId,
                logoField.path,
                logoField.type,
                logoField.order,
                shBackwardCompat,
                partner.name
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
              result.errors.push(`${shBackwardCompat} (${logoField.type}): ${message}`);
              this.logError(`SH Partner logo ${shBackwardCompat}`, message);
              this.showError();
            }
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          const shBackwardCompat = formatShBackwardCompatibility(
            SH_PARTNERS_TABLE,
            partner.partners_id
          );
          result.errors.push(`${shBackwardCompat}: ${message}`);
          this.logError(`SH Partner ${partner.partners_id}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import SH partner logos: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
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
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import SH logo for ${partnerBackwardCompat}: ${logoPath}`
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
      size: 1, // Fake size as required (legacy data doesn't have file size)
      logo_type: logoType,
      alt_text: altText.length > 500 ? altText.substring(0, 497) + '...' : altText,
      display_order: displayOrder,
    };

    await this.context.strategy.writePartnerLogo(logoData);
    // Logo is tracked by path in writePartnerLogo, no need to register here

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
