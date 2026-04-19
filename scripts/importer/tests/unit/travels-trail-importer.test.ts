import { beforeEach, describe, expect, it, vi } from 'vitest';

import { TravelsTrailImporter } from '../../src/importers/phase-07/travels-trail-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('TravelsTrailImporter', () => {
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
    tracker.set('mwnf3_travels:root', 'travels-root-uuid', 'collection');
    tracker.set('pt', 'prt', 'country');
    tracker.setMetadata('default_language_id', 'eng');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_travels.trails')) {
        return [
          {
            project_id: 'IAM',
            country: 'pt',
            lang: 'fr',
            number: 1,
            title: 'Sentier francais',
            subtitle: null,
            description: null,
            curated_by: null,
            local_coordinator: null,
            photo_by: null,
            museum_id: null,
            region_territory: null,
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

    writeCollectionMock = vi.fn().mockResolvedValue('trail-collection-uuid');

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

  it('uses the first named translation when english is missing and logs the fallback', async () => {
    const importer = new TravelsTrailImporter(context);
    const result = await importer.import();

    expect(writeCollectionMock).toHaveBeenCalledWith({
      internal_name: 'Sentier francais',
      backward_compatibility: 'mwnf3_travels:trail:IAM:pt:1',
      context_id: 'travels-context-uuid',
      language_id: 'eng',
      parent_id: 'travels-root-uuid',
      type: 'exhibition trail',
      latitude: null,
      longitude: null,
      map_zoom: null,
      country_id: 'prt',
    });
    expect(logger.warning).toHaveBeenCalledWith(
      'Travels trail mwnf3_travels:trail:IAM:pt:1 has no translation with a name in default language eng, using fra instead',
      undefined
    );
    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
  });
});