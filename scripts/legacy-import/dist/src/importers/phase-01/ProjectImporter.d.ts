import { BaseImporter, type ImportResult } from '../BaseImporter.js';
export declare class ProjectImporter extends BaseImporter {
    getName(): string;
    import(): Promise<ImportResult>;
    private importProject;
    private mapLanguageCode;
}
//# sourceMappingURL=ProjectImporter.d.ts.map