import { describe, expect, it } from 'vitest';

import {
  isBlankMonumentDetailGroup,
  transformMonumentDetail,
} from '../../src/domain/transformers/monument-detail-transformer.js';
import type { LegacyMonumentDetail, MonumentDetailGroup } from '../../src/domain/types/index.js';

const translation = (overrides: Partial<LegacyMonumentDetail> = {}): LegacyMonumentDetail => ({
  project_id: 'BAR',
  country_id: 'it',
  institution_id: 'Mon11',
  monument_id: '12',
  lang_id: 'en',
  detail_id: '3',
  // The legacy schema declares `name` NOT NULL DEFAULT '', so an untouched slot
  // is stored as empty strings rather than NULLs.
  name: '',
  description: '',
  location: undefined,
  date: '',
  artist: '',
  ...overrides,
});

const group = (translations: LegacyMonumentDetail[]): MonumentDetailGroup => ({
  project_id: 'BAR',
  country_id: 'it',
  institution_id: 'Mon11',
  monument_id: '12',
  detail_id: '3',
  translations,
});

describe('isBlankMonumentDetailGroup', () => {
  it('recognises an unused detail slot as blank', () => {
    expect(isBlankMonumentDetailGroup(group([translation()]))).toBe(true);
  });

  it('recognises a slot left blank in every language version', () => {
    expect(
      isBlankMonumentDetailGroup(
        group([translation({ lang_id: 'en' }), translation({ lang_id: 'de' })])
      )
    ).toBe(true);
  });

  it('treats whitespace-only and NULL values as blank', () => {
    expect(
      isBlankMonumentDetailGroup(
        group([
          translation({
            name: '   ',
            description: '\n\t ',
            location: undefined,
            date: '  ',
            artist: undefined,
          }),
        ])
      )
    ).toBe(true);
  });

  it('is not blank when a name is present', () => {
    expect(isBlankMonumentDetailGroup(group([translation({ name: 'Fassade' })]))).toBe(false);
  });

  it('is not blank when only one language version carries content', () => {
    expect(
      isBlankMonumentDetailGroup(
        group([translation({ lang_id: 'en' }), translation({ lang_id: 'de', name: 'Fassade' })])
      )
    ).toBe(false);
  });

  // The four cases below are the ones the predicate must NOT swallow: content
  // without a name is a real data problem, and has to keep failing loudly.
  it.each([
    ['description', { description: 'Die Fassade zeigt den Einfluss des Salzburger Doms.' }],
    ['location', { location: 'Äußere Nordseite' }],
    ['date', { date: '1627-1640' }],
    ['artist', { artist: 'Christoph Gumpp' }],
  ])('is not blank when the name is missing but %s is present', (_field, overrides) => {
    expect(isBlankMonumentDetailGroup(group([translation(overrides)]))).toBe(false);
  });

  it('does not classify a group without translations as blank', () => {
    // transformMonumentDetail is the one that reports this as an error.
    expect(isBlankMonumentDetailGroup(group([]))).toBe(false);
  });
});

describe('transformMonumentDetail', () => {
  it('still rejects a group that has content but no name in any language', () => {
    expect(() =>
      transformMonumentDetail(
        group([
          translation({ lang_id: 'en', description: 'A description with no name.' }),
          translation({ lang_id: 'de', artist: 'Christoph Gumpp' }),
        ]),
        'eng'
      )
    ).toThrow(
      'Monument detail mwnf3:monument_details:BAR:it:Mon11:12:3 missing required name field in all translations'
    );
  });

  it('falls back to another language when the default one has no name', () => {
    const result = transformMonumentDetail(
      group([
        translation({ lang_id: 'en' }),
        translation({ lang_id: 'de', name: 'Chor und Vierung' }),
      ]),
      'eng'
    );

    expect(result.data.internal_name).toBe('Chor und Vierung');
    expect(result.warning).toContain('using deu instead');
  });
});
