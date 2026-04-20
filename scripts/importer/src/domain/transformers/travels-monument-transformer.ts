import type { ItemData } from '../../core/types.js';
import { mapCountryCode, mapLanguageCode } from '../../utils/code-mappings.js';
import {
  selectItemInternalName,
  type ItemInternalNameCandidate,
} from './item-internal-name-transformer.js';

export interface TravelMonumentTranslationCandidate {
  project_id: string;
  country: string;
  itinerary_id: string;
  location_id: string;
  number: string;
  lang: string;
  trail_id: number;
  title: string;
}

export interface TravelMonumentTransformGroup {
  project_id: string;
  country: string;
  trail_id: number;
  itinerary_id: string;
  location_id: string;
  number: string;
  translations: TravelMonumentTranslationCandidate[];
}

export interface TransformedTravelMonument {
  data: Omit<ItemData, 'collection_id' | 'partner_id' | 'project_id'>;
  backwardCompatibility: string;
  warning: string | null;
}

export function transformTravelsMonument(
  group: TravelMonumentTransformGroup,
  defaultLanguageId: string
): TransformedTravelMonument {
  const backwardCompatibility = `mwnf3_travels:monument:${group.project_id}:${group.country}:${group.trail_id}:${group.itinerary_id}:${group.location_id}:${group.number}`;
  const candidates: ItemInternalNameCandidate[] = [];

  for (const translation of group.translations) {
    candidates.push({
      languageId: mapLanguageCode(translation.lang),
      value: translation.title,
    });
  }

  const selectedInternalName = selectItemInternalName(
    candidates,
    defaultLanguageId,
    'Travels monument',
    backwardCompatibility
  );

  return {
    data: {
      internal_name: selectedInternalName.internalName,
      backward_compatibility: backwardCompatibility,
      type: 'monument',
      country_id: mapCountryCode(group.country),
      parent_id: null,
      owner_reference: null,
      mwnf_reference: null,
      latitude: null,
      longitude: null,
      map_zoom: null,
    },
    backwardCompatibility,
    warning: selectedInternalName.warning,
  };
}
