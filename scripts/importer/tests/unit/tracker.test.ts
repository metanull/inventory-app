/**
 * Tests for UnifiedTracker
 */

import { describe, it, expect, beforeEach } from 'vitest';
import { UnifiedTracker } from '../../src/core/tracker.js';

describe('UnifiedTracker', () => {
  let tracker: UnifiedTracker;

  beforeEach(() => {
    tracker = new UnifiedTracker();
  });

  describe('register', () => {
    it('should register an entity', () => {
      tracker.register({
        uuid: 'test-uuid',
        backwardCompatibility: 'mwnf3:langs:en',
        entityType: 'language',
        createdAt: new Date(),
      });

      expect(tracker.exists('mwnf3:langs:en', 'language')).toBe(true);
      expect(tracker.getUuid('mwnf3:langs:en', 'language')).toBe('test-uuid');
    });
  });

  describe('exists', () => {
    it('should return false for non-existent entity', () => {
      expect(tracker.exists('mwnf3:langs:xx', 'language')).toBe(false);
    });

    it('should return true for registered entity', () => {
      tracker.register({
        uuid: 'test-uuid',
        backwardCompatibility: 'mwnf3:langs:en',
        entityType: 'language',
        createdAt: new Date(),
      });

      expect(tracker.exists('mwnf3:langs:en', 'language')).toBe(true);
    });
  });

  describe('getUuid', () => {
    it('should return null for non-existent entity', () => {
      expect(tracker.getUuid('mwnf3:langs:xx', 'language')).toBeNull();
    });

    it('should return uuid for registered entity', () => {
      tracker.register({
        uuid: 'test-uuid',
        backwardCompatibility: 'mwnf3:langs:en',
        entityType: 'language',
        createdAt: new Date(),
      });

      expect(tracker.getUuid('mwnf3:langs:en', 'language')).toBe('test-uuid');
    });
  });

  // #1534. Legacy spells the same reference inconsistently — mwnf3.monuments_pictures
  // holds both `isl` and `ISL`, mwnf3_travels both `iam` and `IAM` — and importers
  // write the legacy spelling verbatim. Resolvers (thg-theme-item-resolver.ts) are
  // therefore allowed to emit either casing, which only works because lookups here
  // fold case. This is a contract, not an incidental detail: keys differing only by
  // case denote the same entity, matching the utf8mb4_unicode_ci column the DB
  // fallback in SqlWriteStrategy queries.
  describe('case-insensitive keys', () => {
    it('finds an entity registered lower-case through an upper-case lookup', () => {
      tracker.register({
        uuid: 'picture-uuid',
        backwardCompatibility: 'mwnf3:monuments_pictures:bar:hu:Mon11:10:1',
        entityType: 'item',
        createdAt: new Date(),
      });

      expect(tracker.getUuid('mwnf3:monuments_pictures:BAR:hu:Mon11:10:1', 'item')).toBe(
        'picture-uuid'
      );
      expect(tracker.exists('mwnf3:monuments_pictures:BAR:hu:Mon11:10:1', 'item')).toBe(true);
    });

    it('finds an entity registered upper-case through a lower-case lookup', () => {
      tracker.register({
        uuid: 'travels-uuid',
        backwardCompatibility: 'mwnf3_travels:monument_picture:IAM:pa:1:I:1:c:_:12',
        entityType: 'item',
        createdAt: new Date(),
      });

      expect(tracker.getUuid('mwnf3_travels:monument_picture:iam:pa:1:i:1:c:_:12', 'item')).toBe(
        'travels-uuid'
      );
    });

    it('treats set() and getUuid() as the same key regardless of casing', () => {
      tracker.set('mwnf3:objects_pictures:EPM:de:Mus21:2:1', 'object-uuid', 'item');

      expect(tracker.getUuid('mwnf3:objects_pictures:epm:de:mus21:2:1', 'item')).toBe('object-uuid');
      expect(tracker.getAll()).toHaveLength(1);
    });

    it('still separates entities of different types that share a key', () => {
      tracker.set('mwnf3_thematic_gallery:thg_gallery:9', 'collection-uuid', 'collection');
      tracker.set('mwnf3_thematic_gallery:thg_gallery:9', 'context-uuid', 'context');

      expect(tracker.getUuid('mwnf3_thematic_gallery:thg_gallery:9', 'collection')).toBe(
        'collection-uuid'
      );
      expect(tracker.getUuid('mwnf3_thematic_gallery:thg_gallery:9', 'context')).toBe(
        'context-uuid'
      );
    });
  });

  describe('set', () => {
    it('should create a new entry if not exists', () => {
      tracker.set('mwnf3:langs:en', 'test-uuid', 'language');

      expect(tracker.exists('mwnf3:langs:en', 'language')).toBe(true);
      expect(tracker.getUuid('mwnf3:langs:en', 'language')).toBe('test-uuid');
    });

    it('should update existing entry uuid', () => {
      tracker.register({
        uuid: 'old-uuid',
        backwardCompatibility: 'mwnf3:langs:en',
        entityType: 'language',
        createdAt: new Date(),
      });

      tracker.set('mwnf3:langs:en', 'new-uuid', 'language');

      expect(tracker.getUuid('mwnf3:langs:en', 'language')).toBe('new-uuid');
    });
  });

  describe('getByType', () => {
    it('should return empty array for no entities', () => {
      expect(tracker.getByType('language')).toEqual([]);
    });

    it('should return entities of specific type', () => {
      tracker.register({
        uuid: 'lang-uuid',
        backwardCompatibility: 'mwnf3:langs:en',
        entityType: 'language',
        createdAt: new Date(),
      });

      tracker.register({
        uuid: 'country-uuid',
        backwardCompatibility: 'mwnf3:countries:fr',
        entityType: 'country',
        createdAt: new Date(),
      });

      const languages = tracker.getByType('language');
      expect(languages.length).toBe(1);
      expect(languages[0]?.uuid).toBe('lang-uuid');
    });
  });

  describe('getStats', () => {
    it('should return zero counts for empty tracker', () => {
      const stats = tracker.getStats();
      expect(stats.language).toBe(0);
      expect(stats.country).toBe(0);
    });

    it('should count entities by type', () => {
      tracker.register({
        uuid: 'lang1',
        backwardCompatibility: 'mwnf3:langs:en',
        entityType: 'language',
        createdAt: new Date(),
      });

      tracker.register({
        uuid: 'lang2',
        backwardCompatibility: 'mwnf3:langs:fr',
        entityType: 'language',
        createdAt: new Date(),
      });

      tracker.register({
        uuid: 'country1',
        backwardCompatibility: 'mwnf3:countries:fr',
        entityType: 'country',
        createdAt: new Date(),
      });

      const stats = tracker.getStats();
      expect(stats.language).toBe(2);
      expect(stats.country).toBe(1);
    });
  });

  describe('getAll', () => {
    it('should return all registered entities', () => {
      tracker.register({
        uuid: 'uuid1',
        backwardCompatibility: 'bc1',
        entityType: 'language',
        createdAt: new Date(),
      });

      tracker.register({
        uuid: 'uuid2',
        backwardCompatibility: 'bc2',
        entityType: 'country',
        createdAt: new Date(),
      });

      const all = tracker.getAll();
      expect(all.length).toBe(2);
    });
  });

  describe('clear', () => {
    it('should remove all entities', () => {
      tracker.register({
        uuid: 'uuid1',
        backwardCompatibility: 'bc1',
        entityType: 'language',
        createdAt: new Date(),
      });

      tracker.clear();

      expect(tracker.getAll().length).toBe(0);
      expect(tracker.exists('bc1', 'language')).toBe(false);
    });
  });

  describe('size', () => {
    it('should return the number of tracked entities', () => {
      expect(tracker.size).toBe(0);

      tracker.register({
        uuid: 'uuid1',
        backwardCompatibility: 'bc1',
        entityType: 'language',
        createdAt: new Date(),
      });

      expect(tracker.size).toBe(1);
    });
  });
});
