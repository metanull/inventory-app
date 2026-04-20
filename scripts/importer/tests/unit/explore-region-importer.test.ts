import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ExploreRegionImporter } from '../../src/importers/phase-06/explore-region-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ExploreRegionImporter', () => {
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
    tracker.set('fr', 'fra', 'language');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_explore.regionsthemes')) {
        return [
          {
            regionId: 6,
            themeId: 4,
          },
          {
            regionId: 6,
            themeId: 7,
          },
        ];
      }

      if (sql.includes('FROM mwnf3_explore.regions')) {
        return [
          {
            regionId: 6,
            countryId: 'eg',
            label: 'Delta',
            geoCoordinates: '30.1,31.2',
            zoom: 8,
            type: 2,
          },
        ];
      }

      if (sql.includes('FROM mwnf3_explore.regiontranslated')) {
        return [
          {
            regionId: 6,
            langId: 'fr',
            spelling: 'Delta FR',
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

    writeCollectionMock = vi.fn().mockResolvedValue('region-collection-uuid');
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

  it('uses themeId from regionsthemes and imports region collections successfully', async () => {
    const importer = new ExploreRegionImporter(context);
    const result = await importer.import();

    expect(queryMock).toHaveBeenCalledWith(
      expect.stringContaining('SELECT regionId, themeId FROM mwnf3_explore.regionsthemes')
    );
    expect(writeCollectionMock).toHaveBeenCalledWith({
      internal_name: 'region_6_delta',
      backward_compatibility: 'mwnf3_explore:region:6',
      context_id: 'explore-context-uuid',
      language_id: 'eng',
      parent_id: 'country-collection-uuid',
      type: 'region',
      latitude: 30.1,
      longitude: 31.2,
      map_zoom: 8,
      country_id: null,
    });
    expect(writeCollectionTranslationMock).toHaveBeenCalledWith({
      collection_id: 'region-collection-uuid',
      language_id: 'eng',
      context_id: 'explore-context-uuid',
      backward_compatibility: 'mwnf3_explore:region:6:translation:eng',
      title: 'Delta',
      description: '',
      extra: JSON.stringify({ territory_level: 2, theme_ids: [4, 7] }),
    });
    expect(writeCollectionTranslationMock).toHaveBeenCalledWith({
      collection_id: 'region-collection-uuid',
      language_id: 'fra',
      context_id: 'explore-context-uuid',
      backward_compatibility: 'mwnf3_explore:region:6:translation:fra',
      title: 'Delta FR',
      description: '',
    });
    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
    expect(result.errors).toHaveLength(0);
  });
});
