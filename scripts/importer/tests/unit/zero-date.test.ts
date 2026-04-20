/**
 * Tests for sanitizeDateValue — explicit date sanitization for legacy MySQL values.
 *
 * mysql2 returns DATETIME columns as Date objects. Legacy MySQL on Windows
 * stored '0000-00-00 00:00:00' as a default, which becomes Invalid Date.
 * MySQL 8+ with STRICT mode rejects zero-dates on INSERT.
 */

import { describe, it, expect } from 'vitest';
import { sanitizeDateValue } from '../../src/utils/html-to-markdown.js';

describe('sanitizeDateValue', () => {
  it('should return null for null', () => {
    expect(sanitizeDateValue(null)).toBeNull();
  });

  it('should return null for undefined', () => {
    expect(sanitizeDateValue(undefined)).toBeNull();
  });

  it('should return null for empty string', () => {
    expect(sanitizeDateValue('')).toBeNull();
  });

  it('should return null for whitespace-only string', () => {
    expect(sanitizeDateValue('   ')).toBeNull();
  });

  it('should return null for zero-date string 0000-00-00', () => {
    expect(sanitizeDateValue('0000-00-00')).toBeNull();
  });

  it('should return null for zero-datetime string 0000-00-00 00:00:00', () => {
    expect(sanitizeDateValue('0000-00-00 00:00:00')).toBeNull();
  });

  it('should return null for zero-datetime with T separator', () => {
    expect(sanitizeDateValue('0000-00-00T00:00:00')).toBeNull();
  });

  it('should return null for Invalid Date object (from mysql2 zero-date)', () => {
    const invalidDate = new Date('0000-00-00 00:00:00');
    expect(invalidDate.toString()).toBe('Invalid Date');
    expect(sanitizeDateValue(invalidDate)).toBeNull();
  });

  it('should return null for explicitly constructed Invalid Date', () => {
    expect(sanitizeDateValue(new Date(NaN))).toBeNull();
  });

  it('should convert valid Date object to ISO-like string', () => {
    const date = new Date('2025-04-17T12:00:00Z');
    const result = sanitizeDateValue(date);
    expect(result).toBe('2025-04-17 12:00:00');
  });

  it('should preserve valid date string', () => {
    expect(sanitizeDateValue('2025-04-17')).toBe('2025-04-17');
  });

  it('should preserve valid datetime string', () => {
    expect(sanitizeDateValue('2025-04-17 12:00:00')).toBe('2025-04-17 12:00:00');
  });

  it('should trim whitespace from valid date strings', () => {
    expect(sanitizeDateValue('  2025-04-17  ')).toBe('2025-04-17');
  });
});
