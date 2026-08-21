/**
 * Project Exhibition Root Keying Importer
 *
 * Creates a "Virtual Exhibitions" marker (root) collection for every mwnf3
 * project that has exhibitions — except ISL, whose root
 * (`mwnf3:exhibitions:root`) is created by ExhibitionsRootCollectionImporter
 * and must remain untouched — and re-parents that project's existing
 * exhibition collections under its root.
 *
 * Why: neither `type='exhibition'` nor the `mwnf3:exhibitions:{id}`
 * backward_compatibility key is project-scoped (the legacy exhibitions table
 * is shared across ISL, BAR, SH, ...). Per-dataset exporters/viewers locate
 * "their" exhibitions through the per-project root marker, exactly as the
 * Discover Islamic Art viewer does through `mwnf3:exhibitions:root`.
 *
 * This step is designed to run standalone against an already-populated
 * database (`--only project-exhibition-root-keying`) — entity lookups fall
 * back to DB queries on backward_compatibility, and re-parenting uses
 * updateCollectionParentId — as well as at the end of a full fresh import,
 * so both paths converge on the same end state without modifying
 * ExhibitionsRootCollectionImporter or Mwnf3ExhibitionImporter.
 *
 * Scope: mwnf3 keyspace only. Sharing History
 * (`mwnf3_sharing_history:sh_projects:{id}`, lowercase keyspace) and
 * Explore/THG keying are future extensions — add a keyspace-specific sibling
 * rather than widening this one.
 *
 * Mapping (per non-ISL project with exhibitions):
 * - internal_name = 'exhibitions_root_{project_id lowercase}'
 * - type = 'collection'
 * - parent_id = the project's collection (`mwnf3:projects:{PROJECT_ID}`)
 * - backward_compatibility = 'mwnf3:exhibitions:root:{PROJECT_ID}'
 * - each `mwnf3:exhibitions:{id}` collection of the project → parent_id = root
 *
 * Dependencies:
 * - ProjectImporter (project collections + contexts must exist)
 * - Mwnf3ExhibitionImporter (exhibition collections must exist)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

const MWNF3_SCHEMA = 'mwnf3';
const ISL_PROJECT_KEY = 'ISL';

interface LegacyExhibitionRow {
  exhibition_id: number;
  project_id: string;
}

export class ProjectExhibitionRootKeyingImporter extends BaseImporter {
  getName(): string {
    return 'ProjectExhibitionRootKeyingImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Keying per-project exhibition root collections (non-ISL)...');

      const exhibitions = await this.context.legacyDb.query<LegacyExhibitionRow>(
        `SELECT exhibition_id, project_id
         FROM ${MWNF3_SCHEMA}.exhibitions
         ORDER BY project_id, exhibition_id`
      );

      // Group exhibition ids per project key, excluding ISL entirely.
      const byProject = new Map<string, number[]>();
      for (const row of exhibitions) {
        const key = row.project_id.toUpperCase();
        if (key === ISL_PROJECT_KEY) {
          continue;
        }
        const list = byProject.get(key) ?? [];
        list.push(row.exhibition_id);
        byProject.set(key, list);
      }

      this.logInfo(
        `Found ${byProject.size} non-ISL project(s) with exhibitions: ${[...byProject.keys()].join(', ')}`
      );

      const defaultLanguageId = await this.getDefaultLanguageIdAsync();

      for (const [projectKey, exhibitionIds] of byProject) {
        try {
          await this.keyProject(projectKey, exhibitionIds, defaultLanguageId, result);
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Project ${projectKey}: ${message}`);
          this.logError(`Project ${projectKey}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to key project exhibition roots: ${message}`);
      this.logError('ProjectExhibitionRootKeyingImporter', message);
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
    const projectBackwardCompat = `${MWNF3_SCHEMA}:projects:${projectKey}`;

    const projectCollectionId = await this.getEntityUuidAsync(projectBackwardCompat, 'collection');
    if (!projectCollectionId) {
      this.logWarning(
        `Project collection not found (${projectBackwardCompat}), skipping ${projectKey} — run ProjectImporter first`
      );
      result.skipped++;
      this.showSkipped();
      return;
    }

    const contextId = await this.getEntityUuidAsync(projectBackwardCompat, 'context');
    if (!contextId) {
      this.logWarning(
        `Project context not found (${projectBackwardCompat}), skipping ${projectKey}`
      );
      result.skipped++;
      this.showSkipped();
      return;
    }

    // 1. Ensure the root marker collection exists.
    const rootBackwardCompat = `${MWNF3_SCHEMA}:exhibitions:root:${projectKey}`;
    let rootCollectionId = await this.getEntityUuidAsync(rootBackwardCompat, 'collection');

    if (rootCollectionId) {
      this.logInfo(`Root collection already exists for ${projectKey} (${rootBackwardCompat})`);
      result.skipped++;
      this.showSkipped();
    } else {
      const internalName = `exhibitions_root_${projectKey.toLowerCase()}`;

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
          description: `Curated virtual exhibitions of the ${projectKey} project.`,
        });

        this.logInfo(`Created root collection for ${projectKey}: ${rootCollectionId}`);
        result.imported++;
        this.showProgress();
      }
    }

    // 2. Re-parent the project's exhibition collections under the root.
    for (const exhibitionId of exhibitionIds) {
      const exhibitionBackwardCompat = `${MWNF3_SCHEMA}:exhibitions:${exhibitionId}`;
      const exhibitionCollectionId = await this.getEntityUuidAsync(
        exhibitionBackwardCompat,
        'collection'
      );

      if (!exhibitionCollectionId) {
        this.logWarning(
          `Exhibition collection not found (${exhibitionBackwardCompat}), skipping — run Mwnf3ExhibitionImporter first`
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
