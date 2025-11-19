import { BaseImporter, ImportResult } from '../BaseImporter.js';
/**
 * Phase 0: Ensures Country reference data exists
 * Uses same data as CountrySeeder
 * Skips if already exists (idempotent)
 */
export declare class CountryImporter extends BaseImporter {
    getName(): string;
    import(): Promise<ImportResult>;
}
//# sourceMappingURL=CountryImporter.d.ts.map