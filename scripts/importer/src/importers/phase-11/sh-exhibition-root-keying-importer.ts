/**
 * Sharing History Exhibition Root Keying Importer
 *
 * Keyspace sibling of ProjectExhibitionRootKeyingImporter (which is
 * deliberately mwnf3-only): creates a "Virtual Exhibitions" marker (root)
 * collection for every Sharing History project that has exhibitions, and
 * re-parents that project's existing exhibition collections under it.
 *
 * Why: SH exhibition collections (`mwnf3_sharing_history:sh_exhibitions:{id}`,
 * created by ShExhibitionImporter) parent directly to the SH project
 * collection. Per-dataset exporters/viewers locate "their" exhibitions
 * through a per-project root marker, exactly as the mwnf3 datasets do
 * through `mwnf3:exhibitions:root[:KEY]`.
 *
 * Keyspace differences vs the mwnf3 step (see #1464):
 * - schema `mwnf3_sharing_history`, table `sh_exhibitions`
 * - bc keys are LOWERCASE (formatShBackwardCompatibility convention):
 *   project `mwnf3_sharing_history:sh_projects:{id lc}`,
 *   root    `mwnf3_sharing_history:sh_exhibitions:root:{id lc}`
 * - no ISL-style exclusion: every SH project is keyed the same way
 *
 * This step runs standalone against an already-populated database
 * (`--only sh-exhibition-root-keying`) — entity lookups fall back to DB
 * queries on backward_compatibility — as well as at the end of a full fresh
 * import, so both paths converge without modifying ShExhibitionImporter.
 *
 * Mapping (per SH project with exhibitions):
 * - internal_name = 'sh_exhibitions_root_{project_id lc}'
 * - type = 'collection'
 * - purpose = 'exhibitions-root' (#1505; ensured on reruns over older DBs)
 * - parent_id = the SH project collection
 * - backward_compatibility = 'mwnf3_sharing_history:sh_exhibitions:root:{project_id lc}'
 * - each 'mwnf3_sharing_history:sh_exhibitions:{id}' collection → parent_id = root
 *
 * Dependencies:
 * - ShProjectImporter (SH project collections + contexts must exist)
 * - ShExhibitionImporter (SH exhibition collections must exist)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

const SH_SCHEMA = 'mwnf3_sharing_history';

interface ShLegacyExhibitionRow {
  exhibition_id: number;
  project_id: string;
}

export class ShExhibitionRootKeyingImporter extends BaseImporter {
  getName(): string {
    return 'ShExhibitionRootKeyingImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Keying per-project Sharing History exhibition root collections...');

      const exhibitions = await this.context.legacyDb.query<ShLegacyExhibitionRow>(
        `SELECT exhibition_id, project_id
         FROM ${SH_SCHEMA}.sh_exhibitions
         ORDER BY project_id, exhibition_id`
      );

      // Group exhibition ids per project key. SH keys are lowercase.
      const byProject = new Map<string, number[]>();
      for (const row of exhibitions) {
        const key = row.project_id.toLowerCase();
        const list = byProject.get(key) ?? [];
        list.push(row.exhibition_id);
        byProject.set(key, list);
      }

      this.logInfo(
        `Found ${byProject.size} SH project(s) with exhibitions: ${[...byProject.keys()].join(', ')}`
      );

      const defaultLanguageId = await this.getDefaultLanguageIdAsync();

      for (const [projectKey, exhibitionIds] of byProject) {
        try {
          await this.keyProject(projectKey, exhibitionIds, defaultLanguageId, result);
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
      result.errors.push(`Failed to key SH exhibition roots: ${message}`);
      this.logError('ShExhibitionRootKeyingImporter', message);
      this.showError();
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async keyProject(
    projectKey: string,
    exhibitionIds: number[],
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

    // 1. Ensure the root marker collection exists.
    const rootBackwardCompat = `${SH_SCHEMA}:sh_exhibitions:root:${projectKey}`;
    let rootCollectionId = await this.getEntityUuidAsync(rootBackwardCompat, 'collection');

    if (rootCollectionId) {
      this.logInfo(`Root collection already exists for ${projectKey} (${rootBackwardCompat})`);
      // Ensure-semantics (#1505): a marker created before the purpose column
      // existed must still end up purposed, without a full re-import.
      const currentPurpose = await this.context.strategy.getCollectionPurpose(rootCollectionId);
      if (currentPurpose === null) {
        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would set purpose 'exhibitions-root' on ${rootBackwardCompat}`
          );
        } else {
          await this.context.strategy.updateCollectionPurpose(rootCollectionId, 'exhibitions-root');
          this.logInfo(`Set purpose 'exhibitions-root' on ${rootBackwardCompat}`);
        }
        result.imported++;
        this.showProgress();
      } else {
        if (currentPurpose !== 'exhibitions-root') {
          this.logWarning(
            `Root collection ${rootBackwardCompat} already has purpose '${currentPurpose}', leaving it untouched`
          );
        }
        result.skipped++;
        this.showSkipped();
      }
    } else {
      const internalName = `sh_exhibitions_root_${projectKey}`;

      if (this.isDryRun || this.isSampleOnlyMode) {
        this.logInfo(
          `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create root collection: ${internalName} (${rootBackwardCompat})`
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
          purpose: 'exhibitions-root',
          latitude: null,
          longitude: null,
          map_zoom: null,
          country_id: null,
        });

        this.registerEntity(rootCollectionId, rootBackwardCompat, 'collection');

        await this.context.strategy.writeCollectionTranslation({
          collection_id: rootCollectionId,
          language_id: defaultLanguageId,
          context_id: contextId,
          backward_compatibility: `${rootBackwardCompat}:translation:${defaultLanguageId}`,
          title: 'Virtual Exhibitions',
          description: `Curated virtual exhibitions of the Sharing History ${projectKey.toUpperCase()} project.`,
        });

        this.logInfo(`Created root collection for ${projectKey}: ${rootCollectionId}`);
        result.imported++;
        this.showProgress();
      }
    }

    // 2. Re-parent the project's exhibition collections under the root.
    for (const exhibitionId of exhibitionIds) {
      const exhibitionBackwardCompat = `${SH_SCHEMA}:sh_exhibitions:${exhibitionId}`;
      const exhibitionCollectionId = await this.getEntityUuidAsync(
        exhibitionBackwardCompat,
        'collection'
      );

      if (!exhibitionCollectionId) {
        this.logWarning(
          `SH exhibition collection not found (${exhibitionBackwardCompat}), skipping — run ShExhibitionImporter first`
        );
        result.skipped++;
        this.showSkipped();
        continue;
      }

      if (this.isDryRun || this.isSampleOnlyMode) {
        this.logInfo(
          `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would re-parent ${exhibitionBackwardCompat} under ${rootBackwardCompat}`
        );
        result.imported++;
        this.showProgress();
        continue;
      }

      // rootCollectionId is always set here outside dry-run (created above if missing).
      if (!rootCollectionId) {
        throw new Error(`Root collection id unresolved for ${projectKey}`);
      }

      const currentParentId =
        await this.context.strategy.getCollectionParentId(exhibitionCollectionId);

      if (currentParentId === rootCollectionId) {
        result.skipped++;
        this.showSkipped();
        continue;
      }

      await this.context.strategy.updateCollectionParentId(
        exhibitionCollectionId,
        rootCollectionId
      );
      this.logInfo(`Re-parented ${exhibitionBackwardCompat} under ${rootBackwardCompat}`);
      result.imported++;
      this.showProgress();
    }
  }
}
