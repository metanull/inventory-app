"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.PermissionRoleImporter = void 0;
const BaseImporter_js_1 = require("../BaseImporter.js");
const child_process_1 = require("child_process");
const util_1 = require("util");
const execAsync = (0, util_1.promisify)(child_process_1.exec);
/**
 * Phase 0: Sync Permissions and Roles
 * Calls Laravel artisan command: php artisan permissions:sync
 * Idempotent - safe to run multiple times
 */
class PermissionRoleImporter extends BaseImporter_js_1.BaseImporter {
    getName() {
        return 'PermissionRoleImporter';
    }
    async import() {
        const result = {
            success: true,
            imported: 0,
            skipped: 0,
            errors: [],
        };
        try {
            if (this.context.dryRun) {
                this.log('[DRY-RUN] Would run: php artisan permissions:sync');
                result.imported++;
                return result;
            }
            this.log('Running: php artisan permissions:sync');
            // Call Laravel artisan command
            // Need to go up 3 levels from legacy-import to reach Laravel root
            const { stderr } = await execAsync('php artisan permissions:sync', {
                cwd: '../../../', // From scripts/legacy-import/ to project root
            });
            if (stderr && !stderr.includes('Syncing')) {
                throw new Error(stderr);
            }
            this.log('Permissions and roles synced successfully');
            result.imported++;
            this.showProgress();
        }
        catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(`Failed to sync permissions: ${message}`);
            result.success = false;
            this.showError();
        }
        console.log(''); // New line after progress dots
        return result;
    }
}
exports.PermissionRoleImporter = PermissionRoleImporter;
//# sourceMappingURL=PermissionRoleImporter.js.map