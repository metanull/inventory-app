/**
 * Tests for backward-compatibility utility functions
 */

import { describe, it, expect } from 'vitest';
import {
  formatBackwardCompatibility,
  parseBackwardCompatibility,
  formatDenormalizedBackwardCompatibility,
  formatImageBackwardCompatibility,
} from '../../src/utils/backward-compatibility.js';

describe('formatBackwardCompatibility', () => {
  it('should format a simple reference', () => {
    const result = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'langs',
      pkValues: ['en'],
    });
    expect(result).toBe('mwnf3:langs:en');
  });

  it('should format a reference with multiple pk values', () => {
    const result = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'museums',
      pkValues: ['louvre', 'fr'],
    });
    expect(result).toBe('mwnf3:museums:louvre:fr');
  });

  it('should handle numeric pk values', () => {
    const result = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'objects',
      pkValues: ['proj', 'country', 'museum', 123],
    });
    expect(result).toBe('mwnf3:objects:proj:country:museum:123');
  });
});

describe('parseBackwardCompatibility', () => {
  it('should parse a simple reference', () => {
    const result = parseBackwardCompatibility('mwnf3:langs:en');
    expect(result).toEqual({
      schema: 'mwnf3',
      table: 'langs',
      pkValues: ['en'],
    });
  });

  it('should parse a reference with multiple pk values', () => {
    const result = parseBackwardCompatibility('mwnf3:museums:louvre:fr');
    expect(result).toEqual({
      schema: 'mwnf3',
      table: 'museums',
      pkValues: ['louvre', 'fr'],
    });
  });

  it('should throw on invalid format', () => {
    expect(() => parseBackwardCompatibility('invalid')).toThrow(
      'Invalid backward_compatibility format'
    );
  });
});

describe('formatDenormalizedBackwardCompatibility', () => {
  it('should exclude language columns', () => {
    const result = formatDenormalizedBackwardCompatibility(
      'mwnf3',
      'objects',
      { project_id: 'proj', country: 'fr', museum_id: 'louvre', number: '001', lang: 'en' },
      ['lang']
    );
    expect(result).toBe('mwnf3:objects:proj:fr:louvre:001');
    expect(result).not.toContain('en');
  });

  it('should use default exclude columns', () => {
    const result = formatDenormalizedBackwardCompatibility('mwnf3', 'test', {
      id: '1',
      lang: 'en',
      language: 'English',
      language_id: 'eng',
    });
    expect(result).toBe('mwnf3:test:1');
  });
});

describe('formatImageBackwardCompatibility', () => {
  it('should append image index', () => {
    const result = formatImageBackwardCompatibility(
      'mwnf3',
      'objects_pictures',
      ['proj', 'fr', 'louvre', '001'],
      1
    );
    expect(result).toBe('mwnf3:objects_pictures:proj:fr:louvre:001:1');
  });
});
