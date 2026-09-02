/**
 * `extra` holds a JSON document, and its texts are legacy content like any
 * other.
 *
 * The catch-all sanitiser at the persistence layer used to skip the field
 * outright — converting a JSON string as a whole would have destroyed it — so
 * nothing inside it was ever converted. Three published websites carried the
 * result: 470 `<i>` pairs in Sharing History's curator justifications, `<p>`
 * and `<span style>` in Water in Islam's exhibition texts, `<br/>` in The Use
 * of Colours in Art. The viewers rendered that HTML, which is what kept it
 * invisible.
 *
 * These tests pin the field being entered rather than skipped, and pin the
 * reasons it was skipped in the first place: the document must survive intact.
 */

import { describe, it, expect } from 'vitest';
import { sanitizeAllStrings, sanitizeJsonField } from '../../src/utils/html-to-markdown.js';

describe('the texts inside a JSON field', () => {
  it('converts a string nested anywhere in the document', () => {
    const extra = JSON.stringify({
      justifications: {
        en: { curator: 'Walter Duncan’s <i>Fantasy in Egyptian Gallery</i>.', partner: null },
      },
    });

    const decoded = JSON.parse(sanitizeJsonField(extra));

    expect(decoded.justifications.en.curator).toBe(
      'Walter Duncan’s *Fantasy in Egyptian Gallery*.'
    );
    expect(decoded.justifications.en.partner).toBeNull();
  });

  it('converts strings inside an array', () => {
    const extra = JSON.stringify({ notes: ['<b>One</b>', 'Two'] });

    expect(JSON.parse(sanitizeJsonField(extra)).notes).toEqual(['**One**', 'Two']);
  });

  it('leaves the shape, the keys and every non-string value alone', () => {
    const extra = JSON.stringify({
      curator_status: 'Y',
      count: 3,
      enabled: true,
      missing: null,
      nested: { path: 'images/2024/plate-01.jpg' },
    });

    expect(JSON.parse(sanitizeJsonField(extra))).toEqual({
      curator_status: 'Y',
      count: 3,
      enabled: true,
      missing: null,
      nested: { path: 'images/2024/plate-01.jpg' },
    });
  });

  it('returns a value that is not JSON untouched', () => {
    // Mangling it here would hide the real problem, which is that a field
    // declared as JSON is not holding JSON.
    expect(sanitizeJsonField('not json at all')).toBe('not json at all');
    expect(sanitizeJsonField('"just a string"')).toBe('"just a string"');
  });

  it('is idempotent, so a re-import converts nothing twice', () => {
    const once = sanitizeJsonField(JSON.stringify({ text: 'An <i>italic</i> title.' }));

    expect(sanitizeJsonField(once)).toBe(once);
  });
});

describe('extra as an object, which is how most callers build it', () => {
  // The first version of this fix handled only the encoded string. Every write
  // that matters hands over an object and lets the strategy stringify it, so
  // nothing was converted and 367 `<i>` tags survived the reimport.
  it('converts the strings inside an object, and returns an object', () => {
    const extra = sanitizeJsonField({
      justifications: { en: { curator: '<i>Aeneid</i>', partner: null } },
      curator_status: 'Y',
      display_order: 3,
    });

    expect(extra).toEqual({
      justifications: { en: { curator: '*Aeneid*', partner: null } },
      curator_status: 'Y',
      display_order: 3,
    });
  });

  it('is reached through the persistence-layer sanitiser', () => {
    const row = sanitizeAllStrings({
      collection_id: 'c1',
      extra: { justifications: { en: { curator: 'A <b>bold</b> claim' } } },
    });

    expect(row.extra).toEqual({ justifications: { en: { curator: 'A **bold** claim' } } });
  });

  it('leaves null and undefined alone', () => {
    expect(sanitizeJsonField(null)).toBeNull();
    expect(sanitizeJsonField(undefined)).toBeUndefined();
    expect(sanitizeAllStrings({ extra: null }).extra).toBeNull();
  });
});

describe('the sanitiser at the persistence layer', () => {
  it('enters extra instead of skipping it, and still converts plain fields', () => {
    const row = sanitizeAllStrings({
      name: 'A <b>bold</b> name',
      extra: JSON.stringify({ justifications: { en: { curator: '<i>Title</i>' } } }),
    });

    expect(row.name).toBe('A **bold** name');
    expect(JSON.parse(row.extra).justifications.en.curator).toBe('*Title*');
  });

  it('keeps extra a valid JSON document', () => {
    const row = sanitizeAllStrings({
      extra: JSON.stringify({ a: '<p>One</p>', b: [1, 2], c: { d: null } }),
    });

    expect(() => JSON.parse(row.extra)).not.toThrow();
    expect(JSON.parse(row.extra)).toEqual({ a: 'One', b: [1, 2], c: { d: null } });
  });
});
