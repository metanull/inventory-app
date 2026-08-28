import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ExploreMonumentCountryBackfillImporter } from '../../src/importers/phase-11/explore-monument-country-backfill-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

/**
 * Monument 1792 sits in an Indian location; legacy resolves `in` through
 * locations.countryId and the live API returns it. 1419 is the deduplicated
 * one — it has no item of its own, having been folded into the BAR item
 * `mwnf3:monuments:BAR:it:Mon13:14` (Palazzo Chigi, Ariccia), so it must never
 * appear in the enumeration this backfill drives off.
 */
const LEGACY_MONUMENTS = [
  { monumentId: 1792, countryId: 'in' },
  { monumentId: 1419, countryId: 'it' },
  { monumentId: 2001, countryId: null },
  { monumentId: 2002, countryId: 'zz' },
];

describe('ExploreMonumentCountryBackfillImporter', () => {
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let findItemsMock: ReturnType<typeof vi.fn>;
  let setCountryMock: ReturnType<typeof vi.fn>;

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

    queryMock = vi.fn().mockResolvedValue(LEGACY_MONUMENTS);
    findItemsMock = vi.fn().mockResolvedValue([]);
    setCountryMock = vi.fn().mockResolvedValue(1);

    context = {
      legacyDb: {
        query: queryMock,
        execute: vi.fn(),
        connect: vi.fn(),
        disconnect: vi.fn(),
      } as unknown as ILegacyDatabase,
      strategy: {
        findItemsWithoutCountryByBackwardCompatibilityPrefix: findItemsMock,
        setItemCountryIdIfUnset: setCountryMock,
      } as unknown as IWriteStrategy,
      tracker: new UnifiedTracker(),
      logger,
      dryRun: false,
    };
  });

  it('maps the legacy location country onto the item', async () => {
    findItemsMock.mockResolvedValue([
      { id: 'item-1792', backward_compatibility: 'mwnf3_explore:monument:1792' },
    ]);

    const result = await new ExploreMonumentCountryBackfillImporter(context).import();

    expect(setCountryMock).toHaveBeenCalledWith('item-1792', 'ind');
    expect(result.imported).toBe(1);
    expect(result.errors).toHaveLength(0);
  });

  /**
   * The dedup guard, and the whole reason this backfill enumerates by the
   * item's OWN backward_compatibility rather than resolving legacy keys
   * through the tracker: monument 1419 resolves to a BAR item whose country is
   * authoritative. Because that item's key is `mwnf3:monuments:BAR:…`, the
   * prefix query cannot return it — there is nothing to guard against at the
   * row level.
   */
  it('enumerates only items whose own key is in the Explore monument keyspace', async () => {
    await new ExploreMonumentCountryBackfillImporter(context).import();

    expect(findItemsMock).toHaveBeenCalledWith('mwnf3_explore:monument:');
  });

  it('is a no-op when every Explore monument already has a country', async () => {
    findItemsMock.mockResolvedValue([]);

    const result = await new ExploreMonumentCountryBackfillImporter(context).import();

    expect(setCountryMock).not.toHaveBeenCalled();
    // Nothing to repair means the legacy database is never even read.
    expect(queryMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(0);
    expect(result.success).toBe(true);
  });

  it('skips a monument whose location carries no country, and one whose code is unknown', async () => {
    findItemsMock.mockResolvedValue([
      { id: 'item-2001', backward_compatibility: 'mwnf3_explore:monument:2001' },
      { id: 'item-2002', backward_compatibility: 'mwnf3_explore:monument:2002' },
      { id: 'item-9999', backward_compatibility: 'mwnf3_explore:monument:9999' },
    ]);

    const result = await new ExploreMonumentCountryBackfillImporter(context).import();

    expect(setCountryMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(3);
    // A code mapCountryCode rejects is a warning, never a failed import.
    expect(result.errors).toHaveLength(0);
    expect(result.warnings).toHaveLength(3);
  });

  /**
   * The write is conditional on country_id still being null. A row another
   * step claimed between the enumeration and the write counts as skipped, not
   * imported.
   */
  it('counts a row another step has since claimed as skipped', async () => {
    findItemsMock.mockResolvedValue([
      { id: 'item-1792', backward_compatibility: 'mwnf3_explore:monument:1792' },
    ]);
    setCountryMock.mockResolvedValue(0);

    const result = await new ExploreMonumentCountryBackfillImporter(context).import();

    expect(result.imported).toBe(0);
    expect(result.skipped).toBe(1);
  });

  it('writes nothing in dry-run mode but still counts the row', async () => {
    findItemsMock.mockResolvedValue([
      { id: 'item-1792', backward_compatibility: 'mwnf3_explore:monument:1792' },
    ]);
    context = { ...context, dryRun: true };

    const result = await new ExploreMonumentCountryBackfillImporter(context).import();

    expect(setCountryMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(1);
  });
});
