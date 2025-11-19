import { BaseImporter, ImportResult } from '../BaseImporter.js';
/**
 * Imports museums from mwnf3.museums and mwnf3.museumnames
 * Maps to Partner model with type='museum'
 */
export declare class MuseumImporter extends BaseImporter {
    getName(): string;
    import(): Promise<ImportResult>;
    private importMuseum;
    private importTranslation;
    /**
     * Map legacy 2-character ISO 639-1 codes to 3-character ISO 639-3 codes
     */
    private mapLanguageCode;
}
//# sourceMappingURL=MuseumImporter.d.ts.map