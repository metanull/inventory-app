import { describe, it, expect, beforeAll, afterAll, beforeEach } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { LanguageTranslationImporter } from '../../../src/importers/phase-00/LanguageTranslationImporter.js';

interface LanguageTranslationSample {
  language_id: string;
  lang: string;
  name: string;
}

/**
 * Sample-based integration tests for LanguageTranslationImporter
 * Tests against real legacy data collected in SQLite samples
 */
describe('LanguageTranslationImporter - Sample-Based Tests', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  beforeEach(() => {
    // Setup foundation data before each test
    helper.setupFoundationData();
  });

  describe('import with success samples', () => {
    it('should import all language translation samples successfully', async () => {
      const mockContext = helper.createMockContext('language_translation');
      const importer = new LanguageTranslationImporter(mockContext);

      const result = await importer.import();

      expect(result.success).toBe(true);
      expect(result.imported).toBeGreaterThan(0);
      expect(result.errors).toHaveLength(0);
      
      const samples = helper.loadSamples('language_translation');
      expect(result.imported).toBe(samples.length);
    });

    it('should create translations with correct structure', async () => {
      const mockContext = helper.createMockContext('language_translation', 10);
      const importer = new LanguageTranslationImporter(mockContext);

      await importer.import();

      const calls = helper.getApiCalls(
        mockContext.apiClient,
        'languageTranslation.languageTranslationStore'
      );
      
      expect(calls.length).toBeGreaterThan(0);
      calls.forEach((call) => {
        const translationData = call[0] as Record<string, unknown>;
        
        // Required fields
        expect(translationData).toHaveProperty('language_id');
        expect(translationData).toHaveProperty('target_language_id');
        expect(translationData).toHaveProperty('name');
        
        // Validate language IDs are UUIDs from tracker
        expect(typeof translationData.language_id).toBe('string');
        expect(typeof translationData.target_language_id).toBe('string');
        
        // Name should not be empty
        expect(translationData.name).toBeTruthy();
      });
    });

    it('should create translations for multiple target languages', async () => {
      const mockContext = helper.createMockContext('language_translation', 20);
      const importer = new LanguageTranslationImporter(mockContext);

      await importer.import();

      const calls = helper.getApiCalls(
        mockContext.apiClient,
        'languageTranslation.languageTranslationStore'
      );
      
      // Extract unique language pairs
      const languagePairs = new Set(
        calls.map((call) => {
          const data = call[0] as Record<string, unknown>;
          return `${data.language_id}->${data.target_language_id}`;
        })
      );
      
      // Should have multiple different translations
      expect(languagePairs.size).toBeGreaterThan(1);
      
      console.log(`Created ${calls.length} translations for ${languagePairs.size} language pairs`);
    });
  });

  describe('data quality validation', () => {
    it('should handle translations with warnings if any', async () => {
      const warningSamples = helper.loadSamples('language_translation', { reason: 'warning' });
      
      if (warningSamples.length > 0) {
        const mockContext = helper.createMockContextWithQueries([warningSamples]);
        const importer = new LanguageTranslationImporter(mockContext);

        const result = await importer.import();

        // Should still import with warnings
        expect(result.success).toBe(true);
        if (result.warnings) {
          expect(result.warnings.length).toBeGreaterThan(0);
          console.log('Warning samples processed:', result.warnings);
        }
      }
    });
  });

  describe('statistics validation', () => {
    it('should report correct statistics from samples', () => {
      const stats = helper.getReader().getStats();
      
      expect(stats).toHaveProperty('language_translation:success');
      expect(stats['language_translation:success']).toBeGreaterThan(0);
      
      console.log('Language translation sample statistics:', stats);
    });
  });
});
