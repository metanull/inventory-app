/**
 * Sharing History Historical Profiles Root Importer
 *
 * #1505: creates a per-project "Historical Profiles" marker (root) collection
 * for every Sharing History project that has Historical Background records
 * (`sh_countries_historicalbackground`), and re-parents that project's HB
 * record collections under it. Until now the HB records sat directly under
 * the project root collection, distinguishable only by their
 * backward_compatibility prefix — which forced viewers to parse legacy keys.
 *
 * Mapping (per SH project with HB records, lowercase key `k`):
 * - internal_name = 'sh_historical_profiles_root_{k}'
 * - type = 'collection'
 * - purpose = 'historical-profiles-root' (ensured on reruns over older DBs)
 * - parent_id = the SH project root collection ({SH_SCHEMA}:sh_projects:{k})
 * - backward_compatibility = '{SH_SCHEMA}:sh_countries_historicalbackground:root:{k}'
 * - each '{SH_SCHEMA}:sh_countries_historicalbackground:{hb_id}' collection
 *   → parent_id = marker
 *
 * The general HB record (legacy gn='yes', country_id NULL — in practice AWE
 * hb_id=1) is re-parented like the country profiles; viewers keep telling it
 * apart by `country_id === null`.
 *
 * Runs standalone against an already-populated database
 * (`--only sh-historical-profiles-root`) as well as at the end of a full
 * fresh import. ShHbRecontextImporter is marker-aware (it targets this
 * marker as the desired parent when it exists), so the two steps converge
 * whichever order they run in; in the registry this step runs after it.
 *
 * Dependencies:
 * - ShProjectImporter (SH project collections + contexts must exist)
 * - ShBibliographyHbImporter (HB record collections must exist)
 * - ShHbRecontextImporter (records already sit in their own project context)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

const SH_SCHEMA = 'mwnf3_sharing_history';

interface ShLegacyHbRow {
  hb_id: number;
  project_id: string;
}

export class ShHistoricalProfilesRootImporter extends BaseImporter {
  getName(): string {
    return 'ShHistoricalProfilesRootImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Keying per-project Sharing History Historical Profiles root collections...');

      const hbs = await this.context.legacyDb.query<ShLegacyHbRow>(
        `SELECT hb_id, project_id
         FROM ${SH_SCHEMA}.sh_countries_historicalbackground
         ORDER BY project_id, hb_id`
      );

      // Group HB record ids per project key. SH keys are lowercase.
      const byProject = new Map<string, number[]>();
      for (const row of hbs) {
        const key = row.project_id.toLowerCase();
        const list = byProject.get(key) ?? [];
        list.push(row.hb_id);
        byProject.set(key, list);
      }

      this.logInfo(
        `Found ${byProject.size} SH project(s) with HB records: ${[...byProject.keys()].join(', ')}`
      );

      const defaultLanguageId = await this.getDefaultLanguageIdAsync();

      for (const [projectKey, hbIds] of byProject) {
        try {
          await this.keyProject(projectKey, hbIds, defaultLanguageId, result);
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`SH project ${projectKey}: ${message}`);
          this.logError(`SH project ${projectKey}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to key SH Historical Profiles roots: ${message}`);
      this.logError('ShHistoricalProfilesRootImporter', message);
      this.showError();
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async keyProject(
    projectKey: string,
    hbIds: number[],
    defaultLanguageId: string,
    result: ImportResult
  ): Promise<void> {
    const projectBackwardCompat = `${SH_SCHEMA}:sh_projects:${projectKey}`;

    const projectCollectionId = await this.getEntityUuidAsync(projectBackwardCompat, 'collection');
    if (!projectCollectionId) {
      this.logWarning(
        `SH project collection not found (${projectBackwardCompat}), skipping ${projectKey} — run ShProjectImporter first`
      );
      result.skipped++;
      this.showSkipped();
      return;
    }

    const contextId = await this.getEntityUuidAsync(projectBackwardCompat, 'context');
    if (!contextId) {
      this.logWarning(
        `SH project context not found (${projectBackwardCompat}), skipping ${projectKey}`
      );
      result.skipped++;
      this.showSkipped();
      return;
    }

    // 1. Ensure the marker collection exists.
    const rootBackwardCompat = `${SH_SCHEMA}:sh_countries_historicalbackground:root:${projectKey}`;
    let rootCollectionId = await this.getEntityUuidAsync(rootBackwardCompat, 'collection');

    if (rootCollectionId) {
      this.logInfo(`Profiles root already exists for ${projectKey} (${rootBackwardCompat})`);
      // Ensure-semantics (#1505): a marker created before the purpose column
      // existed must still end up purposed, without a full re-import.
      const currentPurpose = await this.context.strategy.getCollectionPurpose(rootCollectionId);
      if (currentPurpose === null) {
        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would set purpose 'historical-profiles-root' on ${rootBackwardCompat}`
          );
        } else {
          await this.context.strategy.updateCollectionPurpose(
            rootCollectionId,
            'historical-profiles-root'
          );
          this.logInfo(`Set purpose 'historical-profiles-root' on ${rootBackwardCompat}`);
        }
        result.imported++;
        this.showProgress();
      } else {
        result.skipped++;
        this.showSkipped();
      }
    } else {
      const internalName = `sh_historical_profiles_root_${projectKey}`;

      if (this.isDryRun || this.isSampleOnlyMode) {
        this.logInfo(
          `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create profiles root collection: ${internalName} (${rootBackwardCompat})`
        );
        this.registerEntity('', rootBackwardCompat, 'collection');
        result.imported++;
        this.showProgress();
      } else {
        rootCollectionId = await this.context.strategy.writeCollection({
          internal_name: internalName,
          backward_compatibility: rootBackwardCompat,
          context_id: contextId,
          language_id: defaultLanguageId,
          parent_id: projectCollectionId,
          type: 'collection',
          purpose: 'historical-profiles-root',
          display_order: 2,
        });

        this.registerEntity(rootCollectionId, rootBackwardCompat, 'collection');

        await this.context.strategy.writeCollectionTranslation({
          collection_id: rootCollectionId,
          language_id: defaultLanguageId,
          context_id: contextId,
          backward_compatibility: `${rootBackwardCompat}:translation:${defaultLanguageId}`,
          title: 'Historical Profiles',
          description: `Historical Background country profiles of the Sharing History ${projectKey.toUpperCase()} project.`,
        });

        this.logInfo(`Created profiles root collection for ${projectKey}: ${rootCollectionId}`);
        result.imported++;
        this.showProgress();
      }
    }

    // 2. Re-parent the project's HB record collections under the marker.
    for (const hbId of hbIds) {
      const hbBackwardCompat = `${SH_SCHEMA}:sh_countries_historicalbackground:${hbId}`;
      const hbCollectionId = await this.getEntityUuidAsync(hbBackwardCompat, 'collection');

      if (!hbCollectionId) {
        this.logWarning(
          `SH HB record collection not found (${hbBackwardCompat}), skipping — run ShBibliographyHbImporter first`
        );
        result.skipped++;
        this.showSkipped();
        continue;
      }

      if (this.isDryRun || this.isSampleOnlyMode) {
        this.logInfo(
          `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would re-parent ${hbBackwardCompat} under ${rootBackwardCompat}`
        );
        result.imported++;
        this.showProgress();
        continue;
      }

      // rootCollectionId is always set here outside dry-run (created above if missing).
      if (!rootCollectionId) {
        throw new Error(`Profiles root collection id unresolved for ${projectKey}`);
      }

      const currentParentId = await this.context.strategy.getCollectionParentId(hbCollectionId);

      if (currentParentId === rootCollectionId) {
        result.skipped++;
        this.showSkipped();
        continue;
      }

      await this.context.strategy.updateCollectionParentId(hbCollectionId, rootCollectionId);
      this.logInfo(`Re-parented ${hbBackwardCompat} under ${rootBackwardCompat}`);
      result.imported++;
      this.showProgress();
    }
  }
}
