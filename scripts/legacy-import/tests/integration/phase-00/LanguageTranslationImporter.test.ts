import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { LanguageTranslationImporter } from '../../../src/importers/phase-00/LanguageTranslationImporter.js';
import { BackwardCompatibilityTracker } from '../../../src/utils/BackwardCompatibilityTracker.js';
import { mapLanguageCode } from '../../../src/utils/CodeMappings.js';

interface LanguageTranslationSample {
  language_id: string;
  lang: string;
  name: string;
}

/**
 * Data-driven tests for LanguageTranslationImporter
 * VALIDATES: Exact transformation including code mapping
 */
describe('LanguageTranslationImporter - Data Transformation', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  it('should transform each sample correctly', async () => {
    const samples = helper.loadSamples<LanguageTranslationSample>('language_translation', { limit: 20 });
    
    if (samples.length === 0) {
      console.log('⚠️  No language translation samples');
      return;
    }

    const tracker = new BackwardCompatibilityTracker();
    const mockContext = helper.createMockContextWithQueries([samples]);
    mockContext.tracker = tracker;
    
    const importer = new LanguageTranslationImporter(mockContext);
    await importer.import();

    const calls = helper.getApiCalls(mockContext.apiClient, 'languageTranslation.languageTranslationStore');
    expect(calls.length).toBe(samples.length);

    samples.forEach((sample, i) => {
      const call = calls[i];
      if (!call) throw new Error(`Missing call for sample ${i}`);
      
      const apiData = call[0] as Record<string, unknown>;
      
      // Required fields
      expect(apiData).toHaveProperty('language_id');
      expect(apiData).toHaveProperty('translation_language_id');
      expect(apiData).toHaveProperty('name');
      expect(apiData).toHaveProperty('backward_compatibility');
      
      // Code mappings
      const expectedLangId = mapLanguageCode(sample.language_id);
      const expectedTransLangId = mapLanguageCode(sample.lang);
      
      expect(apiData.language_id).toBe(expectedLangId);
      expect(apiData.translation_language_id).toBe(expectedTransLangId);
      expect(apiData.name).toBe(sample.name);
    });
  });

  it('should map all language codes to ISO 639-3', () => {
    const samples = helper.loadSamples<LanguageTranslationSample>('language_translation', { limit: 20 });
    
    samples.forEach((sample) => {
      const langId = mapLanguageCode(sample.language_id);
      const transLangId = mapLanguageCode(sample.lang);
      
      expect(langId.length).toBe(3);
      expect(transLangId.length).toBe(3);
      expect(langId).toMatch(/^[a-z]{3}$/);
      expect(transLangId).toMatch(/^[a-z]{3}$/);
    });
  });

  it('should report sample statistics', () => {
    const stats = helper.getReader().getStats();
    if (stats['language_translation:success']) {
      console.log(`✓ Language translation samples: ${stats['language_translation:success']}`);
    }
  });
});
