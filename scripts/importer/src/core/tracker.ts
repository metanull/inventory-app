/**
 * Tracker Interface - Unified tracking abstraction
 *
 * This interface abstracts the tracking mechanism used by both
 * API-based (BackwardCompatibilityTracker) and SQL-based (Map<string, string>) importers.
 *
 * Following the Interface Segregation Principle, this provides only the
 * methods needed by importers.
 */

import type { EntityType, ImportedEntity } from './types.js';

/**
 * Interface for tracking imported entities by backward_compatibility reference
 */
export interface ITracker {
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
   * Set a backward_compatibility to UUID mapping directly
   * Used for simple tracking scenarios
   */
  set(backwardCompatibility: string, uuid: string): void;

  /**
   * Get all entities of a specific type
   */
  getByType(entityType: EntityType): ImportedEntity[];

  /**
   * Get import statistics
   */
  getStats(): Record<EntityType, number>;

  /**
   * Get all tracked entities
   */
  getAll(): ImportedEntity[];

  /**
   * Clear all tracked entities
   */
  clear(): void;

  /**
   * Set metadata value
   */
  setMetadata(key: string, value: string): void;

  /**
   * Get metadata value
   */
  getMetadata(key: string): string | null;
}

/**
 * Unified Tracker Implementation
 *
 * Provides a consistent tracking interface that works with both
 * API and SQL import strategies.
 */
export class UnifiedTracker implements ITracker {
  private entities = new Map<string, ImportedEntity>();
  private metadata = new Map<string, string>();

  register(entity: ImportedEntity): void {
    this.entities.set(entity.backwardCompatibility, entity);
  }

  exists(backwardCompatibility: string): boolean {
    return this.entities.has(backwardCompatibility);
  }

  getUuid(backwardCompatibility: string): string | null {
    return this.entities.get(backwardCompatibility)?.uuid ?? null;
  }

  set(backwardCompatibility: string, uuid: string): void {
    // If entity doesn't exist, create a minimal entry
    if (!this.entities.has(backwardCompatibility)) {
      this.entities.set(backwardCompatibility, {
        uuid,
        backwardCompatibility,
        entityType: 'item', // Default type for simple set operations
        createdAt: new Date(),
      });
    } else {
      // Update existing entity's UUID
      const entity = this.entities.get(backwardCompatibility)!;
      entity.uuid = uuid;
    }
  }

  getByType(entityType: EntityType): ImportedEntity[] {
    return Array.from(this.entities.values()).filter((e) => e.entityType === entityType);
  }

  getStats(): Record<EntityType, number> {
    const stats: Record<EntityType, number> = {
      language: 0,
      language_translation: 0,
      country: 0,
      country_translation: 0,
      context: 0,
      collection: 0,
      project: 0,
      partner: 0,
      partner_translation: 0,
      item: 0,
      item_translation: 0,
      image: 0,
      tag: 0,
      author: 0,
      artist: 0,
    };

    for (const entity of this.entities.values()) {
      if (entity.entityType in stats) {
        stats[entity.entityType]++;
      }
    }

    return stats;
  }

  getAll(): ImportedEntity[] {
    return Array.from(this.entities.values());
  }

  clear(): void {
    this.entities.clear();
    this.metadata.clear();
  }

  setMetadata(key: string, value: string): void {
    this.metadata.set(key, value);
  }

  getMetadata(key: string): string | null {
    return this.metadata.get(key) ?? null;
  }

  /**
   * Get the number of tracked entities
   */
  get size(): number {
    return this.entities.size;
  }
}
