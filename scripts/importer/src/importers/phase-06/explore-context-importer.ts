/**
 * Explore Context Importer
 *
 * Creates a single Context record for the Explore application.
 * This context is used for all Explore-related translations.
 *
 * Legacy schema:
 * - mwnf3_explore (the entire database represents this context)
 *
 * New schema:
 * - contexts (id, internal_name, backward_compatibility, is_default)
 *
 * Context: mwnf3_explore:context (single context for all Explore data)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

export class ExploreContextImporter extends BaseImporter {
  getName(): string {
    return 'ExploreContextImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Creating context for Explore application...');

      const backwardCompat = 'mwnf3_explore:context';
      const internalName = 'explore';

      // Check if already exists
      if (await this.entityExistsAsync(backwardCompat, 'context')) {
        this.logInfo(`Context ${internalName} already exists, skipping`);
        result.skipped++;
        this.showSkipped();
        return result;
      }

      // Collect sample
      this.collectSample(
        'explore_context',
        {
          internal_name: internalName,
          backward_compatibility: backwardCompat,
          is_default: false,
        } as Record<string, unknown>,
        'success'
      );

      if (this.isDryRun || this.isSampleOnlyMode) {
        this.logInfo(
          `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create context: ${internalName} (${backwardCompat})`
        );
        this.registerEntity('', backwardCompat, 'context');
        result.imported++;
        this.showProgress();
        return result;
      }

      // Write context using strategy
      const contextId = await this.context.strategy.writeContext({
        internal_name: internalName,
        backward_compatibility: backwardCompat,
        is_default: false,
      });

      this.registerEntity(contextId, backwardCompat, 'context');
      this.logInfo(`Created context: ${internalName} (${contextId})`);

      result.imported++;
      this.showProgress();
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error creating explore context: ${errorMessage}`);
      this.logError('ExploreContextImporter', errorMessage);
      this.showError();
    }

    return result;
  }
}
