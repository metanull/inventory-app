"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.DefaultContextImporter = void 0;
const BaseImporter_js_1 = require("../BaseImporter.js");
/**
 * Phase 0: Ensures Default Context exists
 * Creates the default context with internal_name = "Default Context"
 * Skips if already exists (idempotent)
 */
class DefaultContextImporter extends BaseImporter_js_1.BaseImporter {
    getName() {
        return 'DefaultContextImporter';
    }
    async import() {
        const result = {
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
        }
        catch (error) {
            if (error && typeof error === 'object' && 'response' in error) {
                const axiosError = error;
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
                        const defaultCtx = contexts.find((c) => c.internal_name === 'Default Context');
                        if (defaultCtx) {
                            this.context.tracker.register({
                                uuid: defaultCtx.id,
                                backwardCompatibility: '__default_context__',
                                entityType: 'context',
                                createdAt: new Date(),
                            });
                            this.log(`Registered existing default context: ${defaultCtx.id}`);
                        }
                    }
                    catch (e) {
                        this.log('Warning: Could not register existing default context in tracker');
                    }
                }
                else {
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
exports.DefaultContextImporter = DefaultContextImporter;
//# sourceMappingURL=DefaultContextImporter.js.map