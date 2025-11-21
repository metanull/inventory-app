import { BaseImporter, ImportResult } from '../BaseImporter.js';

/**
 * Phase 0: Ensures Default Context exists
 * Creates the default context with internal_name = "Default Context"
 * Skips if already exists (idempotent)
 */
export class DefaultContextImporter extends BaseImporter {
  getName(): string {
    return 'DefaultContextImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      if (this.context.dryRun) {
        this.logInfo('[DRY-RUN] Would check and create default context if needed');
        result.imported++;
        return result;
      }

      // Step 1: Check if default context already exists
      try {
        const existingResponse = await this.context.apiClient.context.contextGetDefault();
        const existingContext = existingResponse.data.data;

        if (existingContext) {
          // Default context exists - register in tracker and skip
          this.context.tracker.register({
            uuid: existingContext.id,
            backwardCompatibility: '__default_context__',
            entityType: 'context',
            createdAt: new Date(),
          });

          this.logInfo(`Default context already exists: ${existingContext.id}`);
          result.skipped++;
          this.showSkipped();
          this.showSummary(result.imported, result.skipped, result.errors.length);
          result.success = true;
          return result;
        }
      } catch (error) {
        // 404 means no default exists - continue to create
        if (error && typeof error === 'object' && 'response' in error) {
          const axiosError = error as { response?: { status?: number } };
          if (axiosError.response?.status !== 404) {
            // Unexpected error - propagate
            throw error;
          }
          // 404 is expected - no default exists yet
        } else {
          throw error;
        }
      }

      // Step 2: No default context exists - create one
      const defaultContext = {
        internal_name: 'Default Context',
        backward_compatibility: null,
      };

      const response = await this.context.apiClient.context.contextStore(defaultContext);
      const contextId = response.data.data.id;

      // Step 3: Set the newly created context as default
      await this.context.apiClient.context.contextSetDefault(contextId, { is_default: true });

      // Register in tracker for use by other importers
      this.context.tracker.register({
        uuid: contextId,
        backwardCompatibility: '__default_context__',
        entityType: 'context',
        createdAt: new Date(),
      });

      this.logInfo(`Created default context: ${contextId}`);
      result.imported++;
      this.showProgress();
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Default Context: ${message}`);
      result.success = false;
      this.showError();
    }

    this.showSummary(result.imported, result.skipped, result.errors.length);
    result.success = result.errors.length === 0;
    return result;
  }
}
