import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ShHbRecontextImporter } from '../../src/importers/phase-11/sh-hb-recontext-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ShHbRecontextImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let contextByCollection: Record<string, string>;
  let parentByCollection: Record<string, string>;
  let updateContextMock: ReturnType<typeof vi.fn>;
  let updateParentMock: ReturnType<typeof vi.fn>;
  let updateTranslationsContextMock: ReturnType<typeof vi.fn>;

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

  // Legacy fixture: an AWE record already in place, the USA "Germany" record
  // (#1494) sitting in the AWE context, a record of a never-imported project
  // (RUS), and a record that was never imported at all (hb 5).
  const hbRows = [
    { hb_id: 2, project_id: 'AWE' },
    { hb_id: 5, project_id: 'AWE' },
    { hb_id: 20, project_id: 'USA' },
    { hb_id: 21, project_id: 'RUS' },
  ];
  const hbPages = [
    { page_id: 100, hb_id: 20 },
    { page_id: 101, hb_id: 2 },
  ];

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'awe-root', 'collection');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'awe-ctx', 'context');
    tracker.set('mwnf3_sharing_history:sh_projects:usa', 'usa-root', 'collection');
    tracker.set('mwnf3_sharing_history:sh_projects:usa', 'usa-ctx', 'context');
    tracker.set('mwnf3_sharing_history:sh_countries_historicalbackground:2', 'hb2', 'collection');
    tracker.set('mwnf3_sharing_history:sh_countries_historicalbackground:20', 'hb20', 'collection');
    tracker.set('mwnf3_sharing_history:sh_countries_historicalbackground:21', 'hb21', 'collection');
    tracker.set(
      'mwnf3_sharing_history:sh_countries_historicalbackground_pages:100',
      'page100',
      'collection'
    );
    tracker.set(
      'mwnf3_sharing_history:sh_countries_historicalbackground_pages:101',
      'page101',
      'collection'
    );
    // No RUS project scope; hb 5 has no collection at all.

    // Production-like starting state: everything sits in the AWE context.
    contextByCollection = {
      hb2: 'awe-ctx',
      hb20: 'awe-ctx',
      hb21: 'awe-ctx',
      page100: 'awe-ctx',
      page101: 'awe-ctx',
    };
    parentByCollection = {
      hb2: 'awe-root',
      hb20: 'awe-root',
      hb21: 'awe-root',
    };

    const queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('sh_countries_historicalbackground_pages')) {
        return hbPages;
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

    updateContextMock = vi.fn().mockResolvedValue(undefined);
    updateParentMock = vi.fn().mockResolvedValue(undefined);
    updateTranslationsContextMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      getCollectionContextId: vi.fn(
        async (collectionId: string) => contextByCollection[collectionId] ?? null
      ),
      getCollectionParentId: vi.fn(
        async (collectionId: string) => parentByCollection[collectionId] ?? null
      ),
      updateCollectionContextId: updateContextMock,
      updateCollectionParentId: updateParentMock,
      updateCollectionTranslationsContextId: updateTranslationsContextMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('moves a mis-contexted HB record and its pages into its project context', async () => {
    const importer = new ShHbRecontextImporter(context);
    const result = await importer.import();

    // hb 20: translations, collection context, and parent all moved to USA
    expect(updateTranslationsContextMock).toHaveBeenCalledWith('hb20', 'usa-ctx');
    expect(updateContextMock).toHaveBeenCalledWith('hb20', 'usa-ctx');
    expect(updateParentMock).toHaveBeenCalledWith('hb20', 'usa-root');

    // its page: context + translations only (parent stays the HB record)
    expect(updateTranslationsContextMock).toHaveBeenCalledWith('page100', 'usa-ctx');
    expect(updateContextMock).toHaveBeenCalledWith('page100', 'usa-ctx');
    expect(updateParentMock).not.toHaveBeenCalledWith('page100', expect.anything());

    // hb 2 (AWE, already correct) and its page are untouched
    expect(updateContextMock).not.toHaveBeenCalledWith('hb2', expect.anything());
    expect(updateContextMock).not.toHaveBeenCalledWith('page101', expect.anything());
    expect(updateParentMock).not.toHaveBeenCalledWith('hb2', expect.anything());

    expect(result.success).toBe(true);
    expect(result.imported).toBe(2); // hb 20 + page 100
  });

  it('skips never-imported records and warns on missing project scopes, without failing', async () => {
    const importer = new ShHbRecontextImporter(context);
    const result = await importer.import();

    // hb 5 was never imported — quietly skipped
    // hb 21 (RUS): collection exists but the RUS project was never imported
    expect(updateContextMock).not.toHaveBeenCalledWith('hb21', expect.anything());
    expect(updateTranslationsContextMock).not.toHaveBeenCalledWith('hb21', expect.anything());
    expect(result.warnings).toEqual(
      expect.arrayContaining([
        expect.stringContaining('HB 21: SH project rus root/context not found'),
      ])
    );
    expect(result.errors).toEqual([]);
    expect(result.success).toBe(true);
  });

  it('is a full no-op when every record already sits in its project context', async () => {
    contextByCollection.hb20 = 'usa-ctx';
    contextByCollection.page100 = 'usa-ctx';
    parentByCollection.hb20 = 'usa-root';

    const importer = new ShHbRecontextImporter(context);
    const result = await importer.import();

    expect(updateContextMock).not.toHaveBeenCalled();
    expect(updateParentMock).not.toHaveBeenCalled();
    expect(updateTranslationsContextMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(0);
    expect(result.success).toBe(true);
  });

  it('targets the Historical Profiles marker as desired parent when it exists (#1505)', async () => {
    tracker.set(
      'mwnf3_sharing_history:sh_countries_historicalbackground:root:usa',
      'usa-profiles-root',
      'collection'
    );

    const importer = new ShHbRecontextImporter(context);
    const result = await importer.import();

    // hb 20 moves under the USA profiles marker, not the project root.
    expect(updateParentMock).toHaveBeenCalledWith('hb20', 'usa-profiles-root');
    expect(updateParentMock).not.toHaveBeenCalledWith('hb20', 'usa-root');
    expect(result.success).toBe(true);
  });

  it('does not undo the marker re-parenting on a rerun (#1505)', async () => {
    tracker.set(
      'mwnf3_sharing_history:sh_countries_historicalbackground:root:usa',
      'usa-profiles-root',
      'collection'
    );
    contextByCollection.hb20 = 'usa-ctx';
    contextByCollection.page100 = 'usa-ctx';
    parentByCollection.hb20 = 'usa-profiles-root';

    const importer = new ShHbRecontextImporter(context);
    const result = await importer.import();

    expect(updateParentMock).not.toHaveBeenCalledWith('hb20', expect.anything());
    expect(updateContextMock).not.toHaveBeenCalledWith('hb20', expect.anything());
    expect(result.success).toBe(true);
  });

  it('performs no writes in dry-run mode', async () => {
    context.dryRun = true;
    const importer = new ShHbRecontextImporter(context);
    const result = await importer.import();

    expect(updateContextMock).not.toHaveBeenCalled();
    expect(updateParentMock).not.toHaveBeenCalled();
    expect(updateTranslationsContextMock).not.toHaveBeenCalled();
    expect(result.success).toBe(true);
    expect(result.imported).toBeGreaterThan(0); // would-move records are reported
  });
});
