import { beforeEach, describe, expect, it, vi } from 'vitest';

import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import { ShExhibitionShowFlagImporter } from '../../src/importers/phase-11/sh-exhibition-show-flag-importer.js';

describe('ShExhibitionShowFlagImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let getLanguagesMock: ReturnType<typeof vi.fn>;
  let getExtraMock: ReturnType<typeof vi.fn>;
  let setExtraMock: ReturnType<typeof vi.fn>;

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

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_exhibitions')) {
        return [
          { exhibition_id: 1, project_id: 'AWE', show: 'y', new_status: null },
          { exhibition_id: 2, project_id: 'AWE', show: 'n', new_status: null },
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

    getLanguagesMock = vi.fn().mockResolvedValue(['eng']);
    getExtraMock = vi.fn().mockResolvedValue(null);
    setExtraMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      getCollectionTranslationLanguages: getLanguagesMock,
      getCollectionTranslationExtra: getExtraMock,
      setCollectionTranslationExtra: setExtraMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };

    tracker.set('mwnf3_sharing_history:sh_exhibitions:1', 'exh-1-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_exhibitions:2', 'exh-2-uuid', 'collection');
  });

  it('stamps legacy_exhibition (project_id + show) on every translation row', async () => {
    const importer = new ShExhibitionShowFlagImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(setExtraMock).toHaveBeenCalledTimes(2);

    const stamped = setExtraMock.mock.calls.map((call) => ({
      collectionId: call[0] as string,
      extra: JSON.parse(call[2] as string) as Record<string, unknown>,
    }));

    const hidden = stamped.find((s) => s.collectionId === 'exh-2-uuid');
    expect(hidden?.extra.legacy_exhibition).toEqual({ project_id: 'AWE', show: 'n' });

    const visible = stamped.find((s) => s.collectionId === 'exh-1-uuid');
    expect(visible?.extra.legacy_exhibition).toEqual({ project_id: 'AWE', show: 'y' });
  });

  it('merges without clobbering existing extra keys or legacy_exhibition members', async () => {
    getExtraMock.mockResolvedValue({
      bibliography: { eng: 'Some bibliography' },
      legacy_exhibition: { portal_image: 'img.jpg' },
    });

    const importer = new ShExhibitionShowFlagImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    const extra = JSON.parse(setExtraMock.mock.calls[0][2] as string) as Record<string, unknown>;
    expect(extra.bibliography).toEqual({ eng: 'Some bibliography' });
    expect(extra.legacy_exhibition).toMatchObject({ portal_image: 'img.jpg', show: 'y' });
  });

  it('is a no-op on a second run (flag already stamped)', async () => {
    getExtraMock.mockImplementation(async (collectionId: string) => ({
      legacy_exhibition: {
        project_id: 'AWE',
        show: collectionId === 'exh-2-uuid' ? 'n' : 'y',
      },
    }));

    const importer = new ShExhibitionShowFlagImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(setExtraMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(0);
    expect(result.skipped).toBe(2);
  });

  it('skips exhibitions whose collection was never imported, with a warning', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_exhibitions')) {
        return [{ exhibition_id: 99, project_id: 'AWE', show: 'y', new_status: null }];
      }
      return [];
    });

    const importer = new ShExhibitionShowFlagImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('mwnf3_sharing_history:sh_exhibitions:99'),
      undefined
    );
    expect(setExtraMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
  });

  it('skips (with warning) when the collection has no translation rows yet', async () => {
    getLanguagesMock.mockResolvedValue([]);

    const importer = new ShExhibitionShowFlagImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(setExtraMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(2);
  });

  it('performs no writes in dry-run mode', async () => {
    context = { ...context, dryRun: true };

    const importer = new ShExhibitionShowFlagImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(setExtraMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(2);
  });
});
