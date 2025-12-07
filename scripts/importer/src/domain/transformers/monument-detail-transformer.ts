/**
 * Monument Detail Transformer
 *
 * Transforms legacy monument detail data to item entities.
 * Monument details are simpler than monuments with fewer fields.
 */

import type { LegacyMonumentDetail, MonumentDetailGroup } from '../types/index.js';
import type { ItemData, ItemTranslationData } from '../../core/types.js';
import { mapLanguageCode, mapCountryCode } from '../../utils/code-mappings.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import { convertHtmlToMarkdown, stripHtml } from '../../utils/html-to-markdown.js';

/**
 * Transformed monument detail result
 */
export interface TransformedMonumentDetail {
  data: Omit<ItemData, 'collection_id' | 'partner_id' | 'project_id'>;
  backwardCompatibility: string;
  countryId: string;
  parentBackwardCompatibility: string;
  warning: string | null;
}

/**
 * Transformed monument detail translation result
 */
export interface TransformedMonumentDetailTranslation {
  data: Omit<
    ItemTranslationData,
    | 'item_id'
    | 'context_id'
    | 'author_id'
    | 'text_copy_editor_id'
    | 'translator_id'
    | 'translation_copy_editor_id'
  >;
  warnings: string[];
}

/**
 * Tag extraction result for monument details
 */
export interface ExtractedMonumentDetailTags {
  artists: string[];
  languageId: string;
}

/**
 * Group denormalized monument detail rows by non-lang PK columns
 */
export function groupMonumentDetailsByPK(details: LegacyMonumentDetail[]): MonumentDetailGroup[] {
  const groups = new Map<string, MonumentDetailGroup>();

  for (const detail of details) {
    const key = `${detail.project_id}:${detail.country_id}:${detail.institution_id}:${detail.monument_id}:${detail.detail_id}`;

    if (!groups.has(key)) {
      groups.set(key, {
        project_id: detail.project_id,
        country_id: detail.country_id,
        institution_id: detail.institution_id,
        monument_id: detail.monument_id,
        detail_id: detail.detail_id,
        translations: [],
      });
    }

    groups.get(key)!.translations.push(detail);
  }

  return Array.from(groups.values());
}

/**
 * Transform a monument detail group to item data
 * @param group Monument detail group with all translations
 * @param defaultLanguageId Default language ID to use for internal_name
 */
export function transformMonumentDetail(
  group: MonumentDetailGroup,
  defaultLanguageId: string
): TransformedMonumentDetail {
  if (!group.translations || group.translations.length === 0) {
    throw new Error('No translations found for monument detail');
  }

  const backwardCompatibility = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'monument_details',
    pkValues: [
      group.project_id,
      group.country_id,
      group.institution_id,
      group.monument_id,
      group.detail_id,
    ],
  });

  // Build parent monument backward compatibility (without detail_id)
  const parentBackwardCompatibility = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'monuments',
    pkValues: [group.project_id, group.country_id, group.institution_id, group.monument_id],
  });

  const countryId = mapCountryCode(group.country_id);

  // Find translation in default language
  const defaultTranslation = group.translations.find(
    (t) => mapLanguageCode(t.lang_id) === defaultLanguageId
  );

  let selectedTranslation = defaultTranslation;
  let warning: string | null = null;

  if (!defaultTranslation) {
    // Warn and use first available translation
    selectedTranslation = group.translations[0];
    warning = `Monument detail ${backwardCompatibility} has no translation in default language ${defaultLanguageId}, using ${mapLanguageCode(selectedTranslation!.lang_id)} instead`;
  }

  // internal_name must always be converted from selected translation name - no fallback
  if (!selectedTranslation!.name) {
    throw new Error(`Monument detail ${backwardCompatibility} missing required name field`);
  }
  const internalName = stripHtml(selectedTranslation!.name).trim();

  const data: Omit<ItemData, 'collection_id' | 'partner_id' | 'project_id'> = {
    type: 'detail',
    internal_name: internalName,
    owner_reference: null,
    mwnf_reference: null,
    backward_compatibility: backwardCompatibility,
    country_id: countryId,
  };

  return {
    data,
    backwardCompatibility,
    countryId,
    parentBackwardCompatibility,
    warning,
  };
}

/**
 * Transform monument detail translation
 */
export function transformMonumentDetailTranslation(
  detail: LegacyMonumentDetail
): TransformedMonumentDetailTranslation | null {
  const languageId = mapLanguageCode(detail.lang_id);
  const warnings: string[] = [];

  // Skip if description is empty
  if (!detail.description || !detail.description.trim()) {
    return null;
  }

  // Convert HTML fields to Markdown
  // Name is required for ItemTranslationData
  const nameMarkdown = detail.name
    ? convertHtmlToMarkdown(detail.name)
    : convertHtmlToMarkdown(detail.description).slice(0, 100);
  const descriptionMarkdown = convertHtmlToMarkdown(detail.description);
  const locationMarkdown = detail.location ? convertHtmlToMarkdown(detail.location) : null;
  const datesMarkdown = detail.date ? convertHtmlToMarkdown(detail.date) : null;

  // backward_compatibility matches parent item
  const backwardCompatibility = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'monument_details',
    pkValues: [
      detail.project_id,
      detail.country_id,
      detail.institution_id,
      detail.monument_id,
      detail.detail_id,
    ],
  });

  const data: Omit<
    ItemTranslationData,
    | 'item_id'
    | 'context_id'
    | 'author_id'
    | 'text_copy_editor_id'
    | 'translator_id'
    | 'translation_copy_editor_id'
  > = {
    language_id: languageId,
    backward_compatibility: backwardCompatibility,
    name: nameMarkdown,
    description: descriptionMarkdown,
    alternate_name: null,
    type: null,
    holder: null,
    owner: null,
    initial_owner: null,
    dates: datesMarkdown,
    location: locationMarkdown,
    dimensions: null,
    place_of_production: null,
    method_for_datation: null,
    method_for_provenance: null,
    obtention: null,
    bibliography: null,
    extra: null,
  };

  return {
    data,
    warnings,
  };
}

/**
 * Extract artists from monument detail data
 * Artists are stored as text that may contain multiple artists
 */
export function extractMonumentDetailTags(
  detail: LegacyMonumentDetail
): ExtractedMonumentDetailTags {
  const languageId = mapLanguageCode(detail.lang_id);

  // Parse artist field (similar to keywords but for artists)
  const artists: string[] = [];
  if (detail.artist && detail.artist.trim()) {
    // Split by common delimiters
    const rawArtists = detail.artist
      .split(/[;,\n]/)
      .map((a) => a.trim())
      .filter((a) => a.length > 0);
    artists.push(...rawArtists);
  }

  return {
    artists,
    languageId,
  };
}
