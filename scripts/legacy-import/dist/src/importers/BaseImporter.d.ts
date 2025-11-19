import { LegacyDatabase } from '../database/LegacyDatabase.js';
import { InventoryApiClient } from '../api/InventoryApiClient.js';
import { BackwardCompatibilityTracker } from '../utils/BackwardCompatibilityTracker.js';
export interface ImportContext {
    legacyDb: LegacyDatabase;
    apiClient: InventoryApiClient;
    tracker: BackwardCompatibilityTracker;
    dryRun: boolean;
    limit: number;
    logPath?: string;
}
export interface ImportResult {
    success: boolean;
    imported: number;
    skipped: number;
    errors: string[];
}
/**
 * Base class for all importers
 *
 * Each importer is responsible for:
 * 1. Querying legacy database for specific entity type
 * 2. Transforming legacy data to new model format
 * 3. Checking for duplicates via BackwardCompatibilityTracker
 * 4. Calling API to create entities
 * 5. Registering imported entities in tracker
 */
export declare abstract class BaseImporter {
    protected context: ImportContext;
    constructor(context: ImportContext);
    /**
     * Execute the import process
     */
    abstract import(): Promise<ImportResult>;
    /**
     * Get importer name for logging
     */
    abstract getName(): string;
    /**
     * Helper: Log import progress - writes directly to console and file
     */
    protected log(message: string): void;
    /**
     * Helper: Show progress dot (for long-running operations)
     */
    protected showProgress(): void;
    /**
     * Helper: Show skipped indicator
     */
    protected showSkipped(): void;
    /**
     * Helper: Show error indicator
     */
    protected showError(): void;
    /**
     * Helper: Log error
     */
    protected logError(message: string, error?: unknown): void;
    /**
     * Helper: Create import result
     */
    protected createResult(success: boolean, imported: number, skipped: number, errors?: string[]): ImportResult;
}
//# sourceMappingURL=BaseImporter.d.ts.map