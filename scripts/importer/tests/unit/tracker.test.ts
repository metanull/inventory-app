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

      expect(tracker.exists('mwnf3:langs:en')).toBe(true);
      expect(tracker.getUuid('mwnf3:langs:en')).toBe('test-uuid');
    });
  });

  describe('exists', () => {
    it('should return false for non-existent entity', () => {
      expect(tracker.exists('mwnf3:langs:xx')).toBe(false);
    });

    it('should return true for registered entity', () => {
      tracker.register({
        uuid: 'test-uuid',
        backwardCompatibility: 'mwnf3:langs:en',
        entityType: 'language',
        createdAt: new Date(),
      });

      expect(tracker.exists('mwnf3:langs:en')).toBe(true);
    });
  });

  describe('getUuid', () => {
    it('should return null for non-existent entity', () => {
      expect(tracker.getUuid('mwnf3:langs:xx')).toBeNull();
    });

    it('should return uuid for registered entity', () => {
      tracker.register({
        uuid: 'test-uuid',
        backwardCompatibility: 'mwnf3:langs:en',
        entityType: 'language',
        createdAt: new Date(),
      });

      expect(tracker.getUuid('mwnf3:langs:en')).toBe('test-uuid');
    });
  });

  describe('set', () => {
    it('should create a new entry if not exists', () => {
      tracker.set('mwnf3:langs:en', 'test-uuid');

      expect(tracker.exists('mwnf3:langs:en')).toBe(true);
      expect(tracker.getUuid('mwnf3:langs:en')).toBe('test-uuid');
    });

    it('should update existing entry uuid', () => {
      tracker.register({
        uuid: 'old-uuid',
        backwardCompatibility: 'mwnf3:langs:en',
        entityType: 'language',
        createdAt: new Date(),
      });

      tracker.set('mwnf3:langs:en', 'new-uuid');

      expect(tracker.getUuid('mwnf3:langs:en')).toBe('new-uuid');
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
      expect(tracker.exists('bc1')).toBe(false);
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
