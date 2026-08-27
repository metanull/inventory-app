/**
 * Unit tests for bitToBoolean (gap G4) and normalizeSerializedBitBuffers.
 *
 * MySQL bit(1) columns arrive from mysql2 as Buffers; storing one in an `extra`
 * JSON field serialises it as {"type":"Buffer","data":[0]} instead of false.
 * bitToBoolean guards the write paths; normalizeSerializedBitBuffers repairs
 * the rows written before they were guarded.
 */

import { describe, expect, it } from 'vitest';

import { bitToBoolean, normalizeSerializedBitBuffers } from '../../src/utils/legacy-values.js';

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

describe('normalizeSerializedBitBuffers', () => {
  /** The exact shape found in collection_translations.extra for gallery 47. */
  const galleryExtra = {
    thg_gallery: {
      link: 'the_use_of_colours_in_art',
      status: 'A',
      has_timeline: { data: [1], type: 'Buffer' },
      has_country_timeline: { data: [0], type: 'Buffer' },
    },
    exhibition_i18n: { enabled: 'Y' },
  };

  it('converts serialized single-byte Buffers to JSON booleans in place', () => {
    const normalized = normalizeSerializedBitBuffers(galleryExtra) as Record<string, unknown>;

    expect(normalized.thg_gallery).toEqual({
      link: 'the_use_of_colours_in_art',
      status: 'A',
      has_timeline: true,
      has_country_timeline: false,
    });
    expect(JSON.stringify(normalized)).not.toContain('Buffer');
  });

  it('leaves the input untouched — it returns a new object', () => {
    normalizeSerializedBitBuffers(galleryExtra);

    expect(galleryExtra.thg_gallery.has_timeline).toEqual({ data: [1], type: 'Buffer' });
  });

  it('preserves the non-Buffer branches of the document', () => {
    const normalized = normalizeSerializedBitBuffers(galleryExtra) as Record<string, unknown>;

    expect(normalized.exhibition_i18n).toEqual({ enabled: 'Y' });
  });

  it('returns the input itself when nothing needed converting', () => {
    const clean = { thg_gallery: { has_timeline: true, status: 'A' } };

    expect(normalizeSerializedBitBuffers(clean)).toBe(clean);
  });

  it('leaves multi-byte Buffers alone — those are real binary data, not bit(1)', () => {
    const binary = { blob: { data: [1, 2, 3], type: 'Buffer' } };

    expect(normalizeSerializedBitBuffers(binary)).toBe(binary);
  });

  it('normalises Buffers nested inside arrays', () => {
    const nested = { flags: [{ data: [1], type: 'Buffer' }, { data: [0], type: 'Buffer' }] };

    expect(normalizeSerializedBitBuffers(nested)).toEqual({ flags: [true, false] });
  });

  it('ignores objects that merely mention Buffer without the shape', () => {
    const decoy = { note: 'Buffer', type: 'Buffer' };

    expect(normalizeSerializedBitBuffers(decoy)).toBe(decoy);
  });

  it('passes scalars and null through unchanged', () => {
    expect(normalizeSerializedBitBuffers('Buffer')).toBe('Buffer');
    expect(normalizeSerializedBitBuffers(null)).toBeNull();
    expect(normalizeSerializedBitBuffers(7)).toBe(7);
  });
});
