import { describe, it, expect, beforeAll, afterAll, beforeEach } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { CountryTranslationImporter } from '../../../src/importers/phase-00/CountryTranslationImporter.js';

interface CountryTranslationSample {
  country: string;
  lang: string;
  name: string;
}

/**
 * Sample-based integration tests for CountryTranslationImporter
 * Tests against real legacy data collected in SQLite samples
 * 
 * IMPORTANT: This importer handles legacy 2-letter codes that don't match ISO standards
 * See DATA_QUALITY_HANDLING.md for the complete code mapping strategy
 */
describe('CountryTranslationImporter - Sample-Based Tests', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  beforeEach(() => {
    helper.setupFoundationData();
  });

  describe('import with success samples', () => {
    it('should import all country translation samples successfully', async () => {
      const mockContext = helper.createMockContext('country_translation');
      const importer = new CountryTranslationImporter(mockContext);

      const result = await importer.import();

      expect(result.success).toBe(true);
      expect(result.imported).toBeGreaterThan(0);
      expect(result.errors).toHaveLength(0);
      
      const samples = helper.loadSamples('country_translation');
      expect(result.imported).toBe(samples.length);
    });

    it('should create translations with correct structure', async () => {
      const mockContext = helper.createMockContext('country_translation', 20);
      const importer = new CountryTranslationImporter(mockContext);

      await importer.import();

      const calls = helper.getApiCalls(
        mockContext.apiClient,
        'countryTranslation.countryTranslationStore'
      );
      
      expect(calls.length).toBeGreaterThan(0);
      calls.forEach((call) => {
        const translationData = call[0] as Record<string, unknown>;
        
        // Required fields
        expect(translationData).toHaveProperty('country_id');
        expect(translationData).toHaveProperty('language_id');
        expect(translationData).toHaveProperty('name');
        
        // Name should not be empty
        expect(translationData.name).toBeTruthy();
        expect(typeof translationData.name).toBe('string');
      });
    });

    it('should successfully map non-standard legacy country codes', async () => {
      // Load samples and check for non-standard codes
      const samples = helper.loadSamples<{ country_id: string; lang: string }>('country_translation');
      
      // Non-standard codes that should be successfully mapped
      const nonStandardCodes = ['ab', 'ag', 'bu', 'ch', 'dn', 'ia', 'ix'];
      const hasNonStandard = samples.some((s) => nonStandardCodes.includes(s.country_id));
      
      if (hasNonStandard) {
        const mockContext = helper.createMockContext('country_translation');
        const importer = new CountryTranslationImporter(mockContext);

        const result = await importer.import();

        // All should be successfully mapped
        expect(result.success).toBe(true);
        expect(result.errors).toHaveLength(0);
        
        const nonStandardSamples = samples.filter((s) => nonStandardCodes.includes(s.country_id));
        console.log(`Successfully imported ${nonStandardSamples.length} translations with non-standard codes`);
      }
    });

    it('should handle special country codes (pd, ww)', async () => {
      const samples = helper.loadSamples<{ country_id: string }>('country_translation');
      const specialSamples = samples.filter((s) => s.country_id === 'pd' || s.country_id === 'ww');
      
      if (specialSamples.length > 0) {
        const mockContext = helper.createMockContextWithQueries([specialSamples]);
        const importer = new CountryTranslationImporter(mockContext);

        const result = await importer.import();

        expect(result.success).toBe(true);
        expect(result.imported).toBe(specialSamples.length);
        
        console.log(`Processed ${specialSamples.length} special country translations (pd/ww)`);
      }
    });
  });

  describe('language coverage', () => {
    it('should have translations in multiple languages', async () => {
      const mockContext = helper.createMockContext('country_translation', 50);
      const importer = new CountryTranslationImporter(mockContext);

      await importer.import();

      const calls = helper.getApiCalls(
        mockContext.apiClient,
        'countryTranslation.countryTranslationStore'
      );
      
      // Extract unique language IDs
      const languageIds = new Set(
        calls.map((call) => {
          const data = call[0] as Record<string, unknown>;
          return data.language_id;
        })
      );
      
      // Should have translations in multiple languages
      expect(languageIds.size).toBeGreaterThan(1);
      
      console.log(`Created translations in ${languageIds.size} different languages`);
    });

    it('should have translations for multiple countries', async () => {
      const mockContext = helper.createMockContext('country_translation', 50);
      const importer = new CountryTranslationImporter(mockContext);

      await importer.import();

      const calls = helper.getApiCalls(
        mockContext.apiClient,
        'countryTranslation.countryTranslationStore'
      );
      
      // Extract unique country IDs
      const countryIds = new Set(
        calls.map((call) => {
          const data = call[0] as Record<string, unknown>;
          return data.country_id;
        })
      );
      
      // Should have translations for multiple countries
      expect(countryIds.size).toBeGreaterThan(5);
      
      console.log(`Created translations for ${countryIds.size} different countries`);
    });
  });

  describe('data quality', () => {
    it('should handle warning samples if present', async () => {
      const warningSamples = helper.loadSamples('country_translation', { reason: 'warning' });
      
      if (warningSamples.length > 0) {
        const mockContext = helper.createMockContextWithQueries([warningSamples]);
        const importer = new CountryTranslationImporter(mockContext);

        const result = await importer.import();

        expect(result.success).toBe(true);
        if (result.warnings) {
          console.log('Warning samples:', result.warnings);
        }
      }
    });
  });

  describe('statistics validation', () => {
    it('should report correct statistics from samples', () => {
      const stats = helper.getReader().getStats();
      
      expect(stats).toHaveProperty('country_translation:success');
      expect(stats['country_translation:success']).toBeGreaterThan(0);
      
      console.log('Country translation sample statistics:', stats);
    });

    it('should verify comprehensive coverage', () => {
      const count = helper.getReader().getCount('country_translation');
      
      // Should have substantial number of country translations
      expect(count).toBeGreaterThan(50);
      
      console.log(`Total country translation samples: ${count}`);
    });
  });
});
