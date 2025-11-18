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

export class BackwardCompatibilityTracker {
  private entities = new Map<string, ImportedEntity>();

  /**
   * Register a newly imported entity
   */
  register(entity: ImportedEntity): void {
    this.entities.set(entity.backwardCompatibility, entity);
  }

  /**
   * Check if entity was already imported
   */
  exists(backwardCompatibility: string): boolean {
    return this.entities.has(backwardCompatibility);
  }

  /**
   * Get UUID of previously imported entity
   */
  getUuid(backwardCompatibility: string): string | null {
    return this.entities.get(backwardCompatibility)?.uuid ?? null;
  }

  /**
   * Get all entities of a specific type
   */
  getByType(entityType: ImportedEntity['entityType']): ImportedEntity[] {
    return Array.from(this.entities.values()).filter((e) => e.entityType === entityType);
  }

  /**
   * Get import statistics
   */
  getStats(): Record<ImportedEntity['entityType'], number> {
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
   * Clear all tracked entities (use with caution)
   */
  clear(): void {
    this.entities.clear();
  }
}
