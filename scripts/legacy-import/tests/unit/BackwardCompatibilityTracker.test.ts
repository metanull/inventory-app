import { describe, it, expect, beforeEach } from 'vitest';
import { BackwardCompatibilityTracker } from '../../src/utils/BackwardCompatibilityTracker.js';

describe('BackwardCompatibilityTracker', () => {
  let tracker: BackwardCompatibilityTracker;

  beforeEach(() => {
    tracker = new BackwardCompatibilityTracker();
  });

  describe('register', () => {
    it('should register new entity', () => {
      const entity = {
        uuid: 'test-uuid-1',
        backwardCompatibility: 'mwnf3:projects:vm',
        entityType: 'context' as const,
        createdAt: new Date(),
      };

      tracker.register(entity);

      expect(tracker.exists('mwnf3:projects:vm')).toBe(true);
    });

    it('should overwrite existing entity with same backward_compatibility', () => {
      const entity1 = {
        uuid: 'uuid-1',
        backwardCompatibility: 'mwnf3:projects:vm',
        entityType: 'context' as const,
        createdAt: new Date(),
      };
      const entity2 = {
        uuid: 'uuid-2',
        backwardCompatibility: 'mwnf3:projects:vm',
        entityType: 'context' as const,
        createdAt: new Date(),
      };

      tracker.register(entity1);
      tracker.register(entity2);

      expect(tracker.getUuid('mwnf3:projects:vm')).toBe('uuid-2');
    });
  });

  describe('exists', () => {
    it('should return true for registered entity', () => {
      tracker.register({
        uuid: 'test-uuid',
        backwardCompatibility: 'mwnf3:projects:vm',
        entityType: 'context',
        createdAt: new Date(),
      });

      expect(tracker.exists('mwnf3:projects:vm')).toBe(true);
    });

    it('should return false for non-registered entity', () => {
      expect(tracker.exists('mwnf3:projects:unknown')).toBe(false);
    });
  });

  describe('getUuid', () => {
    it('should return UUID for registered entity', () => {
      tracker.register({
        uuid: 'test-uuid-123',
        backwardCompatibility: 'mwnf3:projects:vm',
        entityType: 'context',
        createdAt: new Date(),
      });

      expect(tracker.getUuid('mwnf3:projects:vm')).toBe('test-uuid-123');
    });

    it('should return null for non-registered entity', () => {
      expect(tracker.getUuid('mwnf3:projects:unknown')).toBeNull();
    });
  });

  describe('getByType', () => {
    it('should return all entities of specific type', () => {
      tracker.register({
        uuid: 'context-1',
        backwardCompatibility: 'mwnf3:projects:vm',
        entityType: 'context',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: 'context-2',
        backwardCompatibility: 'sh:projects:sh1',
        entityType: 'context',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: 'item-1',
        backwardCompatibility: 'mwnf3:objects:vm:ma:louvre:001',
        entityType: 'item',
        createdAt: new Date(),
      });

      const contexts = tracker.getByType('context');

      expect(contexts).toHaveLength(2);
      expect(contexts.every((e) => e.entityType === 'context')).toBe(true);
    });

    it('should return empty array for type with no entities', () => {
      const images = tracker.getByType('image');
      expect(images).toHaveLength(0);
    });
  });

  describe('getStats', () => {
    it('should return counts for all entity types', () => {
      tracker.register({
        uuid: 'c1',
        backwardCompatibility: 'mwnf3:projects:vm',
        entityType: 'context',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: 'c2',
        backwardCompatibility: 'sh:projects:sh1',
        entityType: 'collection',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: 'i1',
        backwardCompatibility: 'mwnf3:objects:001',
        entityType: 'item',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: 'i2',
        backwardCompatibility: 'mwnf3:objects:002',
        entityType: 'item',
        createdAt: new Date(),
      });

      const stats = tracker.getStats();

      expect(stats).toEqual({
        context: 1,
        collection: 1,
        partner: 0,
        item: 2,
        image: 0,
        project: 0,
      });
    });
  });

  describe('clear', () => {
    it('should remove all tracked entities', () => {
      tracker.register({
        uuid: 'test',
        backwardCompatibility: 'mwnf3:projects:vm',
        entityType: 'context',
        createdAt: new Date(),
      });

      tracker.clear();

      expect(tracker.exists('mwnf3:projects:vm')).toBe(false);
      expect(tracker.getStats().context).toBe(0);
    });
  });
});
