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

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeCollection: writeCollectionMock,
      writeCollectionTranslation: vi.fn().mockResolvedValue(undefined),
      writeCollectionItem: vi.fn().mockResolvedValue(undefined),
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
});
