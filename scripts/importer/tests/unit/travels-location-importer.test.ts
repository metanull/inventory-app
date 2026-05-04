import { beforeEach, describe, expect, it, vi } from 'vitest';

import { TravelsLocationImporter } from '../../src/importers/phase-07/travels-location-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('TravelsLocationImporter', () => {
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
    // Register parent itinerary collections
    tracker.set('mwnf3_travels:itinerary:IAM:pt:1:I', 'itin-pt-uuid', 'collection');
    tracker.set('mwnf3_travels:itinerary:IAM:es:1:I', 'itin-es-uuid', 'collection');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_travels.tr_locations')) {
        return [
          // Two locations with the same display title "SINTRA" but different BC keys
          {
            project_id: 'IAM',
            country: 'pt',
            itinerary_id: 'I',
            number: 1,
            lang: 'en',
            trail_id: 1,
            title: 'SINTRA',
          },
          {
            project_id: 'IAM',
            country: 'es',
            itinerary_id: 'I',
            number: 1,
            lang: 'en',
            trail_id: 1,
            title: 'SINTRA',
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

    writeCollectionMock = vi.fn().mockResolvedValue('location-collection-uuid');

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
    const importer = new TravelsLocationImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(result.imported).toBe(2);

    const calls = writeCollectionMock.mock.calls;
    expect(calls[0][0]).toMatchObject({
      internal_name: 'travels:location:IAM:pt:1:I:1',
      backward_compatibility: 'mwnf3_travels:location:IAM:pt:1:I:1',
      type: 'location',
    });
    expect(calls[1][0]).toMatchObject({
      internal_name: 'travels:location:IAM:es:1:I:1',
      backward_compatibility: 'mwnf3_travels:location:IAM:es:1:I:1',
      type: 'location',
    });
  });

  it('imports two locations with the same display title as distinct collections with unique internal names', async () => {
    const importer = new TravelsLocationImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(result.imported).toBe(2);

    const internalNames = writeCollectionMock.mock.calls.map(
      (call: [{ internal_name: string }]) => call[0].internal_name
    );
    expect(new Set(internalNames).size).toBe(2);
    expect(internalNames).not.toContain('SINTRA');
    expect(logger.warning).not.toHaveBeenCalled();
  });
});

describe('TravelsLocationImporter cascade regression', () => {
  it('a repeated location title in one country does not prevent the location in another country from being registered for monument lookup', async () => {
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

    const tracker = new UnifiedTracker();
    tracker.set('mwnf3_travels:context', 'ctx-uuid', 'context');
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('mwnf3_travels:itinerary:IAM:pt:1:I', 'itin-pt-uuid', 'collection');
    tracker.set('mwnf3_travels:itinerary:IAM:dz:1:I', 'itin-dz-uuid', 'collection');

    const writeCollectionMock = vi
      .fn()
      .mockResolvedValueOnce('loc-pt-uuid')
      .mockResolvedValueOnce('loc-dz-uuid');

    const context: ImportContext = {
      legacyDb: {
        query: vi.fn(async (sql: string) => {
          if (sql.includes('FROM mwnf3_travels.tr_locations')) {
            return [
              {
                project_id: 'IAM',
                country: 'pt',
                itinerary_id: 'I',
                number: 1,
                lang: 'en',
                trail_id: 1,
                title: 'COIMBRA',
              },
              {
                project_id: 'IAM',
                country: 'dz',
                itinerary_id: 'I',
                number: 1,
                lang: 'en',
                trail_id: 1,
                title: 'COIMBRA',
              },
            ];
          }
          return [];
        }) as ILegacyDatabase['query'],
        execute: vi.fn(),
        connect: vi.fn(),
        disconnect: vi.fn(),
      },
      strategy: {
        exists: vi.fn().mockResolvedValue(false),
        findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
        writeCollection: writeCollectionMock,
      } as unknown as IWriteStrategy,
      tracker,
      logger,
      dryRun: false,
    };

    const importer = new TravelsLocationImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(2);
    expect(result.errors).toHaveLength(0);

    // Both locations should now be registered in the tracker so monument lookups succeed
    expect(tracker.getUuid('mwnf3_travels:location:IAM:pt:1:I:1', 'collection')).toBe('loc-pt-uuid');
    expect(tracker.getUuid('mwnf3_travels:location:IAM:dz:1:I:1', 'collection')).toBe('loc-dz-uuid');
  });
});
