import { describe, expect, it } from 'vitest';

import {
  transformExploreMonument,
  type ExploreLegacyMonument,
} from '../../src/domain/transformers/explore-monument-transformer.js';

const legacyMonument = (overrides: Partial<ExploreLegacyMonument> = {}): ExploreLegacyMonument => ({
  monumentId: 15,
  locationId: 9,
  title: 'Legacy Base Title',
  geoCoordinates: '31.1, 35.2',
  zoom: 16,
  special_monument: null,
  related_monument: null,
  countryId: null,
  ...overrides,
});

describe('transformExploreMonument', () => {
  it('uses the english translation name as internal_name instead of a slugged legacy title', () => {
    const result = transformExploreMonument(
      legacyMonument(),
      [
        { langId: 'fr', name: 'Titre francais' },
        { langId: 'en', name: 'English Monument Name' },
      ],
      'eng'
    );

    expect(result.data.internal_name).toBe('English Monument Name');
    expect(result.data.latitude).toBe(31.1);
    expect(result.data.longitude).toBe(35.2);
    expect(result.locationId).toBe(9);
    expect(result.warnings).toEqual([]);
  });

  /**
   * Legacy carries no country on the monument row and derives one at query
   * time through locationId → locations.countryId. The 2-char legacy code is
   * mapped onto the inventory's ISO alpha-3 country id. See #1593.
   */
  it('maps the joined legacy country code onto the ISO alpha-3 country id', () => {
    const result = transformExploreMonument(
      legacyMonument({ countryId: 'in' }),
      [{ langId: 'en', name: 'Indian Monument' }],
      'eng'
    );

    expect(result.data.country_id).toBe('ind');
    expect(result.warnings).toEqual([]);
  });

  it('leaves country_id null when the location has no country', () => {
    for (const countryId of [null, '', '   ']) {
      const result = transformExploreMonument(
        legacyMonument({ countryId }),
        [{ langId: 'en', name: 'Countryless Monument' }],
        'eng'
      );

      expect(result.data.country_id).toBeNull();
      expect(result.warnings).toEqual([]);
    }
  });

  /**
   * mapCountryCode throws on a code it does not know. A single unmapped
   * location must not take the whole import down with it.
   */
  it('downgrades an unknown country code to a warning rather than throwing', () => {
    const result = transformExploreMonument(
      legacyMonument({ countryId: 'zz' }),
      [{ langId: 'en', name: 'Monument in Nowhere' }],
      'eng'
    );

    expect(result.data.country_id).toBeNull();
    expect(result.warnings).toHaveLength(1);
    expect(result.warnings[0]).toContain("'zz'");
    expect(result.warnings[0]).toContain('mwnf3_explore:monument:15');
  });
});
