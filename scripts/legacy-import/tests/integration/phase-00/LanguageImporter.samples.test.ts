import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { LanguageImporter } from '../../../src/importers/phase-00/LanguageImporter.js';

/**
 * Sample-based integration tests for LanguageImporter
 * Tests against real legacy data collected in SQLite samples
 */
describe('LanguageImporter - Sample-Based Tests', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  describe('import with success samples', () => {
    it('should import all language samples successfully', async () => {
      const mockContext = helper.createMockContext('language');
      const importer = new LanguageImporter(mockContext);

      const result = await importer.import();

      expect(result.success).toBe(true);
      expect(result.imported).toBeGreaterThan(0);
      expect(result.errors).toHaveLength(0);
      
      // Verify all languages were registered in tracker
      const samples = helper.loadSamples('language');
      expect(result.imported).toBe(samples.length);

      // Verify backward compatibility tracking
      samples.forEach((lang) => {
        const backwardCompat = `mwnf3:languages:${(lang as { id: string }).id}`;
        expect(helper.getTracker().exists(backwardCompat)).toBe(true);
      });
    });

    it('should create languages with correct structure', async () => {
      const mockContext = helper.createMockContext('language', 5);
      const importer = new LanguageImporter(mockContext);

      await importer.import();

      // Verify API calls have correct structure
      const calls = helper.getApiCalls(mockContext.apiClient, 'language.languageStore');
      
      expect(calls.length).toBeGreaterThan(0);
      calls.forEach((call) => {
        const languageData = call[0] as Record<string, unknown>;
        
        // Required fields
        expect(languageData).toHaveProperty('id');
        expect(languageData).toHaveProperty('internal_name');
        expect(languageData).toHaveProperty('iso_code');
        expect(languageData).toHaveProperty('backward_compatibility');
        
        // Validate ISO code format (3 characters)
        expect(typeof languageData.iso_code).toBe('string');
        expect((languageData.iso_code as string).length).toBe(3);
        
        // Validate backward compatibility format
        expect(languageData.backward_compatibility).toMatch(/^mwnf3:languages:[a-z]{3}$/);
      });
    });
  });

  describe('import with edge cases', () => {
    it('should handle languages with minimal data', async () => {
      // Load any edge case samples if they exist
      const edgeSamples = helper.loadSamples('language', { reason: 'edge' });
      
      if (edgeSamples.length > 0) {
        const mockContext = helper.createMockContextWithQueries([edgeSamples]);
        const importer = new LanguageImporter(mockContext);

        const result = await importer.import();

        expect(result.success).toBe(true);
        expect(result.imported).toBe(edgeSamples.length);
      }
    });
  });

  describe('statistics validation', () => {
    it('should report correct statistics from samples', () => {
      const stats = helper.getReader().getStats();
      
      // Should have language samples
      expect(stats).toHaveProperty('language:success');
      expect(stats['language:success']).toBeGreaterThan(0);
      
      // Log stats for visibility
      console.log('Language sample statistics:', stats);
    });

    it('should have samples for all supported languages', () => {
      const samples = helper.loadSamples('language');
      const languageIds = samples.map((s) => (s as { id: string }).id);
      
      // Verify we have diverse language coverage
      expect(languageIds.length).toBeGreaterThan(5);
      
      console.log('Sampled languages:', languageIds);
    });
  });
});
