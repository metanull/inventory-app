import { LegacyDatabase } from '../database/LegacyDatabase.js';
import { InventoryApiClient } from '../api/InventoryApiClient.js';
import { BackwardCompatibilityTracker } from '../utils/BackwardCompatibilityTracker.js';

export interface ImportContext {
  legacyDb: LegacyDatabase;
  apiClient: InventoryApiClient;
  tracker: BackwardCompatibilityTracker;
  dryRun: boolean;
  limit: number;
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
 */
export abstract class BaseImporter {
  constructor(protected context: ImportContext) {}

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
  protected log(message: string): void {
    const logLine = `[${this.getName()}] ${message}`;
    console.log(logLine);

    // Write directly to log file if available
    if (this.context.logPath) {
      try {
        // eslint-disable-next-line @typescript-eslint/no-require-imports, no-undef
        const fs = require('fs') as typeof import('fs');
        fs.appendFileSync(this.context.logPath, logLine + '\n', 'utf-8');
      } catch {
        // Ignore write errors
      }
    }
  }

  /**
   * Helper: Show progress dot (for long-running operations)
   */
  protected showProgress(): void {
    process.stdout.write('\x1b[90m.\x1b[0m'); // Gray dot
  }

  /**
   * Helper: Show skipped indicator
   */
  protected showSkipped(): void {
    process.stdout.write('\x1b[33ms\x1b[0m'); // Yellow s
  }

  /**
   * Helper: Show error indicator
   */
  protected showError(): void {
    process.stdout.write('\x1b[31m×\x1b[0m'); // Red cross
  }

  /**
   * Helper: Log error
   */
  protected logError(message: string, error?: unknown): void {
    console.error(`[${this.getName()}] ERROR: ${message}`, error);
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
