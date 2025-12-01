/**
 * Institution Transformer
 *
 * Transforms legacy institution data to partner entities.
 * This is pure business logic with no dependencies on write strategy.
 */

import type { LegacyInstitution, LegacyInstitutionName } from '../types/index.js';
import type { PartnerData, PartnerTranslationData } from '../../core/types.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';

/**
 * Transformed institution result
 */
export interface TransformedInstitution {
  data: PartnerData;
  backwardCompatibility: string;
}

/**
 * Transformed institution translation result
 */
export interface TransformedInstitutionTranslation {
  data: Omit<PartnerTranslationData, 'partner_id' | 'context_id'>;
  languageId: string;
}

/**
 * Extra fields extracted from institution data
 */
export interface InstitutionExtraFields {
  phone?: string;
  fax?: string;
  email?: string;
  url?: string;
  address_legacy?: string;
  city_legacy?: string;
  country_code?: string;
}

/**
 * Transform a legacy institution to partner
 */
export function transformInstitution(legacy: LegacyInstitution): TransformedInstitution {
  const backwardCompatibility = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'institutions',
    pkValues: [legacy.institution_id, legacy.country],
  });

  // internal_name must always be converted from legacy.name - no fallback
  if (!legacy.name) {
    throw new Error(`Institution ${legacy.institution_id}:${legacy.country} missing required name field`);
  }
  const internalName = convertHtmlToMarkdown(legacy.name);

  const data: PartnerData = {
    type: 'institution',
    internal_name: internalName,
    backward_compatibility: backwardCompatibility,
  };

  return {
    data,
    backwardCompatibility,
  };
}

/**
 * Transform a legacy institution name to partner translation
 */
export function transformInstitutionTranslation(
  institution: LegacyInstitution,
  translation: LegacyInstitutionName
): TransformedInstitutionTranslation {
  const languageId = mapLanguageCode(translation.lang);

  const name = translation.name?.trim();
  if (!name) {
    throw new Error(
      `Institution translation missing name: ${institution.institution_id}:${translation.lang}`
    );
  }

  const nameMarkdown = convertHtmlToMarkdown(name);
  const descriptionMarkdown = translation.description
    ? convertHtmlToMarkdown(translation.description)
    : null;

  // Build extra field with all additional data
  const extra: InstitutionExtraFields = {};
  if (institution.address) extra.address_legacy = institution.address;
  if (institution.city) extra.city_legacy = institution.city;
  if (institution.country) extra.country_code = institution.country;

  const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

  const data: Omit<PartnerTranslationData, 'partner_id' | 'context_id'> = {
    language_id: languageId,
    name: nameMarkdown,
    description: descriptionMarkdown,
    city_display: institution.city || null,
    contact_website: institution.url || null,
    contact_phone: institution.phone || null,
    contact_email_general: institution.email || null,
    extra: extraJson,
  };

  return { data, languageId };
}

/**
 * Group institutions with their translations
 */
export interface InstitutionGroup {
  key: string;
  institution: LegacyInstitution;
  translations: LegacyInstitutionName[];
}

/**
 * Group legacy institutions and institution names by institution key
 */
export function groupInstitutionsByKey(
  institutions: LegacyInstitution[],
  institutionNames: LegacyInstitutionName[]
): InstitutionGroup[] {
  const translationMap = new Map<string, LegacyInstitutionName[]>();

  for (const translation of institutionNames) {
    const key = `${translation.institution_id}:${translation.country}`;
    if (!translationMap.has(key)) {
      translationMap.set(key, []);
    }
    translationMap.get(key)!.push(translation);
  }

  return institutions.map((institution) => {
    const key = `${institution.institution_id}:${institution.country}`;
    return {
      key,
      institution,
      translations: translationMap.get(key) || [],
    };
  });
}
