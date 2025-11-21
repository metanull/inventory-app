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
export declare class ImportLogger {
    private logFilePath?;
    private importerName;
    constructor(importerName: string, logFilePath?: string);
    /**
     * Log informational message (only to file, not console)
     */
    info(message: string): void;
    /**
     * Log warning (to file with full details)
     */
    warning(message: string, details?: unknown): void;
    /**
     * Log error (to file with full details)
     * Does NOT write to console - use showError() for console indicator
     */
    error(context: string, error: unknown, additionalContext?: Record<string, unknown>): void;
    /**
     * Show progress indicator on console (gray dot)
     */
    showProgress(): void;
    /**
     * Show skipped indicator on console (yellow 's')
     */
    showSkipped(): void;
    /**
     * Show error indicator on console (red '×')
     */
    showError(): void;
    /**
     * Print one-line summary to console after import completes
     */
    showSummary(imported: number, skipped: number, errors: number): void;
    /**
     * Write directly to log file (no buffering)
     */
    private writeToFile;
    /**
     * Format details object for logging
     */
    private formatDetails;
}
//# sourceMappingURL=ImportLogger.d.ts.map