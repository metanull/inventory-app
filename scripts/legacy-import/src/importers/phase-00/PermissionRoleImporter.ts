import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);

/**
 * Phase 0: Sync Permissions and Roles
 * Calls Laravel artisan command: php artisan permissions:sync
 * Idempotent - safe to run multiple times
 */
export class PermissionRoleImporter extends BaseImporter {
  getName(): string {
    return 'PermissionRoleImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      if (this.context.dryRun) {
        this.logInfo('[DRY-RUN] Would run: php artisan permissions:sync');
        result.imported++;
        return result;
      }

      this.logInfo('Running: php artisan permissions:sync');

      // Call Laravel artisan command
      // Need to go up 3 levels from legacy-import to reach Laravel root
      const { stderr } = await execAsync('php artisan permissions:sync', {
        cwd: '../../../', // From scripts/legacy-import/ to project root
      });

      if (stderr && !stderr.includes('Syncing')) {
        throw new Error(stderr);
      }

      this.logInfo('Permissions and roles synced successfully');
      result.imported++;
      this.showProgress();
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to sync permissions: ${message}`);
      result.success = false;
      this.showError();
    }

    this.showSummary(result.imported, result.skipped, result.errors.length);
    return result;
  }
}
