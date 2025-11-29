/**
 * Tests for Object Transformer
 */

import { describe, it, expect } from 'vitest';
import {
  groupObjectsByPK,
  transformObject,
  transformObjectTranslation,
  extractObjectTags,
  extractObjectArtists,
  parseTagString,
  planTranslations,
} from '../../src/domain/transformers/object-transformer.js';
import type { LegacyObject, ObjectGroup } from '../../src/domain/types/legacy.js';

describe('groupObjectsByPK', () => {
  it('should group objects by non-lang PK columns', () => {
    const objects: LegacyObject[] = [
      { project_id: 'EPM', country: 'eg', museum_id: 'cairo', number: '001', lang: 'en' },
      { project_id: 'EPM', country: 'eg', museum_id: 'cairo', number: '001', lang: 'ar' },
      { project_id: 'EPM', country: 'eg', museum_id: 'cairo', number: '002', lang: 'en' },
    ];

    const groups = groupObjectsByPK(objects);

    expect(groups.length).toBe(2);
    expect(groups[0]?.translations.length).toBe(2);
    expect(groups[1]?.translations.length).toBe(1);
  });

  it('should preserve object data in groups', () => {
    const objects: LegacyObject[] = [
      { project_id: 'EPM', country: 'eg', museum_id: 'cairo', number: '001', lang: 'en', name: 'Test' },
    ];

    const groups = groupObjectsByPK(objects);

    expect(groups[0]?.project_id).toBe('EPM');
    expect(groups[0]?.country).toBe('eg');
    expect(groups[0]?.museum_id).toBe('cairo');
    expect(groups[0]?.number).toBe('001');
    expect(groups[0]?.translations[0]?.name).toBe('Test');
  });
});

describe('transformObject', () => {
  it('should transform object group to item data', () => {
    const group: ObjectGroup = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      translations: [
        { project_id: 'EPM', country: 'eg', museum_id: 'cairo', number: '001', lang: 'en', inventory_id: 'INV001' },
      ],
    };

    const result = transformObject(group);

    expect(result.data.type).toBe('object');
    expect(result.data.internal_name).toBe('INV001');
    expect(result.data.owner_reference).toBe('INV001');
    expect(result.backwardCompatibility).toBe('mwnf3:objects:EPM:eg:cairo:001');
    expect(result.countryId).toBe('egy');
  });

  it('should use number as fallback for internal_name', () => {
    const group: ObjectGroup = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      translations: [
        { project_id: 'EPM', country: 'eg', museum_id: 'cairo', number: '001', lang: 'en' },
      ],
    };

    const result = transformObject(group);

    expect(result.data.internal_name).toBe('001');
  });
});

describe('transformObjectTranslation', () => {
  it('should transform object to translation data', () => {
    const obj: LegacyObject = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      lang: 'en',
      name: 'Test Object',
      description: 'A test description',
    };

    const result = transformObjectTranslation(obj, 'description');

    expect(result).not.toBeNull();
    expect(result?.data.name).toBe('Test Object');
    expect(result?.data.description).toBe('A test description');
    expect(result?.data.language_id).toBe('eng');
  });

  it('should return null for empty description', () => {
    const obj: LegacyObject = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      lang: 'en',
      name: 'Test',
      description: '',
    };

    const result = transformObjectTranslation(obj, 'description');

    expect(result).toBeNull();
  });

  it('should return null for missing name', () => {
    const obj: LegacyObject = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      lang: 'en',
      description: 'Test',
    };

    const result = transformObjectTranslation(obj, 'description');

    expect(result).toBeNull();
  });

  it('should truncate long alternate_name', () => {
    const longName = 'A'.repeat(300);
    const obj: LegacyObject = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      lang: 'en',
      name: 'Test',
      name2: longName,
      description: 'Test description',
    };

    const result = transformObjectTranslation(obj, 'description');

    expect(result?.data.alternate_name?.length).toBe(255);
    expect(result?.warnings.length).toBeGreaterThan(0);
  });

  it('should extract author names', () => {
    const obj: LegacyObject = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      lang: 'en',
      name: 'Test',
      description: 'Test',
      preparedby: 'John Doe',
      copyeditedby: 'Jane Doe',
    };

    const result = transformObjectTranslation(obj, 'description');

    expect(result?.authorName).toBe('John Doe');
    expect(result?.textCopyEditorName).toBe('Jane Doe');
  });
});

describe('extractObjectTags', () => {
  it('should extract materials, dynasties, and keywords', () => {
    const obj: LegacyObject = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      lang: 'en',
      materials: 'gold; silver',
      dynasty: '18th Dynasty',
      keywords: 'pharaoh, egypt',
    };

    const result = extractObjectTags(obj);

    expect(result.materials).toEqual(['gold', 'silver']);
    expect(result.dynasties).toEqual(['18th Dynasty']);
    expect(result.keywords).toEqual(['pharaoh', 'egypt']);
    expect(result.languageId).toBe('eng');
  });
});

describe('extractObjectArtists', () => {
  it('should extract single artist', () => {
    const obj: LegacyObject = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      lang: 'en',
      artist: 'Imhotep',
      birthplace: 'Memphis',
    };

    const result = extractObjectArtists(obj);

    expect(result.length).toBe(1);
    expect(result[0]?.name).toBe('Imhotep');
    expect(result[0]?.birthplace).toBe('Memphis');
  });

  it('should extract multiple artists', () => {
    const obj: LegacyObject = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      lang: 'en',
      artist: 'Artist 1; Artist 2; Artist 3',
    };

    const result = extractObjectArtists(obj);

    expect(result.length).toBe(3);
    expect(result[0]?.name).toBe('Artist 1');
    expect(result[1]?.name).toBe('Artist 2');
    expect(result[2]?.name).toBe('Artist 3');
  });

  it('should return empty array for no artist', () => {
    const obj: LegacyObject = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      lang: 'en',
    };

    const result = extractObjectArtists(obj);

    expect(result).toEqual([]);
  });
});

describe('parseTagString', () => {
  it('should parse semicolon-separated tags', () => {
    const result = parseTagString('tag1; tag2; tag3');
    expect(result).toEqual(['tag1', 'tag2', 'tag3']);
  });

  it('should parse comma-separated tags', () => {
    const result = parseTagString('tag1, tag2, tag3');
    expect(result).toEqual(['tag1', 'tag2', 'tag3']);
  });

  it('should treat structured data as single tag', () => {
    const result = parseTagString('Warp: wool; Weft: cotton');
    expect(result).toEqual(['Warp: wool; Weft: cotton']);
  });

  it('should return empty array for empty string', () => {
    expect(parseTagString('')).toEqual([]);
    expect(parseTagString(null)).toEqual([]);
    expect(parseTagString(undefined)).toEqual([]);
  });
});

describe('planTranslations', () => {
  it('should plan EPM translations using description2', () => {
    const group: ObjectGroup = {
      project_id: 'EPM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      translations: [
        { project_id: 'EPM', country: 'eg', museum_id: 'cairo', number: '001', lang: 'en', description2: 'EPM content' },
      ],
    };

    const plans = planTranslations(group, false);

    expect(plans.length).toBe(1);
    expect(plans[0]?.contextType).toBe('own');
    expect(plans[0]?.descriptionField).toBe('description2');
  });

  it('should plan non-EPM translations with description and description2', () => {
    const group: ObjectGroup = {
      project_id: 'VM',
      country: 'eg',
      museum_id: 'cairo',
      number: '001',
      translations: [
        {
          project_id: 'VM',
          country: 'eg',
          museum_id: 'cairo',
          number: '001',
          lang: 'en',
          description: 'VM content',
          description2: 'EPM content',
        },
      ],
    };

    const plans = planTranslations(group, true);

    expect(plans.length).toBe(2);
    expect(plans[0]?.contextType).toBe('own');
    expect(plans[0]?.descriptionField).toBe('description');
    expect(plans[1]?.contextType).toBe('epm');
    expect(plans[1]?.descriptionField).toBe('description2');
  });
});
