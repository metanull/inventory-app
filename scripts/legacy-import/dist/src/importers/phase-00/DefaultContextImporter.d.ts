import { BaseImporter, ImportResult } from '../BaseImporter.js';
/**
 * Phase 0: Ensures Default Context exists
 * Creates the default context with internal_name = "Default Context"
 * Skips if already exists (idempotent)
 */
export declare class DefaultContextImporter extends BaseImporter {
    getName(): string;
    import(): Promise<ImportResult>;
}
//# sourceMappingURL=DefaultContextImporter.d.ts.map