/**
 * Utils Module Exports
 */

export {
  formatBackwardCompatibility,
  parseBackwardCompatibility,
  formatDenormalizedBackwardCompatibility,
  formatImageBackwardCompatibility,
  type BackwardCompatibilityRef,
} from './backward-compatibility.js';

export {
  LANGUAGE_CODE_MAP,
  COUNTRY_CODE_MAP,
  mapLanguageCode,
  mapCountryCode,
} from './code-mappings.js';

export { convertHtmlToMarkdown, convertHtmlFieldsToMarkdown } from './html-to-markdown.js';
