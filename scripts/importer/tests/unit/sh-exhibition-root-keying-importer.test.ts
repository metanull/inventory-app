import { readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import { ShExhibitionRootKeyingImporter } from '../../src/importers/phase-11/sh-exhibition-root-keying-importer.js';

describe('ShExhibitionRootKeyingImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeCollectionMock: ReturnType<typeof vi.fn>;
  let writeCollectionTranslationMock: ReturnType<typeof vi.fn>;
  let getCollectionParentIdMock: ReturnType<typeof vi.fn>;
  let updateCollectionParentIdMock: ReturnType<typeof vi.fn>;
  let getCollectionPurposeMock: ReturnType<typeof vi.fn>;
  let updateCollectionPurposeMock: ReturnType<typeof vi.fn>;
  let findByBackwardCompatibilityMock: ReturnType<typeof vi.fn>;

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

    // Default legacy data: two AWE exhibitions and one USA exhibition
    // (project ids come uppercase from the legacy table).
    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_exhibitions')) {
        return [
          { exhibition_id: 1, project_id: 'AWE' },
          { exhibition_id: 3, project_id: 'AWE' },
          { exhibition_id: 12, project_id: 'USA' },
        ];
      }
      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    writeCollectionMock = vi.fn().mockResolvedValue('awe-root-uuid');
    writeCollectionTranslationMock = vi.fn().mockResolvedValue(undefined);
    getCollectionParentIdMock = vi.fn().mockResolvedValue('awe-project-collection-uuid');
    updateCollectionParentIdMock = vi.fn().mockResolvedValue(undefined);
    getCollectionPurposeMock = vi.fn().mockResolvedValue('exhibitions-root');
    updateCollectionPurposeMock = vi.fn().mockResolvedValue(undefined);
    findByBackwardCompatibilityMock = vi.fn(async (table: string, bc: string) => {
      if (table === 'languages' && bc === 'en') {
        return 'eng';
      }
      return null;
    });

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: findByBackwardCompatibilityMock,
      writeCollection: writeCollectionMock,
      writeCollectionTranslation: writeCollectionTranslationMock,
      getCollectionParentId: getCollectionParentIdMock,
      updateCollectionParentId: updateCollectionParentIdMock,
      getCollectionPurpose: getCollectionPurposeMock,
      updateCollectionPurpose: updateCollectionPurposeMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };

    // Pre-existing production state: AWE project collection/context and
    // its exhibition collections already imported. USA was never imported
    // (project-cleanup removed it / placeholder project).
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'awe-project-collection-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'awe-context-uuid', 'context');
    tracker.set('mwnf3_sharing_history:sh_exhibitions:1', 'exh-1-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_exhibitions:3', 'exh-3-uuid', 'collection');
  });

  it('creates a lowercase awe root collection and re-parents AWE exhibitions under it', async () => {
    const importer = new ShExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);

    expect(writeCollectionMock).toHaveBeenCalledTimes(1);
    expect(writeCollectionMock).toHaveBeenCalledWith(
      expect.objectContaining({
        internal_name: 'sh_exhibitions_root_awe',
        backward_compatibility: 'mwnf3_sharing_history:sh_exhibitions:root:awe',
        parent_id: 'awe-project-collection-uuid',
        context_id: 'awe-context-uuid',
        type: 'collection',
        purpose: 'exhibitions-root',
      })
    );

    expect(writeCollectionTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({
        collection_id: 'awe-root-uuid',
        backward_compatibility: 'mwnf3_sharing_history:sh_exhibitions:root:awe:translation:eng',
        title: 'Virtual Exhibitions',
      })
    );

    expect(updateCollectionParentIdMock).toHaveBeenCalledTimes(2);
    expect(updateCollectionParentIdMock).toHaveBeenCalledWith('exh-1-uuid', 'awe-root-uuid');
    expect(updateCollectionParentIdMock).toHaveBeenCalledWith('exh-3-uuid', 'awe-root-uuid');
  });

  it('never touches the mwnf3 keyspace', async () => {
    tracker.set('mwnf3:exhibitions:root', 'isl-root-uuid', 'collection');
    tracker.set('mwnf3:exhibitions:root:BAR', 'bar-root-uuid', 'collection');

    const importer = new ShExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    const writtenBcs = writeCollectionMock.mock.calls.map(
      (call) => (call[0] as { backward_compatibility: string }).backward_compatibility
    );
    for (const bc of writtenBcs) {
      expect(bc.startsWith('mwnf3_sharing_history:')).toBe(true);
    }
  });

  it('skips a project whose collection was never imported, with a warning (USA)', async () => {
    const importer = new ShExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('mwnf3_sharing_history:sh_projects:usa'),
      undefined
    );
    // Only the awe root was created — nothing for usa.
    expect(writeCollectionMock).toHaveBeenCalledTimes(1);
  });

  it('is a no-op on a second run (root exists with purpose, exhibitions already parented to it)', async () => {
    tracker.set('mwnf3_sharing_history:sh_exhibitions:root:awe', 'awe-root-uuid', 'collection');
    getCollectionParentIdMock.mockResolvedValue('awe-root-uuid');

    const importer = new ShExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(writeCollectionMock).not.toHaveBeenCalled();
    expect(writeCollectionTranslationMock).not.toHaveBeenCalled();
    expect(updateCollectionParentIdMock).not.toHaveBeenCalled();
    expect(updateCollectionPurposeMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(0);
  });

  it('backfills purpose on an existing unpurposed root (#1505 ensure-semantics)', async () => {
    tracker.set('mwnf3_sharing_history:sh_exhibitions:root:awe', 'awe-root-uuid', 'collection');
    getCollectionParentIdMock.mockResolvedValue('awe-root-uuid');
    getCollectionPurposeMock.mockResolvedValue(null);

    const importer = new ShExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(writeCollectionMock).not.toHaveBeenCalled();
    expect(updateCollectionPurposeMock).toHaveBeenCalledWith('awe-root-uuid', 'exhibitions-root');
    expect(result.imported).toBe(1);
  });

  it('skips exhibitions whose collection was never imported, with a warning', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_exhibitions')) {
        return [
          { exhibition_id: 1, project_id: 'AWE' },
          { exhibition_id: 99, project_id: 'AWE' }, // not in tracker/DB
        ];
      }
      return [];
    });

    const importer = new ShExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('mwnf3_sharing_history:sh_exhibitions:99'),
      undefined
    );
    expect(updateCollectionParentIdMock).toHaveBeenCalledTimes(1);
    expect(updateCollectionParentIdMock).toHaveBeenCalledWith('exh-1-uuid', 'awe-root-uuid');
  });

  it('performs no writes in dry-run mode', async () => {
    context = { ...context, dryRun: true };

    const importer = new ShExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(writeCollectionMock).not.toHaveBeenCalled();
    expect(writeCollectionTranslationMock).not.toHaveBeenCalled();
    expect(updateCollectionParentIdMock).not.toHaveBeenCalled();
  });
});

describe('importer registry order (sh keying)', () => {
  it('registers sh-exhibition-root-keying and sh-exhibition-show-flag after sh-exhibition and before project-cleanup', () => {
    const testDir = dirname(fileURLToPath(import.meta.url));
    const importCliSource = readFileSync(
      join(testDir, '..', '..', 'src', 'cli', 'import.ts'),
      'utf-8'
    );

    const keys = [...importCliSource.matchAll(/key: '([^']+)'/g)].map((m) => m[1]);

    const shExhibitionIndex = keys.indexOf('sh-exhibition');
    const shTranslationIndex = keys.indexOf('sh-exhibition-translation');
    const keyingIndex = keys.indexOf('sh-exhibition-root-keying');
    const showFlagIndex = keys.indexOf('sh-exhibition-show-flag');
    const cleanupIndex = keys.indexOf('project-cleanup');

    expect(shExhibitionIndex).toBeGreaterThanOrEqual(0);
    expect(shTranslationIndex).toBeGreaterThanOrEqual(0);
    expect(keyingIndex).toBeGreaterThan(shExhibitionIndex);
    expect(showFlagIndex).toBeGreaterThan(shTranslationIndex);
    expect(keyingIndex).toBeLessThan(cleanupIndex);
    expect(showFlagIndex).toBeLessThan(cleanupIndex);
  });
});
