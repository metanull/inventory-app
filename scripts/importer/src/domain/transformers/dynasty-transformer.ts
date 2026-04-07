/**
 * Dynasty Transformer
 *
 * Transforms legacy dynasty data into inventory-app format.
 */

import type { DynastyData, DynastyTranslationData } from '../../core/types.js';
import type { LegacyDynasty, LegacyDynastyText } from '../types/index.js';

export interface TransformedDynasty {
  data: DynastyData;
  backwardCompatibility: string;
}

export interface TransformedDynastyTranslation {
  data: Omit<DynastyTranslationData, 'dynasty_id'>;
}

/**
 * Transform a legacy dynasty into DynastyData
 */
export function transformDynasty(legacy: LegacyDynasty): TransformedDynasty {
  const backwardCompatibility = `mwnf3:dynasties:${legacy.dynasty_id}`;

  return {
    data: {
      backward_compatibility: backwardCompatibility,
      from_ah: legacy.from_ah ?? null,
      to_ah: legacy.to_ah ?? null,
      from_ad: legacy.from_ad ?? null,
      to_ad: legacy.to_ad ?? null,
    },
    backwardCompatibility,
  };
}

/**
 * Transform a legacy dynasty text into DynastyTranslationData
 */
export function transformDynastyTranslation(
  legacy: LegacyDynastyText
): TransformedDynastyTranslation {
  const backwardCompatibility = `mwnf3:dynasty_texts:${legacy.dynasty_id}:${legacy.lang_id}`;

  return {
    data: {
      language_id: '', // Will be resolved by importer
      name: legacy.name ?? null,
      also_known_as: legacy.name2 ?? null,
      area: legacy.area ?? null,
      history: legacy.history ?? null,
      date_description_ah: legacy.datedesc_ah ?? null,
      date_description_ad: legacy.datedesc_ad ?? null,
      backward_compatibility: backwardCompatibility,
    },
  };
}
