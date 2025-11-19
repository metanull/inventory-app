import { BaseImporter, ImportResult } from '../BaseImporter.js';
/**
 * Imports monuments from mwnf3.monuments
 *
 * CRITICAL: monuments table is denormalized with language in PK
 * - PK: project_id, country, institution_id, number, LANG (5 columns)
 * - Multiple rows per monument (one per language)
 * - Must group by non-lang columns and create ItemTranslations
 * - backward_compatibility: mwnf3:monuments:{proj}:{country}:{inst}:{num} (NO LANG)
 */
export declare class MonumentImporter extends BaseImporter {
    getName(): string;
    import(): Promise<ImportResult>;
    /**
     * Group denormalized monument rows by non-lang PK columns
     */
    private groupMonumentsByPK;
    private importMonument;
    private importTranslation;
    private mapLanguageCode;
}
//# sourceMappingURL=MonumentImporter.d.ts.map