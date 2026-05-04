import { describe, expect, it } from 'vitest';

import { transformTravelsMonument } from '../../src/domain/transformers/travels-monument-transformer.js';

describe('transformTravelsMonument', () => {
  it('uses a namespaced BC-derived internal_name instead of the display title', () => {
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

    expect(result.data.internal_name).toBe('travels:monument:IAM:pt:1:V:1:a');
    expect(result.data.country_id).toBe('prt');
    expect(result.warning).toBeNull();
  });
});
