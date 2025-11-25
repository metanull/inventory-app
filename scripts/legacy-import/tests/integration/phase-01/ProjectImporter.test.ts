import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { SampleBasedTestHelper } from '../../helpers/SampleBasedTestHelper.js';
import { ProjectImporter } from '../../../src/importers/phase-01/ProjectImporter.js';
import { BackwardCompatibilityTracker } from '../../../src/utils/BackwardCompatibilityTracker.js';

interface ProjectSample {
  project_id: string;
  name: string;
  launchdate: string | null;
}

/**
 * Data-driven tests for ProjectImporter
 * VALIDATES: Project creates Context + Collection + Project entities with exact field transformation
 */
describe('ProjectImporter - Data Transformation', () => {
  let helper: SampleBasedTestHelper;

  beforeAll(() => {
    helper = new SampleBasedTestHelper();
  });

  afterAll(() => {
    helper.close();
  });

  it('should create Context, Collection, and Project for each project sample', async () => {
    const samples = helper.loadSamples<ProjectSample>('project', { limit: 5 });
    
    if (samples.length === 0) {
      console.log('⚠️  No project samples');
      return;
    }

    const tracker = new BackwardCompatibilityTracker();
    const mockContext = helper.createMockContextWithQueries([samples, []]); // projects + translations
    mockContext.tracker = tracker;
    
    const importer = new ProjectImporter(mockContext);
    const result = await importer.import();

    console.log('Import result:', result);

    const contextCalls = helper.getApiCalls(mockContext.apiClient, 'context.contextStore');
    const collectionCalls = helper.getApiCalls(mockContext.apiClient, 'collection.collectionStore');
    const projectCalls = helper.getApiCalls(mockContext.apiClient, 'project.projectStore');
    
    // Should create all three for each project
    expect(contextCalls.length).toBe(samples.length);
    expect(collectionCalls.length).toBe(samples.length);
    expect(projectCalls.length).toBe(samples.length);
    
    console.log(`✓ Created ${samples.length} contexts, collections, and projects`);
  });

  it('should transform Context fields correctly', async () => {
    const samples = helper.loadSamples<ProjectSample>('project', { limit: 3 });
    if (samples.length === 0) return;

    const tracker = new BackwardCompatibilityTracker();
    const mockContext = helper.createMockContextWithQueries([samples, []]);
    mockContext.tracker = tracker;
    
    const importer = new ProjectImporter(mockContext);
    await importer.import();

    const contextCalls = helper.getApiCalls(mockContext.apiClient, 'context.contextStore');
    
    samples.forEach((sample, i) => {
      const call = contextCalls[i];
      if (!call) throw new Error(`Missing context for ${sample.project_id}`);
      
      const apiData = call[0] as Record<string, unknown>;
      
      // Required fields
      expect(apiData).toHaveProperty('internal_name');
      expect(apiData).toHaveProperty('backward_compatibility');
      
      // internal_name should be project_id
      expect(apiData.internal_name).toBe(sample.project_id);
      
      // backward_compatibility format
      expect(apiData.backward_compatibility).toBe(`mwnf3:projects:${sample.project_id}`);
    });
  });

  it('should transform Collection fields correctly', async () => {
    const samples = helper.loadSamples<ProjectSample>('project', { limit: 3 });
    if (samples.length === 0) return;

    const tracker = new BackwardCompatibilityTracker();
    const mockContext = helper.createMockContextWithQueries([samples, []]);
    mockContext.tracker = tracker;
    
    const importer = new ProjectImporter(mockContext);
    await importer.import();

    const collectionCalls = helper.getApiCalls(mockContext.apiClient, 'collection.collectionStore');
    
    collectionCalls.forEach((call) => {
      const apiData = call[0] as Record<string, unknown>;
      
      // Required fields
      expect(apiData).toHaveProperty('internal_name');
      expect(apiData).toHaveProperty('context_id');
      expect(apiData).toHaveProperty('backward_compatibility');
      
      // backward_compatibility should end with :collection
      expect(apiData.backward_compatibility).toMatch(/:collection$/);
    });
  });

  it('should transform Project fields correctly', async () => {
    const samples = helper.loadSamples<ProjectSample>('project', { limit: 3 });
    if (samples.length === 0) return;

    const tracker = new BackwardCompatibilityTracker();
    const mockContext = helper.createMockContextWithQueries([samples, []]);
    mockContext.tracker = tracker;
    
    const importer = new ProjectImporter(mockContext);
    await importer.import();

    const projectCalls = helper.getApiCalls(mockContext.apiClient, 'project.projectStore');
    
    samples.forEach((sample, i) => {
      const call = projectCalls[i];
      if (!call) throw new Error(`Missing project for ${sample.project_id}`);
      
      const apiData = call[0] as Record<string, unknown>;
      
      // Required fields
      expect(apiData).toHaveProperty('internal_name');
      expect(apiData).toHaveProperty('context_id');
      expect(apiData).toHaveProperty('backward_compatibility');
      
      // backward_compatibility should end with :project
      expect(apiData.backward_compatibility).toBe(`mwnf3:projects:${sample.project_id}:project`);
      
      // launchdate transformation
      if (sample.launchdate) {
        expect(apiData).toHaveProperty('launch_date');
      }
    });
  });

  it('should report sample statistics', () => {
    const stats = helper.getReader().getStats();
    if (stats['project:success']) {
      console.log(`✓ Project samples: ${stats['project:success']}`);
    }
  });
});
