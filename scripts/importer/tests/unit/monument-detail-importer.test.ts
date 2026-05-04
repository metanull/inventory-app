import { beforeEach, describe, expect, it, vi } from 'vitest';

import { MonumentDetailImporter } from '../../src/importers/phase-01/monument-detail-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('MonumentDetailImporter', () => {
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

  // A minimal monument detail row (one language version)
  const detailRow = {
    project_id: 'BAR',
    country_id: 'cz',
    institution_id: 'Ins01',
    monument_id: 'Mon11',
    detail_id: '23',
    lang_id: 'en',
    name: 'Detail Name',
    description: 'Detail description text',
    location: null,
    date: null,
    artist: null,
  };

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.setMetadata('default_context_id', 'default-context-uuid');

    // Required dependencies for a monument detail import
    tracker.set('mwnf3:projects:BAR', 'context-uuid', 'context');
    tracker.set('mwnf3:projects:BAR', 'collection-uuid', 'collection');
    tracker.set('mwnf3:institutions:Ins01:cz', 'partner-uuid', 'partner');
    tracker.set('mwnf3:projects:BAR', 'project-uuid', 'project');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3.monument_details')) {
        return [detailRow];
      }
      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    writeItemMock = vi.fn().mockResolvedValue('new-item-uuid');
    writeItemTranslationMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeItem: writeItemMock,
      writeItemTranslation: writeItemTranslationMock,
      writeArtist: vi.fn().mockResolvedValue('artist-uuid'),
      attachArtistsToItem: vi.fn().mockResolvedValue(undefined),
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('imports detail with parent_id set when parent monument is found in tracker', async () => {
    tracker.set('mwnf3:monuments:BAR:cz:Ins01:Mon11', 'parent-item-uuid', 'item');

    const importer = new MonumentDetailImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
    expect(result.errors).toHaveLength(0);

    expect(writeItemMock).toHaveBeenCalledWith(
      expect.objectContaining({
        type: 'detail',
        parent_id: 'parent-item-uuid',
        collection_id: 'collection-uuid',
        partner_id: 'partner-uuid',
        project_id: 'project-uuid',
        backward_compatibility: 'mwnf3:monument_details:BAR:cz:Ins01:Mon11:23',
      })
    );

    expect(logger.warning).not.toHaveBeenCalledWith(
      expect.stringContaining('Parent monument not found')
    );
  });

  it('imports detail with parent_id=null and emits a warning when parent monument is missing', async () => {
    // Parent monument NOT added to tracker — simulates missing parent

    const importer = new MonumentDetailImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
    expect(result.errors).toHaveLength(0);

    expect(writeItemMock).toHaveBeenCalledWith(
      expect.objectContaining({
        type: 'detail',
        parent_id: null,
        collection_id: 'collection-uuid',
        partner_id: 'partner-uuid',
        backward_compatibility: 'mwnf3:monument_details:BAR:cz:Ins01:Mon11:23',
      })
    );

    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('Parent monument not found: mwnf3:monuments:BAR:cz:Ins01:Mon11'),
      undefined
    );
  });

  it('still fails fast when project context is missing', async () => {
    // Remove context entry so contextId lookup fails
    tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.setMetadata('default_context_id', 'default-context-uuid');
    tracker.set('mwnf3:institutions:Ins01:cz', 'partner-uuid', 'partner');
    // context and collection NOT set for the project

    context = { ...context, tracker };

    const importer = new MonumentDetailImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(false);
    expect(result.errors).toHaveLength(1);
    expect(result.errors[0]).toContain('mwnf3:monument_details:BAR:cz:Ins01:Mon11:23');
    expect(writeItemMock).not.toHaveBeenCalled();
  });
});
