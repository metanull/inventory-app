import { BaseImporter, ImportResult } from '../BaseImporter.js';
/**
 * Phase 0: Ensures Language reference data exists
 * Uses same data as LanguageSeeder
 * Skips if already exists (idempotent)
 */
export declare class LanguageImporter extends BaseImporter {
    getName(): string;
    import(): Promise<ImportResult>;
}
//# sourceMappingURL=LanguageImporter.d.ts.map