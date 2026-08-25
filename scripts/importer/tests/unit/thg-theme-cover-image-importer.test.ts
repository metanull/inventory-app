/**
 * Unit tests for ThgThemeCoverImageImporter (gap E4).
 *
 * theme_cover_image names which of a theme's selected pictures leads the theme
 * landing and theme-gallery pages. It was not imported at all.
 */

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ThgThemeCoverImageImporter } from '../../src/importers/phase-10/thg-theme-cover-image-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

const PICTURE_BC = 'mwnf3:objects_pictures:EXHCOLOUR:eg:Mus01:7:2';

const THEME_ITEM = {
  gallery_id: 47,
  theme_id: 5,
  item_id: 3,
  mwnf3_object_project_id: 'EXHCOLOUR',
  mwnf3_object_country_id: 'eg',
  mwnf3_object_partner_id: 'Mus01',
  mwnf3_object_item_id: 7,
  mwnf3_object_item_type: null,
  mwnf3_object_image_id: 2,
};

describe('ThgThemeCoverImageImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let setCollectionExtraMock: ReturnType<typeof vi.fn>;
  let getCollectionExtraMock: ReturnType<typeof vi.fn>;

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

  function buildQueryMock(
    themeItems: Record<string, unknown>[],
    covers: Record<string, unknown>[]
  ) {
    return vi.fn(async (sql: string) => {
      if (sql.includes('theme_cover_image')) {
        return covers;
      }
      if (sql.includes('FROM mwnf3_thematic_gallery.theme_item')) {
        return themeItems;
      }
      return [];
    });
  }

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.set('mwnf3_thematic_gallery:theme:47:5', 'theme-collection-uuid', 'collection');
    tracker.set(PICTURE_BC, 'picture-item-uuid', 'item');

    queryMock = buildQueryMock([THEME_ITEM], [{ gallery_id: 47, theme_id: 5, item_id: 3 }]);

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    getCollectionExtraMock = vi.fn().mockResolvedValue(null);
    setCollectionExtraMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      getCollectionExtra: getCollectionExtraMock,
      setCollectionExtra: setCollectionExtraMock,
    } as unknown as IWriteStrategy;

    context = { legacyDb, strategy, tracker, logger, dryRun: false };
  });

  it('marks the cover picture on the theme collection', async () => {
    const importer = new ThgThemeCoverImageImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(1);
    const [collectionId, serialized] = setCollectionExtraMock.mock.calls[0] as [string, string];

    expect(collectionId).toBe('theme-collection-uuid');
    expect(JSON.parse(serialized)).toEqual({
      thg_theme: {
        cover_picture: {
          backward_compatibility: PICTURE_BC,
          item_id: 'picture-item-uuid',
        },
      },
    });
  });

  it('preserves other keys already stored in the theme collection extra', async () => {
    getCollectionExtraMock.mockResolvedValue({
      unrelated: 'keep me',
      thg_theme: { some_other_key: 1 },
    });

    const importer = new ThgThemeCoverImageImporter(context);
    await importer.import();

    const serialized = setCollectionExtraMock.mock.calls[0][1] as string;
    const extra = JSON.parse(serialized) as Record<string, Record<string, unknown>>;

    expect(extra.unrelated).toBe('keep me');
    expect(extra.thg_theme.some_other_key).toBe(1);
    expect(extra.thg_theme.cover_picture).toBeDefined();
  });

  it('warns and skips when the referenced theme_item does not exist', async () => {
    queryMock = buildQueryMock([], [{ gallery_id: 47, theme_id: 5, item_id: 3 }]);
    context = { ...context, legacyDb: { ...legacyDb, query: queryMock as ILegacyDatabase['query'] } };

    const importer = new ThgThemeCoverImageImporter(context);
    const result = await importer.import();

    expect(setCollectionExtraMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
    expect(result.warnings).toEqual([expect.stringContaining('47.5.3')]);
  });

  it('warns and skips when the theme collection is missing', async () => {
    tracker = new UnifiedTracker();
    tracker.set(PICTURE_BC, 'picture-item-uuid', 'item');
    context = { ...context, tracker };

    const importer = new ThgThemeCoverImageImporter(context);
    const result = await importer.import();

    expect(setCollectionExtraMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
    expect(result.warnings).toEqual([
      expect.stringContaining('mwnf3_thematic_gallery:theme:47:5'),
    ]);
  });

  it('resolves covers from any picture family, including Explore', async () => {
    queryMock = buildQueryMock(
      [
        {
          gallery_id: 54,
          theme_id: 3,
          item_id: 1,
          explore_monument_item_id: 1791,
          explore_monument_item_type: '',
          explore_monument_image_id: 1,
        },
      ],
      [{ gallery_id: 54, theme_id: 3, item_id: 1 }]
    );
    tracker.set('mwnf3_thematic_gallery:theme:54:3', 'theme-54-3', 'collection');
    tracker.set('mwnf3_explore:monument_picture:1791:_:1', 'explore-picture-uuid', 'item');
    context = { ...context, legacyDb: { ...legacyDb, query: queryMock as ILegacyDatabase['query'] } };

    const importer = new ThgThemeCoverImageImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(1);
    const serialized = setCollectionExtraMock.mock.calls[0][1] as string;
    const extra = JSON.parse(serialized) as Record<string, Record<string, Record<string, unknown>>>;
    expect(extra.thg_theme.cover_picture.item_id).toBe('explore-picture-uuid');
  });

  it('writes nothing in dry-run mode', async () => {
    context.dryRun = true;

    const importer = new ThgThemeCoverImageImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(1);
    expect(setCollectionExtraMock).not.toHaveBeenCalled();
  });
});
