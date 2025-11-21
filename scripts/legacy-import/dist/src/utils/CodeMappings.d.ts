/**
 * Centralized code mappings for legacy data import
 * These mappings convert legacy 2-character codes to ISO standard 3-character codes
 */
/**
 * Map legacy 2-character ISO 639-1 language codes to 3-character ISO 639-3 codes
 */
export declare const LANGUAGE_CODE_MAP: Record<string, string>;
/**
 * Map legacy 2-character country codes to 3-character ISO 3166-1 alpha-3 codes
 *
 * NOTE: Some legacy codes are non-standard (e.g., 'ab' for Albania instead of standard 'al')
 * These mappings are based on actual data in mwnf3.countrynames table
 */
export declare const COUNTRY_CODE_MAP: Record<string, string>;
/**
 * Map legacy 2-character language code to 3-character ISO 639-3 code
 * Throws error if code is not found
 */
export declare function mapLanguageCode(legacyCode: string): string;
/**
 * Map legacy 2-character country code to 3-character ISO 3166-1 alpha-3 code
 * Throws error if code is not found
 */
export declare function mapCountryCode(legacyCode: string): string;
//# sourceMappingURL=CodeMappings.d.ts.map