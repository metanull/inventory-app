/**
 * Museum Project Link Backfill Importer
 *
 * Standalone, idempotent backfill of `partners.project_id` for museums, on a
 * database that was imported before PartnerImporter carried the link.
 *
 * Why the link matters: legacy's partner list for a DXA gallery
 * (`app/MWNF/SQL/mwnf3/Partners.blade.php`) is a three-branch UNION, and its
 * third branch — the one legacy's own comment labels MWNF-384 — is
 *
 *     SELECT m.country, m.museum_id, m.project_id, 0 withObject
 *     FROM mwnf3.museums m WHERE m.project_id = <the gallery's project>
 *
 * i.e. a museum created under the gallery's own project is listed even when it
 * holds nothing. `museums.project_id` was the only input to that branch and the
 * importer discarded it, so no exporter could reproduce it (carpets shipped 70
 * partners against legacy's 72; the two missing rows are `jo/Mus31` and
 * `pt/Mus31`, both created under DCA, both `hasObjects: 0`).
 *
 * Why a single FK is enough: `mwnf3.museums` is `PRIMARY KEY (museum_id,
 * country)`, which is exactly the key the importer groups on, so one inventory
 * partner corresponds to exactly one legacy row and therefore exactly one
 * `project_id`. There is no many-to-many to model.
 *
 * Scope and safety:
 * - Museums only. `mwnf3.institutions` has no project column, and schools are
 *   already linked by SchoolImporter — the update is keyed by the museum's own
 *   backward_compatibility, so it cannot reach either.
 * - Writes only where `partners.project_id` IS NULL, so the ten ISL schools (and
 *   anything a rerun already set) are never overwritten.
 * - `museums.project_id` is `NOT NULL DEFAULT ''` in legacy; the empty string is
 *   the unset value and is skipped rather than looked up.
 * - A project code with no imported project is warned about and skipped, not
 *   fatal: ProjectCleanupImporter removes projects that ended up with no items.
 *
 * Run standalone with `--only museum-project-link-backfill`. It is a near-no-op
 * after a fresh full import, because PartnerImporter now sets the column itself.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';

interface LegacyMuseumProjectRow {
  museum_id: string;
  country: string;
  project_id: string;
}

export class MuseumProjectLinkBackfillImporter extends BaseImporter {
  /** legacy `mwnf3.projects.project_id` → inventory project UUID (or null when absent). */
  private projectUuidCache = new Map<string, string | null>();

  getName(): string {
    return 'MuseumProjectLinkBackfillImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Backfilling partners.project_id from mwnf3.museums.project_id...');

      const museums = await this.context.legacyDb.query<LegacyMuseumProjectRow>(
        'SELECT museum_id, country, project_id FROM mwnf3.museums ORDER BY museum_id, country'
      );
      this.logInfo(`Found ${museums.length} legacy museum(s)`);

      for (const museum of museums) {
        const key = `${museum.museum_id}:${museum.country}`;
        try {
          await this.backfillOne(key, museum, result);
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Museum ${key}: ${message}`);
          this.logError(`Museum ${key}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length, result.warnings.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to backfill museum project links: ${message}`);
      this.logError('MuseumProjectLinkBackfillImporter', message);
      this.showError();
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async backfillOne(
    key: string,
    museum: LegacyMuseumProjectRow,
    result: ImportResult
  ): Promise<void> {
    const legacyProjectId = museum.project_id?.trim();
    if (!legacyProjectId) {
      result.skipped++;
      this.showSkipped();
      return;
    }

    const partnerBC = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'museums',
      pkValues: [museum.museum_id, museum.country],
    });
    const partnerId = await this.getEntityUuidAsync(partnerBC, 'partner');
    if (!partnerId) {
      const warning = `Museum ${key}: partner ${partnerBC} not found, skipping — run PartnerImporter first`;
      this.logWarning(warning);
      result.warnings.push(warning);
      result.skipped++;
      this.showSkipped();
      return;
    }

    const projectId = await this.lookupProjectUuid(legacyProjectId);
    if (!projectId) {
      const warning = `Museum ${key}: project ${legacyProjectId} not found, skipping project assignment`;
      this.logWarning(warning);
      result.warnings.push(warning);
      result.skipped++;
      this.showSkipped();
      return;
    }

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would set project ${legacyProjectId} on ${partnerBC}`
      );
      result.imported++;
      this.showProgress();
      return;
    }

    const updated = await this.context.strategy.setPartnerProjectIdIfUnset(partnerId, projectId);
    if (updated === 0) {
      // Already linked — a rerun, or PartnerImporter set it on the way in.
      result.skipped++;
      this.showSkipped();
      return;
    }

    result.imported++;
    this.showProgress();
  }

  /**
   * Legacy project code → inventory project UUID, memoized. Hundreds of museums
   * share a handful of project codes.
   */
  private async lookupProjectUuid(legacyProjectId: string): Promise<string | null> {
    const cached = this.projectUuidCache.get(legacyProjectId);
    if (cached !== undefined) {
      return cached;
    }

    const projectBC = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'projects',
      pkValues: [legacyProjectId],
    });
    const projectId = await this.getEntityUuidAsync(projectBC, 'project');
    this.projectUuidCache.set(legacyProjectId, projectId);
    return projectId;
  }
}
