import { beforeEach, describe, expect, it, vi } from 'vitest';

import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import { CollectionPurposeBackfillImporter } from '../../src/importers/phase-11/collection-purpose-backfill-importer.js';

describe('CollectionPurposeBackfillImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let backfillMock: ReturnType<typeof vi.fn>;

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

    legacyDb = {
      query: vi.fn().mockResolvedValue([]),
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    backfillMock = vi.fn().mockResolvedValue(0);

    strategy = {
      backfillCollectionPurposeByBackwardCompatibility: backfillMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('backfills every known marker keyspace with LIKE-escaped patterns', async () => {
    backfillMock.mockResolvedValue(1);

    const importer = new CollectionPurposeBackfillImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);

    // Prefix rules end with an unescaped %, literal `_` is escaped.
    expect(backfillMock).toHaveBeenCalledWith(
      'mwnf3\\_sharing\\_history:sh\\_exhibitions:root:%',
      'exhibitions-root'
    );
    expect(backfillMock).toHaveBeenCalledWith(
      'mwnf3\\_sharing\\_history:sh\\_countries\\_historicalbackground:root:%',
      'historical-profiles-root'
    );
    expect(backfillMock).toHaveBeenCalledWith(
      'mwnf3\\_sharing\\_history:sh\\_national\\_context\\_exhibitions:%',
      'national-context'
    );
    // Exact rules carry no wildcard.
    expect(backfillMock).toHaveBeenCalledWith('mwnf3:exhibitions:root', 'exhibitions-root');
    expect(backfillMock).toHaveBeenCalledWith('mwnf3:artintro:root', 'artistic-introduction-root');
    expect(backfillMock).toHaveBeenCalledWith('mwnf3\\_travels:root', 'travels-root');
    expect(backfillMock).toHaveBeenCalledWith(
      'mwnf3\\_thematic\\_gallery:galleries\\_root',
      'galleries-root'
    );
    expect(backfillMock).toHaveBeenCalledWith(
      'mwnf3\\_explore:root:explore\\_by\\_itinerary',
      'explore-itineraries-root'
    );

    // Every rule ran, and each updated row counts as imported.
    expect(result.imported).toBe(backfillMock.mock.calls.length);
    expect(result.errors).toEqual([]);
  });

  it('counts untouched keyspaces as skipped, not imported', async () => {
    backfillMock.mockResolvedValue(0);

    const importer = new CollectionPurposeBackfillImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(result.imported).toBe(0);
    expect(result.skipped).toBe(backfillMock.mock.calls.length);
  });

  it('performs no writes in dry-run mode', async () => {
    context = { ...context, dryRun: true };

    const importer = new CollectionPurposeBackfillImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(backfillMock).not.toHaveBeenCalled();
  });
});
