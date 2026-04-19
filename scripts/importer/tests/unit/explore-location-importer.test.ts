import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ExploreLocationImporter } from '../../src/importers/phase-06/explore-location-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ExploreLocationImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeCollectionMock: ReturnType<typeof vi.fn>;
  let writeCollectionTranslationMock: ReturnType<typeof vi.fn>;

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
    tracker.set('mwnf3_explore:context', 'explore-context-uuid', 'context');
    tracker.set('mwnf3_explore:country:eg', 'country-collection-uuid', 'collection');
    tracker.setMetadata('default_language_id', 'eng');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_explore.locations')) {
        return [
          {
            locationId: 21,
            countryId: 'eg',
            label: 'Alexandria',
            geoCoordinates: '31.2,29.9',
            zoom: 9,
            path: null,
            how_to_reach: null,
            info: null,
            contact: null,
            description: 'A city on the Mediterranean coast.',
          },
        ];
      }

      if (sql.includes('FROM mwnf3_explore.locationtranslated')) {
        return [
          {
            locationId: 21,
            langId: 'fr',
            spelling: 'Alexandrie',
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
    writeCollectionTranslationMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeCollection: writeCollectionMock,
      writeCollectionTranslation: writeCollectionTranslationMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('uses the human-readable label as internal_name instead of a technical slug', async () => {
    const importer = new ExploreLocationImporter(context);
    const result = await importer.import();

    expect(writeCollectionMock).toHaveBeenCalledWith({
      internal_name: 'Alexandria',
      backward_compatibility: 'mwnf3_explore:location:21',
      context_id: 'explore-context-uuid',
      language_id: 'eng',
      parent_id: 'country-collection-uuid',
      type: 'location',
      latitude: 31.2,
      longitude: 29.9,
      map_zoom: 9,
      country_id: null,
    });
    expect(writeCollectionTranslationMock).toHaveBeenCalledWith({
      collection_id: 'location-collection-uuid',
      language_id: 'eng',
      context_id: 'explore-context-uuid',
      backward_compatibility: 'mwnf3_explore:location:21:translation:eng',
      title: 'Alexandria',
      description: 'A city on the Mediterranean coast.',
    });
    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
  });
});