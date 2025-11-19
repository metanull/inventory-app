import { BaseImporter, ImportResult } from '../BaseImporter.js';
/**
 * Phase 0: Sync Permissions and Roles
 * Calls Laravel artisan command: php artisan permissions:sync
 * Idempotent - safe to run multiple times
 */
export declare class PermissionRoleImporter extends BaseImporter {
    getName(): string;
    import(): Promise<ImportResult>;
}
//# sourceMappingURL=PermissionRoleImporter.d.ts.map