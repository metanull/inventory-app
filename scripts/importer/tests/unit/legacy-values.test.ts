/**
 * Unit tests for bitToBoolean (gap G4).
 *
 * MySQL bit(1) columns arrive from mysql2 as Buffers; storing one in an `extra`
 * JSON field serialises it as {"type":"Buffer","data":[0]} instead of false.
 */

import { describe, expect, it } from 'vitest';

import { bitToBoolean } from '../../src/utils/legacy-values.js';

describe('bitToBoolean', () => {
  it('converts the Buffers mysql2 returns for bit(1) columns', () => {
    expect(bitToBoolean(Buffer.from([1]))).toBe(true);
    expect(bitToBoolean(Buffer.from([0]))).toBe(false);
  });

  it('keeps null and undefined distinguishable from false', () => {
    expect(bitToBoolean(null)).toBeNull();
    expect(bitToBoolean(undefined)).toBeNull();
    expect(bitToBoolean('')).toBeNull();
  });

  it('passes booleans through', () => {
    expect(bitToBoolean(true)).toBe(true);
    expect(bitToBoolean(false)).toBe(false);
  });

  it('converts the numeric and textual shapes a driver may return', () => {
    expect(bitToBoolean(1)).toBe(true);
    expect(bitToBoolean(0)).toBe(false);
    expect(bitToBoolean('1')).toBe(true);
    expect(bitToBoolean('0')).toBe(false);
  });

  it('serialises to a JSON boolean rather than a Buffer', () => {
    expect(JSON.stringify({ flag: bitToBoolean(Buffer.from([0])) })).toBe('{"flag":false}');
    expect(JSON.stringify({ flag: Buffer.from([0]) })).toContain('Buffer');
  });

  it('returns null for shapes it cannot interpret', () => {
    expect(bitToBoolean({})).toBeNull();
  });
});
