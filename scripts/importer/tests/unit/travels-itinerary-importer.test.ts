import { beforeEach, describe, expect, it, vi } from 'vitest';

import { TravelsItineraryImporter } from '../../src/importers/phase-07/travels-itinerary-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('TravelsItineraryImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeCollectionMock: ReturnType<typeof vi.fn>;

  const logger: ILogger = {
    info: vi.fn(),
    warning: vi.fn(),
    skip: vi.fn(),
    error: vi.fn(),
    exception: vi.fn(),
    showProgress: vi.fn(),
    showSkipped: vi.fn(),
    showError: vi.fn(),
    showSummary: vi.fn(),
  };

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.set('mwnf3_travels:context', 'travels-context-uuid', 'context');
    tracker.setMetadata('default_language_id', 'eng');
    // Register parent trail collections
    tracker.set('mwnf3_travels:trail:IAM:pt:1', 'trail-1-uuid', 'collection');
    tracker.set('mwnf3_travels:trail:IAM:es:1', 'trail-2-uuid', 'collection');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_travels.tr_itineraries')) {
        return [
          // Two itineraries with the same display title "Mudejar Art" but different BC keys
          {
            project_id: 'IAM',
            country: 'pt',
            number: 'I',
            lang: 'en',
            trail_id: 1,
            title: 'Mudejar Art',
            description: null,
            days: null,
          },
          {
            project_id: 'IAM',
            country: 'es',
            number: 'I',
            lang: 'en',
            trail_id: 1,
            title: 'Mudejar Art',
            description: null,
            days: null,
          },
        ];
      }
      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    writeCollectionMock = vi.fn().mockResolvedValue('itinerary-collection-uuid');

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeCollection: writeCollectionMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('uses a namespaced BC-derived internal_name instead of the display title', async () => {
    const importer = new TravelsItineraryImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(result.imported).toBe(2);

    const calls = writeCollectionMock.mock.calls;
    expect(calls[0][0]).toMatchObject({
      internal_name: 'travels:itinerary:IAM:pt:1:I',
      backward_compatibility: 'mwnf3_travels:itinerary:IAM:pt:1:I',
      type: 'itinerary',
    });
    expect(calls[1][0]).toMatchObject({
      internal_name: 'travels:itinerary:IAM:es:1:I',
      backward_compatibility: 'mwnf3_travels:itinerary:IAM:es:1:I',
      type: 'itinerary',
    });
  });

  it('imports two itineraries with the same display title as distinct collections with unique internal names', async () => {
    const importer = new TravelsItineraryImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(result.imported).toBe(2);

    const internalNames = writeCollectionMock.mock.calls.map(
      (call: unknown[]) => (call[0] as { internal_name: string }).internal_name
    );
    expect(new Set(internalNames).size).toBe(2);
    expect(internalNames).not.toContain('Mudejar Art');
    expect(logger.warning).not.toHaveBeenCalled();
  });
});
