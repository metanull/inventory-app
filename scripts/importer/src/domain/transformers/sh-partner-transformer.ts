/**
 * SH Partner Transformer
 *
 * Transforms Sharing History partner data to the new format.
 * Handles partner deduplication via partner_sh_partners mapping.
 */

import type { ShLegacyPartner, ShLegacyPartnerName } from '../types/index.js';
import type { PartnerData, PartnerTranslationData } from '../../core/types.js';
import { mapCountryCode, mapLanguageCode } from '../../utils/code-mappings.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';
import { formatShBackwardCompatibility } from './sh-project-transformer.js';

const SH_PARTNERS_TABLE = 'sh_partners';

/**
 * Extra fields stored in partner's extra JSON column
 */
export interface ShPartnerExtraFields {
  source: 'mwnf3_sharing_history';
  partner_category?: string;
  contact_person_1?: {
    name?: string;
    title?: string;
    phone?: string;
    fax?: string;
    email?: string;
  };
  contact_person_2?: {
    name?: string;
    title?: string;
    phone?: string;
    fax?: string;
    email?: string;
  };
  urls?: string[];
  logos?: string[];
  region_id?: string;
  portal_display?: string;
}

/**
 * Transformed SH Partner
 */
export interface TransformedShPartner {
  data: Omit<PartnerData, 'project_id'>;
  backwardCompatibility: string;
  extra: ShPartnerExtraFields;
}

/**
 * Transformed SH Partner Translation
 */
export interface TransformedShPartnerTranslation {
  data: Omit<PartnerTranslationData, 'partner_id' | 'context_id' | 'backward_compatibility'>;
  languageId: string;
}

/**
 * Parse GPS coordinates from legacy format
 * Format: "lat,lng" or "lat, lng"
 */
function parseGeoCoordinates(geoCoordinates: string | null | undefined): {
  latitude: number | null;
  longitude: number | null;
} {
  if (!geoCoordinates) {
    return { latitude: null, longitude: null };
  }

  const parts = geoCoordinates.split(',').map((s) => s.trim());
  if (parts.length !== 2) {
    return { latitude: null, longitude: null };
  }

  const latitude = parseFloat(parts[0]);
  const longitude = parseFloat(parts[1]);

  if (isNaN(latitude) || isNaN(longitude)) {
    return { latitude: null, longitude: null };
  }

  return { latitude, longitude };
}

/**
 * Transform a SH partner to new format
 */
export function transformShPartner(legacy: ShLegacyPartner): TransformedShPartner {
  const backwardCompat = formatShBackwardCompatibility(SH_PARTNERS_TABLE, legacy.partners_id);
  const { latitude, longitude } = parseGeoCoordinates(legacy.geoCoordinates);
  const mapZoom = legacy.zoom ? parseInt(legacy.zoom, 10) : null;

  // Determine partner type from category (only 'museum' or 'institution' allowed)
  const partnerType: 'museum' | 'institution' = legacy.partner_category
    ?.toLowerCase()
    .includes('museum')
    ? 'museum'
    : 'institution';

  // Build extra fields
  const extra: ShPartnerExtraFields = {
    source: 'mwnf3_sharing_history',
    partner_category: legacy.partner_category,
  };

  // Contact person 1
  if (legacy.cp1_name || legacy.cp1_title || legacy.cp1_phone || legacy.cp1_email) {
    extra.contact_person_1 = {
      name: legacy.cp1_name || undefined,
      title: legacy.cp1_title || undefined,
      phone: legacy.cp1_phone || undefined,
      fax: legacy.cp1_fax || undefined,
      email: legacy.cp1_email || undefined,
    };
  }

  // Contact person 2
  if (legacy.cp2_name || legacy.cp2_title || legacy.cp2_phone || legacy.cp2_email) {
    extra.contact_person_2 = {
      name: legacy.cp2_name || undefined,
      title: legacy.cp2_title || undefined,
      phone: legacy.cp2_phone || undefined,
      fax: legacy.cp2_fax || undefined,
      email: legacy.cp2_email || undefined,
    };
  }

  // URLs
  const urls = [legacy.url, legacy.url2, legacy.url3, legacy.url4, legacy.url5].filter(
    (u): u is string => !!u
  );
  if (urls.length > 0) {
    extra.urls = urls;
  }

  // Logos
  const logos = [legacy.logo, legacy.logo1, legacy.logo2, legacy.logo3].filter(
    (l): l is string => !!l
  );
  if (logos.length > 0) {
    extra.logos = logos;
  }

  if (legacy.region_id) {
    extra.region_id = legacy.region_id;
  }
  if (legacy.portal_display) {
    extra.portal_display = legacy.portal_display;
  }

  const data: Omit<PartnerData, 'project_id'> = {
    internal_name: convertHtmlToMarkdown(legacy.name),
    type: partnerType,
    backward_compatibility: backwardCompat,
    country_id: legacy.country ? mapCountryCode(legacy.country) : null,
    latitude,
    longitude,
    map_zoom: mapZoom,
    visible: true,
  };

  return {
    data,
    backwardCompatibility: backwardCompat,
    extra,
  };
}

/**
 * Transform a SH partner name to translation
 * Ensures all fields are properly set to null (not undefined) for SQL compatibility
 */
export function transformShPartnerTranslation(
  legacy: ShLegacyPartnerName
): TransformedShPartnerTranslation {
  const languageId = mapLanguageCode(legacy.lang);

  // Build description from available fields
  const descriptionParts: string[] = [];
  if (legacy.description) {
    descriptionParts.push(convertHtmlToMarkdown(legacy.description));
  }
  if (legacy.department) {
    descriptionParts.push(`Department: ${convertHtmlToMarkdown(legacy.department)}`);
  }

  // Build description including city if available
  if (legacy.city) {
    descriptionParts.unshift(`City: ${convertHtmlToMarkdown(legacy.city)}`);
  }

  // Ensure all optional fields are null (not undefined) for SQL compatibility
  // Name is required - use legacy.name or fallback to partner_id
  const data: Omit<PartnerTranslationData, 'partner_id' | 'context_id' | 'backward_compatibility'> =
    {
      language_id: languageId,
      name: legacy.name ? convertHtmlToMarkdown(legacy.name) : `Partner ${legacy.partners_id}`,
      description: descriptionParts.length > 0 ? descriptionParts.join('\n\n') : null,
      city_display: null,
      contact_website: null,
      contact_phone: null,
      contact_email_general: null,
      extra: null,
    };

  return {
    data,
    languageId,
  };
}

/**
 * Group SH partners with their translations
 */
export interface ShPartnerGroup {
  partner: ShLegacyPartner;
  translations: ShLegacyPartnerName[];
}

/**
 * Group partners by partners_id
 */
export function groupShPartnersByKey(
  partners: ShLegacyPartner[],
  partnerNames: ShLegacyPartnerName[]
): ShPartnerGroup[] {
  const translationMap = new Map<string, ShLegacyPartnerName[]>();

  for (const name of partnerNames) {
    if (!translationMap.has(name.partners_id)) {
      translationMap.set(name.partners_id, []);
    }
    translationMap.get(name.partners_id)!.push(name);
  }

  return partners.map((partner) => ({
    partner,
    translations: translationMap.get(partner.partners_id) || [],
  }));
}
