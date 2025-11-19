"use strict";
/**
 * Tracks imported entities by backward_compatibility reference
 * to avoid duplicating data across schemas
 */
Object.defineProperty(exports, "__esModule", { value: true });
exports.BackwardCompatibilityTracker = void 0;
class BackwardCompatibilityTracker {
    entities = new Map();
    /**
     * Register a newly imported entity
     */
    register(entity) {
        this.entities.set(entity.backwardCompatibility, entity);
    }
    /**
     * Check if entity was already imported
     */
    exists(backwardCompatibility) {
        return this.entities.has(backwardCompatibility);
    }
    /**
     * Get UUID of previously imported entity
     */
    getUuid(backwardCompatibility) {
        return this.entities.get(backwardCompatibility)?.uuid ?? null;
    }
    /**
     * Get all entities of a specific type
     */
    getByType(entityType) {
        return Array.from(this.entities.values()).filter((e) => e.entityType === entityType);
    }
    /**
     * Get import statistics
     */
    getStats() {
        const stats = {
            context: 0,
            collection: 0,
            partner: 0,
            item: 0,
            image: 0,
        };
        for (const entity of this.entities.values()) {
            stats[entity.entityType]++;
        }
        return stats;
    }
    /**
     * Get all tracked entities
     */
    getAll() {
        return Array.from(this.entities.values());
    }
    /**
     * Clear all tracked entities (use with caution)
     */
    clear() {
        this.entities.clear();
    }
}
exports.BackwardCompatibilityTracker = BackwardCompatibilityTracker;
//# sourceMappingURL=BackwardCompatibilityTracker.js.map