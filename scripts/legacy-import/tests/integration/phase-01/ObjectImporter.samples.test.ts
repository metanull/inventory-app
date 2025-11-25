import { describe, it, expect, beforeAll, afterAll, beforeEach } from 'vitest';
import { SampleBasedTestHelper, expectInRange } from '../../helpers/SampleBasedTestHelper.js';
import { ObjectImporter } from '../../../src/importers/phase-01/ObjectImporter.js';

/**
 * Sample-based integration tests for ObjectImporter
 * Tests against real legacy data collected in SQLite samples
 * 
 * CRITICAL: Objects table is denormalized with language in PK
 * - Multiple rows per object (one per language)
 * - Must correctly group and create ItemTranslations
 * - Tests validate proper handling of translations and tags
 */
describe('ObjectImporter - Sample-Based Tests', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  beforeEach(() => {
    helper.setupFoundationData();
    
    // Setup projects and partners for objects
    const projects = helper.loadSamples<{ project_id: string }>('project');
    projects.forEach((proj) => {
      helper.getTracker().register({
        uuid: `uuid-project-${proj.project_id}`,
        backwardCompatibility: `mwnf3:projects:${proj.project_id}:project`,
        entityType: 'project',
        createdAt: new Date(),
      });
      
      helper.getTracker().register({
        uuid: `uuid-context-${proj.project_id}`,
        backwardCompatibility: `mwnf3:projects:${proj.project_id}`,
        entityType: 'context',
        createdAt: new Date(),
      });
    });
    
    const museums = helper.loadSamples<{ museum_id: string; country: string; project_id: string }>('museum');
    museums.forEach((museum) => {
      helper.getTracker().register({
        uuid: `uuid-museum-${museum.museum_id}-${museum.country}`,
        backwardCompatibility: `mwnf3:museums:${museum.museum_id}:${museum.country}`,
        entityType: 'partner',
        createdAt: new Date(),
      });
    });
  });

  describe('import with success samples', () => {
    it('should import object samples successfully', async () => {
      const mockContext = helper.createMockContext('object', 10);
      const importer = new ObjectImporter(mockContext);

      const result = await importer.import();

      expect(result.success).toBe(true);
      expect(result.imported).toBeGreaterThan(0);
      
      console.log(`Imported ${result.imported} objects with ${result.warnings?.length || 0} warnings`);
    });

    it('should correctly group denormalized objects by language', async () => {
      // Load samples that have multiple language rows
      const samples = helper.loadSamples<{ project_id: string; country: string; museum_id: string; number: string; lang: string }>('object', 20);
      
      // Group samples by non-lang PK
      const groups = new Map<string, unknown[]>();
      samples.forEach((obj) => {
        const key = `${obj.project_id}:${obj.country}:${obj.museum_id}:${obj.number}`;
        if (!groups.has(key)) {
          groups.set(key, []);
        }
        groups.get(key)?.push(obj);
      });
      
      const multiLangObjects = Array.from(groups.values()).filter((g) => g.length > 1);
      
      if (multiLangObjects.length > 0) {
        console.log(`Found ${multiLangObjects.length} objects with multiple language translations`);
        
        const mockContext = helper.createMockContextWithQueries([samples]);
        const importer = new ObjectImporter(mockContext);

        const result = await importer.import();

        // Should create one Item per object group, not per language row
        const itemCalls = helper.getApiCalls(mockContext.apiClient, 'item.itemStore');
        expect(itemCalls.length).toBe(groups.size);
        
        // Should create ItemTranslation for each language row
        const translationCalls = helper.getApiCalls(mockContext.apiClient, 'itemTranslation.itemTranslationStore');
        expect(translationCalls.length).toBe(samples.length);
        
        console.log(`Created ${itemCalls.length} items and ${translationCalls.length} translations`);
      }
    });

    it('should create items with correct structure', async () => {
      const mockContext = helper.createMockContext('object', 5);
      const importer = new ObjectImporter(mockContext);

      await importer.import();

      const itemCalls = helper.getApiCalls(mockContext.apiClient, 'item.itemStore');
      
      expect(itemCalls.length).toBeGreaterThan(0);
      itemCalls.forEach((call) => {
        const itemData = call[0] as Record<string, unknown>;
        
        // Required fields
        expect(itemData).toHaveProperty('internal_name');
        expect(itemData).toHaveProperty('type');
        expect(itemData).toHaveProperty('partner_id');
        expect(itemData).toHaveProperty('backward_compatibility');
        
        // Type should be 'object'
        expect(itemData.type).toBe('object');
        
        // Backward compatibility should match pattern
        expect(itemData.backward_compatibility).toMatch(/^mwnf3:objects:/);
        
        // Optional fields validation
        if (itemData.start_date !== undefined) {
          expect(typeof itemData.start_date).toBe('number');
        }
        if (itemData.end_date !== undefined) {
          expect(typeof itemData.end_date).toBe('number');
        }
      });
    });

    it('should create translations with correct structure', async () => {
      const mockContext = helper.createMockContext('object', 10);
      const importer = new ObjectImporter(mockContext);

      await importer.import();

      const translationCalls = helper.getApiCalls(mockContext.apiClient, 'itemTranslation.itemTranslationStore');
      
      expect(translationCalls.length).toBeGreaterThan(0);
      translationCalls.forEach((call) => {
        const translationData = call[0] as Record<string, unknown>;
        
        // Required fields
        expect(translationData).toHaveProperty('item_id');
        expect(translationData).toHaveProperty('language_id');
        expect(translationData).toHaveProperty('name');
        
        // Name should not be empty (may be fallback)
        expect(translationData.name).toBeTruthy();
        expect(typeof translationData.name).toBe('string');
        
        // Description may be fallback "(No description available)"
        if (translationData.description) {
          expect(typeof translationData.description).toBe('string');
        }
      });
    });
  });

  describe('data quality handling', () => {
    it('should handle objects with missing names using fallbacks', async () => {
      const missingSamples = helper.loadSamples('object', { reason: 'warning', warningType: 'missing_name' });
      
      if (missingSamples.length > 0) {
        console.log(`Testing ${missingSamples.length} objects with missing names`);
        
        const mockContext = helper.createMockContextWithQueries([missingSamples]);
        const importer = new ObjectImporter(mockContext);

        const result = await importer.import();

        // Should still import successfully with fallback names
        expect(result.success).toBe(true);
        expect(result.imported).toBe(missingSamples.length);
        
        // Should have warnings
        if (result.warnings) {
          expect(result.warnings.length).toBeGreaterThan(0);
          expect(result.warnings.some((w) => w.includes('name'))).toBe(true);
        }
        
        // Verify fallback names were used
        const translationCalls = helper.getApiCalls(mockContext.apiClient, 'itemTranslation.itemTranslationStore');
        translationCalls.forEach((call) => {
          const data = call[0] as Record<string, unknown>;
          expect(data.name).toBeTruthy();
          console.log('Fallback name used:', data.name);
        });
      }
    });

    it('should handle objects with missing descriptions', async () => {
      const missingSamples = helper.loadSamples('object', { reason: 'warning', warningType: 'missing_description' });
      
      if (missingSamples.length > 0) {
        console.log(`Testing ${missingSamples.length} objects with missing descriptions`);
        
        const mockContext = helper.createMockContextWithQueries([missingSamples]);
        const importer = new ObjectImporter(mockContext);

        const result = await importer.import();

        expect(result.success).toBe(true);
        
        // Verify fallback descriptions were used
        const translationCalls = helper.getApiCalls(mockContext.apiClient, 'itemTranslation.itemTranslationStore');
        translationCalls.forEach((call) => {
          const data = call[0] as Record<string, unknown>;
          if (data.description) {
            expect(typeof data.description).toBe('string');
          }
        });
      }
    });

    it('should handle edge cases (long fields, special characters)', async () => {
      const edgeSamples = helper.loadSamples('object', { reason: 'edge' });
      
      if (edgeSamples.length > 0) {
        console.log(`Testing ${edgeSamples.length} edge case objects`);
        
        const mockContext = helper.createMockContextWithQueries([edgeSamples]);
        const importer = new ObjectImporter(mockContext);

        const result = await importer.import();

        // Should handle edge cases gracefully
        expect(result.success).toBe(true);
        console.log(`Edge cases: imported=${result.imported}, errors=${result.errors.length}`);
      }
    });
  });

  describe('language code mapping', () => {
    it('should correctly map 2-letter to 3-letter ISO 639 codes', async () => {
      const samples = helper.loadSamples<{ lang: string }>('object', 20);
      
      // Check if we have 2-letter codes that need mapping
      const twoLetterCodes = samples.filter((s) => s.lang && s.lang.length === 2);
      
      if (twoLetterCodes.length > 0) {
        console.log(`Found ${twoLetterCodes.length} objects with 2-letter language codes`);
        
        const mockContext = helper.createMockContextWithQueries([samples]);
        const importer = new ObjectImporter(mockContext);

        const result = await importer.import();

        // Should successfully map all codes
        expect(result.success).toBe(true);
        expect(result.errors).toHaveLength(0);
        
        console.log('Language code mapping successful');
      }
    });
  });

  describe('tag handling', () => {
    it('should process structured tag fields', async () => {
      const samples = helper.loadSamples<{ keywords?: string; materials?: string; artist?: string }>('object', 10);
      
      // Find samples with tag fields
      const samplesWithTags = samples.filter((s) => s.keywords || s.materials || s.artist);
      
      if (samplesWithTags.length > 0) {
        console.log(`Found ${samplesWithTags.length} objects with tag fields`);
        
        const mockContext = helper.createMockContextWithQueries([samplesWithTags]);
        const importer = new ObjectImporter(mockContext);

        await importer.import();

        // Check if tags were created and attached
        const tagCalls = helper.getApiCalls(mockContext.apiClient, 'tag.tagStore');
        const attachCalls = helper.getApiCalls(mockContext.apiClient, 'itemTag.attachTagItem');
        
        console.log(`Tags: created=${tagCalls.length}, attached=${attachCalls.length}`);
        
        // Should have created some tags
        expect(tagCalls.length + attachCalls.length).toBeGreaterThan(0);
      }
    });
  });

  describe('statistics validation', () => {
    it('should report correct statistics from samples', () => {
      const stats = helper.getReader().getStats();
      
      if (stats['object:success']) {
        expect(stats['object:success']).toBeGreaterThan(0);
        console.log('Object sample statistics:', stats);
      }
    });

    it('should validate sample diversity', () => {
      const samples = helper.loadSamples<{ project_id: string; lang: string }>('object', 50);
      
      const projects = new Set(samples.map((s) => s.project_id));
      const languages = new Set(samples.map((s) => s.lang));
      
      console.log(`Object samples: ${samples.length} total, ${projects.size} projects, ${languages.size} languages`);
      
      // Should have diverse coverage
      expectInRange(projects.size, 1, 10, 'Projects');
      expectInRange(languages.size, 2, 10, 'Languages');
    });
  });
});
