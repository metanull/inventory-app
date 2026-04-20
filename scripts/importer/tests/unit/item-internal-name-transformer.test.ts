import { describe, expect, it } from 'vitest';

import { selectItemInternalName } from '../../src/domain/transformers/item-internal-name-transformer.js';

describe('selectItemInternalName', () => {
  it('uses the default language translation when it has a name', () => {
    const result = selectItemInternalName(
      [
        { languageId: 'fra', value: 'Nom francais' },
        { languageId: 'eng', value: 'English Name' },
      ],
      'eng',
      'Travels monument',
      'mwnf3_travels:monument:IAM:pt:1:V:1:a'
    );

    expect(result.internalName).toBe('English Name');
    expect(result.warning).toBeNull();
  });

  it('falls back to the first named translation with an explicit warning', () => {
    const result = selectItemInternalName(
      [
        { languageId: 'eng', value: null },
        { languageId: 'fra', value: 'Nom francais' },
        { languageId: 'ita', value: 'Nome italiano' },
      ],
      'eng',
      'Explore monument',
      'mwnf3_explore:monument:123'
    );

    expect(result.internalName).toBe('Nom francais');
    expect(result.warning).toBe(
      'Explore monument mwnf3_explore:monument:123 has no translation with a name in default language eng, using fra instead'
    );
  });

  it('throws when no translation provides a usable name', () => {
    expect(() =>
      selectItemInternalName(
        [
          { languageId: 'eng', value: null },
          { languageId: 'fra', value: '   ' },
        ],
        'eng',
        'Object',
        'mwnf3:objects:EPM:eg:cairo:001'
      )
    ).toThrow(
      'Object mwnf3:objects:EPM:eg:cairo:001 missing required name field in all translations'
    );
  });
});
