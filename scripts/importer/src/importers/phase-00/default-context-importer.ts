/**
 * Default Context Importer
 *
 * Creates the default context at the beginning of the import process.
 * This context has is_default = true, unlike all other contexts.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

const DEFAULT_CONTEXT_BACKWARD_COMPAT = '__default_context__';

export class DefaultContextImporter extends BaseImporter {
  getName(): string {
    return 'DefaultContextImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Creating default context...');

      // Check if default context already exists
      if (this.entityExists(DEFAULT_CONTEXT_BACKWARD_COMPAT, 'context')) {
        this.logInfo('Default context already exists, skipping');
        result.skipped++;
        this.showSkipped();
        this.showSummary(result.imported, result.skipped, result.errors.length);
        return result;
      }

      // Check in database
      const existingId = await this.context.strategy.findByBackwardCompatibility(
        'contexts',
        DEFAULT_CONTEXT_BACKWARD_COMPAT
      );

      if (existingId) {
        this.logInfo(`Default context already exists in database: ${existingId}`);
        this.registerEntity(existingId, DEFAULT_CONTEXT_BACKWARD_COMPAT, 'context');
        this.context.tracker.setMetadata('default_context_id', existingId);
        result.skipped++;
        this.showSkipped();
        this.showSummary(result.imported, result.skipped, result.errors.length);
        return result;
      }

      if (this.isDryRun || this.isSampleOnlyMode) {
        this.logInfo(
          `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create default context`
        );
        const sampleContextId = 'sample-default-context';
        this.registerEntity(sampleContextId, DEFAULT_CONTEXT_BACKWARD_COMPAT, 'context');
        this.context.tracker.setMetadata('default_context_id', sampleContextId);
        result.imported++;
        this.showProgress();
        this.showSummary(result.imported, result.skipped, result.errors.length);
        return result;
      }

      // Create the default context with is_default = true
      const contextId = await this.context.strategy.writeContext({
        internal_name: 'Default Context',
        backward_compatibility: DEFAULT_CONTEXT_BACKWARD_COMPAT,
        is_default: true,
      });

      this.registerEntity(contextId, DEFAULT_CONTEXT_BACKWARD_COMPAT, 'context');
      // Store default context ID in metadata for use by other importers
      this.context.tracker.setMetadata('default_context_id', contextId);
      this.logInfo(`Created default context: ${contextId}`);

      result.imported++;
      this.showProgress();
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Default context: ${message}`);
      this.logError('Failed to create default context', message);
      this.showError();
    }

    this.showSummary(result.imported, result.skipped, result.errors.length);
    result.success = result.errors.length === 0;
    return result;
  }
}
