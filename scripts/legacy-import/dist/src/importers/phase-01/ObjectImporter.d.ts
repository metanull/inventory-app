import { BaseImporter, ImportResult } from '../BaseImporter.js';
/**
 * Imports objects from mwnf3.objects
 *
 * CRITICAL: objects table is denormalized with language in PK
 * - PK: project_id, country, museum_id, number, LANG (5 columns)
 * - Multiple rows per object (one per language)
 * - Must group by non-lang columns and create ItemTranslations
 * - backward_compatibility: mwnf3:objects:{proj}:{country}:{museum}:{num} (NO LANG)
 */
export declare class ObjectImporter extends BaseImporter {
    getName(): string;
    import(): Promise<ImportResult>;
    /**
     * Group denormalized object rows by non-lang PK columns
     */
    private groupObjectsByPK;
    private importObject;
    private importTranslation;
    private mapLanguageCode;
}
//# sourceMappingURL=ObjectImporter.d.ts.map