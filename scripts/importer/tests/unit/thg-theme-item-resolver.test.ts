/**
 * Unit tests for thg-theme-item-resolver.ts
 *
 * Verifies that resolvePictureItemBackwardCompatibility returns the correct
 * picture item backward-compatibility key for every supported source family,
 * and returns null for unsupported families or rows with missing image columns.
 */

import { describe, expect, it } from 'vitest';
import {
  resolvePictureItemBackwardCompatibility,
  THEME_ITEM_SELECT_COLUMNS,
} from '../../src/importers/phase-10/thg-theme-item-resolver.js';
import type { LegacyThemeItem } from '../../src/importers/phase-10/thg-theme-item-resolver.js';

/** A fully-null base row for building test fixtures. */
const NULL_ROW: LegacyThemeItem = {
  mwnf3_object_project_id: null,
  mwnf3_object_country_id: null,
  mwnf3_object_partner_id: null,
  mwnf3_object_item_id: null,
  mwnf3_object_item_type: null,
  mwnf3_object_image_id: null,
  mwnf3_monument_project_id: null,
  mwnf3_monument_country_id: null,
  mwnf3_monument_partner_id: null,
  mwnf3_monument_item_id: null,
  mwnf3_monument_item_type: null,
  mwnf3_monument_image_id: null,
  mwnf3_monument_detail_project_id: null,
  mwnf3_monument_detail_country_id: null,
  mwnf3_monument_detail_partner_id: null,
  mwnf3_monument_detail_item_id: null,
  mwnf3_monument_detail_detail_id: null,
  mwnf3_monument_detail_image_id: null,
  sh_object_project_id: null,
  sh_object_country_id: null,
  sh_object_item_id: null,
  sh_object_item_type: null,
  sh_object_image_id: null,
  sh_monument_project_id: null,
  sh_monument_country_id: null,
  sh_monument_item_id: null,
  sh_monument_item_type: null,
  sh_monument_image_id: null,
  sh_monument_detail_project_id: null,
  sh_monument_detail_country_id: null,
  sh_monument_detail_item_id: null,
  sh_monument_detail_detail_id: null,
  sh_monument_detail_image_id: null,
};

describe('resolvePictureItemBackwardCompatibility', () => {
  describe('mwnf3 object picture', () => {
    it('resolves to objects_pictures key', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        mwnf3_object_project_id: 'BAR',
        mwnf3_object_country_id: 'hr',
        mwnf3_object_partner_id: 'Mon11',
        mwnf3_object_item_id: 33,
        mwnf3_object_item_type: null,
        mwnf3_object_image_id: 1,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBe(
        'mwnf3:objects_pictures:BAR:hr:Mon11:33:1'
      );
    });

    it('returns null when image_id is missing', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        mwnf3_object_project_id: 'BAR',
        mwnf3_object_country_id: 'hr',
        mwnf3_object_partner_id: 'Mon11',
        mwnf3_object_item_id: 33,
        mwnf3_object_image_id: null,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBeNull();
    });

    it('returns null when item_id is missing', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        mwnf3_object_project_id: 'BAR',
        mwnf3_object_country_id: 'hr',
        mwnf3_object_partner_id: 'Mon11',
        mwnf3_object_item_id: null,
        mwnf3_object_image_id: 2,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBeNull();
    });

    it('does not include item_type in the key (matches ObjectPictureImporter behaviour)', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        mwnf3_object_project_id: 'ISL',
        mwnf3_object_country_id: 'MAR',
        mwnf3_object_partner_id: 'MUS001',
        mwnf3_object_item_id: 42,
        mwnf3_object_item_type: 'detail', // should NOT appear in key
        mwnf3_object_image_id: 3,
      };
      const result = resolvePictureItemBackwardCompatibility(row);
      expect(result).toBe('mwnf3:objects_pictures:ISL:MAR:MUS001:42:3');
      expect(result).not.toContain('detail');
    });
  });

  describe('mwnf3 monument picture', () => {
    it('resolves to monuments_pictures key', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        mwnf3_monument_project_id: 'BAR',
        mwnf3_monument_country_id: 'hr',
        mwnf3_monument_partner_id: 'Mon11',
        mwnf3_monument_item_id: 33,
        mwnf3_monument_item_type: null,
        mwnf3_monument_image_id: 2,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBe(
        'mwnf3:monuments_pictures:BAR:hr:Mon11:33:2'
      );
    });

    it('returns null when image_id is missing', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        mwnf3_monument_project_id: 'BAR',
        mwnf3_monument_country_id: 'hr',
        mwnf3_monument_partner_id: 'Mon11',
        mwnf3_monument_item_id: 33,
        mwnf3_monument_image_id: null,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBeNull();
    });
  });

  describe('mwnf3 monument detail picture', () => {
    it('resolves to monument_detail_pictures key — St. Joseph Dream example', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        mwnf3_monument_detail_project_id: 'BAR',
        mwnf3_monument_detail_country_id: 'hr',
        mwnf3_monument_detail_partner_id: 'Mon11',
        mwnf3_monument_detail_item_id: 33,
        mwnf3_monument_detail_detail_id: 4,
        mwnf3_monument_detail_image_id: 1,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBe(
        'mwnf3:monument_detail_pictures:BAR:hr:Mon11:33:4:1'
      );
    });

    it('returns null when image_id is missing', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        mwnf3_monument_detail_project_id: 'BAR',
        mwnf3_monument_detail_country_id: 'hr',
        mwnf3_monument_detail_partner_id: 'Mon11',
        mwnf3_monument_detail_item_id: 33,
        mwnf3_monument_detail_detail_id: 4,
        mwnf3_monument_detail_image_id: null,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBeNull();
    });

    it('returns null when detail_id is missing', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        mwnf3_monument_detail_project_id: 'BAR',
        mwnf3_monument_detail_country_id: 'hr',
        mwnf3_monument_detail_partner_id: 'Mon11',
        mwnf3_monument_detail_item_id: 33,
        mwnf3_monument_detail_detail_id: null,
        mwnf3_monument_detail_image_id: 1,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBeNull();
    });
  });

  describe('SH object picture', () => {
    it('resolves to sh_object_images key with lowercased strings', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        sh_object_project_id: 'ISL',
        sh_object_country_id: 'MA',
        sh_object_item_id: 7,
        sh_object_item_type: null,
        sh_object_image_id: 2,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBe(
        'mwnf3_sharing_history:sh_object_images:isl:ma:7:_:2'
      );
    });

    it('uses underscore for blank item_type', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        sh_object_project_id: 'ISL',
        sh_object_country_id: 'MA',
        sh_object_item_id: 7,
        sh_object_item_type: '',
        sh_object_image_id: 1,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBe(
        'mwnf3_sharing_history:sh_object_images:isl:ma:7:_:1'
      );
    });

    it('lowercases non-empty item_type', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        sh_object_project_id: 'ISL',
        sh_object_country_id: 'MA',
        sh_object_item_id: 7,
        sh_object_item_type: 'Detail',
        sh_object_image_id: 3,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBe(
        'mwnf3_sharing_history:sh_object_images:isl:ma:7:detail:3'
      );
    });

    it('returns null when image_id is missing', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        sh_object_project_id: 'ISL',
        sh_object_country_id: 'MA',
        sh_object_item_id: 7,
        sh_object_image_id: null,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBeNull();
    });
  });

  describe('SH monument picture', () => {
    it('resolves to sh_monument_images key with lowercased strings', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        sh_monument_project_id: 'BAR',
        sh_monument_country_id: 'HR',
        sh_monument_item_id: 11,
        sh_monument_item_type: null,
        sh_monument_image_id: 1,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBe(
        'mwnf3_sharing_history:sh_monument_images:bar:hr:11:_:1'
      );
    });

    it('uses underscore for blank item_type', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        sh_monument_project_id: 'BAR',
        sh_monument_country_id: 'HR',
        sh_monument_item_id: 11,
        sh_monument_item_type: '',
        sh_monument_image_id: 2,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBe(
        'mwnf3_sharing_history:sh_monument_images:bar:hr:11:_:2'
      );
    });

    it('returns null when image_id is missing', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        sh_monument_project_id: 'BAR',
        sh_monument_country_id: 'HR',
        sh_monument_item_id: 11,
        sh_monument_image_id: null,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBeNull();
    });
  });

  describe('SH monument detail picture', () => {
    it('resolves to sh_monument_detail_pictures key with lowercased strings', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        sh_monument_detail_project_id: 'BAR',
        sh_monument_detail_country_id: 'HR',
        sh_monument_detail_item_id: 33,
        sh_monument_detail_detail_id: 4,
        sh_monument_detail_image_id: 1,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBe(
        'mwnf3_sharing_history:sh_monument_detail_pictures:bar:hr:33:4:1'
      );
    });

    it('returns null when image_id is missing', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        sh_monument_detail_project_id: 'BAR',
        sh_monument_detail_country_id: 'HR',
        sh_monument_detail_item_id: 33,
        sh_monument_detail_detail_id: 4,
        sh_monument_detail_image_id: null,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBeNull();
    });

    it('returns null when detail_id is missing', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        sh_monument_detail_project_id: 'BAR',
        sh_monument_detail_country_id: 'HR',
        sh_monument_detail_item_id: 33,
        sh_monument_detail_detail_id: null,
        sh_monument_detail_image_id: 1,
      };
      expect(resolvePictureItemBackwardCompatibility(row)).toBeNull();
    });
  });

  describe('unsupported / empty rows', () => {
    it('returns null for a fully null row (unsupported source family)', () => {
      expect(resolvePictureItemBackwardCompatibility(NULL_ROW)).toBeNull();
    });
  });

  describe('priority — first matching family wins', () => {
    it('prefers mwnf3 object over monument when both are set', () => {
      const row: LegacyThemeItem = {
        ...NULL_ROW,
        mwnf3_object_project_id: 'ISL',
        mwnf3_object_country_id: 'MAR',
        mwnf3_object_partner_id: 'MUS',
        mwnf3_object_item_id: 1,
        mwnf3_object_image_id: 1,
        mwnf3_monument_project_id: 'BAR',
        mwnf3_monument_country_id: 'HR',
        mwnf3_monument_partner_id: 'MON',
        mwnf3_monument_item_id: 2,
        mwnf3_monument_image_id: 2,
      };
      const result = resolvePictureItemBackwardCompatibility(row);
      expect(result).toContain('objects_pictures');
      expect(result).not.toContain('monuments_pictures');
    });
  });
});

describe('THEME_ITEM_SELECT_COLUMNS', () => {
  it('contains the mwnf3 object image column', () => {
    expect(THEME_ITEM_SELECT_COLUMNS).toContain('mwnf3_object_image_id');
  });

  it('contains the mwnf3 monument image column', () => {
    expect(THEME_ITEM_SELECT_COLUMNS).toContain('mwnf3_monument_image_id');
  });

  it('contains the mwnf3 monument detail image column', () => {
    expect(THEME_ITEM_SELECT_COLUMNS).toContain('mwnf3_monument_detail_image_id');
  });

  it('contains the SH object image column', () => {
    expect(THEME_ITEM_SELECT_COLUMNS).toContain('sh_object_image_id');
  });

  it('contains the SH monument image column', () => {
    expect(THEME_ITEM_SELECT_COLUMNS).toContain('sh_monument_image_id');
  });

  it('contains the SH monument detail image column', () => {
    expect(THEME_ITEM_SELECT_COLUMNS).toContain('sh_monument_detail_image_id');
  });
});
