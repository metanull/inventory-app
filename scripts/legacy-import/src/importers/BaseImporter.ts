import { LegacyDatabase } from '../database/LegacyDatabase.js';
import { InventoryApiClient } from '../api/InventoryApiClient.js';
import { BackwardCompatibilityTracker } from '../utils/BackwardCompatibilityTracker.js';
import { ImportLogger } from '../utils/ImportLogger.js';

export interface ImportContext {
  legacyDb: LegacyDatabase;
  apiClient: InventoryApiClient;
  tracker: BackwardCompatibilityTracker;
  dryRun: boolean;
  logPath?: string; // Path to log file for direct writes
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
 *
 * LOGGING CONVENTION:
 * - Console: Dot format only (., s, ×) + one-line summary at end
 * - Log file: All details, errors, warnings with full context
 */
export abstract class BaseImporter {
  protected logger: ImportLogger;

  constructor(protected context: ImportContext) {
    this.logger = new ImportLogger(this.getName(), context.logPath);
  }

  /**
   * Execute the import process
   */
  abstract import(): Promise<ImportResult>;

  /**
   * Get importer name for logging
   */
  abstract getName(): string;

  /**
   * Helper: Show progress dot (for long-running operations)
   */
  protected showProgress(): void {
    this.logger.showProgress();
  }

  /**
   * Helper: Show skipped indicator
   */
  protected showSkipped(): void {
    this.logger.showSkipped();
  }

  /**
   * Helper: Show error indicator
   */
  protected showError(): void {
    this.logger.showError();
  }

  /**
   * Helper: Show summary line (imported, skipped, errors)
   */
  protected showSummary(imported: number, skipped: number, errors: number): void {
    this.logger.showSummary(imported, skipped, errors);
  }

  /**
   * Helper: Log info message (to file only)
   */
  protected logInfo(message: string): void {
    this.logger.info(message);
  }

  /**
   * Helper: Log warning (to file with details)
   */
  protected logWarning(message: string, details?: unknown): void {
    this.logger.warning(message, details);
  }

  /**
   * Helper: Log error with full context (to file only)
   */
  protected logError(
    context: string,
    error: unknown,
    additionalContext?: Record<string, unknown>
  ): void {
    this.logger.error(context, error, additionalContext);
  }

  /**
   * Helper: Create import result
   */
  protected createResult(
    success: boolean,
    imported: number,
    skipped: number,
    errors: string[] = []
  ): ImportResult {
    return { success, imported, skipped, errors };
  }
}
