/**
 * Country Transformer
 *
 * Transforms legacy country data to the new format.
 * This is pure business logic with no dependencies on write strategy.
 */

import type { LegacyCountry, LegacyCountryName } from '../types/index.js';
import type { CountryData, CountryTranslationData } from '../../core/types.js';
import { mapCountryCode, mapLanguageCode } from '../../utils/code-mappings.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';

/**
 * Transformed country result
 */
export interface TransformedCountry {
  data: CountryData;
  backwardCompatibility: string;
}

/**
 * Transformed country translation result
 */
export interface TransformedCountryTranslation {
  data: CountryTranslationData;
}

/**
 * Transform a legacy country to the new format
 */
export function transformCountry(legacy: LegacyCountry): TransformedCountry {
  const iso3Code = mapCountryCode(legacy.code);

  const backwardCompatibility = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'countries',
    pkValues: [legacy.code],
  });

  if (!legacy.name) {
    throw new Error(
      `Country ${legacy.code} has no name for internal_name`
    );
  }

  const data: CountryData = {
    id: iso3Code,
    internal_name: legacy.name,
    backward_compatibility: backwardCompatibility,
  };

  return {
    data,
    backwardCompatibility,
  };
}

/**
 * Transform a legacy country name to country translation
 */
export function transformCountryTranslation(
  legacy: LegacyCountryName
): TransformedCountryTranslation {
  const countryId = mapCountryCode(legacy.code);
  const languageId = mapLanguageCode(legacy.lang);

  const data: CountryTranslationData = {
    country_id: countryId,
    language_id: languageId,
    name: legacy.name,
  };

  return { data };
}
