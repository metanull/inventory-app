"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const vitest_1 = require("vitest");
const BackwardCompatibilityTracker_js_1 = require("../../src/utils/BackwardCompatibilityTracker.js");
(0, vitest_1.describe)('BackwardCompatibilityTracker', () => {
    let tracker;
    (0, vitest_1.beforeEach)(() => {
        tracker = new BackwardCompatibilityTracker_js_1.BackwardCompatibilityTracker();
    });
    (0, vitest_1.describe)('register', () => {
        (0, vitest_1.it)('should register new entity', () => {
            const entity = {
                uuid: 'test-uuid-1',
                backwardCompatibility: 'mwnf3:projects:vm',
                entityType: 'context',
                createdAt: new Date(),
            };
            tracker.register(entity);
            (0, vitest_1.expect)(tracker.exists('mwnf3:projects:vm')).toBe(true);
        });
        (0, vitest_1.it)('should overwrite existing entity with same backward_compatibility', () => {
            const entity1 = {
                uuid: 'uuid-1',
                backwardCompatibility: 'mwnf3:projects:vm',
                entityType: 'context',
                createdAt: new Date(),
            };
            const entity2 = {
                uuid: 'uuid-2',
                backwardCompatibility: 'mwnf3:projects:vm',
                entityType: 'context',
                createdAt: new Date(),
            };
            tracker.register(entity1);
            tracker.register(entity2);
            (0, vitest_1.expect)(tracker.getUuid('mwnf3:projects:vm')).toBe('uuid-2');
        });
    });
    (0, vitest_1.describe)('exists', () => {
        (0, vitest_1.it)('should return true for registered entity', () => {
            tracker.register({
                uuid: 'test-uuid',
                backwardCompatibility: 'mwnf3:projects:vm',
                entityType: 'context',
                createdAt: new Date(),
            });
            (0, vitest_1.expect)(tracker.exists('mwnf3:projects:vm')).toBe(true);
        });
        (0, vitest_1.it)('should return false for non-registered entity', () => {
            (0, vitest_1.expect)(tracker.exists('mwnf3:projects:unknown')).toBe(false);
        });
    });
    (0, vitest_1.describe)('getUuid', () => {
        (0, vitest_1.it)('should return UUID for registered entity', () => {
            tracker.register({
                uuid: 'test-uuid-123',
                backwardCompatibility: 'mwnf3:projects:vm',
                entityType: 'context',
                createdAt: new Date(),
            });
            (0, vitest_1.expect)(tracker.getUuid('mwnf3:projects:vm')).toBe('test-uuid-123');
        });
        (0, vitest_1.it)('should return null for non-registered entity', () => {
            (0, vitest_1.expect)(tracker.getUuid('mwnf3:projects:unknown')).toBeNull();
        });
    });
    (0, vitest_1.describe)('getByType', () => {
        (0, vitest_1.it)('should return all entities of specific type', () => {
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
            (0, vitest_1.expect)(contexts).toHaveLength(2);
            (0, vitest_1.expect)(contexts.every((e) => e.entityType === 'context')).toBe(true);
        });
        (0, vitest_1.it)('should return empty array for type with no entities', () => {
            const images = tracker.getByType('image');
            (0, vitest_1.expect)(images).toHaveLength(0);
        });
    });
    (0, vitest_1.describe)('getStats', () => {
        (0, vitest_1.it)('should return counts for all entity types', () => {
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
            (0, vitest_1.expect)(stats).toEqual({
                context: 1,
                collection: 1,
                partner: 0,
                item: 2,
                image: 0,
            });
        });
    });
    (0, vitest_1.describe)('clear', () => {
        (0, vitest_1.it)('should remove all tracked entities', () => {
            tracker.register({
                uuid: 'test',
                backwardCompatibility: 'mwnf3:projects:vm',
                entityType: 'context',
                createdAt: new Date(),
            });
            tracker.clear();
            (0, vitest_1.expect)(tracker.exists('mwnf3:projects:vm')).toBe(false);
            (0, vitest_1.expect)(tracker.getStats().context).toBe(0);
        });
    });
});
//# sourceMappingURL=BackwardCompatibilityTracker.test.js.map