import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ExploreMonumentImporter } from '../../src/importers/phase-06/explore-monument-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ExploreMonumentImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeItemMock: ReturnType<typeof vi.fn>;
  let writeCollectionItemMock: ReturnType<typeof vi.fn>;

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
    tracker.set('mwnf3_explore:location:2', 'location-collection-uuid', 'collection');
    tracker.set('mwnf3:monuments:IAM:eg:Mus01:5', 'canonical-item-uuid', 'item');
    tracker.setMetadata('default_language_id', 'eng');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_explore.exploremonument_vm')) {
        return [
          {
            monumentId: 123,
            REF_monuments_project_id: 'IAM',
            REF_monuments_country: 'eg',
            REF_monuments_institution_id: 'Mus01',
            REF_monuments_number: 5,
          },
        ];
      }

      if (sql.includes('FROM mwnf3_explore.exploremonument_tr')) {
        return [];
      }

      if (sql.includes('FROM mwnf3_explore.exploremonument_sh')) {
        return [];
      }

      if (sql.includes('FROM mwnf3_explore.exploremonumentext')) {
        return [
          {
            monumentId: 123,
            langId: 'en',
            name: 'Referenced monument',
          },
        ];
      }

      if (sql.includes('FROM mwnf3_explore.exploremonument')) {
        return [
          {
            monumentId: 123,
            locationId: 2,
            title: 'Referenced monument',
            geoCoordinates: null,
            zoom: null,
            special_monument: null,
            related_monument: null,
            REF_tr_monuments_project_id: null,
            REF_tr_monuments_country: null,
            REF_tr_monuments_itinerary_id: null,
            REF_tr_monuments_location_id: null,
            REF_tr_monuments_number: null,
            REF_tr_monuments_lang: null,
            REF_tr_monuments_trail_id: null,
            REF_monuments_project_id: null,
            REF_monuments_country: null,
            REF_monuments_institution_id: null,
            REF_monuments_number: null,
            REF_monuments_lang: null,
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

    writeItemMock = vi.fn();
    writeCollectionItemMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeItem: writeItemMock,
      writeCollectionItem: writeCollectionItemMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('reuses the canonical source item for referenced Explore monuments instead of creating a shell item', async () => {
    const importer = new ExploreMonumentImporter(context);
    const result = await importer.import();

    expect(writeItemMock).not.toHaveBeenCalled();
    expect(writeCollectionItemMock).toHaveBeenCalledWith({
      collection_id: 'location-collection-uuid',
      item_id: 'canonical-item-uuid',
      backward_compatibility: 'mwnf3_explore:monument:123:collection_link:2',
      display_order: null,
    });
    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
  });

  it('logs info (not warning) when a monument resolves to multiple source candidates', async () => {
    // Monument 500 appears in both vm and travels tables → resolvedCandidates mode
    tracker.set('mwnf3:monuments:IAM:eg:Mus01:7', 'vm-candidate-uuid', 'item');
    tracker.set('mwnf3_travels:monument:IAM:pt:1:I:1:c', 'travels-candidate-uuid', 'item');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_explore.exploremonument_vm')) {
        return [
          {
            monumentId: 500,
            REF_monuments_project_id: 'IAM',
            REF_monuments_country: 'eg',
            REF_monuments_institution_id: 'Mus01',
            REF_monuments_number: 7,
          },
        ];
      }
      if (sql.includes('FROM mwnf3_explore.exploremonument_tr')) {
        return [
          {
            monumentId: 500,
            REF_tr_monuments_project_id: 'IAM',
            REF_tr_monuments_country: 'pt',
            REF_tr_monuments_itinerary_id: 'I',
            REF_tr_monuments_location_id: '1',
            REF_tr_monuments_number: 'c',
            REF_tr_monuments_trail_id: 1,
          },
        ];
      }
      if (sql.includes('FROM mwnf3_explore.exploremonument_sh')) return [];
      if (sql.includes('FROM mwnf3_explore.exploremonumentext')) {
        return [{ monumentId: 500, langId: 'en', name: 'Multi-candidate monument' }];
      }
      if (sql.includes('FROM mwnf3_explore.exploremonument')) {
        return [
          {
            monumentId: 500,
            locationId: 2,
            title: 'Multi-candidate monument',
            geoCoordinates: null,
            zoom: null,
            special_monument: null,
            related_monument: null,
            REF_tr_monuments_project_id: null,
            REF_tr_monuments_country: null,
            REF_tr_monuments_itinerary_id: null,
            REF_tr_monuments_location_id: null,
            REF_tr_monuments_number: null,
            REF_tr_monuments_lang: null,
            REF_tr_monuments_trail_id: null,
            REF_monuments_project_id: null,
            REF_monuments_country: null,
            REF_monuments_institution_id: null,
            REF_monuments_number: null,
            REF_monuments_lang: null,
          },
        ];
      }
      return [];
    });

    context = {
      ...context,
      legacyDb: {
        query: queryMock as ILegacyDatabase['query'],
        execute: vi.fn(),
        connect: vi.fn(),
        disconnect: vi.fn(),
      },
    };

    const importer = new ExploreMonumentImporter(context);
    await importer.import();

    // resolvedCandidates must NOT trigger a warning — it's expected multi-link behavior
    expect(logger.warning).not.toHaveBeenCalledWith(
      expect.stringContaining('resolves to multiple source items'),
      undefined
    );
    // But it should be logged at info level
    expect(logger.info).toHaveBeenCalledWith(
      expect.stringContaining('resolves to multiple source items')
    );
  });
});