import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ShMonumentDetailImporter } from '../../src/importers/phase-03/sh-monument-detail-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ShMonumentDetailImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeItemMock: ReturnType<typeof vi.fn>;
  let writeItemTranslationMock: ReturnType<typeof vi.fn>;

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

  // A minimal SH monument detail row (one language version)
  // Country code must be lowercase to match COUNTRY_CODE_MAP expectations
  const detailRow = {
    project_id: 'AWE',
    country: 'cz',
    number: 1,
    detail_id: 5,
  };

  const detailTextRow = {
    project_id: 'AWE',
    country: 'cz',
    number: 1,
    detail_id: 5,
    lang: 'en',
    name: 'SH Detail Name',
    description: 'SH detail description text',
    location: '',
    date: '',
    artist: '',
  };

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.setMetadata('default_context_id', 'default-context-uuid');

    // Required SH project dependencies (all keys normalised to lowercase by formatShBackwardCompatibility)
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'sh-context-uuid', 'context');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'sh-collection-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'sh-project-uuid', 'project');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_monument_details')) {
        return [detailRow];
      }
      if (sql.includes('FROM mwnf3_sharing_history.sh_monument_detail_texts')) {
        return [detailTextRow];
      }
      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    writeItemMock = vi.fn().mockResolvedValue('new-sh-item-uuid');
    writeItemTranslationMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeItem: writeItemMock,
      writeItemTranslation: writeItemTranslationMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('imports SH detail with parent_id set when parent monument is found in tracker', async () => {
    // formatShBackwardCompatibility('sh_monuments', 'AWE', 'CZ', 1) → 'mwnf3_sharing_history:sh_monuments:awe:cz:1'
    tracker.set('mwnf3_sharing_history:sh_monuments:awe:cz:1', 'sh-parent-item-uuid', 'item');

    const importer = new ShMonumentDetailImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
    expect(result.errors).toHaveLength(0);

    expect(writeItemMock).toHaveBeenCalledWith(
      expect.objectContaining({
        type: 'detail',
        parent_id: 'sh-parent-item-uuid',
        collection_id: 'sh-collection-uuid',
        backward_compatibility: 'mwnf3_sharing_history:sh_monument_details:awe:cz:1:5',
      })
    );

    expect(logger.warning).not.toHaveBeenCalledWith(
      expect.stringContaining('Parent monument not found')
    );
  });

  it('imports SH detail with parent_id=null and emits a warning when parent monument is missing', async () => {
    // Parent monument NOT added to tracker — simulates missing parent

    const importer = new ShMonumentDetailImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
    expect(result.errors).toHaveLength(0);

    expect(writeItemMock).toHaveBeenCalledWith(
      expect.objectContaining({
        type: 'detail',
        parent_id: null,
        collection_id: 'sh-collection-uuid',
        backward_compatibility: 'mwnf3_sharing_history:sh_monument_details:awe:cz:1:5',
      })
    );

    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining(
        'Parent monument not found: mwnf3_sharing_history:sh_monuments:awe:cz:1'
      ),
      undefined
    );
  });

  it('still fails fast when SH collection is missing', async () => {
    // Remove collection entry so collectionId lookup fails
    tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.setMetadata('default_context_id', 'default-context-uuid');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'sh-context-uuid', 'context');
    // collection NOT set

    context = { ...context, tracker };

    const importer = new ShMonumentDetailImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(false);
    expect(result.errors).toHaveLength(1);
    expect(result.errors[0]).toContain('mwnf3_sharing_history:sh_monument_details:awe:cz:1:5');
    expect(writeItemMock).not.toHaveBeenCalled();
  });
});
