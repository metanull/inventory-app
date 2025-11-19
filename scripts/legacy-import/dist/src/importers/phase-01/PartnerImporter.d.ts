import { BaseImporter, ImportContext, ImportResult } from '../BaseImporter.js';
/**
 * Phase 1 Task 2: Import Partners (Museums + Institutions)
 * Orchestrates museum and institution imports
 */
export declare class PartnerImporter extends BaseImporter {
    private museumImporter;
    private institutionImporter;
    constructor(context: ImportContext);
    getName(): string;
    import(): Promise<ImportResult>;
}
//# sourceMappingURL=PartnerImporter.d.ts.map