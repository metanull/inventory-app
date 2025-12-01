/**
 * Museum Transformer
 *
 * Transforms legacy museum data to partner entities.
 * This is pure business logic with no dependencies on write strategy.
 */

import type { LegacyMuseum, LegacyMuseumName } from '../types/index.js';
import type { PartnerData, PartnerTranslationData } from '../../core/types.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';

/**
 * Transformed museum result
 */
export interface TransformedMuseum {
  data: PartnerData;
  backwardCompatibility: string;
}

/**
 * Transformed museum translation result
 */
export interface TransformedMuseumTranslation {
  data: Omit<PartnerTranslationData, 'partner_id' | 'context_id'>;
  languageId: string;
}

/**
 * Extra fields extracted from museum data
 */
export interface MuseumExtraFields {
  phone?: string;
  fax?: string;
  email?: string;
  url?: string;
  address_legacy?: string;
  ex_name?: string;
  ex_description?: string;
  opening_hours?: string;
  how_to_reach?: string;
  geoCoordinates?: string;
  country_code?: string;
}

/**
 * Transform a legacy museum to partner
 */
export function transformMuseum(legacy: LegacyMuseum): TransformedMuseum {
  const backwardCompatibility = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'museums',
    pkValues: [legacy.museum_id, legacy.country],
  });

  // internal_name must always be converted from legacy.name - no fallback
  if (!legacy.name) {
    throw new Error(`Museum ${legacy.museum_id}:${legacy.country} missing required name field`);
  }
  const internalName = convertHtmlToMarkdown(legacy.name);

  const data: PartnerData = {
    type: 'museum',
    internal_name: internalName,
    backward_compatibility: backwardCompatibility,
  };

  return {
    data,
    backwardCompatibility,
  };
}

/**
 * Transform a legacy museum name to partner translation
 */
export function transformMuseumTranslation(
  museum: LegacyMuseum,
  translation: LegacyMuseumName
): TransformedMuseumTranslation {
  const languageId = mapLanguageCode(translation.lang);

  const name = translation.name?.trim();
  if (!name) {
    throw new Error(`Museum translation missing name: ${museum.museum_id}:${translation.lang}`);
  }

  const nameMarkdown = convertHtmlToMarkdown(name);
  const descriptionMarkdown = translation.description
    ? convertHtmlToMarkdown(translation.description)
    : null;

  // Build extra field with all additional data
  const extra: MuseumExtraFields = {};
  if (museum.phone) extra.phone = museum.phone;
  if (museum.fax) extra.fax = museum.fax;
  if (museum.email) extra.email = museum.email;
  if (museum.url) extra.url = museum.url;
  if (museum.address) extra.address_legacy = museum.address;
  if (translation.ex_name) extra.ex_name = translation.ex_name;
  if (translation.ex_description) extra.ex_description = translation.ex_description;
  if (translation.opening_hours) extra.opening_hours = translation.opening_hours;
  if (translation.how_to_reach) extra.how_to_reach = translation.how_to_reach;
  if (museum.geoCoordinates) extra.geoCoordinates = museum.geoCoordinates;
  if (museum.country) extra.country_code = museum.country;

  const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

  const data: Omit<PartnerTranslationData, 'partner_id' | 'context_id'> = {
    language_id: languageId,
    name: nameMarkdown,
    description: descriptionMarkdown,
    city_display: translation.city || null,
    contact_website: museum.url || null,
    contact_phone: museum.phone || null,
    contact_email_general: museum.email || null,
    extra: extraJson,
  };

  return { data, languageId };
}

/**
 * Group museums with their translations
 */
export interface MuseumGroup {
  key: string;
  museum: LegacyMuseum;
  translations: LegacyMuseumName[];
}

/**
 * Group legacy museums and museum names by museum key
 */
export function groupMuseumsByKey(
  museums: LegacyMuseum[],
  museumNames: LegacyMuseumName[]
): MuseumGroup[] {
  const translationMap = new Map<string, LegacyMuseumName[]>();

  for (const translation of museumNames) {
    const key = `${translation.museum_id}:${translation.country}`;
    if (!translationMap.has(key)) {
      translationMap.set(key, []);
    }
    translationMap.get(key)!.push(translation);
  }

  return museums.map((museum) => {
    const key = `${museum.museum_id}:${museum.country}`;
    return {
      key,
      museum,
      translations: translationMap.get(key) || [],
    };
  });
}
