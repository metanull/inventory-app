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

  // Legacy fixture: one AWE record, the USA "Germany" record (#1494), and a
  // record of a project that was never imported (RUS).
  const hbRows = [
    { hb_id: 1, countryId: 'pa', gn: 'no', project_id: 'AWE' },
    { hb_id: 20, countryId: 'de', gn: 'no', project_id: 'USA' },
    { hb_id: 21, countryId: 'fr', gn: 'no', project_id: 'RUS' },
  ];
  const hbTexts = [
    { hb_id: 1, lang: 'en', name: 'Palestine' },
    { hb_id: 20, lang: 'en', name: 'Historical Profile / Germany' },
  ];
  const hbPages = [{ page_id: 100, hb_id: 20, sort_order: 1, remark: null }];
  const hbPageTexts = [{ page_id: 100, lang: 'en', subtitle: 'Germany page', text: 'Text.' }];

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'sh-root-collection-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'sh-context-uuid', 'context');
    tracker.set('mwnf3_sharing_history:sh_projects:usa', 'usa-root-collection-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_projects:usa', 'usa-context-uuid', 'context');
    // Note: no RUS project root/context — its HB record must be skipped.

    // Match the most specific table names first: the bare
    // sh_countries_historicalbackground branch must come last, since its name
    // is a prefix of every satellite table.
    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('sh_countries_historicalbackground_page_texts')) {
        return hbPageTexts;
      }
      if (sql.includes('sh_countries_historicalbackground_pages')) {
        return hbPages;
      }
      if (sql.includes('sh_countries_historicalbackground_image_texts')) {
        return [];
      }
      if (sql.includes('sh_countries_historicalbackground_images')) {
        return [];
      }
      if (sql.includes('sh_countries_historicalbackground_maps')) {
        return [];
      }
      if (sql.includes('sh_countries_historicalbackground_texts')) {
        return hbTexts;
      }
      if (sql.includes('sh_countries_historicalbackground')) {
        return hbRows;
      }
      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    writeCollectionMock = vi.fn(async (data: { backward_compatibility: string }) => {
      return `uuid-for:${data.backward_compatibility}`;
    });
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
      imageExists: vi.fn().mockResolvedValue(null),
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

  it("scopes each HB record to its own project's root and context (#1494)", async () => {
    const importer = new ShBibliographyHbImporter(context);
    const result = await importer.import();

    // hb 20 (USA) lands under the USA project root, in the USA context
    expect(writeCollectionMock).toHaveBeenCalledWith(
      expect.objectContaining({
        backward_compatibility: 'mwnf3_sharing_history:sh_countries_historicalbackground:20',
        context_id: 'usa-context-uuid',
        parent_id: 'usa-root-collection-uuid',
      })
    );
    expect(writeCollectionTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({
        title: 'Historical Profile / Germany',
        context_id: 'usa-context-uuid',
      })
    );
    expect(result.success).toBe(true);
  });

  it("gives HB pages the context of their parent record's project (#1494)", async () => {
    const importer = new ShBibliographyHbImporter(context);
    await importer.import();

    expect(writeCollectionMock).toHaveBeenCalledWith(
      expect.objectContaining({
        backward_compatibility: 'mwnf3_sharing_history:sh_countries_historicalbackground_pages:100',
        context_id: 'usa-context-uuid',
        parent_id: 'uuid-for:mwnf3_sharing_history:sh_countries_historicalbackground:20',
      })
    );
    expect(writeCollectionTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({
        title: 'Germany page',
        context_id: 'usa-context-uuid',
      })
    );
  });

  it('skips (with a warning) HB records whose project root/context is missing', async () => {
    const importer = new ShBibliographyHbImporter(context);
    const result = await importer.import();

    expect(writeCollectionMock).not.toHaveBeenCalledWith(
      expect.objectContaining({
        backward_compatibility: 'mwnf3_sharing_history:sh_countries_historicalbackground:21',
      })
    );
    expect(result.warnings).toEqual(
      expect.arrayContaining([expect.stringContaining('HB 21: SH project RUS root/context not found')])
    );
    expect(result.success).toBe(true);
  });
});
