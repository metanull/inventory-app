import { appendFileSync } from 'fs';

/**
 * Centralized logging utility for import operations
 *
 * CONSOLE OUTPUT:
 * - Uses dot format for progress: . (success), s (skipped), × (error)
 * - Minimal console output during import
 * - One-line summary at end
 *
 * LOG FILE OUTPUT:
 * - Direct writes (no buffering)
 * - All technical details (errors, warnings, responses)
 * - Full exception traces when available
 */
export class ImportLogger {
  private logFilePath?: string;
  private importerName: string;

  constructor(importerName: string, logFilePath?: string) {
    this.importerName = importerName;
    this.logFilePath = logFilePath;
  }

  /**
   * Log informational message (only to file, not console)
   */
  info(message: string): void {
    this.writeToFile(`[${this.importerName}] ${message}`);
  }

  /**
   * Log warning (to file with full details)
   */
  warning(message: string, details?: unknown): void {
    const logLine = `[${this.importerName}] WARNING: ${message}`;
    this.writeToFile(logLine);

    if (details) {
      this.writeToFile(this.formatDetails(details));
    }
  }

  /**
   * Log error (to file with full details)
   * Does NOT write to console - use showError() for console indicator
   */
  error(context: string, error: unknown, additionalContext?: Record<string, unknown>): void {
    const errorMessage = error instanceof Error ? error.message : String(error);
    this.writeToFile(`[${this.importerName}] ERROR: ${context}`);
    this.writeToFile(`  Message: ${errorMessage}`);

    // Log additional context (e.g., record identifiers)
    if (additionalContext) {
      Object.entries(additionalContext).forEach(([key, value]) => {
        this.writeToFile(`  ${key}: ${JSON.stringify(value)}`);
      });
    }

    // Extract and log HTTP response data for axios errors
    if (error && typeof error === 'object' && 'response' in error) {
      const axiosError = error as {
        response?: { status?: number; statusText?: string; data?: unknown };
      };
      if (axiosError.response) {
        this.writeToFile(`  HTTP Status: ${axiosError.response.status} ${axiosError.response.statusText}`);
        if (axiosError.response.data) {
          this.writeToFile(`  Response: ${JSON.stringify(axiosError.response.data, null, 2)}`);
        }
      }
    }

    // Log stack trace if available
    if (error instanceof Error && error.stack) {
      this.writeToFile(`  Stack: ${error.stack}`);
    }
  }

  /**
   * Show progress indicator on console (gray dot)
   */
  showProgress(): void {
    process.stdout.write('\x1b[90m.\x1b[0m');
  }

  /**
   * Show skipped indicator on console (yellow 's')
   */
  showSkipped(): void {
    process.stdout.write('\x1b[33ms\x1b[0m');
  }

  /**
   * Show error indicator on console (red '×')
   */
  showError(): void {
    process.stdout.write('\x1b[31m×\x1b[0m');
  }

  /**
   * Print one-line summary to console after import completes
   */
  showSummary(imported: number, skipped: number, errors: number): void {
    console.log(''); // New line after progress dots
    console.log(` ${imported} imported, ${skipped} skipped, ${errors} errors`);
  }

  /**
   * Write directly to log file (no buffering)
   */
  private writeToFile(message: string): void {
    if (!this.logFilePath) return;

    try {
      appendFileSync(this.logFilePath, message + '\n', 'utf-8');
    } catch {
      // Ignore write errors - don't let logging failures stop the import
    }
  }

  /**
   * Format details object for logging
   */
  private formatDetails(details: unknown): string {
    if (typeof details === 'string') {
      return `  Details: ${details}`;
    }
    return `  Details: ${JSON.stringify(details, null, 2)}`;
  }
}
