import { describe, it, expect, beforeAll, afterAll, beforeEach } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { MonumentImporter } from '../../../src/importers/phase-01/MonumentImporter.js';
import { BackwardCompatibilityTracker } from '../../../src/utils/BackwardCompatibilityTracker.js';
import { mapLanguageCode } from '../../../src/utils/CodeMappings.js';

interface MonumentSample {
  project_id: string;
  country: string;
  institution_id: string;
  number: string;
  lang: string;
  working_number?: string;
  name?: string;
  description?: string;
  location?: string;
  address?: string;
}

/**
 * Data-driven tests for MonumentImporter
 * VALIDATES: Exact transformation of denormalized monument data
 * CRITICAL: Monuments are denormalized (lang in PK) - must group correctly
 */
describe('MonumentImporter - Data Transformation', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  it('should create ONE Item per monument (not per language row)', async () => {
    const samples = helper.loadSamples<MonumentSample>('monument', { limit: 20 });
    
    if (samples.length === 0) {
      console.log('⚠️  No monument samples');
      return;
    }

    // Group by non-lang PK
    const unique = new Map<string, MonumentSample[]>();
    samples.forEach((m) => {
      const key = `${m.project_id}:${m.country}:${m.institution_id}:${m.number}`;
      if (!unique.has(key)) unique.set(key, []);
      unique.get(key)!.push(m);
    });

    // Setup dependencies
    const tracker = new BackwardCompatibilityTracker();
    samples.forEach((s) => {
      tracker.register({
        uuid: `uuid-context-${s.project_id}`,
        backwardCompatibility: `mwnf3:projects:${s.project_id}`,
        entityType: 'context',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: `uuid-collection-${s.project_id}`,
        backwardCompatibility: `mwnf3:projects:${s.project_id}:collection`,
        entityType: 'collection',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: `uuid-institution-${s.institution_id}-${s.country}`,
        backwardCompatibility: `mwnf3:institutions:${s.institution_id}:${s.country}`,
        entityType: 'partner',
        createdAt: new Date(),
      });
    });

    const mockContext = helper.createMockContextWithQueries([samples]);
    mockContext.tracker = tracker;
    
    const importer = new MonumentImporter(mockContext);
    const result = await importer.import();

    console.log('Import result:', result);

    const itemCalls = helper.getApiCalls(mockContext.apiClient, 'item.itemStore');
    expect(itemCalls.length).toBe(unique.size);
    
    console.log(`✓ Grouping: ${samples.length} rows → ${unique.size} items`);
  });

  it('should transform Item fields correctly', async () => {
    const samples = helper.loadSamples<MonumentSample>('monument', { limit: 5 });
    if (samples.length === 0) return;

    const tracker = new BackwardCompatibilityTracker();
    samples.forEach((s) => {
      tracker.register({
        uuid: `uuid-context-${s.project_id}`,
        backwardCompatibility: `mwnf3:projects:${s.project_id}`,
        entityType: 'context',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: `uuid-collection-${s.project_id}`,
        backwardCompatibility: `mwnf3:projects:${s.project_id}:collection`,
        entityType: 'collection',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: `uuid-institution-${s.institution_id}-${s.country}`,
        backwardCompatibility: `mwnf3:institutions:${s.institution_id}:${s.country}`,
        entityType: 'partner',
        createdAt: new Date(),
      });
    });

    const mockContext = helper.createMockContextWithQueries([samples]);
    mockContext.tracker = tracker;
    const importer = new MonumentImporter(mockContext);
    await importer.import();

    const itemCalls = helper.getApiCalls(mockContext.apiClient, 'item.itemStore');
    
    itemCalls.forEach((call) => {
      const itemData = call[0] as Record<string, unknown>;
      
      // Required fields
      expect(itemData.type).toBe('monument');
      expect(itemData).toHaveProperty('internal_name');
      expect(itemData).toHaveProperty('partner_id');
      expect(itemData).toHaveProperty('backward_compatibility');
      
      // backward_compatibility MUST NOT include language
      expect(itemData.backward_compatibility).toMatch(/^mwnf3:monuments:/);
      expect(itemData.backward_compatibility).not.toMatch(/:eng|:fra|:ara/);
    });
  });

  it('should transform ItemTranslation fields correctly', async () => {
    const samples = helper.loadSamples<MonumentSample>('monument', { limit: 10 });
    if (samples.length === 0) return;

    const tracker = new BackwardCompatibilityTracker();
    samples.forEach((s) => {
      tracker.register({
        uuid: `uuid-context-${s.project_id}`,
        backwardCompatibility: `mwnf3:projects:${s.project_id}`,
        entityType: 'context',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: `uuid-collection-${s.project_id}`,
        backwardCompatibility: `mwnf3:projects:${s.project_id}:collection`,
        entityType: 'collection',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: `uuid-institution-${s.institution_id}-${s.country}`,
        backwardCompatibility: `mwnf3:institutions:${s.institution_id}:${s.country}`,
        entityType: 'partner',
        createdAt: new Date(),
      });
    });

    const mockContext = helper.createMockContextWithQueries([samples]);
    mockContext.tracker = tracker;
    const importer = new MonumentImporter(mockContext);
    await importer.import();

    const translationCalls = helper.getApiCalls(mockContext.apiClient, 'itemTranslation.itemTranslationStore');
    expect(translationCalls.length).toBe(samples.length);

    samples.forEach((sample, i) => {
      const call = translationCalls[i];
      if (!call) throw new Error(`Missing translation for sample ${i}`);
      
      const apiData = call[0] as Record<string, unknown>;
      
      // Required fields
      expect(apiData).toHaveProperty('item_id');
      expect(apiData).toHaveProperty('language_id');
      expect(apiData).toHaveProperty('name');
      
      // Language mapping
      const expectedLangId = mapLanguageCode(sample.lang);
      expect(apiData.language_id).toBe(expectedLangId);
      expect((apiData.language_id as string).length).toBe(3);
      
      // Name must exist
      expect(typeof apiData.name).toBe('string');
      expect(apiData.name).toBeTruthy();
    });
  });

  it('should report sample statistics', () => {
    const stats = helper.getReader().getStats();
    if (stats['monument:success']) {
      console.log(`✓ Monument samples: ${stats['monument:success']}`);
    }
  });
});
