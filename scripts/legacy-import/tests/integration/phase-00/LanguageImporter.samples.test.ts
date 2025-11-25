import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { LanguageImporter } from '../../../src/importers/phase-00/LanguageImporter.js';

interface LanguageSample {
  id: string;
  internal_name: string;
  iso_code: string;
  backward_compatibility: string;
  is_default: boolean;
}

/**
 * Data-driven tests for LanguageImporter
 * Validates that each sample record is transformed correctly and produces the correct API calls
 */
describe('LanguageImporter - Sample-Based Tests', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  describe('data transformation validation', () => {
    it('should transform each language sample into correct API call', async () => {
      const samples = helper.loadSamples<LanguageSample>('language');
      
      if (samples.length === 0) {
        console.log('⚠️  No language samples found - skipping test');
        return;
      }

      const mockContext = helper.createMockContextWithQueries([samples]);
      const importer = new LanguageImporter(mockContext);

      await importer.import();

      const calls = helper.getApiCalls(mockContext.apiClient, 'language.languageStore');
      
      // Should have one call per sample
      expect(calls.length).toBe(samples.length);
      
      // Validate each call against its corresponding sample
      samples.forEach((sample, index) => {
        const call = calls[index];
        if (!call) {
          throw new Error(`Missing API call for sample ${index}: ${sample.id}`);
        }
        const apiCallData = call[0] as Record<string, unknown>;
        
        // Verify exact field mapping
        expect(apiCallData.id).toBe(sample.id);
        expect(apiCallData.internal_name).toBe(sample.internal_name);
        expect(apiCallData.iso_code).toBe(sample.iso_code);
        expect(apiCallData.backward_compatibility).toBe(sample.backward_compatibility);
        
        // is_default should NOT be in the store call (handled separately)
        expect(apiCallData).not.toHaveProperty('is_default');
        
        console.log(`✓ Language ${sample.id}: API call matches sample data`);
      });
    });

    it('should validate ISO code format for all samples', () => {
      const samples = helper.loadSamples<LanguageSample>('language');
      
      samples.forEach((sample) => {
        // ISO 639-3 codes are exactly 3 lowercase letters
        expect(sample.iso_code).toMatch(/^[a-z]{3}$/);
        expect(sample.iso_code).toBe(sample.id); // id and iso_code should match
      });
    });

    it('should validate backward_compatibility format for all samples', () => {
      const samples = helper.loadSamples<LanguageSample>('language');
      
      samples.forEach((sample) => {
        // Format: mwnf3:languages:{id}
        const expectedBackwardCompat = `mwnf3:languages:${sample.id}`;
        expect(sample.backward_compatibility).toBe(expectedBackwardCompat);
      });
    });
  });

  describe('special handling', () => {
    it('should handle default language (eng) with separate API call', async () => {
      const samples = helper.loadSamples<LanguageSample>('language');
      const engSample = samples.find((s) => s.id === 'eng' && s.is_default);
      
      if (!engSample) {
        console.log('⚠️  No default English language sample found');
        return;
      }

      const mockContext = helper.createMockContextWithQueries([[engSample]]);
      const importer = new LanguageImporter(mockContext);

      await importer.import();

      // Verify languageSetDefault was called for eng
      const setDefaultCalls = helper.getApiCalls(mockContext.apiClient, 'language.languageSetDefault');
      expect(setDefaultCalls.length).toBe(1);
      const defaultCall = setDefaultCalls[0];
      if (!defaultCall) {
        throw new Error('Missing languageSetDefault API call');
      }
      expect(defaultCall[0]).toBe('eng');
      expect(defaultCall[1]).toEqual({ is_default: true });
    });
  });

  describe('sample data quality', () => {
    it('should have samples for all critical languages', () => {
      const samples = helper.loadSamples<LanguageSample>('language');
      const languageIds = samples.map((s) => s.id);
      
      // Critical languages that must be present
      const criticalLanguages = ['eng', 'fra', 'ara'];
      
      criticalLanguages.forEach((lang) => {
        expect(languageIds).toContain(lang);
      });
      
      console.log(`✓ Found ${languageIds.length} language samples:`, languageIds.join(', '));
    });
  });
});
