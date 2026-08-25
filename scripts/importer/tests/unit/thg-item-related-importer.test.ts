/**
 * Unit tests for ThgItemRelatedImporter (gap E5).
 *
 * theme_item_related addresses its target by the related row's OWN
 * (related_gallery_id, related_theme_id, related_item_id) triple. The importer
 * used to assume the target lived in the source's theme, so the 8 cross-theme
 * rows — 3 of them cross-exhibition — missed the lookup cache and were dropped.
 */

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ThgItemRelatedImporter } from '../../src/importers/phase-10/thg-item-related-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

/** A theme_item row referencing one mwnf3 object picture. */
function themeItem(
  galleryId: number,
  themeId: number,
  itemId: number,
  objectNumber: number
): Record<string, unknown> {
  return {
    gallery_id: galleryId,
    theme_id: themeId,
    item_id: itemId,
    mwnf3_object_project_id: 'EXHCOLOUR',
    mwnf3_object_country_id: 'eg',
    mwnf3_object_partner_id: 'Mus01',
    mwnf3_object_item_id: objectNumber,
    mwnf3_object_item_type: null,
    mwnf3_object_image_id: 1,
  };
}

describe('ThgItemRelatedImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeItemItemLinkMock: ReturnType<typeof vi.fn>;

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
    related: Record<string, unknown>[]
  ) {
    return vi.fn(async (sql: string) => {
      if (sql.includes('theme_item_related')) {
        return related;
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
    tracker.set('mwnf3_thematic_gallery:thg_gallery:47', 'context-uuid-47', 'context');
    tracker.set('mwnf3_thematic_gallery:thg_gallery:54', 'context-uuid-54', 'context');
    tracker.set('mwnf3:objects_pictures:EXHCOLOUR:eg:Mus01:1:1', 'item-picture-1', 'item');
    tracker.set('mwnf3:objects_pictures:EXHCOLOUR:eg:Mus01:2:1', 'item-picture-2', 'item');

    queryMock = buildQueryMock(
      [themeItem(47, 5, 14, 1), themeItem(47, 1, 5, 2)],
      [
        {
          gallery_id: 47,
          theme_id: 5,
          item_id: 14,
          related_gallery_id: 47,
          related_theme_id: 1,
          related_item_id: 5,
        },
      ]
    );

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    writeItemItemLinkMock = vi.fn().mockResolvedValue('link-uuid');

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeItemItemLink: writeItemItemLinkMock,
    } as unknown as IWriteStrategy;

    context = { legacyDb, strategy, tracker, logger, dryRun: false };
  });

  it('resolves a target that lives in another theme of the same exhibition', async () => {
    const importer = new ThgItemRelatedImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(1);
    expect(writeItemItemLinkMock).toHaveBeenCalledWith(
      expect.objectContaining({
        source_id: 'item-picture-1',
        target_id: 'item-picture-2',
        context_id: 'context-uuid-47',
      })
    );
  });

  it('resolves a target that lives in another exhibition', async () => {
    queryMock = buildQueryMock(
      [themeItem(54, 11, 1, 1), themeItem(47, 0, 2, 2)],
      [
        {
          gallery_id: 54,
          theme_id: 11,
          item_id: 1,
          related_gallery_id: 47,
          related_theme_id: 0,
          related_item_id: 2,
        },
      ]
    );
    context = { ...context, legacyDb: { ...legacyDb, query: queryMock as ILegacyDatabase['query'] } };

    const importer = new ThgItemRelatedImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(1);
    expect(writeItemItemLinkMock).toHaveBeenCalledWith(
      expect.objectContaining({
        source_id: 'item-picture-1',
        target_id: 'item-picture-2',
        // the link belongs to the exhibition that authored it
        context_id: 'context-uuid-54',
      })
    );
  });

  it('treats related_theme_id 0 as a real theme, not as "unset"', async () => {
    queryMock = buildQueryMock(
      [themeItem(47, 3, 1, 1), themeItem(47, 0, 9, 2)],
      [
        {
          gallery_id: 47,
          theme_id: 3,
          item_id: 1,
          related_gallery_id: 47,
          related_theme_id: 0,
          related_item_id: 9,
        },
      ]
    );
    context = { ...context, legacyDb: { ...legacyDb, query: queryMock as ILegacyDatabase['query'] } };

    const importer = new ThgItemRelatedImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(1);
    expect(writeItemItemLinkMock).toHaveBeenCalledWith(
      expect.objectContaining({ target_id: 'item-picture-2' })
    );
  });

  it('keeps the backward-compatibility key addressed by source triple and related item', async () => {
    const importer = new ThgItemRelatedImporter(context);
    await importer.import();

    expect(writeItemItemLinkMock).toHaveBeenCalledWith(
      expect.objectContaining({
        backward_compatibility: 'mwnf3_thematic_gallery:theme_item_related:47:5:14:5',
      })
    );
  });

  it('falls back to the source gallery and theme when the related columns are null', async () => {
    queryMock = buildQueryMock(
      [themeItem(47, 5, 14, 1), themeItem(47, 5, 5, 2)],
      [
        {
          gallery_id: 47,
          theme_id: 5,
          item_id: 14,
          related_gallery_id: null,
          related_theme_id: null,
          related_item_id: 5,
        },
      ]
    );
    context = { ...context, legacyDb: { ...legacyDb, query: queryMock as ILegacyDatabase['query'] } };

    const importer = new ThgItemRelatedImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(1);
  });

  it('names the missing target key when the related theme_item does not exist', async () => {
    queryMock = buildQueryMock(
      [themeItem(47, 5, 14, 1)],
      [
        {
          gallery_id: 47,
          theme_id: 5,
          item_id: 14,
          related_gallery_id: 52,
          related_theme_id: 33,
          related_item_id: 7,
        },
      ]
    );
    context = { ...context, legacyDb: { ...legacyDb, query: queryMock as ILegacyDatabase['query'] } };

    const importer = new ThgItemRelatedImporter(context);
    const result = await importer.import();

    expect(writeItemItemLinkMock).not.toHaveBeenCalled();
    expect(result.warnings).toEqual([expect.stringContaining('52.33.7')]);
  });

  it('selects the related gallery and theme columns', async () => {
    const importer = new ThgItemRelatedImporter(context);
    await importer.import();

    const sql = queryMock.mock.calls
      .map((args: unknown[]) => args[0] as string)
      .find((s) => s.includes('theme_item_related'));

    expect(sql).toContain('related_gallery_id');
    expect(sql).toContain('related_theme_id');
  });
});
