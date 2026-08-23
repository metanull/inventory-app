import { readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import { ProjectExhibitionRootKeyingImporter } from '../../src/importers/phase-11/project-exhibition-root-keying-importer.js';

describe('ProjectExhibitionRootKeyingImporter', () => {
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

    // Default legacy data: one ISL exhibition (must be ignored) and two BAR ones.
    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3.exhibitions')) {
        return [
          { exhibition_id: 1, project_id: 'ISL' },
          { exhibition_id: 10, project_id: 'BAR' },
          { exhibition_id: 11, project_id: 'BAR' },
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

    writeCollectionMock = vi.fn().mockResolvedValue('bar-root-uuid');
    writeCollectionTranslationMock = vi.fn().mockResolvedValue(undefined);
    getCollectionParentIdMock = vi.fn().mockResolvedValue('bar-project-collection-uuid');
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

    // Pre-existing production state: BAR project collection/context and
    // exhibition collections already imported.
    tracker.set('mwnf3:projects:BAR', 'bar-project-collection-uuid', 'collection');
    tracker.set('mwnf3:projects:BAR', 'bar-context-uuid', 'context');
    tracker.set('mwnf3:exhibitions:10', 'exh-10-uuid', 'collection');
    tracker.set('mwnf3:exhibitions:11', 'exh-11-uuid', 'collection');
  });

  it('creates a BAR root collection and re-parents BAR exhibitions under it', async () => {
    const importer = new ProjectExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);

    expect(writeCollectionMock).toHaveBeenCalledTimes(1);
    expect(writeCollectionMock).toHaveBeenCalledWith(
      expect.objectContaining({
        internal_name: 'exhibitions_root_bar',
        backward_compatibility: 'mwnf3:exhibitions:root:BAR',
        parent_id: 'bar-project-collection-uuid',
        context_id: 'bar-context-uuid',
        type: 'collection',
        purpose: 'exhibitions-root',
      })
    );

    expect(writeCollectionTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({
        collection_id: 'bar-root-uuid',
        backward_compatibility: 'mwnf3:exhibitions:root:BAR:translation:eng',
        title: 'Virtual Exhibitions',
      })
    );

    expect(updateCollectionParentIdMock).toHaveBeenCalledTimes(2);
    expect(updateCollectionParentIdMock).toHaveBeenCalledWith('exh-10-uuid', 'bar-root-uuid');
    expect(updateCollectionParentIdMock).toHaveBeenCalledWith('exh-11-uuid', 'bar-root-uuid');

    // root + 2 re-parented exhibitions
    expect(result.imported).toBe(3);
  });

  it('never touches ISL: no ISL root key is created and ISL exhibitions are ignored', async () => {
    tracker.set('mwnf3:exhibitions:root', 'isl-root-uuid', 'collection');
    tracker.set('mwnf3:exhibitions:1', 'exh-1-isl-uuid', 'collection');

    const importer = new ProjectExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    const writtenBcs = writeCollectionMock.mock.calls.map(
      (call) => (call[0] as { backward_compatibility: string }).backward_compatibility
    );
    expect(writtenBcs).not.toContain('mwnf3:exhibitions:root:ISL');
    expect(updateCollectionParentIdMock).not.toHaveBeenCalledWith(
      'exh-1-isl-uuid',
      expect.anything()
    );
  });

  it('is a no-op on a second run (root exists with purpose, exhibitions already parented to it)', async () => {
    tracker.set('mwnf3:exhibitions:root:BAR', 'bar-root-uuid', 'collection');
    getCollectionParentIdMock.mockResolvedValue('bar-root-uuid');

    const importer = new ProjectExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(writeCollectionMock).not.toHaveBeenCalled();
    expect(writeCollectionTranslationMock).not.toHaveBeenCalled();
    expect(updateCollectionParentIdMock).not.toHaveBeenCalled();
    expect(updateCollectionPurposeMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(0);
    // root existed (1) + two already-parented exhibitions (2)
    expect(result.skipped).toBe(3);
  });

  it('backfills purpose on an existing unpurposed root (#1505 ensure-semantics)', async () => {
    tracker.set('mwnf3:exhibitions:root:BAR', 'bar-root-uuid', 'collection');
    getCollectionParentIdMock.mockResolvedValue('bar-root-uuid');
    getCollectionPurposeMock.mockResolvedValue(null);

    const importer = new ProjectExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(writeCollectionMock).not.toHaveBeenCalled();
    expect(updateCollectionPurposeMock).toHaveBeenCalledWith('bar-root-uuid', 'exhibitions-root');
    expect(result.imported).toBe(1);
  });

  it('skips a project whose collection was never imported, with a warning', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.exhibitions')) {
        return [{ exhibition_id: 99, project_id: 'XYZ' }];
      }
      return [];
    });

    const importer = new ProjectExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('mwnf3:projects:XYZ'),
      undefined
    );
    expect(writeCollectionMock).not.toHaveBeenCalled();
    expect(updateCollectionParentIdMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
  });

  it('skips exhibitions whose collection was never imported, with a warning', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.exhibitions')) {
        return [
          { exhibition_id: 10, project_id: 'BAR' },
          { exhibition_id: 12, project_id: 'BAR' }, // not in tracker/DB
        ];
      }
      return [];
    });

    const importer = new ProjectExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('mwnf3:exhibitions:12'),
      undefined
    );
    expect(updateCollectionParentIdMock).toHaveBeenCalledTimes(1);
    expect(updateCollectionParentIdMock).toHaveBeenCalledWith('exh-10-uuid', 'bar-root-uuid');
    expect(result.skipped).toBe(1);
  });

  it('performs no writes in dry-run mode', async () => {
    context = { ...context, dryRun: true };

    const importer = new ProjectExhibitionRootKeyingImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(writeCollectionMock).not.toHaveBeenCalled();
    expect(writeCollectionTranslationMock).not.toHaveBeenCalled();
    expect(updateCollectionParentIdMock).not.toHaveBeenCalled();
    // would-create root + would-re-parent 2 exhibitions
    expect(result.imported).toBe(3);
  });
});

describe('importer registry order', () => {
  it('registers project-exhibition-root-keying after mwnf3-exhibition and before project-cleanup', () => {
    const testDir = dirname(fileURLToPath(import.meta.url));
    const importCliSource = readFileSync(
      join(testDir, '..', '..', 'src', 'cli', 'import.ts'),
      'utf-8'
    );

    const keys = [...importCliSource.matchAll(/key: '([^']+)'/g)].map((m) => m[1]);

    const exhibitionIndex = keys.indexOf('mwnf3-exhibition');
    const keyingIndex = keys.indexOf('project-exhibition-root-keying');
    const cleanupIndex = keys.indexOf('project-cleanup');

    expect(exhibitionIndex).toBeGreaterThanOrEqual(0);
    expect(keyingIndex).toBeGreaterThanOrEqual(0);
    expect(cleanupIndex).toBeGreaterThanOrEqual(0);
    expect(keyingIndex).toBeGreaterThan(exhibitionIndex);
    expect(keyingIndex).toBeLessThan(cleanupIndex);
  });
});
