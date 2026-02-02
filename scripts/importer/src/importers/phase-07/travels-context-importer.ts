/**
 * Travels Context Importer
 *
 * Creates a single Context record for the Travels application.
 * This context groups all travel-related collections and translations.
 *
 * Legacy schema:
 * - mwnf3_travels.trails (no direct context equivalent, we create one)
 *
 * New schema:
 * - contexts (id, internal_name, is_default, backward_compatibility)
 *
 * Mapping:
 * - internal_name = 'travels'
 * - is_default = false
 * - backward_compatibility = 'mwnf3_travels:context'
 *
 * Dependencies: None (this is the first importer in phase-07)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

export class TravelsContextImporter extends BaseImporter {
  getName(): string {
    return 'TravelsContextImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      const backwardCompat = 'mwnf3_travels:context';
      const internalName = 'travels';

      this.logInfo('Creating Travels context...');

      // Check if already exists
      if (await this.entityExistsAsync(backwardCompat, 'context')) {
        this.logInfo('Travels context already exists, skipping');
        result.skipped++;
        this.showSkipped();
        return result;
      }

      // Collect sample
      this.collectSample(
        'travels_context',
        { internal_name: internalName, backward_compatibility: backwardCompat },
        'foundation',
        'Travels application context'
      );

      if (this.isDryRun || this.isSampleOnlyMode) {
        this.logInfo(
          `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create context: ${internalName}`
        );
        this.registerEntity('', backwardCompat, 'context');
        result.imported++;
        this.showProgress();
        return result;
      }

      // Write context using strategy
      const contextId = await this.context.strategy.writeContext({
        internal_name: internalName,
        is_default: false,
        backward_compatibility: backwardCompat,
      });

      this.registerEntity(contextId, backwardCompat, 'context');
      this.logInfo(`Created Travels context: ${contextId}`);

      result.imported++;
      this.showProgress();
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error creating Travels context: ${errorMessage}`);
      this.logError('TravelsContextImporter', errorMessage);
      this.showError();
    }

    return result;
  }
}
