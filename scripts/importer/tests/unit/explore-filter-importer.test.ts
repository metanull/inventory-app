/**
 * Unit tests for ExploreFilterImporter.
 *
 * The filter-monument links are the largest consumer of ExploreMonumentResolver:
 * 4,992 legacy rows, 907 of which point at one of the 259 Explore monuments that
 * carry more than one cross-reference. Those resolve to several source items, and
 * the filter tag has to reach every one of them — the rule the rest of the Explore
 * pipeline already follows.
 */

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ExploreFilterImporter } from '../../src/importers/phase-06/explore-filter-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

interface Fixtures {
  filters: Array<{ filterId: string; name: string; filtertype: string | null }>;
  links: Array<{ filterId: string; monumentId: number }>;
  monuments: number[];
  vm: number[];
  tr: number[];
}

const VM_BC = 'mwnf3:monuments:ISL:pa:Mon01:4';
const TRAVELS_BC = 'mwnf3_travels:monument:IAM:pa:1:I:1:c';

describe('ExploreFilterImporter', () => {
  let tracker: UnifiedTracker;
  let attachTagsToItemMock: ReturnType<typeof vi.fn>;

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

  function buildContext(fixtures: Fixtures): ImportContext {
    const queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_explore.filters_explore_monuments')) {
        return fixtures.links;
      }
      if (sql.includes('FROM mwnf3_explore.filters')) {
        return fixtures.filters;
      }
      if (sql.includes('exploremonument_vm')) {
        return fixtures.vm.map((monumentId) => ({
          monumentId,
          REF_monuments_project_id: 'ISL',
          REF_monuments_country: 'pa',
          REF_monuments_institution_id: 'Mon01',
          REF_monuments_number: 4,
        }));
      }
      if (sql.includes('exploremonument_tr')) {
        return fixtures.tr.map((monumentId) => ({
          monumentId,
          REF_tr_monuments_project_id: 'IAM',
          REF_tr_monuments_country: 'pa',
          REF_tr_monuments_itinerary_id: 'I',
          REF_tr_monuments_location_id: '1',
          REF_tr_monuments_number: 'c',
          REF_tr_monuments_trail_id: 1,
        }));
      }
      if (sql.includes('exploremonument_sh')) {
        return [];
      }
      if (sql.includes('FROM mwnf3_explore.exploremonument')) {
        return fixtures.monuments.map((monumentId) => ({
          monumentId,
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
        }));
      }
      return [];
    });

    const legacyDb: ILegacyDatabase = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    attachTagsToItemMock = vi.fn().mockResolvedValue(undefined);

    const strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeTag: vi.fn().mockResolvedValue('tag-uuid-hammam'),
      attachTagsToItem: attachTagsToItemMock,
    } as unknown as IWriteStrategy;

    return { legacyDb, strategy, tracker, logger, dryRun: false };
  }

  const FILTERS = [{ filterId: 'hammam', name: 'Hammam', filtertype: '1' }];

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.set(VM_BC, 'item-vm-monument', 'item');
    tracker.set(TRAVELS_BC, 'item-travels-monument', 'item');
    tracker.set('mwnf3_explore:monument:500', 'item-native-500', 'item');
  });

  it('tags every candidate when a monument is duplicated between VM and Travels', async () => {
    const context = buildContext({
      filters: FILTERS,
      links: [{ filterId: 'hammam', monumentId: 869 }],
      monuments: [869],
      vm: [869],
      tr: [869],
    });

    const importer = new ExploreFilterImporter(context);
    const result = await importer.import();

    expect(attachTagsToItemMock).toHaveBeenCalledTimes(2);
    expect(attachTagsToItemMock).toHaveBeenCalledWith('item-vm-monument', ['tag-uuid-hammam']);
    expect(attachTagsToItemMock).toHaveBeenCalledWith('item-travels-monument', ['tag-uuid-hammam']);
    // one filter tag + one link
    expect(result.imported).toBe(2);
    expect(result.skipped).toBe(0);
  });

  it('tags the single resolved item when a monument has one cross-reference', async () => {
    const context = buildContext({
      filters: FILTERS,
      links: [{ filterId: 'hammam', monumentId: 869 }],
      monuments: [869],
      vm: [869],
      tr: [],
    });

    const importer = new ExploreFilterImporter(context);
    await importer.import();

    expect(attachTagsToItemMock).toHaveBeenCalledTimes(1);
    expect(attachTagsToItemMock).toHaveBeenCalledWith('item-vm-monument', ['tag-uuid-hammam']);
  });

  it('tags the native Explore item when the monument has no cross-reference', async () => {
    const context = buildContext({
      filters: FILTERS,
      links: [{ filterId: 'hammam', monumentId: 500 }],
      monuments: [500],
      vm: [],
      tr: [],
    });

    const importer = new ExploreFilterImporter(context);
    await importer.import();

    expect(attachTagsToItemMock).toHaveBeenCalledWith('item-native-500', ['tag-uuid-hammam']);
  });

  it('warns and skips a link whose candidates were never imported', async () => {
    tracker = new UnifiedTracker();

    const context = buildContext({
      filters: FILTERS,
      links: [{ filterId: 'hammam', monumentId: 869 }],
      monuments: [869],
      vm: [869],
      tr: [869],
    });

    const importer = new ExploreFilterImporter(context);
    const result = await importer.import();

    expect(attachTagsToItemMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
    expect(vi.mocked(logger.warning).mock.calls[0]?.[0]).toContain(
      'mwnf3_explore:monument:869'
    );
  });

  it('writes nothing in dry-run mode', async () => {
    const context = buildContext({
      filters: FILTERS,
      links: [{ filterId: 'hammam', monumentId: 869 }],
      monuments: [869],
      vm: [869],
      tr: [869],
    });
    context.dryRun = true;

    const importer = new ExploreFilterImporter(context);
    await importer.import();

    expect(attachTagsToItemMock).not.toHaveBeenCalled();
  });
});
