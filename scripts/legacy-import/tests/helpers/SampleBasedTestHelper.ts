import { vi } from 'vitest';
import { SampleReader, SampleRecord } from '../../src/utils/SampleReader.js';
import { BackwardCompatibilityTracker } from '../../src/utils/BackwardCompatibilityTracker.js';
import type { ImportContext } from '../../src/importers/BaseImporter.js';
import * as path from 'path';

/**
 * Helper class for creating sample-based tests of importers
 * Uses real data from SQLite samples collected during import
 */
export class SampleBasedTestHelper {
  private reader: SampleReader;
  private tracker: BackwardCompatibilityTracker;
  private sampleDbPath: string;

  constructor(sampleDbPath?: string) {
    this.sampleDbPath =
      sampleDbPath ||
      path.resolve(__dirname, '../../test-fixtures/samples.sqlite');
    this.reader = new SampleReader(this.sampleDbPath);
    this.tracker = new BackwardCompatibilityTracker();
  }

  /**
   * Reset the tracker (call this in beforeEach to ensure clean state)
   */
  resetTracker(): void {
    this.tracker = new BackwardCompatibilityTracker();
  }

  /**
   * Get the SampleReader instance
   */
  getReader(): SampleReader {
    return this.reader;
  }

  /**
   * Get the BackwardCompatibilityTracker instance
   */
  getTracker(): BackwardCompatibilityTracker {
    return this.tracker;
  }

  /**
   * Create a mock ImportContext with a mocked legacy DB that returns sample data
   * @param entityType The entity type to load samples for
   * @param limit Max number of samples to load (default: all)
   * @returns Mocked ImportContext
   */
  createMockContext(entityType: string, limit?: number): ImportContext {
    // Load samples from SQLite
    const samples = this.reader.getByEntityType(entityType, limit);
    const legacyRecords = samples.map((s) => this.reader.parseRawData(s));

    // Create mock legacy DB that returns our sample data
    const mockLegacyDb = {
      query: vi.fn().mockResolvedValue(legacyRecords),
    };

    // Create mock API client
    const mockApiClient = this.createMockApiClient();

    return {
      legacyDb: mockLegacyDb as unknown as ImportContext['legacyDb'],
      apiClient: mockApiClient as unknown as ImportContext['apiClient'],
      tracker: this.tracker,
      dryRun: false,
    };
  }

  /**
   * Create a mock ImportContext with custom legacy DB query results
   * Useful for testing specific scenarios
   * @param queryResults Array of results to return for each query() call
   * @returns Mocked ImportContext
   */
  createMockContextWithQueries(queryResults: unknown[][]): ImportContext {
    const mockLegacyDb = {
      query: vi.fn(),
    };

    // Setup sequential query results
    let mockQuery = vi.mocked(mockLegacyDb.query);
    queryResults.forEach((result) => {
      mockQuery = mockQuery.mockResolvedValueOnce(result);
    });

    const mockApiClient = this.createMockApiClient();

    return {
      legacyDb: mockLegacyDb as unknown as ImportContext['legacyDb'],
      apiClient: mockApiClient as unknown as ImportContext['apiClient'],
      tracker: this.tracker,
      dryRun: false,
    };
  }

  /**
   * Load samples for a specific entity type with optional filtering
   * @param entityType Entity type (e.g., 'object', 'monument')
   * @param options Filter options
   * @returns Array of parsed legacy records
   */
  loadSamples<T = Record<string, unknown>>(
    entityType: string,
    options?: {
      reason?: 'success' | 'warning' | 'edge';
      warningType?: string;
      edgeType?: string;
      language?: string;
      limit?: number;
    }
  ): T[] {
    let samples: SampleRecord[];

    if (options?.reason === 'warning' && options?.warningType) {
      samples = this.reader.getWarningSamples(entityType, options.warningType);
    } else if (options?.reason === 'edge' && options?.edgeType) {
      samples = this.reader.getEdgeCaseSamples(entityType, options.edgeType);
    } else if (options?.reason === 'success') {
      samples = this.reader.getSuccessSamples(entityType, options?.limit || 20);
    } else if (options?.language) {
      samples = this.reader.getByLanguage(entityType, options.language, options?.limit || 20);
    } else {
      samples = this.reader.getByEntityType(entityType, options?.limit);
    }

    return samples.map((s) => this.reader.parseRawData<T>(s));
  }

  /**
   * Create a mock API client with all required endpoints
   * Tracks created entities and provides UUID generation
   */
  private createMockApiClient() {
    let uuidCounter = 1;
    const generateUuid = () => `test-uuid-${uuidCounter++}`;

    return {
      language: {
        languageStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
        languageIndex: vi.fn().mockResolvedValue({ data: { data: [] } }),
      },
      languageTranslation: {
        languageTranslationStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
      },
      country: {
        countryStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
        countryIndex: vi.fn().mockResolvedValue({ data: { data: [] } }),
      },
      countryTranslation: {
        countryTranslationStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
      },
      context: {
        contextStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
        contextGetDefault: vi.fn().mockResolvedValue({
          data: { data: { id: 'test-uuid-default-context' } },
        }),
        contextSetDefault: vi.fn().mockResolvedValue({
          data: { data: { id: 'test-uuid-default-context' } },
        }),
        contextClearDefault: vi.fn().mockResolvedValue({ data: {} }),
      },
      project: {
        projectStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
      },
      collection: {
        collectionStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
      },
      collectionTranslation: {
        collectionTranslationStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
      },
      partner: {
        partnerStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
      },
      partnerTranslation: {
        partnerTranslationStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
      },
      item: {
        itemStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
        itemUpdateTags: vi.fn().mockResolvedValue({ data: { data: {} } }),
      },
      itemTranslation: {
        itemTranslationStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
      },
      tag: {
        tagStore: vi.fn().mockImplementation(async (data) => ({
          data: { data: { id: generateUuid(), ...data } },
        })),
        tagIndex: vi.fn().mockResolvedValue({ data: { data: [] } }),
      },
      itemTag: {
        attachTagItem: vi.fn().mockResolvedValue({ data: { data: {} } }),
      },
    };
  }

  /**
   * Setup common foundation data in tracker
   * Languages, countries, default context, etc.
   */
  setupFoundationData() {
    // Load foundation data from samples and register in tracker
    const languages = this.loadSamples('language');
    languages.forEach((lang) => {
      const langId = (lang as { id: string }).id;
      this.tracker.register({
        uuid: `uuid-lang-${langId}`,
        backwardCompatibility: `mwnf3:languages:${langId}`,
        entityType: 'context', // Using 'context' as valid entityType
        createdAt: new Date(),
      });
    });

    const countries = this.loadSamples('country');
    countries.forEach((country) => {
      const countryId = (country as { id: string }).id;
      this.tracker.register({
        uuid: `uuid-country-${countryId}`,
        backwardCompatibility: `mwnf3:countries:${countryId}`,
        entityType: 'context', // Using 'context' as valid entityType
        createdAt: new Date(),
      });
    });

    // Register default context
    this.tracker.register({
      uuid: 'test-uuid-default-context',
      backwardCompatibility: '__default_context__',
      entityType: 'context',
      createdAt: new Date(),
    });
  }

  /**
   * Verify that expected API calls were made
   * @param mockApiClient The mocked API client
   * @param endpoint Endpoint to check (e.g., 'item.itemStore')
   * @param expectedCalls Number of expected calls
   */
  verifyApiCalls(
    mockApiClient: unknown,
    endpoint: string,
    expectedCalls: number
  ): void {
    const parts = endpoint.split('.');
    if (parts.length !== 2) return;
    const [service, method] = parts;
    if (!service || !method) return;
    const apiClient = mockApiClient as Record<string, Record<string, unknown>>;
    const serviceObj = apiClient[service];
    if (!serviceObj) return;
    const mock = serviceObj[method];
    if (vi.isMockFunction(mock)) {
      expect(mock).toHaveBeenCalledTimes(expectedCalls);
    }
  }

  /**
   * Get all calls made to an API endpoint
   * @param mockApiClient The mocked API client
   * @param endpoint Endpoint to check (e.g., 'item.itemStore')
   * @returns Array of call arguments
   */
  getApiCalls(mockApiClient: unknown, endpoint: string): unknown[][] {
    const parts = endpoint.split('.');
    if (parts.length !== 2) return [];
    const [service, method] = parts;
    if (!service || !method) return [];
    const apiClient = mockApiClient as Record<string, Record<string, unknown>>;
    const serviceObj = apiClient[service];
    if (!serviceObj) return [];
    const mock = serviceObj[method];
    if (vi.isMockFunction(mock)) {
      return mock.mock.calls;
    }
    return [];
  }

  /**
   * Clean up resources
   */
  close(): void {
    this.reader.close();
  }
}

// Helper function to use in tests
export function expectInRange(actual: number, min: number, max: number, message?: string): void {
  expect(actual).toBeGreaterThanOrEqual(min);
  expect(actual).toBeLessThanOrEqual(max);
  if (message) {
    console.log(`${message}: ${actual} (expected ${min}-${max})`);
  }
}
