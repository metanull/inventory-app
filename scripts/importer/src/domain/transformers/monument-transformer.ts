/**
 * Monument Transformer
 *
 * Transforms legacy monument data to item entities.
 * Similar to ObjectTransformer but for monuments.
 */

import type { LegacyMonument, MonumentGroup } from '../types/index.js';
import type { ItemData, ItemTranslationData } from '../../core/types.js';
import { mapLanguageCode, mapCountryCode } from '../../utils/code-mappings.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';
import { parseTagString } from './object-transformer.js';

/**
 * Transformed monument result
 */
export interface TransformedMonument {
  data: Omit<ItemData, 'collection_id' | 'partner_id' | 'project_id'>;
  backwardCompatibility: string;
  countryId: string;
}

/**
 * Transformed monument translation result
 */
export interface TransformedMonumentTranslation {
  data: Omit<
    ItemTranslationData,
    | 'item_id'
    | 'context_id'
    | 'author_id'
    | 'text_copy_editor_id'
    | 'translator_id'
    | 'translation_copy_editor_id'
  >;
  authorName: string | null;
  textCopyEditorName: string | null;
  translatorName: string | null;
  translationCopyEditorName: string | null;
  warnings: string[];
}

/**
 * Tag extraction result for monuments
 */
export interface ExtractedMonumentTags {
  keywords: string[];
  languageId: string;
}

/**
 * Group denormalized monument rows by non-lang PK columns
 */
export function groupMonumentsByPK(monuments: LegacyMonument[]): MonumentGroup[] {
  const groups = new Map<string, MonumentGroup>();

  for (const monument of monuments) {
    const key = `${monument.project_id}:${monument.country}:${monument.institution_id}:${monument.number}`;

    if (!groups.has(key)) {
      groups.set(key, {
        project_id: monument.project_id,
        country: monument.country,
        institution_id: monument.institution_id,
        number: monument.number,
        translations: [],
      });
    }

    groups.get(key)!.translations.push(monument);
  }

  return Array.from(groups.values());
}

/**
 * Transform a monument group to item data
 */
export function transformMonument(group: MonumentGroup): TransformedMonument {
  const firstTranslation = group.translations[0];
  if (!firstTranslation) {
    throw new Error('No translations found for monument');
  }

  const backwardCompatibility = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'monuments',
    pkValues: [group.project_id, group.country, group.institution_id, group.number],
  });

  const countryId = mapCountryCode(group.country);

  // internal_name must always be converted from firstTranslation.name - no fallback
  if (!firstTranslation.name) {
    throw new Error(
      `Monument ${group.project_id}:${group.country}:${group.institution_id}:${group.number} missing required name field`
    );
  }
  const internalName = convertHtmlToMarkdown(firstTranslation.name);

  const data: Omit<ItemData, 'collection_id' | 'partner_id' | 'project_id'> = {
    type: 'monument',
    internal_name: internalName,
    owner_reference: firstTranslation.inventory_id || null,
    mwnf_reference: firstTranslation.working_number || null,
    backward_compatibility: backwardCompatibility,
    country_id: countryId,
  };

  return {
    data,
    backwardCompatibility,
    countryId,
  };
}

/**
 * Transform monument translation
 */
export function transformMonumentTranslation(
  monument: LegacyMonument,
  descriptionField: 'description' | 'description2' = 'description'
): TransformedMonumentTranslation | null {
  const languageId = mapLanguageCode(monument.lang);
  const warnings: string[] = [];
  const monumentKey = `${monument.project_id}:${monument.institution_id}:${monument.number}`;

  // Determine which description to use
  const sourceDescription =
    descriptionField === 'description2' ? monument.description2 : monument.description;

  // Skip if description is empty
  if (!sourceDescription || !sourceDescription.trim()) {
    return null;
  }

  // Validate name
  const name = monument.name?.trim() || null;
  if (!name) {
    warnings.push(`${monumentKey}:${monument.lang} - Missing required 'name' field`);
    return null;
  }

  // Convert HTML to Markdown
  const nameMarkdown = convertHtmlToMarkdown(name);
  const descriptionMarkdown = convertHtmlToMarkdown(sourceDescription);
  const bibliographyMarkdown = monument.bibliography
    ? convertHtmlToMarkdown(monument.bibliography)
    : null;

  // Handle alternate_name with truncation
  let alternateNameMarkdown = monument.name2 ? convertHtmlToMarkdown(monument.name2) : null;
  if (alternateNameMarkdown && alternateNameMarkdown.length > 255) {
    warnings.push(
      `${monumentKey}:${monument.lang} - alternate_name truncated (${alternateNameMarkdown.length} → 255 chars)`
    );
    alternateNameMarkdown = alternateNameMarkdown.substring(0, 252) + '...';
  }

  // Handle type with truncation
  let typeMarkdown = monument.typeof ? convertHtmlToMarkdown(monument.typeof) : null;
  if (typeMarkdown && typeMarkdown.length > 255) {
    warnings.push(
      `${monumentKey}:${monument.lang} - type truncated (${typeMarkdown.length} → 255 chars)`
    );
    typeMarkdown = typeMarkdown.substring(0, 252) + '...';
  }

  // Convert other fields
  const ownerMarkdown = monument.current_owner
    ? convertHtmlToMarkdown(monument.current_owner)
    : null;
  const initialOwnerMarkdown = monument.original_owner
    ? convertHtmlToMarkdown(monument.original_owner)
    : null;
  const datesMarkdown = monument.date_description
    ? convertHtmlToMarkdown(monument.date_description)
    : null;
  const methodForDatationMarkdown = monument.datationmethod
    ? convertHtmlToMarkdown(monument.datationmethod)
    : null;

  // Convert location (composed from multiple fields)
  const locationParts = [monument.location, monument.province]
    .filter(Boolean)
    .map((part) => convertHtmlToMarkdown(part));
  const locationMarkdown = locationParts.length > 0 ? locationParts.join(', ') : null;

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
    name: nameMarkdown,
    description: descriptionMarkdown,
    alternate_name: alternateNameMarkdown,
    type: typeMarkdown,
    holder: null,
    owner: ownerMarkdown,
    initial_owner: initialOwnerMarkdown,
    dates: datesMarkdown,
    location: locationMarkdown,
    dimensions: null,
    place_of_production: null,
    method_for_datation: methodForDatationMarkdown,
    method_for_provenance: null,
    obtention: null,
    bibliography: bibliographyMarkdown,
    extra: null,
  };

  return {
    data,
    authorName: monument.preparedby?.trim() || null,
    textCopyEditorName: monument.copyeditedby?.trim() || null,
    translatorName: monument.translationby?.trim() || null,
    translationCopyEditorName: monument.translationcopyeditedby?.trim() || null,
    warnings,
  };
}

/**
 * Extract tags from monument data
 */
export function extractMonumentTags(monument: LegacyMonument): ExtractedMonumentTags {
  const languageId = mapLanguageCode(monument.lang);

  return {
    keywords: parseTagString(monument.keywords),
    languageId,
  };
}

/**
 * Monument translation plan type
 */
export interface MonumentTranslationPlan {
  translation: LegacyMonument;
  contextType: 'own' | 'epm';
  descriptionField: 'description' | 'description2';
}

/**
 * Plan translations for monument with EPM handling
 */
export function planMonumentTranslations(
  group: MonumentGroup,
  hasEpmContext: boolean
): MonumentTranslationPlan[] {
  const plans: MonumentTranslationPlan[] = [];

  for (const translation of group.translations) {
    if (translation.project_id === 'EPM') {
      // EPM project: only use description2
      if (translation.description2 && translation.description2.trim()) {
        plans.push({
          translation,
          contextType: 'own',
          descriptionField: 'description2',
        });
      }
    } else {
      // Other projects: create in own context with description
      if (translation.description && translation.description.trim()) {
        plans.push({
          translation,
          contextType: 'own',
          descriptionField: 'description',
        });
      }

      // Also create EPM translation if description2 exists
      if (translation.description2 && translation.description2.trim() && hasEpmContext) {
        plans.push({
          translation,
          contextType: 'epm',
          descriptionField: 'description2',
        });
      }
    }
  }

  return plans;
}
