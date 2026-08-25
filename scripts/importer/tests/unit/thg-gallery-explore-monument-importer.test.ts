/**
 * Unit tests for ThgGalleryExploreMonumentImporter.
 *
 * Explore monuments are often references to a monument that already exists in
 * another dataset. Legacy monument 869 (gallery 54) is the real case that drove
 * these tests: it carries both a Virtual Museum and a Travels cross-reference,
 * so the resolver reports two candidates and the gallery must end up linked to
 * both — the rule the rest of the Explore pipeline already follows.
 */

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ThgGalleryExploreMonumentImporter } from '../../src/importers/phase-10/thg-gallery-explore-monument-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

interface ExploreFixtures {
  /** thg_gallery_explore_monuments rows */
  links: Array<{ gallery_id: number; item_id: number }>;
  /** exploremonument rows (direct REF_* columns left empty) */
  monuments: number[];
  /** exploremonument_vm rows */
  vm: Array<{ monumentId: number }>;
  /** exploremonument_tr rows */
  tr: Array<{ monumentId: number }>;
}

const VM_BC = 'mwnf3:monuments:ISL:pa:Mon01:4';
const TRAVELS_BC = 'mwnf3_travels:monument:IAM:pa:1:I:1:c';

describe('ThgGalleryExploreMonumentImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let attachItemsToCollectionMock: ReturnType<typeof vi.fn>;

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

  function buildQueryMock(fixtures: ExploreFixtures) {
    return vi.fn(async (sql: string) => {
      if (sql.includes('thg_gallery_explore_monuments')) {
        return fixtures.links;
      }
      if (sql.includes('exploremonument_vm')) {
        return fixtures.vm.map((row) => ({
          monumentId: row.monumentId,
          REF_monuments_project_id: 'ISL',
          REF_monuments_country: 'pa',
          REF_monuments_institution_id: 'Mon01',
          REF_monuments_number: 4,
        }));
      }
      if (sql.includes('exploremonument_tr')) {
        return fixtures.tr.map((row) => ({
          monumentId: row.monumentId,
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
  }

  function buildContext(fixtures: ExploreFixtures): ImportContext {
    legacyDb = {
      query: buildQueryMock(fixtures) as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    attachItemsToCollectionMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      attachItemsToCollection: attachItemsToCollectionMock,
    } as unknown as IWriteStrategy;

    return { legacyDb, strategy, tracker, logger, dryRun: false };
  }

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.set('mwnf3_thematic_gallery:thg_gallery:54', 'collection-uuid-54', 'collection');
    tracker.set(VM_BC, 'item-vm-monument', 'item');
    tracker.set(TRAVELS_BC, 'item-travels-monument', 'item');
    tracker.set('mwnf3_explore:monument:1334', 'item-native-1334', 'item');
  });

  it('attaches both candidates when a monument is duplicated between VM and Travels', async () => {
    context = buildContext({
      links: [{ gallery_id: 54, item_id: 869 }],
      monuments: [869],
      vm: [{ monumentId: 869 }],
      tr: [{ monumentId: 869 }],
    });

    const importer = new ThgGalleryExploreMonumentImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(1);
    expect(attachItemsToCollectionMock).toHaveBeenCalledWith('collection-uuid-54', [
      'item-vm-monument',
      'item-travels-monument',
    ]);
    expect(result.warnings ?? []).toEqual([]);
  });

  it('attaches the single resolved item when a monument has one cross-reference', async () => {
    context = buildContext({
      links: [{ gallery_id: 54, item_id: 869 }],
      monuments: [869],
      vm: [{ monumentId: 869 }],
      tr: [],
    });

    const importer = new ThgGalleryExploreMonumentImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(1);
    expect(attachItemsToCollectionMock).toHaveBeenCalledWith('collection-uuid-54', [
      'item-vm-monument',
    ]);
  });

  it('attaches the native Explore item when the monument has no cross-reference', async () => {
    context = buildContext({
      links: [{ gallery_id: 54, item_id: 1334 }],
      monuments: [1334],
      vm: [],
      tr: [],
    });

    const importer = new ThgGalleryExploreMonumentImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(1);
    expect(attachItemsToCollectionMock).toHaveBeenCalledWith('collection-uuid-54', [
      'item-native-1334',
    ]);
  });

  it('warns and skips when no candidate resolves to an imported item', async () => {
    tracker = new UnifiedTracker();
    tracker.set('mwnf3_thematic_gallery:thg_gallery:54', 'collection-uuid-54', 'collection');

    context = buildContext({
      links: [{ gallery_id: 54, item_id: 869 }],
      monuments: [869],
      vm: [{ monumentId: 869 }],
      tr: [{ monumentId: 869 }],
    });

    const importer = new ThgGalleryExploreMonumentImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(0);
    expect(attachItemsToCollectionMock).not.toHaveBeenCalled();
    expect(result.warnings).toEqual([expect.stringContaining('mwnf3_explore:monument:869')]);
  });

  it('writes nothing in dry-run mode', async () => {
    context = buildContext({
      links: [{ gallery_id: 54, item_id: 869 }],
      monuments: [869],
      vm: [{ monumentId: 869 }],
      tr: [{ monumentId: 869 }],
    });
    context.dryRun = true;

    const importer = new ThgGalleryExploreMonumentImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(1);
    expect(attachItemsToCollectionMock).not.toHaveBeenCalled();
  });
});
