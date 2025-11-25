import { describe, it, expect, beforeAll, afterAll, beforeEach } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { ProjectImporter } from '../../../src/importers/phase-01/ProjectImporter.js';

interface ProjectSample {
  project_id: string;
  name: string;
  launchdate: Date | null;
}

/**
 * Sample-based integration tests for ProjectImporter
 * Tests against real legacy data collected in SQLite samples
 */
describe('ProjectImporter - Sample-Based Tests', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  beforeEach(() => {
    helper.setupFoundationData();
  });

  describe('import with success samples', () => {
    it('should import all project samples successfully', async () => {
      const mockContext = helper.createMockContext('project');
      const importer = new ProjectImporter(mockContext);

      const result = await importer.import();

      expect(result.success).toBe(true);
      expect(result.imported).toBeGreaterThan(0);
      expect(result.errors).toHaveLength(0);
      
      const samples = helper.loadSamples('project');
      expect(result.imported).toBe(samples.length);
      
      console.log(`Imported ${result.imported} projects`);
    });

    it('should create both Project and Context for each project', async () => {
      const mockContext = helper.createMockContext('project', 5);
      const importer = new ProjectImporter(mockContext);

      await importer.import();

      const projectCalls = helper.getApiCalls(mockContext.apiClient, 'project.projectStore');
      const contextCalls = helper.getApiCalls(mockContext.apiClient, 'context.contextStore');
      
      // Should create equal numbers of projects and contexts
      expect(projectCalls.length).toBe(contextCalls.length);
      expect(projectCalls.length).toBeGreaterThan(0);
      
      console.log(`Created ${projectCalls.length} projects and ${contextCalls.length} contexts`);
    });

    it('should create projects with correct structure', async () => {
      const mockContext = helper.createMockContext('project', 5);
      const importer = new ProjectImporter(mockContext);

      await importer.import();

      const projectCalls = helper.getApiCalls(mockContext.apiClient, 'project.projectStore');
      
      projectCalls.forEach((call) => {
        const projectData = call[0] as Record<string, unknown>;
        
        // Required fields
        expect(projectData).toHaveProperty('internal_name');
        expect(projectData).toHaveProperty('backward_compatibility');
        
        // Backward compatibility format
        expect(projectData.backward_compatibility).toMatch(/^mwnf3:projects:.+:project$/);
        
        // Internal name should not be empty
        expect(projectData.internal_name).toBeTruthy();
      });
    });

    it('should create contexts with correct structure', async () => {
      const mockContext = helper.createMockContext('project', 5);
      const importer = new ProjectImporter(mockContext);

      await importer.import();

      const contextCalls = helper.getApiCalls(mockContext.apiClient, 'context.contextStore');
      
      contextCalls.forEach((call) => {
        const contextData = call[0] as Record<string, unknown>;
        
        // Required fields
        expect(contextData).toHaveProperty('name');
        expect(contextData).toHaveProperty('backward_compatibility');
        
        // Backward compatibility format (no ':project' suffix for context)
        expect(contextData.backward_compatibility).toMatch(/^mwnf3:projects:[^:]+$/);
        
        // Name should not be empty
        expect(contextData.name).toBeTruthy();
      });
    });

    it('should register both entities in tracker', async () => {
      const samples = helper.loadSamples<{ project_id: string }>('project', { limit: 3 });
      const mockContext = helper.createMockContextWithQueries([samples]);
      const importer = new ProjectImporter(mockContext);

      await importer.import();

      // Verify tracker has both project and context entries
      samples.forEach((proj) => {
        const projectBackwardCompat = `mwnf3:projects:${proj.project_id}:project`;
        const contextBackwardCompat = `mwnf3:projects:${proj.project_id}`;
        
        expect(helper.getTracker().exists(projectBackwardCompat)).toBe(true);
        expect(helper.getTracker().exists(contextBackwardCompat)).toBe(true);
      });
    });
  });

  describe('data quality', () => {
    it('should handle warning samples if present', async () => {
      const warningSamples = helper.loadSamples('project', { reason: 'warning' });
      
      if (warningSamples.length > 0) {
        console.log(`Testing ${warningSamples.length} project warning samples`);
        
        const mockContext = helper.createMockContextWithQueries([warningSamples]);
        const importer = new ProjectImporter(mockContext);

        const result = await importer.import();

        expect(result.success).toBe(true);
        if (result.warnings) {
          console.log('Project warnings:', result.warnings);
        }
      }
    });
  });

  describe('statistics validation', () => {
    it('should report correct statistics from samples', () => {
      const stats = helper.getReader().getStats();
      
      if (stats['project:success']) {
        expect(stats['project:success']).toBeGreaterThan(0);
        console.log('Project sample statistics:', stats);
      }
    });

    it('should have diverse project coverage', () => {
      const samples = helper.loadSamples<{ project_id: string }>('project');
      const projectIds = samples.map((s) => s.project_id);
      
      // Should have multiple different projects
      expect(new Set(projectIds).size).toBeGreaterThan(1);
      
      console.log(`Sampled ${projectIds.length} projects:`, Array.from(new Set(projectIds)));
    });
  });
});
