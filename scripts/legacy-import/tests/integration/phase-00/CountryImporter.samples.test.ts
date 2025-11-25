import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { CountryImporter } from '../../../src/importers/phase-00/CountryImporter.js';

interface CountrySample {
  id: string;
  internal_name: string;
  iso_code: string;
  backward_compatibility: string;
}

/**
 * Data-driven tests for CountryImporter
 * Validates that each sample is transformed correctly into API calls
 */
describe('CountryImporter - Data Transformation Tests', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  describe('import with success samples', () => {
    it('should import all country samples successfully', async () => {
      const mockContext = helper.createMockContext('country');
      const importer = new CountryImporter(mockContext);

      const result = await importer.import();

      expect(result.success).toBe(true);
      expect(result.imported).toBeGreaterThan(0);
      expect(result.errors).toHaveLength(0);
      
      const samples = helper.loadSamples('country');
      expect(result.imported).toBe(samples.length);

      // Verify backward compatibility tracking
      samples.forEach((country) => {
        const countryId = (country as { id: string }).id;
        const backwardCompat = `mwnf3:countries:${countryId}`;
        expect(helper.getTracker().exists(backwardCompat)).toBe(true);
      });
    });

    it('should create countries with correct ISO codes', async () => {
      const mockContext = helper.createMockContext('country', 10);
      const importer = new CountryImporter(mockContext);

      await importer.import();

      const calls = helper.getApiCalls(mockContext.apiClient, 'country.countryStore');
      
      expect(calls.length).toBeGreaterThan(0);
      calls.forEach((call) => {
        const countryData = call[0] as Record<string, unknown>;
        
        // Required fields
        expect(countryData).toHaveProperty('id');
        expect(countryData).toHaveProperty('internal_name');
        expect(countryData).toHaveProperty('iso_code');
        expect(countryData).toHaveProperty('backward_compatibility');
        
        // Validate ISO code format (3 characters, ISO 3166-1 alpha-3)
        expect(typeof countryData.iso_code).toBe('string');
        expect((countryData.iso_code as string).length).toBe(3);
        expect((countryData.iso_code as string)).toMatch(/^[A-Z]{3}$/);
        
        // Validate backward compatibility format
        expect(countryData.backward_compatibility).toMatch(/^mwnf3:countries:[a-z]{2,3}$/);
      });
    });

    it('should handle non-standard legacy country codes', async () => {
      // Load samples to check if we have non-standard codes
      const samples = helper.loadSamples('country');
      const legacyCodes = samples.map((s) => (s as { id: string }).id);
      
      // Check if we have some known non-standard codes
      const nonStandardCodes = ['ab', 'ag', 'bu', 'ch', 'dn', 'ia', 'ix', 'pd', 'ww'];
      const hasNonStandard = legacyCodes.some((code) => nonStandardCodes.includes(code));
      
      if (hasNonStandard) {
        const mockContext = helper.createMockContext('country');
        const importer = new CountryImporter(mockContext);

        const result = await importer.import();

        // Should successfully map all non-standard codes
        expect(result.success).toBe(true);
        expect(result.errors).toHaveLength(0);
        
        console.log('Successfully imported countries with non-standard codes:', legacyCodes);
      }
    });
  });

  describe('special cases', () => {
    it('should handle special country codes (pd, ww)', async () => {
      const samples = helper.loadSamples('country');
      const specialCodes = samples.filter((s) => {
        const id = (s as { id: string }).id;
        return id === 'pd' || id === 'ww';
      });
      
      if (specialCodes.length > 0) {
        console.log('Found special country codes:', specialCodes.map((s) => (s as { id: string }).id));
        
        const mockContext = helper.createMockContextWithQueries([specialCodes]);
        const importer = new CountryImporter(mockContext);

        const result = await importer.import();

        expect(result.success).toBe(true);
        expect(result.imported).toBe(specialCodes.length);
      }
    });
  });

  describe('statistics validation', () => {
    it('should report correct statistics from samples', () => {
      const stats = helper.getReader().getStats();
      
      expect(stats).toHaveProperty('country:success');
      expect(stats['country:success']).toBeGreaterThan(0);
      
      console.log('Country sample statistics:', stats);
    });

    it('should have diverse country coverage', () => {
      const samples = helper.loadSamples('country');
      const countryIds = samples.map((s) => (s as { id: string }).id);
      
      // Should have many different countries
      expect(countryIds.length).toBeGreaterThan(10);
      
      console.log(`Sampled ${countryIds.length} countries`);
    });
  });
});
