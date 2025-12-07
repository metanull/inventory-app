/**
 * Object Transformer
 *
 * Transforms legacy object data to item entities.
 * This contains the shared business logic for handling:
 * - Denormalized data (multiple language rows per object)
 * - EPM description2 handling
 * - HTML to Markdown conversion
 * - Field truncation for database limits
 * - Tag and artist extraction
 */

import type { LegacyObject, ObjectGroup } from '../types/index.js';
import type { ItemData, ItemTranslationData } from '../../core/types.js';
import { mapLanguageCode, mapCountryCode } from '../../utils/code-mappings.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import { convertHtmlToMarkdown, stripHtml } from '../../utils/html-to-markdown.js';

/**
 * Transformed object result
 */
export interface TransformedObject {
  data: Omit<ItemData, 'collection_id' | 'partner_id' | 'project_id'>;
  backwardCompatibility: string;
  countryId: string;
  warning: string | null;
}

/**
 * Transformed object translation result
 */
export interface TransformedObjectTranslation {
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
 * Tag extraction result
 */
export interface ExtractedTags {
  materials: string[];
  dynasties: string[];
  keywords: string[];
  languageId: string;
}

/**
 * Artist extraction result
 */
export interface ExtractedArtist {
  name: string;
  birthplace: string | null;
  deathplace: string | null;
  birthdate: string | null;
  deathdate: string | null;
  periodActivity: string | null;
}

/**
 * Group denormalized object rows by non-lang PK columns
 */
export function groupObjectsByPK(objects: LegacyObject[]): ObjectGroup[] {
  const groups = new Map<string, ObjectGroup>();

  for (const obj of objects) {
    const key = `${obj.project_id}:${obj.country}:${obj.museum_id}:${obj.number}`;

    if (!groups.has(key)) {
      groups.set(key, {
        project_id: obj.project_id,
        country: obj.country,
        museum_id: obj.museum_id,
        number: obj.number,
        translations: [],
      });
    }

    groups.get(key)!.translations.push(obj);
  }

  return Array.from(groups.values());
}

/**
 * Transform an object group to item data
 * @param group Object group with all translations
 * @param defaultLanguageId Default language ID to use for internal_name
 */
export function transformObject(group: ObjectGroup, defaultLanguageId: string): TransformedObject {
  if (!group.translations || group.translations.length === 0) {
    throw new Error('No translations found for object');
  }

  const backwardCompatibility = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'objects',
    pkValues: [group.project_id, group.country, group.museum_id, group.number],
  });

  const countryId = mapCountryCode(group.country);

  // Find translation in default language
  const defaultTranslation = group.translations.find(
    (t) => mapLanguageCode(t.lang) === defaultLanguageId
  );

  let selectedTranslation = defaultTranslation;
  let warning: string | null = null;

  if (!defaultTranslation) {
    // Warn and use first available translation
    selectedTranslation = group.translations[0];
    warning = `Object ${backwardCompatibility} has no translation in default language ${defaultLanguageId}, using ${mapLanguageCode(selectedTranslation!.lang)} instead`;
  }

  // internal_name must always be converted from selected translation name - no fallback
  if (!selectedTranslation!.name) {
    throw new Error(`Object ${backwardCompatibility} missing required name field`);
  }
  const internalName = stripHtml(selectedTranslation!.name).trim();

  const data: Omit<ItemData, 'collection_id' | 'partner_id' | 'project_id'> = {
    type: 'object',
    internal_name: internalName,
    owner_reference: selectedTranslation!.inventory_id || null,
    mwnf_reference: selectedTranslation!.working_number || null,
    backward_compatibility: backwardCompatibility,
    country_id: countryId,
  };

  return {
    data,
    backwardCompatibility,
    countryId,
    warning,
  };
}

/**
 * Transform object translation
 * @param obj Legacy object data
 * @param descriptionField Which description field to use
 */
export function transformObjectTranslation(
  obj: LegacyObject,
  descriptionField: 'description' | 'description2' = 'description'
): TransformedObjectTranslation | null {
  const languageId = mapLanguageCode(obj.lang);
  const warnings: string[] = [];
  const objectKey = `${obj.project_id}:${obj.museum_id}:${obj.number}`;

  // Determine which description to use
  const sourceDescription =
    descriptionField === 'description2' ? obj.description2 : obj.description;

  // Skip if description is empty
  if (!sourceDescription || !sourceDescription.trim()) {
    return null;
  }

  // Validate name
  const name = obj.name?.trim() || null;
  if (!name) {
    warnings.push(`${objectKey}:${obj.lang} - Missing required 'name' field`);
    return null;
  }

  // Convert HTML to Markdown
  const nameMarkdown = convertHtmlToMarkdown(name);
  const descriptionMarkdown = convertHtmlToMarkdown(sourceDescription);
  const bibliographyMarkdown = obj.bibliography ? convertHtmlToMarkdown(obj.bibliography) : null;

  // Handle alternate_name with truncation
  let alternateNameMarkdown = obj.name2 ? convertHtmlToMarkdown(obj.name2) : null;
  if (alternateNameMarkdown && alternateNameMarkdown.length > 255) {
    warnings.push(
      `${objectKey}:${obj.lang} - alternate_name truncated (${alternateNameMarkdown.length} → 255 chars)`
    );
    alternateNameMarkdown = alternateNameMarkdown.substring(0, 252) + '...';
  }

  // Handle type with truncation
  let typeMarkdown = obj.typeof ? convertHtmlToMarkdown(obj.typeof) : null;
  if (typeMarkdown && typeMarkdown.length > 255) {
    warnings.push(`${objectKey}:${obj.lang} - type truncated (${typeMarkdown.length} → 255 chars)`);
    typeMarkdown = typeMarkdown.substring(0, 252) + '...';
  }

  // Convert other fields
  const holderMarkdown = obj.holding_museum ? convertHtmlToMarkdown(obj.holding_museum) : null;
  const ownerMarkdown = obj.current_owner ? convertHtmlToMarkdown(obj.current_owner) : null;
  const initialOwnerMarkdown = obj.original_owner
    ? convertHtmlToMarkdown(obj.original_owner)
    : null;
  const datesMarkdown = obj.date_description ? convertHtmlToMarkdown(obj.date_description) : null;
  const dimensionsMarkdown = obj.dimensions ? convertHtmlToMarkdown(obj.dimensions) : null;
  const placeOfProductionMarkdown = obj.production_place
    ? convertHtmlToMarkdown(obj.production_place)
    : null;
  const methodForDatationMarkdown = obj.datationmethod
    ? convertHtmlToMarkdown(obj.datationmethod)
    : null;
  const methodForProvenanceMarkdown = obj.provenancemethod
    ? convertHtmlToMarkdown(obj.provenancemethod)
    : null;
  const obtentionMarkdown = obj.obtentionmethod ? convertHtmlToMarkdown(obj.obtentionmethod) : null;

  // Convert location (composed from multiple fields)
  const locationParts = [obj.location, obj.province]
    .filter(Boolean)
    .map((part) => convertHtmlToMarkdown(part));
  const locationMarkdown = locationParts.length > 0 ? locationParts.join(', ') : null;

  // Build extra field for fields without dedicated columns
  const extraData: Record<string, unknown> = {};
  if (obj.workshop) extraData.workshop = obj.workshop;
  if (obj.copyright) extraData.copyright = obj.copyright;
  if (obj.binding_desc) extraData.binding_desc = obj.binding_desc;
  const extraField = Object.keys(extraData).length > 0 ? JSON.stringify(extraData) : null;

  // backward_compatibility matches parent item
  const backwardCompatibility = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'objects',
    pkValues: [obj.project_id, obj.country, obj.museum_id, obj.number],
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
    alternate_name: alternateNameMarkdown,
    type: typeMarkdown,
    holder: holderMarkdown,
    owner: ownerMarkdown,
    initial_owner: initialOwnerMarkdown,
    dates: datesMarkdown,
    location: locationMarkdown,
    dimensions: dimensionsMarkdown,
    place_of_production: placeOfProductionMarkdown,
    method_for_datation: methodForDatationMarkdown,
    method_for_provenance: methodForProvenanceMarkdown,
    obtention: obtentionMarkdown,
    bibliography: bibliographyMarkdown,
    extra: extraField,
  };

  return {
    data,
    authorName: obj.preparedby?.trim() || null,
    textCopyEditorName: obj.copyeditedby?.trim() || null,
    translatorName: obj.translationby?.trim() || null,
    translationCopyEditorName: obj.translationcopyeditedby?.trim() || null,
    warnings,
  };
}

/**
 * Extract tags from object data
 */
export function extractObjectTags(obj: LegacyObject): ExtractedTags {
  const languageId = mapLanguageCode(obj.lang);

  return {
    materials: parseTagString(obj.materials),
    dynasties: parseTagString(obj.dynasty),
    keywords: parseTagString(obj.keywords),
    languageId,
  };
}

/**
 * Extract artists from object data
 */
export function extractObjectArtists(obj: LegacyObject): ExtractedArtist[] {
  if (!obj.artist || obj.artist.trim() === '') {
    return [];
  }

  // Split by semicolon to handle multiple artists
  const artistNames = obj.artist
    .split(';')
    .map((name) => name.trim())
    .filter((name) => name !== '');

  return artistNames.map((name) => ({
    name,
    birthplace: obj.birthplace || null,
    deathplace: obj.deathplace || null,
    birthdate: obj.birthdate || null,
    deathdate: obj.deathdate || null,
    periodActivity: obj.period_activity || null,
  }));
}

/**
 * Parse tag string - handles structured data and simple lists
 * IMPORTANT: Fields with colons are STRUCTURED DATA
 * - If colon found: treat as single structured tag (don't split)
 * - Otherwise: split by semicolon (;) primary, comma (,) fallback
 */
export function parseTagString(tagString: string | undefined | null): string[] {
  if (!tagString || tagString.trim() === '') {
    return [];
  }

  // Check if this is structured data (contains colon)
  const isStructured = tagString.includes(':');

  if (isStructured) {
    // Structured data - keep as single tag
    return [tagString.trim()];
  }

  // Simple list - use semicolon as primary separator, comma as fallback
  const separator = tagString.includes(';') ? ';' : ',';
  return tagString
    .split(separator)
    .map((t) => t.trim())
    .filter((t) => t !== '');
}

/**
 * Determine which translations need to be created for EPM handling
 *
 * EPM (European Project Mediterranean) logic:
 * - For EPM project: only use description2 as description
 * - For other projects:
 *   - Create translation in own context using description
 *   - If description2 exists and EPM context exists, create EPM translation
 */
export interface TranslationPlan {
  translation: LegacyObject;
  contextType: 'own' | 'epm';
  descriptionField: 'description' | 'description2';
}

export function planTranslations(group: ObjectGroup, hasEpmContext: boolean): TranslationPlan[] {
  const plans: TranslationPlan[] = [];

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
