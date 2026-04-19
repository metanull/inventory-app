import { describe, expect, it } from 'vitest';

import { transformTravelsMonument } from '../../src/domain/transformers/travels-monument-transformer.js';

describe('transformTravelsMonument', () => {
  it('uses the english title as internal_name instead of a technical slug', () => {
    const result = transformTravelsMonument(
      {
        project_id: 'IAM',
        country: 'pt',
        trail_id: 1,
        itinerary_id: 'V',
        location_id: '1',
        number: 'a',
        translations: [
          {
            project_id: 'IAM',
            country: 'pt',
            trail_id: 1,
            itinerary_id: 'V',
            location_id: '1',
            number: 'a',
            lang: 'en',
            title: 'Islamic Beja',
          },
          {
            project_id: 'IAM',
            country: 'pt',
            trail_id: 1,
            itinerary_id: 'V',
            location_id: '1',
            number: 'a',
            lang: 'fr',
            title: 'Beja islamique',
          },
        ],
      },
      'eng'
    );

    expect(result.data.internal_name).toBe('Islamic Beja');
    expect(result.data.country_id).toBe('prt');
    expect(result.warning).toBeNull();
  });
});