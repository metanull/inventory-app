/**
 * Sharing History HB Recontext Importer
 *
 * Standalone repair step for #1494: ShBibliographyHbImporter used to hardcode
 * its parent/context resolution to `mwnf3_sharing_history:sh_projects:awe`,
 * so the single USA-project Historical Background record
 * (`sh_countries_historicalbackground` hb_id=20, "Historical Profile /
 * Germany") and its pages were imported into the **awe** context. Legacy AWE
 * only ever showed 18 country profiles.
 *
 * This step walks every legacy HB record, resolves the record's own
 * project's root collection + context by backward-compatibility key
 * (`sh_projects:{project_id lc}` — ShProjectImporter imports ALL sh_projects,
 * including the unpublished USA/RUS test projects), and moves any HB record
 * collection whose context or parent doesn't match:
 * - collection_translations.context_id (moved first, so a rerun repairs a
 *   partial failure — the translations update itself is a guarded no-op when
 *   already in the target context)
 * - collections.context_id
 * - collections.parent_id → the project's Historical Profiles marker when it
 *   exists (`sh_countries_historicalbackground:root:{k}`, created by
 *   ShHistoricalProfilesRootImporter, #1505), else the project's root
 *   collection — so this step never undoes the marker re-parenting,
 *   whichever order the two steps run in
 * and the record's page collections (context + translations only — their
 * parent is the HB record itself, which is already correct).
 *
 * Idempotent and re-import-safe by design:
 * - records already in their project's context are skipped (the normal state
 *   after a full re-import with the fixed ShBibliographyHbImporter);
 * - records that were never imported are skipped;
 * - a missing project root/context is a warning + skip, never an error.
 *
 * Run standalone with `--only sh-hb-recontext` — entity lookups fall back to
 * DB queries on backward_compatibility.
 *
 * Dependencies:
 * - ShProjectImporter (SH project collections + contexts)
 * - ShBibliographyHbImporter (HB collections)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

const SH_SCHEMA = 'mwnf3_sharing_history';

interface ShLegacyHbRow {
  hb_id: number;
  project_id: string;
}

interface ShLegacyHbPageRow {
  page_id: number;
  hb_id: number;
}

export class ShHbRecontextImporter extends BaseImporter {
  getName(): string {
    return 'ShHbRecontextImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo(
        'Re-contexting SH Historical Background records into their legacy project (#1494)...'
      );

      const hbs = await this.context.legacyDb.query<ShLegacyHbRow>(
        `SELECT hb_id, project_id
         FROM ${SH_SCHEMA}.sh_countries_historicalbackground
         ORDER BY hb_id`
      );

      const pages = await this.context.legacyDb.query<ShLegacyHbPageRow>(
        `SELECT page_id, hb_id
         FROM ${SH_SCHEMA}.sh_countries_historicalbackground_pages
         ORDER BY page_id`
      );

      const pagesByHb = new Map<number, number[]>();
      for (const page of pages) {
        const list = pagesByHb.get(page.hb_id) ?? [];
        list.push(page.page_id);
        pagesByHb.set(page.hb_id, list);
      }

      this.logInfo(`Found ${hbs.length} HB records, ${pages.length} HB pages`);

      for (const hb of hbs) {
        try {
          await this.recontextRecord(hb, pagesByHb.get(hb.hb_id) ?? [], result);
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`HB ${hb.hb_id}: ${message}`);
          this.logError(`HB ${hb.hb_id}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to recontext SH HB records: ${message}`);
      this.logError('ShHbRecontextImporter', message);
      this.showError();
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async recontextRecord(
    hb: ShLegacyHbRow,
    pageIds: number[],
    result: ImportResult
  ): Promise<void> {
    const backwardCompat = `${SH_SCHEMA}:sh_countries_historicalbackground:${hb.hb_id}`;
    const collectionId = await this.getEntityUuidAsync(backwardCompat, 'collection');
    if (!collectionId) {
      // Never imported (e.g. its project was skipped) — nothing to move.
      this.logInfo(`HB ${hb.hb_id}: collection not found (${backwardCompat}), nothing to move`);
      result.skipped++;
      this.showSkipped();
      return;
    }

    const projectKey = hb.project_id.toLowerCase();
    const projectBackwardCompat = `${SH_SCHEMA}:sh_projects:${projectKey}`;
    const rootId = await this.getEntityUuidAsync(projectBackwardCompat, 'collection');
    const contextId = await this.getEntityUuidAsync(projectBackwardCompat, 'context');
    if (!rootId || !contextId) {
      result.warnings = result.warnings || [];
      result.warnings.push(
        `HB ${hb.hb_id}: SH project ${projectKey} root/context not found (${projectBackwardCompat}), cannot recontext`
      );
      this.logWarning(
        `HB ${hb.hb_id}: SH project ${projectKey} root/context not found, skipping — run ShProjectImporter first`
      );
      result.skipped++;
      this.showSkipped();
      return;
    }

    // #1505: when the per-project Historical Profiles marker exists, HB
    // records belong under it, not directly under the project root.
    const profilesRootBackwardCompat = `${SH_SCHEMA}:sh_countries_historicalbackground:root:${projectKey}`;
    const profilesRootId = await this.getEntityUuidAsync(profilesRootBackwardCompat, 'collection');
    const desiredParentId = profilesRootId ?? rootId;

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would ensure ${backwardCompat} (+${pageIds.length} pages) sits in project ${projectKey} context`
      );
      result.imported++;
      this.showProgress();
      return;
    }

    // ── HB record collection ────────────────────────────────────────────
    const currentContextId = await this.context.strategy.getCollectionContextId(collectionId);
    const currentParentId = await this.context.strategy.getCollectionParentId(collectionId);
    const needsContext = currentContextId !== contextId;
    const needsParent = currentParentId !== desiredParentId;

    if (!needsContext && !needsParent) {
      result.skipped++;
      this.showSkipped();
    } else {
      // Translations first: if this run dies halfway, the collection row
      // still mismatches and a rerun redoes the whole move (the translations
      // update is a no-op for rows already in the target context).
      await this.context.strategy.updateCollectionTranslationsContextId(collectionId, contextId);
      if (needsContext) {
        await this.context.strategy.updateCollectionContextId(collectionId, contextId);
      }
      if (needsParent) {
        await this.context.strategy.updateCollectionParentId(collectionId, desiredParentId);
      }
      this.logInfo(`Moved ${backwardCompat} into project ${projectKey} context`);
      result.imported++;
      this.showProgress();
    }

    // ── Page collections: context only (parent is the HB record) ────────
    for (const pageId of pageIds) {
      const pageBackwardCompat = `${SH_SCHEMA}:sh_countries_historicalbackground_pages:${pageId}`;
      const pageCollectionId = await this.getEntityUuidAsync(pageBackwardCompat, 'collection');
      if (!pageCollectionId) {
        this.logInfo(`HB page ${pageId}: collection not found, nothing to move`);
        result.skipped++;
        this.showSkipped();
        continue;
      }

      const pageContextId = await this.context.strategy.getCollectionContextId(pageCollectionId);
      if (pageContextId === contextId) {
        result.skipped++;
        this.showSkipped();
        continue;
      }

      await this.context.strategy.updateCollectionTranslationsContextId(
        pageCollectionId,
        contextId
      );
      await this.context.strategy.updateCollectionContextId(pageCollectionId, contextId);
      this.logInfo(`Moved ${pageBackwardCompat} into project ${projectKey} context`);
      result.imported++;
      this.showProgress();
    }
  }
}
