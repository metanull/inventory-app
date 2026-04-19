import { describe, expect, it } from 'vitest';

import { transformExploreMonument } from '../../src/domain/transformers/explore-monument-transformer.js';

describe('transformExploreMonument', () => {
  it('uses the english translation name as internal_name instead of a slugged legacy title', () => {
    const result = transformExploreMonument(
      {
        monumentId: 15,
        locationId: 9,
        title: 'Legacy Base Title',
        geoCoordinates: '31.1, 35.2',
        zoom: 16,
        special_monument: null,
        related_monument: null,
      },
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
    expect(result.warning).toBeNull();
  });
});