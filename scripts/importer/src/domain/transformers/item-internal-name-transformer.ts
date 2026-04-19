import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';

export interface ItemInternalNameCandidate {
  languageId: string;
  value: string | null;
}

export interface SelectedItemInternalName {
  internalName: string;
  warning: string | null;
}

function hasDisplayName(value: string | null): value is string {
  if (typeof value !== 'string') {
    return false;
  }

  if (value.trim() === '') {
    return false;
  }

  return true;
}

export function selectItemInternalName(
  candidates: ItemInternalNameCandidate[],
  defaultLanguageId: string,
  entityLabel: string,
  backwardCompatibility: string
): SelectedItemInternalName {
  let defaultLanguageCandidate: ItemInternalNameCandidate | null = null;

  for (const candidate of candidates) {
    if (candidate.languageId === defaultLanguageId && hasDisplayName(candidate.value)) {
      defaultLanguageCandidate = candidate;
      break;
    }
  }

  if (defaultLanguageCandidate !== null) {
    return {
      internalName: convertHtmlToMarkdown(defaultLanguageCandidate.value),
      warning: null,
    };
  }

  let fallbackCandidate: ItemInternalNameCandidate | null = null;

  for (const candidate of candidates) {
    if (hasDisplayName(candidate.value)) {
      fallbackCandidate = candidate;
      break;
    }
  }

  if (fallbackCandidate !== null) {
    return {
      internalName: convertHtmlToMarkdown(fallbackCandidate.value),
      warning: `${entityLabel} ${backwardCompatibility} has no translation with a name in default language ${defaultLanguageId}, using ${fallbackCandidate.languageId} instead`,
    };
  }

  throw new Error(`${entityLabel} ${backwardCompatibility} missing required name field in all translations`);
}