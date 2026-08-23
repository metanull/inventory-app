import { readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import { ShHistoricalProfilesRootImporter } from '../../src/importers/phase-11/sh-historical-profiles-root-importer.js';

describe('ShHistoricalProfilesRootImporter', () => {
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
  let parentByCollection: Record<string, string>;

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

    // Legacy fixture: AWE has one profile record, USA the "Germany" record
    // (#1494), RUS was never imported as a project.
    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_countries_historicalbackground')) {
        return [
          { hb_id: 2, project_id: 'AWE' },
          { hb_id: 21, project_id: 'RUS' },
          { hb_id: 20, project_id: 'USA' },
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

    let nextId = 0;
    writeCollectionMock = vi.fn(async () => `profiles-root-uuid-${++nextId}`);
    writeCollectionTranslationMock = vi.fn().mockResolvedValue(undefined);
    getCollectionParentIdMock = vi.fn(
      async (collectionId: string) => parentByCollection[collectionId] ?? null
    );
    updateCollectionParentIdMock = vi.fn().mockResolvedValue(undefined);
    getCollectionPurposeMock = vi.fn().mockResolvedValue('historical-profiles-root');
    updateCollectionPurposeMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
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

    // Production-like state: AWE + USA project scopes and their HB record
    // collections exist (post sh-hb-recontext: each in its own project),
    // records still parented to the project roots.
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'awe-project-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'awe-context-uuid', 'context');
    tracker.set('mwnf3_sharing_history:sh_projects:usa', 'usa-project-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_projects:usa', 'usa-context-uuid', 'context');
    tracker.set('mwnf3_sharing_history:sh_countries_historicalbackground:2', 'hb2', 'collection');
    tracker.set('mwnf3_sharing_history:sh_countries_historicalbackground:20', 'hb20', 'collection');

    parentByCollection = {
      hb2: 'awe-project-uuid',
      hb20: 'usa-project-uuid',
    };
  });

  it('creates a purposed marker per project and re-parents the HB records under it', async () => {
    const importer = new ShHistoricalProfilesRootImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);

    // One marker per project with HB records and an imported project scope.
    expect(writeCollectionMock).toHaveBeenCalledTimes(2);
    expect(writeCollectionMock).toHaveBeenCalledWith(
      expect.objectContaining({
        internal_name: 'sh_historical_profiles_root_awe',
        backward_compatibility: 'mwnf3_sharing_history:sh_countries_historicalbackground:root:awe',
        parent_id: 'awe-project-uuid',
        context_id: 'awe-context-uuid',
        type: 'collection',
        purpose: 'historical-profiles-root',
      })
    );
    expect(writeCollectionMock).toHaveBeenCalledWith(
      expect.objectContaining({
        backward_compatibility: 'mwnf3_sharing_history:sh_countries_historicalbackground:root:usa',
        parent_id: 'usa-project-uuid',
        context_id: 'usa-context-uuid',
        purpose: 'historical-profiles-root',
      })
    );

    expect(writeCollectionTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({
        title: 'Historical Profiles',
        backward_compatibility:
          'mwnf3_sharing_history:sh_countries_historicalbackground:root:awe:translation:eng',
      })
    );

    // Records re-parented under their own project's marker (awe first).
    expect(updateCollectionParentIdMock).toHaveBeenCalledWith('hb2', 'profiles-root-uuid-1');
    expect(updateCollectionParentIdMock).toHaveBeenCalledWith('hb20', 'profiles-root-uuid-2');
  });

  it('skips a project whose collection was never imported, with a warning (RUS)', async () => {
    const importer = new ShHistoricalProfilesRootImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('mwnf3_sharing_history:sh_projects:rus'),
      undefined
    );
    expect(writeCollectionMock).toHaveBeenCalledTimes(2); // awe + usa only
  });

  it('is a no-op on a second run (markers exist with purpose, records already parented)', async () => {
    tracker.set(
      'mwnf3_sharing_history:sh_countries_historicalbackground:root:awe',
      'awe-marker-uuid',
      'collection'
    );
    tracker.set(
      'mwnf3_sharing_history:sh_countries_historicalbackground:root:usa',
      'usa-marker-uuid',
      'collection'
    );
    parentByCollection.hb2 = 'awe-marker-uuid';
    parentByCollection.hb20 = 'usa-marker-uuid';

    const importer = new ShHistoricalProfilesRootImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(writeCollectionMock).not.toHaveBeenCalled();
    expect(writeCollectionTranslationMock).not.toHaveBeenCalled();
    expect(updateCollectionParentIdMock).not.toHaveBeenCalled();
    expect(updateCollectionPurposeMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(0);
  });

  it('backfills purpose on an existing unpurposed marker (#1505 ensure-semantics)', async () => {
    tracker.set(
      'mwnf3_sharing_history:sh_countries_historicalbackground:root:awe',
      'awe-marker-uuid',
      'collection'
    );
    parentByCollection.hb2 = 'awe-marker-uuid';
    getCollectionPurposeMock.mockResolvedValue(null);

    const importer = new ShHistoricalProfilesRootImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(updateCollectionPurposeMock).toHaveBeenCalledWith(
      'awe-marker-uuid',
      'historical-profiles-root'
    );
  });

  it('performs no writes in dry-run mode', async () => {
    context = { ...context, dryRun: true };

    const importer = new ShHistoricalProfilesRootImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(writeCollectionMock).not.toHaveBeenCalled();
    expect(writeCollectionTranslationMock).not.toHaveBeenCalled();
    expect(updateCollectionParentIdMock).not.toHaveBeenCalled();
    expect(updateCollectionPurposeMock).not.toHaveBeenCalled();
  });
});

describe('importer registry order (sh historical profiles root)', () => {
  it('registers sh-historical-profiles-root after sh-hb-recontext, and collection-purpose-backfill after it, both before project-cleanup', () => {
    const testDir = dirname(fileURLToPath(import.meta.url));
    const importCliSource = readFileSync(
      join(testDir, '..', '..', 'src', 'cli', 'import.ts'),
      'utf-8'
    );

    const keys = [...importCliSource.matchAll(/key: '([^']+)'/g)].map((m) => m[1]);

    const recontextIndex = keys.indexOf('sh-hb-recontext');
    const profilesIndex = keys.indexOf('sh-historical-profiles-root');
    const backfillIndex = keys.indexOf('collection-purpose-backfill');
    const cleanupIndex = keys.indexOf('project-cleanup');

    expect(recontextIndex).toBeGreaterThanOrEqual(0);
    expect(profilesIndex).toBeGreaterThan(recontextIndex);
    expect(backfillIndex).toBeGreaterThan(profilesIndex);
    expect(backfillIndex).toBeLessThan(cleanupIndex);
  });
});
