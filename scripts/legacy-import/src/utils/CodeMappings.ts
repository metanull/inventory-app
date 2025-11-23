/**
 * Centralized code mappings for legacy data import
 * These mappings convert legacy 2-character codes to ISO standard 3-character codes
 */

/**
 * Map legacy 2-character ISO 639-1 language codes to 3-character ISO 639-3 codes
 */
export const LANGUAGE_CODE_MAP: Record<string, string> = {
  en: 'eng',
  fr: 'fra',
  es: 'spa',
  de: 'deu',
  it: 'ita',
  pt: 'por',
  ar: 'ara',
  ru: 'rus',
  zh: 'zho',
  ja: 'jpn',
  tr: 'tur',
  el: 'ell', // Greek
  ch: 'zho', // Chinese (non-standard code, should be 'zh')
  cs: 'ces', // Czech
  fa: 'fas', // Farsi/Persian
  he: 'heb', // Hebrew
  hr: 'hrv', // Croatian
  hu: 'hun', // Hungarian
  se: 'swe', // Swedish
  si: 'slv', // Slovenian
};

/**
 * Map legacy 2-character country codes to 3-character ISO 3166-1 alpha-3 codes
 *
 * NOTE: Some legacy codes are non-standard (e.g., 'ab' for Albania instead of standard 'al')
 * These mappings are based on actual data in mwnf3.countrynames table
 */
export const COUNTRY_CODE_MAP: Record<string, string> = {
  // Standard ISO 3166-1 alpha-2 codes
  ae: 'are', // United Arab Emirates
  al: 'alb', // Albania
  ar: 'arg', // Argentina
  at: 'aut', // Austria
  au: 'aus', // Australia
  az: 'aze', // Azerbaijan
  ba: 'bih', // Bosnia and Herzegovina
  be: 'bel', // Belgium
  bg: 'bgr', // Bulgaria
  br: 'bra', // Brazil
  ca: 'can', // Canada
  ch: 'che', // Switzerland
  cl: 'chl', // Chile
  cn: 'chn', // China
  cz: 'cze', // Czech Republic
  de: 'deu', // Germany
  dk: 'dnk', // Denmark
  dz: 'dza', // Algeria
  eg: 'egy', // Egypt
  es: 'esp', // Spain
  fi: 'fin', // Finland
  fr: 'fra', // France
  gb: 'gbr', // United Kingdom
  gr: 'grc', // Greece
  hr: 'hrv', // Croatia
  hu: 'hun', // Hungary
  ie: 'irl', // Ireland
  il: 'isr', // Israel
  in: 'ind', // India
  iq: 'irq', // Iraq
  ir: 'irn', // Iran
  it: 'ita', // Italy
  jo: 'jor', // Jordan
  jp: 'jpn', // Japan
  kr: 'kor', // Korea (South)
  kw: 'kwt', // Kuwait
  lb: 'lbn', // Lebanon
  ly: 'lby', // Libya
  ma: 'mar', // Morocco
  mx: 'mex', // Mexico
  my: 'mys', // Malaysia
  nl: 'nld', // Netherlands
  no: 'nor', // Norway
  nw: 'nor', // Norway (alternative code)
  om: 'omn', // Oman
  pl: 'pol', // Poland
  pt: 'prt', // Portugal
  qa: 'qat', // Qatar
  ro: 'rou', // Romania
  rs: 'srb', // Serbia
  ru: 'rus', // Russia
  sa: 'sau', // Saudi Arabia
  se: 'swe', // Sweden
  si: 'svn', // Slovenia
  sk: 'svk', // Slovakia
  sy: 'syr', // Syria
  tn: 'tun', // Tunisia
  tr: 'tur', // Turkey
  ua: 'ukr', // Ukraine
  us: 'usa', // United States
  ye: 'yem', // Yemen
  za: 'zaf', // South Africa

  // Non-standard legacy codes (from mwnf3.countrynames)
  ab: 'alb', // Albania (non-standard code)
  dn: 'dnk', // Denmark (non-standard code)
  on: 'omn', // Oman (non-standard code)
  pa: 'pse', // Palestine
  qt: 'qat', // Qatar (non-standard code)
  rm: 'rou', // Romania (non-standard code)
  sb: 'srb', // Serbia (non-standard code)
  sw: 'che', // Switzerland (non-standard code)
  uc: 'ukr', // Ukraine (non-standard code)
  uk: 'gbr', // United Kingdom (non-standard code)
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
