/**
 * Partner Hierarchy Importer
 *
 * Imports partner hierarchy levels from legacy tables:
 * - mwnf3.partner_museums (tier 1: partner)
 * - mwnf3.associated_museums (tier 2: associated_partner)
 * - mwnf3.further_associated_museums (tier 3: minor_contributor)
 *
 * These map to collection_partner.level in inventory-app.
 *
 * Dependencies:
 * - ProjectImporter (creates collections from projects)
 * - PartnerImporter (creates partners from museums)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';

const DEFAULT_ASSOCIATED_MUSEUM_PROJECT_ID = 'ISL';

interface LegacyPartnerMuseum {
  partner_id: number;
  project_id: string;
  museum_id: string;
  country_id: string;
}

interface LegacyAssociatedMuseum {
  associated_id: number;
  partner_id: number | null;
  project_id: string | null;
  museum_id: string;
  country_id: string;
}

interface LegacyFurtherAssociatedMuseum {
  fur_associated_id: number;
  partner_id: number;
  project_id: string;
  museum_id: string;
  country_id: string;
}

export class PartnerHierarchyImporter extends BaseImporter {
  getName(): string {
    return 'PartnerHierarchyImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing partner hierarchy levels...');

      // Import tier 1: partner_museums → level = 'partner'
      await this.importTier(result, 'partner');

      // Import tier 2: associated_museums → level = 'associated_partner'
      await this.importTier(result, 'associated_partner');

      // Import tier 3: further_associated_museums → level = 'minor_contributor'
      await this.importTier(result, 'minor_contributor');

      this.showSummary(
        result.imported,
        result.skipped,
        result.errors.length,
        result.warnings?.length
      );
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import partner hierarchy: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importTier(
    result: ImportResult,
    level: 'partner' | 'associated_partner' | 'minor_contributor'
  ): Promise<void> {
    if (level === 'partner') {
      await this.importPartnerMuseums(result);
    } else if (level === 'associated_partner') {
      await this.importAssociatedMuseums(result);
    } else {
      await this.importFurtherAssociatedMuseums(result);
    }
  }

  private async importPartnerMuseums(result: ImportResult): Promise<void> {
    this.logInfo('Importing tier 1: partner_museums...');

    const rows = await this.context.legacyDb.query<LegacyPartnerMuseum>(
      'SELECT partner_id, project_id, museum_id, country_id FROM mwnf3.partner_museums ORDER BY partner_id'
    );

    this.logInfo(`Found ${rows.length} partner_museums rows`);

    for (const row of rows) {
      try {
        const imported = await this.attachPartnerToProject(
          row.project_id,
          row.museum_id,
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
        const bc = `partner_museums:${row.partner_id}`;
        result.errors.push(`${bc}: ${message}`);
        this.logError(`PartnerMuseum ${bc}`, message);
        this.showError();
      }
    }
  }

  private async importAssociatedMuseums(result: ImportResult): Promise<void> {
    this.logInfo('Importing tier 2: associated_museums...');

    const rows = await this.context.legacyDb.query<LegacyAssociatedMuseum>(
      'SELECT associated_id, partner_id, project_id, museum_id, country_id FROM mwnf3.associated_museums ORDER BY associated_id'
    );

    this.logInfo(`Found ${rows.length} associated_museums rows`);

    for (const row of rows) {
      try {
        const projectId = this.resolveAssociatedMuseumProjectId(row, result);

        const imported = await this.attachPartnerToProject(
          projectId,
          row.museum_id,
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
        const bc = `associated_museums:${row.associated_id}`;
        result.errors.push(`${bc}: ${message}`);
        this.logError(`AssociatedMuseum ${bc}`, message);
        this.showError();
      }
    }
  }

  private resolveAssociatedMuseumProjectId(
    row: LegacyAssociatedMuseum,
    result: ImportResult
  ): string {
    if (row.project_id) {
      return row.project_id;
    }

    const warning =
      `associated_museums id=${row.associated_id} has no project_id, ` +
      `assigning default legacy project ${DEFAULT_ASSOCIATED_MUSEUM_PROJECT_ID}`;
    this.logWarning(warning);
    result.warnings!.push(warning);

    return DEFAULT_ASSOCIATED_MUSEUM_PROJECT_ID;
  }

  private async importFurtherAssociatedMuseums(result: ImportResult): Promise<void> {
    this.logInfo('Importing tier 3: further_associated_museums...');

    const rows = await this.context.legacyDb.query<LegacyFurtherAssociatedMuseum>(
      'SELECT fur_associated_id, partner_id, project_id, museum_id, country_id FROM mwnf3.further_associated_museums ORDER BY fur_associated_id'
    );

    this.logInfo(`Found ${rows.length} further_associated_museums rows`);

    for (const row of rows) {
      try {
        const imported = await this.attachPartnerToProject(
          row.project_id,
          row.museum_id,
          row.country_id,
          'minor_contributor'
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
        const bc = `further_associated_museums:${row.fur_associated_id}`;
        result.errors.push(`${bc}: ${message}`);
        this.logError(`FurtherAssociatedMuseum ${bc}`, message);
        this.showError();
      }
    }
  }

  private async attachPartnerToProject(
    projectId: string,
    museumId: string,
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
        `Collection not found for project ${projectId}, skipping partner hierarchy entry`
      );
      return false;
    }

    // Resolve partner from museum
    const partnerBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'museums',
      pkValues: [museumId, countryId],
    });
    const partnerId = await this.getEntityUuidAsync(partnerBackwardCompat, 'partner');
    if (!partnerId) {
      this.logWarning(
        `Partner not found for museum ${museumId}:${countryId}, skipping partner hierarchy entry`
      );
      return false;
    }

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would attach partner ${museumId}:${countryId} to project ${projectId} with level=${level}`
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
