/**
 * Tracks imported entities by backward_compatibility reference
 * to avoid duplicating data across schemas
 */
export interface ImportedEntity {
    uuid: string;
    backwardCompatibility: string;
    entityType: 'context' | 'collection' | 'partner' | 'item' | 'image';
    createdAt: Date;
}
export declare class BackwardCompatibilityTracker {
    private entities;
    /**
     * Register a newly imported entity
     */
    register(entity: ImportedEntity): void;
    /**
     * Check if entity was already imported
     */
    exists(backwardCompatibility: string): boolean;
    /**
     * Get UUID of previously imported entity
     */
    getUuid(backwardCompatibility: string): string | null;
    /**
     * Get all entities of a specific type
     */
    getByType(entityType: ImportedEntity['entityType']): ImportedEntity[];
    /**
     * Get import statistics
     */
    getStats(): Record<ImportedEntity['entityType'], number>;
    /**
     * Clear all tracked entities (use with caution)
     */
    clear(): void;
}
//# sourceMappingURL=BackwardCompatibilityTracker.d.ts.map