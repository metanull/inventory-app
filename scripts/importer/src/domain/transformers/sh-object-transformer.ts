/**
 * SH Object Transformer
 *
 * Transforms Sharing History object data to the new format.
 * Key differences from mwnf3:
 * - PK: (project_id, country, number) - no partner in PK
 * - Partner linked via FK `partners_id`
 * - pd_country stored in extra JSON as `country_id_present_days`
 */

import type { ShLegacyObject, ShLegacyObjectText, ShObjectGroup } from '../types/index.js';
import type { ItemData, ItemTranslationData } from '../../core/types.js';
import { mapCountryCode, mapLanguageCode } from '../../utils/code-mappings.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';
import { formatShBackwardCompatibility } from './sh-project-transformer.js';

const SH_OBJECTS_TABLE = 'sh_objects';

/**
 * Transformed SH Object
 */
export interface TransformedShObject {
  data: Omit<ItemData, 'partner_id' | 'collection_id' | 'project_id'>;
  backwardCompatibility: string;
  warning?: string;
}

/**
 * Transformed SH Object Translation
 */
export interface TransformedShObjectTranslation {
  data: Omit<ItemTranslationData, 'item_id' | 'context_id' | 'backward_compatibility'>;
  languageId: string;
}

/**
 * Group SH objects by primary key
 */
export function groupShObjectsByPK(
  objects: ShLegacyObject[],
  objectTexts: ShLegacyObjectText[]
): ShObjectGroup[] {
  // Create a map of translations by PK
  const translationMap = new Map<string, ShLegacyObjectText[]>();

  for (const text of objectTexts) {
    const key = `${text.project_id}:${text.country}:${text.number}`;
    if (!translationMap.has(key)) {
      translationMap.set(key, []);
    }
    translationMap.get(key)!.push(text);
  }

  // Group objects with their translations
  return objects.map((obj) => {
    const key = `${obj.project_id}:${obj.country}:${obj.number}`;
    return {
      project_id: obj.project_id,
      country: obj.country,
      number: obj.number,
      partners_id: obj.partners_id,
      working_number: obj.working_number,
      inventory_id: obj.inventory_id,
      start_date: obj.start_date,
      end_date: obj.end_date,
      display_status: obj.display_status,
      pd_country: obj.pd_country,
      translations: translationMap.get(key) || [],
    };
  });
}

/**
 * Transform a SH object group to Item
 */
export function transformShObject(
  group: ShObjectGroup,
  defaultLanguageId: string
): TransformedShObject {
  const backwardCompat = formatShBackwardCompatibility(
    SH_OBJECTS_TABLE,
    group.project_id,
    group.country,
    group.number
  );

  // Find default language translation for internal_name
  let internalName = `SH Object ${group.project_id}:${group.country}:${group.number}`;
  let warning: string | undefined;

  const defaultTranslation = group.translations.find(
    (t) => mapLanguageCode(t.lang) === defaultLanguageId
  );

  if (defaultTranslation?.name) {
    internalName = convertHtmlToMarkdown(defaultTranslation.name);
  } else if (group.translations.length > 0 && group.translations[0].name) {
    // Fallback to first available translation
    internalName = convertHtmlToMarkdown(group.translations[0].name);
    warning = `SH Object ${backwardCompat} missing translation in default language, using ${group.translations[0].lang}`;
  }

  const data: Omit<ItemData, 'partner_id' | 'collection_id' | 'project_id'> = {
    internal_name: internalName,
    type: 'object',
    backward_compatibility: backwardCompat,
    country_id: mapCountryCode(group.country),
    parent_id: null,
    owner_reference: group.inventory_id || null,
    mwnf_reference: group.working_number || null,
  };

  return {
    data,
    backwardCompatibility: backwardCompat,
    warning,
  };
}

/**
 * Transform a SH object text to ItemTranslation
 * Returns null if required fields (name, description) are missing
 */
export function transformShObjectTranslation(
  text: ShLegacyObjectText
): TransformedShObjectTranslation | null {
  const languageId = mapLanguageCode(text.lang);

  // Validate required fields
  const name = text.name?.trim() || null;
  if (!name) {
    return null;
  }

  // Build description combining description and description2
  const descriptions = [text.description, text.description2]
    .filter((d): d is string => !!d && d.trim() !== '')
    .map((d) => convertHtmlToMarkdown(d));

  if (descriptions.length === 0) {
    return null;
  }

  const description = descriptions.join('\n\n');

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

  // Build owner info
  const owner = text.current_owner ? convertHtmlToMarkdown(text.current_owner) : null;
  const initialOwner = text.original_owner ? convertHtmlToMarkdown(text.original_owner) : null;
  const holder =
    text.holding_museum || text.holding_institution_org
      ? convertHtmlToMarkdown(text.holding_museum || text.holding_institution_org || '')
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
  if (text.archival) extra.archival = convertHtmlToMarkdown(text.archival);
  if (text.provenance) extra.provenance = convertHtmlToMarkdown(text.provenance);
  if (text.dimensions) extra.dimensions = convertHtmlToMarkdown(text.dimensions);
  if (text.materials) extra.materials = convertHtmlToMarkdown(text.materials);
  if (text.artist) extra.artist = convertHtmlToMarkdown(text.artist);
  if (text.birthdate) extra.artist_birthdate = text.birthdate;
  if (text.birthplace) extra.artist_birthplace = convertHtmlToMarkdown(text.birthplace);
  if (text.deathdate) extra.artist_deathdate = text.deathdate;
  if (text.deathplace) extra.artist_deathplace = convertHtmlToMarkdown(text.deathplace);
  if (text.period_activity) extra.period_activity = convertHtmlToMarkdown(text.period_activity);
  if (text.production_place) extra.production_place = convertHtmlToMarkdown(text.production_place);
  if (text.workshop) extra.workshop = convertHtmlToMarkdown(text.workshop);
  if (text.datationmethod) extra.datation_method = convertHtmlToMarkdown(text.datationmethod);
  if (text.provenancemethod) extra.provenance_method = convertHtmlToMarkdown(text.provenancemethod);
  if (text.obtentionmethod) extra.obtention_method = convertHtmlToMarkdown(text.obtentionmethod);
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
    owner,
    initial_owner: initialOwner,
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
