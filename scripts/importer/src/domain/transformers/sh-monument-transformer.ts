/**
 * SH Monument Transformer
 *
 * Transforms Sharing History monument data to the new format.
 * Key differences from mwnf3:
 * - PK: (project_id, country, number) - no partner in PK
 * - Partner linked via FK `partners_id`
 * - pd_country stored in extra JSON as `country_id_present_days`
 */

import type { ShLegacyMonument, ShLegacyMonumentText, ShMonumentGroup } from '../types/index.js';
import type { ItemData, ItemTranslationData } from '../../core/types.js';
import { mapCountryCode, mapLanguageCode } from '../../utils/code-mappings.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';
import { formatShBackwardCompatibility } from './sh-project-transformer.js';

const SH_MONUMENTS_TABLE = 'sh_monuments';

/**
 * Transformed SH Monument
 */
export interface TransformedShMonument {
  data: Omit<ItemData, 'partner_id' | 'collection_id' | 'project_id'>;
  backwardCompatibility: string;
  warning?: string;
}

/**
 * Transformed SH Monument Translation
 */
export interface TransformedShMonumentTranslation {
  data: Omit<ItemTranslationData, 'item_id' | 'context_id' | 'backward_compatibility'>;
  languageId: string;
}

/**
 * Group SH monuments by primary key
 */
export function groupShMonumentsByPK(
  monuments: ShLegacyMonument[],
  monumentTexts: ShLegacyMonumentText[]
): ShMonumentGroup[] {
  // Create a map of translations by PK
  const translationMap = new Map<string, ShLegacyMonumentText[]>();

  for (const text of monumentTexts) {
    const key = `${text.project_id}:${text.country}:${text.number}`;
    if (!translationMap.has(key)) {
      translationMap.set(key, []);
    }
    translationMap.get(key)!.push(text);
  }

  // Group monuments with their translations
  return monuments.map((mon) => {
    const key = `${mon.project_id}:${mon.country}:${mon.number}`;
    return {
      project_id: mon.project_id,
      country: mon.country,
      number: mon.number,
      partners_id: mon.partners_id,
      working_number: mon.working_number,
      start_date: mon.start_date,
      end_date: mon.end_date,
      display_status: mon.display_status,
      pd_country: mon.pd_country,
      translations: translationMap.get(key) || [],
    };
  });
}

/**
 * Transform a SH monument group to Item
 */
export function transformShMonument(
  group: ShMonumentGroup,
  defaultLanguageId: string
): TransformedShMonument {
  const backwardCompat = formatShBackwardCompatibility(
    SH_MONUMENTS_TABLE,
    group.project_id,
    group.country,
    group.number
  );

  // Find default language translation for internal_name
  let internalName = `SH Monument ${group.project_id}:${group.country}:${group.number}`;
  let warning: string | undefined;

  const defaultTranslation = group.translations.find(
    (t) => mapLanguageCode(t.lang) === defaultLanguageId
  );

  if (defaultTranslation?.name) {
    internalName = convertHtmlToMarkdown(defaultTranslation.name);
  } else if (group.translations.length > 0 && group.translations[0].name) {
    // Fallback to first available translation
    internalName = convertHtmlToMarkdown(group.translations[0].name);
    warning = `SH Monument ${backwardCompat} missing translation in default language, using ${group.translations[0].lang}`;
  }

  const data: Omit<ItemData, 'partner_id' | 'collection_id' | 'project_id'> = {
    internal_name: internalName,
    type: 'monument',
    backward_compatibility: backwardCompat,
    country_id: mapCountryCode(group.country),
    parent_id: null,
    owner_reference: null,
    mwnf_reference: group.working_number || null,
  };

  return {
    data,
    backwardCompatibility: backwardCompat,
    warning,
  };
}

/**
 * Transform a SH monument text to ItemTranslation
 * Returns null if required fields (name) is missing
 * Description is built from description, description2, and history (at least one should be present)
 */
export function transformShMonumentTranslation(
  text: ShLegacyMonumentText
): TransformedShMonumentTranslation | null {
  const languageId = mapLanguageCode(text.lang);

  // Validate required fields - name is required
  const name = text.name?.trim() || null;
  if (!name) {
    return null;
  }

  // Build description combining description, description2, and history (use whichever is available)
  const descriptions = [text.description, text.description2, text.history]
    .filter((d): d is string => !!d && d.trim() !== '')
    .map((d) => convertHtmlToMarkdown(d));

  // Use combined description, or fallback to empty string if none available
  // (allow items with name but no description to be imported)
  const description = descriptions.length > 0 ? descriptions.join('\n\n') : '';

  // Build alternate name from name2, second_name, third_name
  const alternateNames = [text.name2, text.second_name, text.third_name]
    .filter((n): n is string => !!n && n.trim() !== '')
    .map((n) => convertHtmlToMarkdown(n));
  let alternateName = alternateNames.length > 0 ? alternateNames.join('; ') : null;

  // Truncate if needed
  if (alternateName && alternateName.length > 255) {
    alternateName = alternateName.substring(0, 252) + '...';
  }

  // Build dates string
  const datesParts: string[] = [];
  if (text.date_description) {
    datesParts.push(convertHtmlToMarkdown(text.date_description));
  }
  if (text.dynasty) {
    datesParts.push(`Dynasty: ${convertHtmlToMarkdown(text.dynasty)}`);
  }
  const dates = datesParts.length > 0 ? datesParts.join('; ') : null;

  // Build holder info from institution
  const holder =
    text.institution || text.responsible_institution_org
      ? convertHtmlToMarkdown(text.institution || text.responsible_institution_org || '')
      : null;

  // Build location
  const locationParts = [text.location, text.province]
    .filter((l): l is string => !!l && l.trim() !== '')
    .map((l) => convertHtmlToMarkdown(l));
  const location = locationParts.length > 0 ? locationParts.join(', ') : null;

  // Build type (with truncation)
  let type = text.typeof ? convertHtmlToMarkdown(text.typeof) : null;
  if (type && type.length > 255) {
    type = type.substring(0, 252) + '...';
  }

  // Build extra JSON for translation-specific fields
  const extra: Record<string, unknown> = {};

  // Monument contact information (language-specific visitor info)
  const monumentContact: Record<string, string> = {};
  if (text.address) monumentContact.address = convertHtmlToMarkdown(text.address);
  if (text.phone) monumentContact.phone = text.phone;
  if (text.fax) monumentContact.fax = text.fax;
  if (text.email) monumentContact.email = text.email;
  // institution is already used for holder, but store responsible_institution_org if different
  if (text.responsible_institution_org && text.responsible_institution_org !== text.institution) {
    monumentContact.responsible_institution = convertHtmlToMarkdown(text.responsible_institution_org);
  }
  if (Object.keys(monumentContact).length > 0) {
    extra.monument_contact = monumentContact;
  }

  // Additional descriptive fields
  if (text.patrons) extra.patrons = convertHtmlToMarkdown(text.patrons);
  if (text.architects) extra.architects = convertHtmlToMarkdown(text.architects);
  if (text.datationmethod) extra.datation_method = convertHtmlToMarkdown(text.datationmethod);
  if (text.external_sources) extra.external_sources = convertHtmlToMarkdown(text.external_sources);
  if (text.linkcatalogs) extra.linkcatalogs = text.linkcatalogs;

  // Notice fields
  if (text.notice) extra.notice = convertHtmlToMarkdown(text.notice);
  if (text.notice_b) extra.notice_b = convertHtmlToMarkdown(text.notice_b);
  if (text.notice_c) extra.notice_c = convertHtmlToMarkdown(text.notice_c);
  if (text.copyright) extra.copyright = text.copyright;

  const extraField = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

  const data: Omit<ItemTranslationData, 'item_id' | 'context_id' | 'backward_compatibility'> = {
    language_id: languageId,
    name: convertHtmlToMarkdown(name),
    description,
    alternate_name: alternateName,
    type,
    holder,
    owner: null,
    initial_owner: null,
    dates,
    location,
    bibliography: text.bibliography ? convertHtmlToMarkdown(text.bibliography) : null,
    extra: extraField,
  };

  return {
    data,
    languageId,
  };
}
