/**
 * Sharing History Item Display-Status Importer
 *
 * Stamps the legacy `display_status = 'N'` flag ("HB/HCR illustration only")
 * into `item_translations.extra.legacy_display_status` for the affected SH
 * objects and monuments.
 *
 * Why: legacy excludes `display_status='N'` items from database search and
 * Permanent Collection browsing (`modules/database_results.php:444,458`)
 * while still using them to illustrate Historical Background and timeline
 * pages. ShObjectImporter/ShMonumentImporter read the column but never
 * persisted it (`items` has no extra column), leaving the ~462 `N` items
 * indistinguishable in the inventory DB.
 *
 * Only `N` is stamped — `A` (active) is the implied default, keeping the
 * touch surface minimal. The flag is MERGED into each translation row's
 * existing `extra` (bibliography etc. preserved).
 *
 * Standalone (`--only sh-item-display-status`) and idempotent; also safe at
 * the end of a full fresh import.
 *
 * Dependencies:
 * - ShObjectImporter / ShMonumentImporter (items + translations must exist)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { formatShBackwardCompatibility } from '../../domain/transformers/index.js';
import { sanitizeJsonField } from '../../utils/html-to-markdown.js';
import { jsonValuesEqual } from '../../utils/json-equal.js';

const SH_SCHEMA = 'mwnf3_sharing_history';

interface ShLegacyDisplayStatusRow {
  project_id: string;
  country: string;
  number: number;
}

export class ShItemDisplayStatusImporter extends BaseImporter {
  getName(): string {
    return 'ShItemDisplayStatusImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Stamping legacy display_status=N on SH item translations...');

      await this.stampTable('sh_objects', result);
      await this.stampTable('sh_monuments', result);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to stamp SH display_status: ${message}`);
      this.logError('ShItemDisplayStatusImporter', message);
      this.showError();
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async stampTable(table: 'sh_objects' | 'sh_monuments', result: ImportResult) {
    const rows = await this.context.legacyDb.query<ShLegacyDisplayStatusRow>(
      `SELECT project_id, country, number
       FROM ${SH_SCHEMA}.${table}
       WHERE display_status = 'N'
       ORDER BY project_id, country, number`
    );

    this.logInfo(`Found ${rows.length} ${table} rows with display_status=N`);

    for (const legacy of rows) {
      const itemBackwardCompat = `${SH_SCHEMA}:${table}:${legacy.project_id.toLowerCase()}:${legacy.country.toLowerCase()}:${legacy.number}`;

      try {
        await this.stampItem(itemBackwardCompat, legacy.project_id, result);
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`${itemBackwardCompat}: ${message}`);
        this.logError(itemBackwardCompat, message);
        this.showError();
      }
    }
  }

  private async stampItem(
    itemBackwardCompat: string,
    projectId: string,
    result: ImportResult
  ): Promise<void> {
    const itemId = await this.getEntityUuidAsync(itemBackwardCompat, 'item');
    if (!itemId) {
      this.logWarning(`SH item not found (${itemBackwardCompat}), skipping`);
      result.skipped++;
      this.showSkipped();
      return;
    }

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would stamp legacy_display_status=N on ${itemBackwardCompat}`
      );
      result.imported++;
      this.showProgress();
      return;
    }

    // Scoped to this SH project's own context — the same canonical item can
    // also carry a translation under a different context (its own direct
    // project, or Explore), and the unscoped getItemTranslationExtra/
    // setItemTranslationExtra match ANY row for (item, language), which
    // silently overwrote that sibling context's extra with this importer's
    // own value. Confirmed live: an AWE monument that is also imported
    // directly under its own project lost patrons/architects/notice this way.
    const contextBackwardCompat = formatShBackwardCompatibility('sh_projects', projectId);
    const contextId = await this.getEntityUuidAsync(contextBackwardCompat, 'context');
    if (!contextId) {
      this.logWarning(`SH project context not found (${contextBackwardCompat}), skipping`);
      result.skipped++;
      this.showSkipped();
      return;
    }

    const languageIds = await this.context.strategy.getItemTranslationLanguages(itemId);
    if (languageIds.length === 0) {
      this.logWarning(`No translations found for ${itemBackwardCompat}`);
      result.skipped++;
      this.showSkipped();
      return;
    }

    let changed = false;
    for (const langId of languageIds) {
      const existing = await this.context.strategy.getItemTranslationExtraByContext(
        itemId,
        langId,
        contextId
      );

      // The whole `extra` is written, so the whole `extra` decides. `raw` is
      // what's actually sent to setItemTranslationExtraByContext, which
      // sanitises its own argument as every set*Extra* strategy method does;
      // sanitising here too, before the call, would double-convert on every
      // write — `merged` sanitises separately, only to know what the write
      // will persist, so it can be compared against what's already there.
      // Compared with a key-order-independent check: MySQL's JSON column
      // does not preserve insertion order, so `existing`'s keys rarely
      // match a freshly-built object's own order even when every value is
      // identical — a plain JSON.stringify comparison never converges.
      const raw = { ...(existing || {}), legacy_display_status: 'N' };
      const merged = sanitizeJsonField(raw);

      // Idempotency: skip the write when nothing would change.
      if (jsonValuesEqual(existing || {}, merged)) {
        continue;
      }
      await this.context.strategy.setItemTranslationExtraByContext(
        itemId,
        langId,
        contextId,
        JSON.stringify(raw)
      );
      changed = true;
    }

    if (changed) {
      result.imported++;
      this.showProgress();
    } else {
      result.skipped++;
      this.showSkipped();
    }
  }
}
