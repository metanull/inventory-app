import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ShNationalContextImporter } from '../../src/importers/phase-03/sh-national-context-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ShNationalContextImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeCollectionMock: ReturnType<typeof vi.fn>;
  let writeCollectionItemMock: ReturnType<typeof vi.fn>;

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
    tracker.set('mwnf3_sharing_history:sh_exhibitions:1', 'sh-exhibition-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'sh-context-uuid', 'context');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_national_context_exhibitions')) {
        return [{ country: 'pa', exhibition_id: 1 }];
      }

      if (
        sql.includes('SELECT exhibition_id, project_id FROM mwnf3_sharing_history.sh_exhibitions')
      ) {
        return [{ exhibition_id: 1, project_id: 'AWE' }];
      }

      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    writeCollectionMock = vi.fn().mockResolvedValue('nc-collection-uuid');
    writeCollectionItemMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeCollection: writeCollectionMock,
      writeCollectionTranslation: vi.fn().mockResolvedValue(undefined),
      writeCollectionItem: writeCollectionItemMock,
      writeItemItemLink: vi.fn().mockResolvedValue('link-uuid'),
      writeItemItemLinkTranslation: vi.fn().mockResolvedValue(undefined),
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('writes canonical ISO country ids for national context collections', async () => {
    const importer = new ShNationalContextImporter(context);
    const result = await importer.import();

    expect(writeCollectionMock).toHaveBeenCalledWith(
      expect.objectContaining({
        backward_compatibility: 'mwnf3_sharing_history:sh_national_context_exhibitions:pa:1',
        country_id: 'pse',
        context_id: 'sh-context-uuid',
        parent_id: 'sh-exhibition-uuid',
      })
    );
    expect(result.success).toBe(true);
  });

  it('never queries sh_national_context_exhibition_texts', async () => {
    const importer = new ShNationalContextImporter(context);
    await importer.import();

    const allCalls: string[] = (queryMock.mock.calls as [string][]).map(([sql]) => sql);
    expect(allCalls.some((sql) => sql.includes('sh_national_context_exhibition_texts'))).toBe(
      false
    );
  });

  describe('importNCImages', () => {
    beforeEach(() => {
      // Pre-register the NC collection so image step can find it
      tracker.set(
        'mwnf3_sharing_history:sh_national_context_exhibitions:tn:3',
        'nc-collection-tn-uuid',
        'collection'
      );
      // Pre-register items that image rows reference
      tracker.set('mwnf3_sharing_history:sh_objects:awe:tn:10', 'obj-item-uuid', 'item');
      tracker.set('mwnf3_sharing_history:sh_monuments:awe:rm:66', 'mon-item-uuid', 'item');

      // Override queryMock: no NC exhibitions (step 1 is a no-op), return image rows for step 2
      queryMock.mockImplementation(async (sql: string) => {
        if (sql.includes('FROM mwnf3_sharing_history.sh_national_context_exhibitions')) {
          return [];
        }
        if (sql.includes('SELECT exhibition_id, project_id FROM mwnf3_sharing_history.sh_exhibitions')) {
          return [];
        }
        if (sql.includes('FROM mwnf3_sharing_history.sh_national_context_exhibition_images')) {
          return [
            {
              image_id: 101,
              country: 'tn',
              exhibition_id: 3,
              image_item: 'AWE;tn;10',
              item_type: 'obj',
              sort_order: 1,
            },
            {
              image_id: 102,
              country: 'tn',
              exhibition_id: 3,
              image_item: 'AWE;rm;66',
              item_type: 'mon',
              sort_order: 2,
            },
          ];
        }
        return [];
      });
    });

    it('maps item_type obj to sh_objects BC and creates collection_item with sort_order', async () => {
      queryMock.mockImplementation(async (sql: string) => {
        if (sql.includes('FROM mwnf3_sharing_history.sh_national_context_exhibitions')) return [];
        if (sql.includes('SELECT exhibition_id, project_id FROM mwnf3_sharing_history.sh_exhibitions')) return [];
        if (sql.includes('FROM mwnf3_sharing_history.sh_national_context_exhibition_images')) {
          return [
            {
              image_id: 101,
              country: 'tn',
              exhibition_id: 3,
              image_item: 'AWE;tn;10',
              item_type: 'obj',
              sort_order: 5,
            },
          ];
        }
        return [];
      });

      const importer = new ShNationalContextImporter(context);
      const result = await importer.import();

      expect(writeCollectionItemMock).toHaveBeenCalledWith(
        expect.objectContaining({
          collection_id: 'nc-collection-tn-uuid',
          item_id: 'obj-item-uuid',
          display_order: 5,
          extra: expect.objectContaining({
            source_image_id: 101,
            source_image_item: 'AWE;tn;10',
          }),
        })
      );
      expect(result.imported).toBe(1);
      expect(result.success).toBe(true);
    });

    it('maps item_type mon to sh_monuments BC and creates collection_item with sort_order', async () => {
      queryMock.mockImplementation(async (sql: string) => {
        if (sql.includes('FROM mwnf3_sharing_history.sh_national_context_exhibitions')) return [];
        if (sql.includes('SELECT exhibition_id, project_id FROM mwnf3_sharing_history.sh_exhibitions')) return [];
        if (sql.includes('FROM mwnf3_sharing_history.sh_national_context_exhibition_images')) {
          return [
            {
              image_id: 102,
              country: 'tn',
              exhibition_id: 3,
              image_item: 'AWE;rm;66',
              item_type: 'mon',
              sort_order: 3,
            },
          ];
        }
        return [];
      });

      const importer = new ShNationalContextImporter(context);
      const result = await importer.import();

      expect(writeCollectionItemMock).toHaveBeenCalledWith(
        expect.objectContaining({
          collection_id: 'nc-collection-tn-uuid',
          item_id: 'mon-item-uuid',
          display_order: 3,
          extra: expect.objectContaining({
            source_image_id: 102,
            source_image_item: 'AWE;rm;66',
          }),
        })
      );
      expect(result.imported).toBe(1);
      expect(result.success).toBe(true);
    });

    it('logs warning and skips row when image_item is malformed (too few parts)', async () => {
      queryMock.mockImplementation(async (sql: string) => {
        if (sql.includes('FROM mwnf3_sharing_history.sh_national_context_exhibitions')) return [];
        if (sql.includes('SELECT exhibition_id, project_id FROM mwnf3_sharing_history.sh_exhibitions')) return [];
        if (sql.includes('FROM mwnf3_sharing_history.sh_national_context_exhibition_images')) {
          return [
            {
              image_id: 200,
              country: 'tn',
              exhibition_id: 3,
              image_item: 'MALFORMED',
              item_type: 'obj',
              sort_order: 1,
            },
          ];
        }
        return [];
      });

      const importer = new ShNationalContextImporter(context);
      const result = await importer.import();

      expect(writeCollectionItemMock).not.toHaveBeenCalled();
      expect(result.skipped).toBeGreaterThanOrEqual(1);
      expect(result.warnings).toEqual(
        expect.arrayContaining([expect.stringContaining('NC image 200')])
      );
      expect(result.warnings).toEqual(
        expect.arrayContaining([expect.stringContaining('MALFORMED')])
      );
      expect(result.success).toBe(true);
    });

    it('logs warning and skips row when item_type is unknown', async () => {
      queryMock.mockImplementation(async (sql: string) => {
        if (sql.includes('FROM mwnf3_sharing_history.sh_national_context_exhibitions')) return [];
        if (sql.includes('SELECT exhibition_id, project_id FROM mwnf3_sharing_history.sh_exhibitions')) return [];
        if (sql.includes('FROM mwnf3_sharing_history.sh_national_context_exhibition_images')) {
          return [
            {
              image_id: 300,
              country: 'tn',
              exhibition_id: 3,
              image_item: 'AWE;tn;10',
              item_type: 'unknown_type',
              sort_order: 1,
            },
          ];
        }
        return [];
      });

      const importer = new ShNationalContextImporter(context);
      const result = await importer.import();

      expect(writeCollectionItemMock).not.toHaveBeenCalled();
      expect(result.skipped).toBeGreaterThanOrEqual(1);
      expect(result.warnings).toEqual(
        expect.arrayContaining([expect.stringContaining('NC image 300')])
      );
      expect(result.warnings).toEqual(
        expect.arrayContaining([expect.stringContaining('unknown_type')])
      );
      expect(result.success).toBe(true);
    });
  });
});
