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

    const defaultContext = {
      internal_name: 'Default Context',
      backward_compatibility: null,
    };

    try {
      if (this.context.dryRun) {
        this.log('[DRY-RUN] Would create default context');
        result.imported++;
        return result;
      }

      // Try to create - if 422, it already exists
      const response = await this.context.apiClient.context.contextStore(defaultContext);
      const contextId = response.data.data.id;

      // Register in tracker for use by other importers
      this.context.tracker.register({
        uuid: contextId,
        backwardCompatibility: '__default_context__',
        entityType: 'context',
        createdAt: new Date(),
      });

      this.log(`Created default context: ${contextId}`);
      result.imported++;
      this.showProgress();
    } catch (error) {
      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { status?: number; data?: unknown } };
        if (axiosError.response?.status === 422) {
          // Already exists - try to find it and register in tracker
          this.log('Default context already exists');
          result.skipped++;
          this.showSkipped();

          // Try to get existing default context
          try {
            // Query contexts to find "Default Context"
            const contextsResponse = await this.context.apiClient.context.contextIndex();
            const contexts = contextsResponse.data.data;
            const defaultCtx = contexts.find(
              (c: { internal_name?: string }) => c.internal_name === 'Default Context'
            );

            if (defaultCtx) {
              this.context.tracker.register({
                uuid: defaultCtx.id,
                backwardCompatibility: '__default_context__',
                entityType: 'context',
                createdAt: new Date(),
              });
              this.log(`Registered existing default context: ${defaultCtx.id}`);
            }
          } catch {
            this.log('Warning: Could not register existing default context in tracker');
          }
        } else {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Default Context: ${message}`);
          this.showError();
        }
      }
    }
    console.log(''); // New line after progress dots

    result.success = result.errors.length === 0;
    return result;
  }
}
