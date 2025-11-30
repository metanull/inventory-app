/**
 * Language Transformer
 *
 * Transforms legacy language data to the new format.
 * This is pure business logic with no dependencies on write strategy.
 */

import type { LegacyLanguage, LegacyLanguageName } from '../types/index.js';
import type { LanguageData, LanguageTranslationData } from '../../core/types.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';

/**
 * Transformed language result
 */
export interface TransformedLanguage {
  data: LanguageData;
  backwardCompatibility: string;
}

/**
 * Transformed language translation result
 */
export interface TransformedLanguageTranslation {
  data: LanguageTranslationData;
}

/**
 * Transform a legacy language to the new format
 */
export function transformLanguage(legacy: LegacyLanguage): TransformedLanguage {
  const iso3Code = mapLanguageCode(legacy.code);

  const backwardCompatibility = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'langs',
    pkValues: [legacy.code],
  });

  const data: LanguageData = {
    id: iso3Code,
    internal_name: legacy.name || iso3Code,
    is_default: legacy.code === 'en', // English is default
    is_enabled: legacy.active === 1 || legacy.active === true,
    backward_compatibility: backwardCompatibility,
  };

  return {
    data,
    backwardCompatibility,
  };
}

/**
 * Transform a legacy language name to language translation
 */
export function transformLanguageTranslation(
  legacy: LegacyLanguageName
): TransformedLanguageTranslation {
  const languageId = mapLanguageCode(legacy.code);
  const targetLanguageId = mapLanguageCode(legacy.lang);

  const data: LanguageTranslationData = {
    language_id: languageId,
    target_language_id: targetLanguageId,
    name: legacy.name,
  };

  return { data };
}
