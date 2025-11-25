/**
 * Centralized code mappings for legacy data import
 * These mappings convert legacy 2-character codes to ISO standard 3-character codes
 */

/**
 * Map LEGACY 2-character language codes (from mwnf3 `lang` fields) to ISO 639-3 codes
 * This maps the ACTUAL codes used in the legacy database
 * Many match ISO 639-1 standards, but some are custom (e.g., 'se' for Swedish instead of 'sv')
 */
export const LANGUAGE_CODE_MAP: Record<string, string> = {
  // Legacy codes that match ISO 639-1 standards
  ar: 'ara', // Arabic
  cs: 'ces', // Czech
  de: 'deu', // German
  en: 'eng', // English
  es: 'spa', // Spanish
  fa: 'fas', // Farsi/Persian
  fr: 'fra', // French
  he: 'heb', // Hebrew
  hr: 'hrv', // Croatian
  hu: 'hun', // Hungarian
  it: 'ita', // Italian
  ja: 'jpn', // Japanese
  pt: 'por', // Portuguese
  ru: 'rus', // Russian
  tr: 'tur', // Turkish
  zh: 'zho', // Chinese
  el: 'ell', // Greek

  // Legacy-specific non-standard codes
  ch: 'zho', // Chinese (legacy: 'ch', standard ISO: 'zh')
  se: 'swe', // Swedish (legacy: 'se', standard ISO: 'sv')
  si: 'slv', // Slovenian (legacy: 'si', standard ISO: 'sl')
};

/**
 * Map legacy 2-character country codes to 3-character ISO 3166-1 alpha-3 codes
 *
 * NOTE: Some legacy codes are non-standard (e.g., 'ab' for Albania instead of standard 'al')
 * These mappings are based on actual data in mwnf3.countrynames table
 */
/**
 * Map LEGACY 2-character country codes (from mwnf3.countrynames) to ISO 3166-1 alpha-3 codes
 * This maps the ACTUAL codes used in the legacy database, NOT standard ISO codes
 * Many legacy codes are non-standard custom codes that differ from ISO 3166-1 alpha-2
 */
export const COUNTRY_CODE_MAP: Record<string, string> = {
  // Legacy codes that happen to match ISO 3166-1 alpha-2 standards
  at: 'aut', // Austria
  az: 'aze', // Azerbaijan
  be: 'bel', // Belgium
  br: 'bra', // Brazil
  ca: 'can', // Canada
  cz: 'cze', // Czech Republic
  de: 'deu', // Germany
  dz: 'dza', // Algeria
  eg: 'egy', // Egypt
  es: 'esp', // Spain
  fr: 'fra', // France
  gr: 'grc', // Greece
  hr: 'hrv', // Croatia
  hu: 'hun', // Hungary
  iq: 'irq', // Iraq
  jo: 'jor', // Jordan
  jp: 'jpn', // Japan
  lb: 'lbn', // Lebanon
  ly: 'lby', // Libya
  ma: 'mar', // Morocco
  pl: 'pol', // Poland
  pt: 'prt', // Portugal
  ro: 'rou', // Romania
  ru: 'rus', // Russia
  sa: 'sau', // Saudi Arabia
  sy: 'syr', // Syria
  tn: 'tun', // Tunisia
  tr: 'tur', // Turkey

  // Legacy-specific non-standard codes  
  ab: 'alb', // Albania (legacy: 'ab', standard ISO: 'al')
  ag: 'arg', // Argentina (legacy: 'ag', standard ISO: 'ar')
  al: 'aus', // Australia (legacy: 'al', standard ISO: 'au')
  bg: 'bgd', // Bangladesh (legacy: 'bg', standard ISO: 'bd')
  bh: 'bhr', // Bahrain (legacy: 'bh', standard ISO: 'bh' - same)
  bl: 'blr', // Belarus (legacy: 'bl', standard ISO: 'by')
  bs: 'bih', // Bosnia-Herzegovina (legacy: 'bs', standard ISO: 'ba')
  bu: 'bgr', // Bulgaria (legacy: 'bu', standard ISO: 'bg')
  ch: 'chn', // China (legacy: 'ch', standard ISO: 'cn')
  co: 'com', // Comoros (legacy: 'co', standard ISO: 'km')
  cy: 'cyp', // Cyprus (legacy: 'cy', standard ISO: 'cy' - same)
  dj: 'dji', // Djibouti (legacy: 'dj', standard ISO: 'dj' - same)
  dn: 'dnk', // Denmark (legacy: 'dn', standard ISO: 'dk')
  et: 'est', // Estonia (legacy: 'et', standard ISO: 'ee')
  fn: 'fin', // Finland (legacy: 'fn', standard ISO: 'fi')
  ge: 'geo', // Georgia (legacy: 'ge', standard ISO: 'ge' - same)
  ia: 'irn', // Iran (legacy: 'ia', standard ISO: 'ir')
  is: 'isr', // Israel (legacy: 'is', standard ISO: 'il')
  ix: 'ita', // Italy/Sicily (legacy: 'ix' for regional variant)
  ln: 'ltu', // Lithuania (legacy: 'ln', standard ISO: 'lt')
  lt: 'lva', // Latvia (legacy: 'lt', standard ISO: 'lv')
  lx: 'lux', // Luxembourg (legacy: 'lx', standard ISO: 'lu')
  mc: 'mkd', // North Macedonia (legacy: 'mc', standard ISO: 'mk')
  md: 'mda', // Moldova (legacy: 'md', standard ISO: 'md' - same)
  ml: 'mlt', // Malta (legacy: 'ml', standard ISO: 'mt')
  mn: 'mne', // Montenegro (legacy: 'mn', standard ISO: 'me')
  mt: 'mrt', // Mauritania (legacy: 'mt', standard ISO: 'mr')
  nt: 'nld', // Netherlands (legacy: 'nt', standard ISO: 'nl')
  on: 'omn', // Oman (legacy: 'on', standard ISO: 'om')
  pa: 'pse', // Palestine (legacy: 'pa', standard ISO: 'ps')
  pd: 'zzzpd', // Public domain (special: no specific country)
  px: 'pse', // Palestinian Territories (alternative)
  qt: 'qat', // Qatar (legacy: 'qt', standard ISO: 'qa')
  rm: 'rou', // Romania (legacy: 'rm', standard ISO: 'ro')
  sb: 'srb', // Serbia (legacy: 'sb', standard ISO: 'rs')
  sd: 'sdn', // Sudan (legacy: 'sd', standard ISO: 'sd' - same)
  sf: 'zaf', // South Africa (legacy: 'sf', standard ISO: 'za')
  sl: 'svk', // Slovakia (legacy: 'sl', standard ISO: 'sk')
  so: 'som', // Somalia (legacy: 'so', standard ISO: 'so' - same)
  sw: 'che', // Switzerland (legacy: 'sw', standard ISO: 'ch')
  uc: 'ukr', // Ukraine (legacy: 'uc', standard ISO: 'ua')
  uk: 'gbr', // United Kingdom (legacy: 'uk', standard ISO: 'gb')
  va: 'vat', // Vatican City (legacy: 'va', standard ISO: 'va' - same)
  ww: 'zzzww', // Other/Worldwide (special: no specific country)
  ym: 'yem', // Yemen (legacy: 'ym', standard ISO: 'ye')
};

/**
 * Map legacy 2-character language code to 3-character ISO 639-3 code
 * Throws error if code is not found
 */
export function mapLanguageCode(legacyCode: string): string {
  const mapped = LANGUAGE_CODE_MAP[legacyCode];
  if (!mapped) {
    throw new Error(
      `Unknown language code '${legacyCode}'. Add mapping to LANGUAGE_CODE_MAP in CodeMappings.ts`
    );
  }
  return mapped;
}

/**
 * Map legacy 2-character country code to 3-character ISO 3166-1 alpha-3 code
 * Throws error if code is not found
 */
export function mapCountryCode(legacyCode: string): string {
  const mapped = COUNTRY_CODE_MAP[legacyCode];
  if (!mapped) {
    throw new Error(
      `Unknown country code '${legacyCode}'. Add mapping to COUNTRY_CODE_MAP in CodeMappings.ts`
    );
  }
  return mapped;
}
