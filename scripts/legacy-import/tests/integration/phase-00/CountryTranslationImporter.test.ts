import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { CountryTranslationImporter } from '../../../src/importers/phase-00/CountryTranslationImporter.js';
import { BackwardCompatibilityTracker } from '../../../src/utils/BackwardCompatibilityTracker.js';
import { mapCountryCode, mapLanguageCode } from '../../../src/utils/CodeMappings.js';

interface CountryTranslationSample {
  country: string;
  lang: string;
  name: string;
}

/**
 * Data-driven tests for CountryTranslationImporter
 * VALIDATES: Exact transformation including code mapping
 */
describe('CountryTranslationImporter - Data Transformation', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  it('should transform each sample correctly', async () => {
    const samples = helper.loadSamples<CountryTranslationSample>('country_translation', {
      limit: 20,
    });

    if (samples.length === 0) {
      console.log('⚠️  No country translation samples');
      return;
    }

    const tracker = new BackwardCompatibilityTracker();
    const mockContext = helper.createMockContextWithQueries([samples]);
    mockContext.tracker = tracker;

    const importer = new CountryTranslationImporter(mockContext);
    await importer.import();

    const calls = helper.getApiCalls(
      mockContext.apiClient,
      'countryTranslation.countryTranslationStore'
    );
    expect(calls.length).toBe(samples.length);

    samples.forEach((sample, i) => {
      const call = calls[i];
      if (!call) throw new Error(`Missing call for sample ${i}`);

      const apiData = call[0] as Record<string, unknown>;

      // Required fields
      expect(apiData).toHaveProperty('country_id');
      expect(apiData).toHaveProperty('language_id');
      expect(apiData).toHaveProperty('name');
      expect(apiData).toHaveProperty('backward_compatibility');

      // Code mappings
      const expectedCountryId = mapCountryCode(sample.country);
      const expectedLangId = mapLanguageCode(sample.lang);

      expect(apiData.country_id).toBe(expectedCountryId);
      expect(apiData.language_id).toBe(expectedLangId);
      expect(apiData.name).toBe(sample.name);

      // backward_compatibility format
      expect(apiData.backward_compatibility).toBe(
        `mwnf3:countrynames:${sample.country}:${sample.lang}`
      );
    });
  });

  it('should map non-standard country codes correctly', () => {
    const samples = helper.loadSamples<CountryTranslationSample>('country_translation', {
      limit: 50,
    });

    // Check for known non-standard codes
    const nonStandard = ['ab', 'ag', 'bu', 'ch', 'dn', 'ia', 'ix', 'pd', 'ww'];
    const found = samples.filter((s) => nonStandard.includes(s.country));

    if (found.length > 0) {
      console.log(`✓ Found ${found.length} non-standard country codes`);
      found.forEach((s) => {
        const mapped = mapCountryCode(s.country);
        expect(mapped.length).toBe(3);
        console.log(`  ${s.country} → ${mapped}`);
      });
    }
  });

  it('should map 2-letter language codes to ISO 639-3', () => {
    const samples = helper.loadSamples<CountryTranslationSample>('country_translation', {
      limit: 20,
    });

    samples.forEach((sample) => {
      const mapped = mapLanguageCode(sample.lang);
      expect(mapped.length).toBe(3);
      expect(mapped).toMatch(/^[a-z]{3}$/);
    });
  });

  it('should report sample statistics', () => {
    const stats = helper.getReader().getStats();
    if (stats['country_translation:success']) {
      console.log(`✓ Country translation samples: ${stats['country_translation:success']}`);
    }
  });
});
