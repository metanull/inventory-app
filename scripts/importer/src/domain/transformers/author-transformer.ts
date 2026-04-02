/**
 * Author Transformer
 *
 * Transforms legacy author data into inventory-app format.
 */

import type { AuthorData, AuthorTranslationData } from '../../core/types.js';
import type { LegacyAuthor, LegacyAuthorCv, LegacyShAuthorCv } from '../types/index.js';

export interface TransformedAuthor {
  data: AuthorData;
  backwardCompatibility: string;
}

export interface TransformedAuthorTranslation {
  data: Omit<AuthorTranslationData, 'author_id'>;
}

/**
 * Build display name from name parts
 */
function buildDisplayName(legacy: LegacyAuthor): string {
  const parts: string[] = [];
  if (legacy.firstname) parts.push(legacy.firstname.trim());
  if (legacy.lastname) parts.push(legacy.lastname.trim());
  if (parts.length === 0 && legacy.givenname) parts.push(legacy.givenname.trim());
  return parts.join(' ') || `Author ${legacy.author_id}`;
}

/**
 * Transform a legacy author into AuthorData
 */
export function transformAuthor(
  legacy: LegacyAuthor,
  schema: string = 'mwnf3',
  table: string = 'authors'
): TransformedAuthor {
  const backwardCompatibility = `${schema}:${table}:${legacy.author_id}`;
  const displayName = buildDisplayName(legacy);

  return {
    data: {
      name: displayName,
      firstname: legacy.firstname?.trim() ?? null,
      lastname: legacy.lastname?.trim() ?? null,
      givenname: legacy.givenname?.trim() ?? null,
      originalname: legacy.originalname?.trim() ?? null,
      internal_name: displayName,
      backward_compatibility: backwardCompatibility,
    },
    backwardCompatibility,
  };
}

/**
 * Transform a legacy author CV into AuthorTranslationData
 */
export function transformAuthorCv(
  legacy: LegacyAuthorCv,
  schema: string = 'mwnf3'
): TransformedAuthorTranslation {
  const backwardCompatibility = `${schema}:authors_cv:${legacy.author_id}:${legacy.project_id}:${legacy.lang_id}`;

  return {
    data: {
      language_id: '', // Will be resolved by importer
      context_id: '', // Will be resolved by importer
      curriculum: legacy.curriculum ?? null,
      backward_compatibility: backwardCompatibility,
    },
  };
}

/**
 * Transform a legacy SH author CV into AuthorTranslationData
 */
export function transformShAuthorCv(legacy: LegacyShAuthorCv): TransformedAuthorTranslation {
  const backwardCompatibility = `mwnf3_sharing_history:sh_authors_cv:${legacy.author_id}:${legacy.project_id}:${legacy.lang}`;

  return {
    data: {
      language_id: '', // Will be resolved by importer
      context_id: '', // Will be resolved by importer
      curriculum: legacy.curriculum ?? null,
      backward_compatibility: backwardCompatibility,
    },
  };
}
