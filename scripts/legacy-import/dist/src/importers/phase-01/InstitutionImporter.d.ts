import { BaseImporter, ImportResult } from '../BaseImporter.js';
/**
 * Imports institutions from mwnf3.institutions and mwnf3.institutionnames
 * Maps to Partner model with type='institution'
 */
export declare class InstitutionImporter extends BaseImporter {
    getName(): string;
    import(): Promise<ImportResult>;
    private importInstitution;
    private importTranslation;
    /**
     * Map legacy 2-character ISO 639-1 codes to 3-character ISO 639-3 codes
     */
    private mapLanguageCode;
}
//# sourceMappingURL=InstitutionImporter.d.ts.map