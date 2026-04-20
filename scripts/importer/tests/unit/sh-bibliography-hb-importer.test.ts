import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ShBibliographyHbImporter } from '../../src/importers/phase-03/sh-bibliography-hb-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ShBibliographyHbImporter', () => {
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
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'sh-root-collection-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'sh-context-uuid', 'context');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_bibliography_langs')) {
        return [];
      }

      if (sql.includes('FROM mwnf3_sharing_history.sh_bibliography')) {
        return [];
      }

      if (sql.includes('FROM mwnf3_sharing_history.sh_countries_historicalbackground_texts')) {
        return [{ hb_id: 1, lang: 'en', name: 'Palestine' }];
      }

      if (sql.includes('FROM mwnf3_sharing_history.sh_countries_historicalbackground')) {
        return [{ hb_id: 1, countryId: 'pa', gn: 'no', project_id: 'AWE' }];
      }

      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    writeCollectionMock = vi.fn().mockResolvedValue('hb-collection-uuid');
    writeCollectionTranslationMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeCollection: writeCollectionMock,
      writeCollectionTranslation: writeCollectionTranslationMock,
      getCollectionTranslationLanguages: vi.fn().mockResolvedValue([]),
      getItemTranslationLanguages: vi.fn().mockResolvedValue([]),
      getCollectionTranslationExtra: vi.fn().mockResolvedValue(null),
      setCollectionTranslationExtra: vi.fn().mockResolvedValue(undefined),
      getItemTranslationExtra: vi.fn().mockResolvedValue(null),
      setItemTranslationExtra: vi.fn().mockResolvedValue(undefined),
      writeCollectionImage: vi.fn().mockResolvedValue('image-uuid'),
      attachTagsToCollectionImage: vi.fn().mockResolvedValue(undefined),
      writeCollectionItem: vi.fn().mockResolvedValue(undefined),
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('writes canonical ISO country ids for HB parent collections', async () => {
    const importer = new ShBibliographyHbImporter(context);
    const result = await importer.import();

    expect(writeCollectionMock).toHaveBeenCalledWith(
      expect.objectContaining({
        backward_compatibility: 'mwnf3_sharing_history:sh_countries_historicalbackground:1',
        country_id: 'pse',
        context_id: 'sh-context-uuid',
        parent_id: 'sh-root-collection-uuid',
      })
    );
    expect(writeCollectionTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({
        title: 'Palestine',
      })
    );
    expect(result.success).toBe(true);
  });
});
