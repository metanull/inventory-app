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
export class BaseImporter {
  context;
  constructor(context) {
    this.context = context;
  }
  /**
   * Helper: Log import progress
   */
  log(message) {
    console.log(`[${this.getName()}] ${message}`);
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
//# sourceMappingURL=BaseImporter.js.map
