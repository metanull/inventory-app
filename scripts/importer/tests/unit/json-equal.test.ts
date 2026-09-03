import { describe, expect, it } from 'vitest';
import { jsonValuesEqual } from '../../src/utils/json-equal.js';

describe('jsonValuesEqual', () => {
  it('is true for identical primitives and structures', () => {
    expect(jsonValuesEqual('a', 'a')).toBe(true);
    expect(jsonValuesEqual(1, 1)).toBe(true);
    expect(jsonValuesEqual(null, null)).toBe(true);
    expect(jsonValuesEqual({ a: 1, b: 2 }, { a: 1, b: 2 })).toBe(true);
  });

  it('is true when key order differs — the whole point', () => {
    // Exactly what a MySQL JSON column round-trip produces: same content,
    // keys in a different order than they were inserted with.
    expect(
      jsonValuesEqual(
        { history: 'x', architects: 'y', external_sources: 'z' },
        { external_sources: 'z', history: 'x', architects: 'y' }
      )
    ).toBe(true);
  });

  it('is true for nested objects with differently-ordered keys', () => {
    expect(
      jsonValuesEqual(
        { monument_contact: { phone: '1', email: 'a' } },
        { monument_contact: { email: 'a', phone: '1' } }
      )
    ).toBe(true);
  });

  it('detects a real value difference', () => {
    expect(jsonValuesEqual({ history: 'x' }, { history: 'y' })).toBe(false);
  });

  it('detects a missing or extra key', () => {
    expect(jsonValuesEqual({ a: 1 }, { a: 1, b: 2 })).toBe(false);
    expect(jsonValuesEqual({ a: 1, b: 2 }, { a: 1 })).toBe(false);
  });

  it('compares arrays by position, not as sets', () => {
    expect(jsonValuesEqual([1, 2], [2, 1])).toBe(false);
    expect(jsonValuesEqual([1, 2], [1, 2])).toBe(true);
  });

  it('handles empty objects', () => {
    expect(jsonValuesEqual({}, {})).toBe(true);
  });
});
