"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.BaseImporter = void 0;
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
class BaseImporter {
    context;
    constructor(context) {
        this.context = context;
    }
    /**
     * Helper: Log import progress - writes directly to console and file
     */
    log(message) {
        const logLine = `[${this.getName()}] ${message}`;
        console.log(logLine);
        // Write directly to log file if available
        if (this.context.logPath) {
            try {
                // eslint-disable-next-line @typescript-eslint/no-require-imports, no-undef
                const fs = require('fs');
                fs.appendFileSync(this.context.logPath, logLine + '\n', 'utf-8');
            }
            catch {
                // Ignore write errors
            }
        }
    }
    /**
     * Helper: Show progress dot (for long-running operations)
     */
    showProgress() {
        process.stdout.write('\x1b[32m.\x1b[0m'); // Green dot
    }
    /**
     * Helper: Show skipped indicator
     */
    showSkipped() {
        process.stdout.write('\x1b[33m?\x1b[0m'); // Yellow question mark
    }
    /**
     * Helper: Show error indicator
     */
    showError() {
        process.stdout.write('\x1b[31m×\x1b[0m'); // Red cross
    }
    /**
     * Helper: Log error
     */
    logError(message, error) {
        console.error(`[${this.getName()}] ERROR: ${message}`, error);
    }
    /**
     * Helper: Create import result
     */
    createResult(success, imported, skipped, errors = []) {
        return { success, imported, skipped, errors };
    }
}
exports.BaseImporter = BaseImporter;
//# sourceMappingURL=BaseImporter.js.map