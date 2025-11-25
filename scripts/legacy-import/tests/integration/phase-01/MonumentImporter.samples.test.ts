import { describe, it, expect, beforeAll, afterAll, beforeEach } from 'vitest';
import { SampleBasedTestHelper, expectInRange } from '../../helpers/SampleBasedTestHelper.js';
import { MonumentImporter } from '../../../src/importers/phase-01/MonumentImporter.js';

/**
 * Sample-based integration tests for MonumentImporter
 * Tests against real legacy data collected in SQLite samples
 * 
 * CRITICAL: Monuments table is denormalized with language in PK
 * - Multiple rows per monument (one per language)
 * - Must correctly group and create ItemTranslations
 * - Similar to ObjectImporter but uses institutions instead of museums
 */
describe('MonumentImporter - Sample-Based Tests', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  beforeEach(() => {
    helper.setupFoundationData();
    
    // Setup projects and institutions for monuments
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
    
    const institutions = helper.loadSamples<{ institution_id: string; country: string }>('institution');
    institutions.forEach((inst) => {
      helper.getTracker().register({
        uuid: `uuid-institution-${inst.institution_id}-${inst.country}`,
        backwardCompatibility: `mwnf3:institutions:${inst.institution_id}:${inst.country}`,
        entityType: 'partner',
        createdAt: new Date(),
      });
    });
  });

  describe('import with success samples', () => {
    it('should import monument samples successfully', async () => {
      const mockContext = helper.createMockContext('monument', 10);
      const importer = new MonumentImporter(mockContext);

      const result = await importer.import();

      expect(result.success).toBe(true);
      expect(result.imported).toBeGreaterThan(0);
      
      console.log(`Imported ${result.imported} monuments with ${result.warnings?.length || 0} warnings`);
    });

    it('should correctly group denormalized monuments by language', async () => {
      const samples = helper.loadSamples<{ project_id: string; country: string; institution_id: string; number: string; lang: string }>('monument', 20);
      
      // Group samples by non-lang PK
      const groups = new Map<string, unknown[]>();
      samples.forEach((mon) => {
        const key = `${mon.project_id}:${mon.country}:${mon.institution_id}:${mon.number}`;
        if (!groups.has(key)) {
          groups.set(key, []);
        }
        groups.get(key)?.push(mon);
      });
      
      const multiLangMonuments = Array.from(groups.values()).filter((g) => g.length > 1);
      
      if (multiLangMonuments.length > 0) {
        console.log(`Found ${multiLangMonuments.length} monuments with multiple language translations`);
        
        const mockContext = helper.createMockContextWithQueries([samples]);
        const importer = new MonumentImporter(mockContext);

        const result = await importer.import();

        // Should create one Item per monument group
        const itemCalls = helper.getApiCalls(mockContext.apiClient, 'item.itemStore');
        expect(itemCalls.length).toBe(groups.size);
        
        // Should create ItemTranslation for each language row
        const translationCalls = helper.getApiCalls(mockContext.apiClient, 'itemTranslation.itemTranslationStore');
        expect(translationCalls.length).toBe(samples.length);
        
        console.log(`Created ${itemCalls.length} items and ${translationCalls.length} translations`);
      }
    });

    it('should create monuments with correct structure', async () => {
      const mockContext = helper.createMockContext('monument', 5);
      const importer = new MonumentImporter(mockContext);

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
        
        // Type should be 'monument'
        expect(itemData.type).toBe('monument');
        
        // Backward compatibility should match pattern
        expect(itemData.backward_compatibility).toMatch(/^mwnf3:monuments:/);
        
        // Monument-specific fields
        if (itemData.latitude !== undefined) {
          expect(typeof itemData.latitude).toBe('number');
        }
        if (itemData.longitude !== undefined) {
          expect(typeof itemData.longitude).toBe('number');
        }
      });
    });

    it('should create translations with monument-specific fields', async () => {
      const mockContext = helper.createMockContext('monument', 10);
      const importer = new MonumentImporter(mockContext);

      await importer.import();

      const translationCalls = helper.getApiCalls(mockContext.apiClient, 'itemTranslation.itemTranslationStore');
      
      expect(translationCalls.length).toBeGreaterThan(0);
      translationCalls.forEach((call) => {
        const translationData = call[0] as Record<string, unknown>;
        
        // Required fields
        expect(translationData).toHaveProperty('item_id');
        expect(translationData).toHaveProperty('language_id');
        expect(translationData).toHaveProperty('name');
        
        // Name should not be empty
        expect(translationData.name).toBeTruthy();
        
        // Monument-specific optional fields
        if (translationData.location) {
          expect(typeof translationData.location).toBe('string');
        }
        if (translationData.address) {
          expect(typeof translationData.address).toBe('string');
        }
        if (translationData.history) {
          expect(typeof translationData.history).toBe('string');
        }
      });
    });
  });

  describe('data quality handling', () => {
    it('should handle monuments with missing names using fallbacks', async () => {
      const missingSamples = helper.loadSamples('monument', { reason: 'warning', warningType: 'missing_name' });
      
      if (missingSamples.length > 0) {
        console.log(`Testing ${missingSamples.length} monuments with missing names`);
        
        const mockContext = helper.createMockContextWithQueries([missingSamples]);
        const importer = new MonumentImporter(mockContext);

        const result = await importer.import();

        // Should still import with fallback names
        expect(result.success).toBe(true);
        expect(result.imported).toBe(missingSamples.length);
        
        // Verify fallback names
        const translationCalls = helper.getApiCalls(mockContext.apiClient, 'itemTranslation.itemTranslationStore');
        translationCalls.forEach((call) => {
          const data = call[0] as Record<string, unknown>;
          expect(data.name).toBeTruthy();
        });
      }
    });

    it('should handle monuments with missing descriptions', async () => {
      const missingSamples = helper.loadSamples('monument', { reason: 'warning', warningType: 'missing_description' });
      
      if (missingSamples.length > 0) {
        console.log(`Testing ${missingSamples.length} monuments with missing descriptions`);
        
        const mockContext = helper.createMockContextWithQueries([missingSamples]);
        const importer = new MonumentImporter(mockContext);

        const result = await importer.import();

        expect(result.success).toBe(true);
      }
    });

    it('should handle edge cases gracefully', async () => {
      const edgeSamples = helper.loadSamples('monument', { reason: 'edge' });
      
      if (edgeSamples.length > 0) {
        console.log(`Testing ${edgeSamples.length} edge case monuments`);
        
        const mockContext = helper.createMockContextWithQueries([edgeSamples]);
        const importer = new MonumentImporter(mockContext);

        const result = await importer.import();

        expect(result.success).toBe(true);
        console.log(`Edge cases: imported=${result.imported}, errors=${result.errors.length}`);
      }
    });
  });

  describe('language code mapping', () => {
    it('should correctly map language codes', async () => {
      const samples = helper.loadSamples<{ lang: string }>('monument', 20);
      
      const twoLetterCodes = samples.filter((s) => s.lang && s.lang.length === 2);
      
      if (twoLetterCodes.length > 0) {
        console.log(`Found ${twoLetterCodes.length} monuments with 2-letter language codes`);
        
        const mockContext = helper.createMockContextWithQueries([samples]);
        const importer = new MonumentImporter(mockContext);

        const result = await importer.import();

        expect(result.success).toBe(true);
        expect(result.errors).toHaveLength(0);
      }
    });
  });

  describe('tag handling', () => {
    it('should process monument tag fields', async () => {
      const samples = helper.loadSamples<{ keywords?: string; patrons?: string; architects?: string }>('monument', 10);
      
      const samplesWithTags = samples.filter((s) => s.keywords || s.patrons || s.architects);
      
      if (samplesWithTags.length > 0) {
        console.log(`Found ${samplesWithTags.length} monuments with tag fields`);
        
        const mockContext = helper.createMockContextWithQueries([samplesWithTags]);
        const importer = new MonumentImporter(mockContext);

        await importer.import();

        const tagCalls = helper.getApiCalls(mockContext.apiClient, 'tag.tagStore');
        const attachCalls = helper.getApiCalls(mockContext.apiClient, 'itemTag.attachTagItem');
        
        console.log(`Tags: created=${tagCalls.length}, attached=${attachCalls.length}`);
        
        expect(tagCalls.length + attachCalls.length).toBeGreaterThan(0);
      }
    });
  });

  describe('location data handling', () => {
    it('should preserve location coordinates when present', async () => {
      const samples = helper.loadSamples<{ location?: string }>('monument', 20);
      
      const mockContext = helper.createMockContextWithQueries([samples]);
      const importer = new MonumentImporter(mockContext);

      await importer.import();

      const itemCalls = helper.getApiCalls(mockContext.apiClient, 'item.itemStore');
      
      // Check if any items have coordinates
      const withCoordinates = itemCalls.filter((call) => {
        const data = call[0] as Record<string, unknown>;
        return data.latitude !== undefined && data.longitude !== undefined;
      });
      
      if (withCoordinates.length > 0) {
        console.log(`${withCoordinates.length} monuments have coordinates`);
        
        withCoordinates.forEach((call) => {
          const data = call[0] as Record<string, unknown>;
          expect(typeof data.latitude).toBe('number');
          expect(typeof data.longitude).toBe('number');
          expect(data.latitude as number).toBeGreaterThanOrEqual(-90);
          expect(data.latitude as number).toBeLessThanOrEqual(90);
          expect(data.longitude as number).toBeGreaterThanOrEqual(-180);
          expect(data.longitude as number).toBeLessThanOrEqual(180);
        });
      }
    });
  });

  describe('statistics validation', () => {
    it('should report correct statistics from samples', () => {
      const stats = helper.getReader().getStats();
      
      if (stats['monument:success']) {
        expect(stats['monument:success']).toBeGreaterThan(0);
        console.log('Monument sample statistics:', stats);
      }
    });

    it('should validate sample diversity', () => {
      const samples = helper.loadSamples<{ project_id: string; lang: string }>('monument', 50);
      
      const projects = new Set(samples.map((s) => s.project_id));
      const languages = new Set(samples.map((s) => s.lang));
      
      console.log(`Monument samples: ${samples.length} total, ${projects.size} projects, ${languages.size} languages`);
      
      expectInRange(projects.size, 1, 10, 'Projects');
      expectInRange(languages.size, 2, 10, 'Languages');
    });
  });
});
