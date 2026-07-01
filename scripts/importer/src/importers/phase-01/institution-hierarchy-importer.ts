/**
 * Institution Hierarchy Importer
 *
 * Imports institution hierarchy levels from legacy tables:
 * - mwnf3.partner_institutions (tier 1: partner)
 * - mwnf3.associated_institutions (tier 2: associated_partner)
 *
 * Unlike museums, legacy has no `further_associated_institutions` table, so
 * there is no `minor_contributor` tier for institutions.
 *
 * These map to collection_partner.level in inventory-app.
 *
 * This is the curated "Partner Institutions" list shown on the legacy
 * Partners page — a much narrower set than "every institution that owns an
 * item in this project" (which is how institutions were included in the
 * exporter before this importer existed: most institutions own monuments as
 * the country's generic administrative authority, e.g. a Ministry of
 * Culture, without being a listed project partner).
 *
 * Dependencies:
 * - ProjectImporter (creates collections from projects)
 * - PartnerImporter (creates partners from institutions)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';

const DEFAULT_ASSOCIATED_INSTITUTION_PROJECT_ID = 'ISL';

interface LegacyPartnerInstitution {
  partner_id: number;
  project_id: string;
  institution_id: string;
  country_id: string;
}

interface LegacyAssociatedInstitution {
  associated_id: number;
  partner_id: number | null;
  project_id: string | null;
  institution_id: string;
  country_id: string;
}

export class InstitutionHierarchyImporter extends BaseImporter {
  getName(): string {
    return 'InstitutionHierarchyImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing institution hierarchy levels...');

      // Import tier 1: partner_institutions → level = 'partner'
      await this.importPartnerInstitutions(result);

      // Import tier 2: associated_institutions → level = 'associated_partner'
      await this.importAssociatedInstitutions(result);

      this.showSummary(
        result.imported,
        result.skipped,
        result.errors.length,
        result.warnings?.length
      );
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import institution hierarchy: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importPartnerInstitutions(result: ImportResult): Promise<void> {
    this.logInfo('Importing tier 1: partner_institutions...');

    const rows = await this.context.legacyDb.query<LegacyPartnerInstitution>(
      'SELECT partner_id, project_id, institution_id, country_id FROM mwnf3.partner_institutions ORDER BY partner_id'
    );

    this.logInfo(`Found ${rows.length} partner_institutions rows`);

    for (const row of rows) {
      try {
        const imported = await this.attachInstitutionToProject(
          row.project_id,
          row.institution_id,
          row.country_id,
          'partner'
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
        const bc = `partner_institutions:${row.partner_id}`;
        result.errors.push(`${bc}: ${message}`);
        this.logError(`PartnerInstitution ${bc}`, message);
        this.showError();
      }
    }
  }

  private async importAssociatedInstitutions(result: ImportResult): Promise<void> {
    this.logInfo('Importing tier 2: associated_institutions...');

    const rows = await this.context.legacyDb.query<LegacyAssociatedInstitution>(
      'SELECT associated_id, partner_id, project_id, institution_id, country_id FROM mwnf3.associated_institutions ORDER BY associated_id'
    );

    this.logInfo(`Found ${rows.length} associated_institutions rows`);

    for (const row of rows) {
      try {
        const projectId = this.resolveAssociatedInstitutionProjectId(row, result);

        const imported = await this.attachInstitutionToProject(
          projectId,
          row.institution_id,
          row.country_id,
          'associated_partner'
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
        const bc = `associated_institutions:${row.associated_id}`;
        result.errors.push(`${bc}: ${message}`);
        this.logError(`AssociatedInstitution ${bc}`, message);
        this.showError();
      }
    }
  }

  private resolveAssociatedInstitutionProjectId(
    row: LegacyAssociatedInstitution,
    result: ImportResult
  ): string {
    if (row.project_id) {
      return row.project_id;
    }

    const warning =
      `associated_institutions id=${row.associated_id} has no project_id, ` +
      `assigning default legacy project ${DEFAULT_ASSOCIATED_INSTITUTION_PROJECT_ID}`;
    this.logWarning(warning);
    result.warnings.push(warning);

    return DEFAULT_ASSOCIATED_INSTITUTION_PROJECT_ID;
  }

  private async attachInstitutionToProject(
    projectId: string,
    institutionId: string,
    countryId: string,
    level: string
  ): Promise<boolean> {
    // Resolve collection from project
    const collectionBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'projects',
      pkValues: [projectId],
    });
    const collectionId = await this.getEntityUuidAsync(collectionBackwardCompat, 'collection');
    if (!collectionId) {
      this.logWarning(
        `Collection not found for project ${projectId}, skipping institution hierarchy entry`
      );
      return false;
    }

    // Resolve partner from institution
    const partnerBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'institutions',
      pkValues: [institutionId, countryId],
    });
    const partnerId = await this.getEntityUuidAsync(partnerBackwardCompat, 'partner');
    if (!partnerId) {
      this.logWarning(
        `Partner not found for institution ${institutionId}:${countryId}, skipping institution hierarchy entry`
      );
      return false;
    }

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would attach institution ${institutionId}:${countryId} to project ${projectId} with level=${level}`
      );
      return true;
    }

    await this.context.strategy.attachPartnerToCollectionWithLevel(
      collectionId,
      partnerId,
      'project',
      level
    );

    return true;
  }
}
