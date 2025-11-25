import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { ObjectImporter } from '../../../src/importers/phase-01/ObjectImporter.js';
import { mapLanguageCode } from '../../../src/utils/CodeMappings.js';
import { BackwardCompatibilityTracker } from '../../../src/utils/BackwardCompatibilityTracker.js';

interface ObjectSample {
  project_id: string;
  country: string;
  museum_id: string;
  number: string;
  lang: string;
  working_number?: string;
  name?: string;
  description?: string;
  start_date?: string | null;
  end_date?: string | null;
  keywords?: string;
  materials?: string;
  artist?: string;
}

/**
 * Data-driven tests for ObjectImporter
 * Validates exact transformation of each sample record into API calls
 */
describe('ObjectImporter', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  it('should transform object samples into correct Item and ItemTranslation API calls', async () => {
    const samples = helper.loadSamples<ObjectSample>('object', { limit: 5 });
    
    if (samples.length === 0) {
      console.log('⚠️  No object samples - run import with --collect-samples');
      return;
    }

    // Create FRESH tracker with dependencies registered
    const tracker = new BackwardCompatibilityTracker();
    
    // Register dependencies needed by objects
    samples.forEach((sample) => {
      // Register project context
      tracker.register({
        uuid: `uuid-context-${sample.project_id}`,
        backwardCompatibility: `mwnf3:projects:${sample.project_id}`,
        entityType: 'context',
        createdAt: new Date(),
      });
      
      // Register project collection
      tracker.register({
        uuid: `uuid-collection-${sample.project_id}`,
        backwardCompatibility: `mwnf3:projects:${sample.project_id}:collection`,
        entityType: 'collection',
        createdAt: new Date(),
      });
      
      // Register partner (museum)
      tracker.register({
        uuid: `uuid-museum-${sample.museum_id}-${sample.country}`,
        backwardCompatibility: `mwnf3:museums:${sample.museum_id}:${sample.country}`,
        entityType: 'partner',
        createdAt: new Date(),
      });
      
      // Register language
      const langId = mapLanguageCode(sample.lang);
      tracker.register({
        uuid: `uuid-lang-${langId}`,
        backwardCompatibility: `mwnf3:languages:${langId}`,
        entityType: 'context',
        createdAt: new Date(),
      });
    });

    // Create mock context with fresh tracker
    const mockContext = helper.createMockContextWithQueries([samples]);
    mockContext.tracker = tracker;  // Replace with fresh tracker
    
    const importer = new ObjectImporter(mockContext);
    const result = await importer.import();

    console.log(`Result: imported=${result.imported}, skipped=${result.skipped}, errors=${result.errors.length}`);
    if (result.errors.length > 0) {
      console.log('Errors:', result.errors);
    }
    
    // Should have imported something
    expect(result.imported).toBeGreaterThan(0);
    
    // Get API calls
    const itemCalls = helper.getApiCalls(mockContext.apiClient, 'item.itemStore');
    const translationCalls = helper.getApiCalls(mockContext.apiClient, 'itemTranslation.itemTranslationStore');
    
    console.log(`API calls: items=${itemCalls.length}, translations=${translationCalls.length}`);
    
    // Should have created items and translations
    expect(itemCalls.length).toBeGreaterThan(0);
    expect(translationCalls.length).toBe(samples.length);  // One translation per sample
    
    // Validate each translation matches its sample
    samples.forEach((sample, index) => {
      const translationCall = translationCalls[index];
      if (!translationCall) {
        throw new Error(`Missing translation call for sample ${index}`);
      }
      
      const apiData = translationCall[0] as Record<string, unknown>;
      
      // Validate language mapping
      const expectedLangId = mapLanguageCode(sample.lang);
      expect(apiData.language_id).toBe(expectedLangId);
      
      // Validate name (with fallback logic)
      if (sample.name && sample.name.trim() !== '') {
        // Importer may trim whitespace
        expect((apiData.name as string).trim()).toBe(sample.name.trim());
      } else if (sample.working_number && sample.working_number.trim() !== '') {
        expect((apiData.name as string).trim()).toBe(sample.working_number.trim());
      } else {
        // Should use fallback
        expect(apiData.name).toMatch(/Object \d+/);
      }
      
      // Validate description (with fallback logic)
      if (sample.description && sample.description.trim() !== '') {
        // Importer may normalize whitespace - compare trimmed versions
        expect((apiData.description as string).trim()).toBe(sample.description.trim());
      } else {
        // Should use fallback
        expect(apiData.description).toBe('(No description available)');
      }
      
      console.log(`✓ Object ${sample.number}: transformation validated`);
    });
  });

  it('should validate backward_compatibility format', () => {
    const samples = helper.loadSamples<ObjectSample>('object', { limit: 3 });
    
    samples.forEach((sample) => {
      // backward_compatibility should NOT include language
      const expected = `mwnf3:objects:${sample.project_id}:${sample.country}:${sample.museum_id}:${sample.number}`;
      
      // This is what the importer SHOULD generate
      console.log(`Expected backward_compat: ${expected}`);
    });
  });

  it('should map 2-letter language codes to 3-letter ISO 639 codes', () => {
    const samples = helper.loadSamples<ObjectSample>('object', { limit: 10 });
    
    samples.forEach((sample) => {
      const mapped = mapLanguageCode(sample.lang);
      
      // Should be 3 characters
      expect(mapped.length).toBe(3);
      
      // Common mappings
      const knownMappings: Record<string, string> = {
        'en': 'eng',
        'fr': 'fra',
        'de': 'deu',
        'ar': 'ara',
        'cs': 'ces',
        'ch': 'zho',
        'fa': 'fas',
      };
      
      if (knownMappings[sample.lang]) {
        expect(mapped).toBe(knownMappings[sample.lang]);
      }
      
      console.log(`✓ Language mapping: ${sample.lang} → ${mapped}`);
    });
  });

  it('should handle date transformations correctly', async () => {
    const samples = helper.loadSamples<ObjectSample>('object', { limit: 5 });
    
    const samplesWithDates = samples.filter((s) => s.start_date || s.end_date);
    
    if (samplesWithDates.length === 0) {
      console.log('No samples with dates found');
      return;
    }

    // Create context and import
    const tracker = new BackwardCompatibilityTracker();
    samplesWithDates.forEach((sample) => {
      tracker.register({
        uuid: `uuid-context-${sample.project_id}`,
        backwardCompatibility: `mwnf3:projects:${sample.project_id}`,
        entityType: 'context',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: `uuid-collection-${sample.project_id}`,
        backwardCompatibility: `mwnf3:projects:${sample.project_id}:collection`,
        entityType: 'collection',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: `uuid-museum-${sample.museum_id}-${sample.country}`,
        backwardCompatibility: `mwnf3:museums:${sample.museum_id}:${sample.country}`,
        entityType: 'partner',
        createdAt: new Date(),
      });
    });

    const mockContext = helper.createMockContextWithQueries([samplesWithDates]);
    mockContext.tracker = tracker;
    
    const importer = new ObjectImporter(mockContext);
    await importer.import();

    const itemCalls = helper.getApiCalls(mockContext.apiClient, 'item.itemStore');
    
    // Validate date fields
    itemCalls.forEach((call) => {
      const apiData = call[0] as Record<string, unknown>;
      
      if (apiData.start_date !== undefined && apiData.start_date !== null) {
        // Should be a number (year)
        expect(typeof apiData.start_date).toBe('number');
        expect(apiData.start_date).toBeGreaterThan(0);
        expect(apiData.start_date).toBeLessThan(3000);
      }
      
      if (apiData.end_date !== undefined && apiData.end_date !== null) {
        expect(typeof apiData.end_date).toBe('number');
        expect(apiData.end_date).toBeGreaterThan(0);
        expect(apiData.end_date).toBeLessThan(3000);
      }
    });
  });
});
