/**
 * Tests for isZeroDate and sanitizeAllStrings zero-date handling
 */

import { describe, it, expect } from 'vitest';
import { isZeroDate, sanitizeAllStrings } from '../../src/utils/html-to-markdown.js';

describe('isZeroDate', () => {
  it('should detect 0000-00-00 as zero date', () => {
    expect(isZeroDate('0000-00-00')).toBe(true);
  });

  it('should detect 0000-00-00 00:00:00 as zero date', () => {
    expect(isZeroDate('0000-00-00 00:00:00')).toBe(true);
  });

  it('should detect 0000-00-00T00:00:00 as zero date', () => {
    expect(isZeroDate('0000-00-00T00:00:00')).toBe(true);
  });

  it('should not flag a valid date', () => {
    expect(isZeroDate('2025-04-17')).toBe(false);
  });

  it('should not flag a valid datetime', () => {
    expect(isZeroDate('2025-04-17 12:00:00')).toBe(false);
  });

  it('should not flag a regular string', () => {
    expect(isZeroDate('hello')).toBe(false);
  });

  it('should not flag an empty string', () => {
    expect(isZeroDate('')).toBe(false);
  });
});

describe('sanitizeAllStrings zero-date handling', () => {
  it('should convert zero-date string fields to null', () => {
    const data = {
      id: 'abc',
      name: 'Test Project',
      launch_date: '0000-00-00 00:00:00',
      is_enabled: true,
    };

    const result = sanitizeAllStrings(data);

    expect(result.launch_date).toBeNull();
    expect(result.name).toBe('Test Project');
    expect(result.id).toBe('abc');
    expect(result.is_enabled).toBe(true);
  });

  it('should preserve valid date strings', () => {
    const data = {
      launch_date: '2025-04-17 12:00:00',
    };

    const result = sanitizeAllStrings(data);

    expect(result.launch_date).toBe('2025-04-17 12:00:00');
  });

  it('should handle multiple zero-date fields', () => {
    const data = {
      start_date: '0000-00-00',
      end_date: '0000-00-00 00:00:00',
      name: 'Test',
    };

    const result = sanitizeAllStrings(data);

    expect(result.start_date).toBeNull();
    expect(result.end_date).toBeNull();
    expect(result.name).toBe('Test');
  });

  it('should not affect null or non-string fields', () => {
    const data = {
      launch_date: null as string | null,
      count: 42,
    };

    const result = sanitizeAllStrings(data);

    expect(result.launch_date).toBeNull();
    expect(result.count).toBe(42);
  });
});
