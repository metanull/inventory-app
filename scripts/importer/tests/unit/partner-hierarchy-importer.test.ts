import { beforeEach, describe, expect, it, vi } from 'vitest';

import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import { PartnerHierarchyImporter } from '../../src/importers/phase-01/partner-hierarchy-importer.js';

describe('PartnerHierarchyImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let attachPartnerToCollectionWithLevelMock: ReturnType<typeof vi.fn>;
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
    tracker.set('mwnf3:museums:Mus01:jo', 'partner-uuid', 'partner');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3.associated_museums')) {
        return [];
      }

      if (sql.includes('FROM mwnf3.partner_museums')) {
        return [];
      }

      if (sql.includes('FROM mwnf3.further_associated_museums')) {
        return [];
      }

      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    attachPartnerToCollectionWithLevelMock = vi.fn().mockResolvedValue(undefined);
    findByBackwardCompatibilityMock = vi.fn().mockResolvedValue(null);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: findByBackwardCompatibilityMock,
      attachPartnerToCollectionWithLevel: attachPartnerToCollectionWithLevelMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('assigns missing associated_museums project_id to the legacy ISL project', async () => {
    tracker.set('mwnf3:projects:ISL', 'isl-collection-uuid', 'collection');
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.associated_museums')) {
        return [
          {
            associated_id: 3,
            partner_id: null,
            project_id: null,
            museum_id: 'Mus01',
            country_id: 'jo',
          },
        ];
      }

      return [];
    });

    const importer = new PartnerHierarchyImporter(context);
    const result = await importer.import();

    expect(logger.warning).toHaveBeenCalledWith(
      'associated_museums id=3 has no project_id, assigning default legacy project ISL',
      undefined
    );
    expect(attachPartnerToCollectionWithLevelMock).toHaveBeenCalledWith(
      'isl-collection-uuid',
      'partner-uuid',
      'project',
      'associated_partner'
    );
    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
    expect(result.skipped).toBe(0);
    expect(result.warnings).toEqual([
      'associated_museums id=3 has no project_id, assigning default legacy project ISL',
    ]);
  });

  it('keeps explicit associated_museums project_id values unchanged', async () => {
    tracker.set('mwnf3:projects:ABC', 'abc-collection-uuid', 'collection');
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.associated_museums')) {
        return [
          {
            associated_id: 4,
            partner_id: 12,
            project_id: 'ABC',
            museum_id: 'Mus01',
            country_id: 'jo',
          },
        ];
      }

      return [];
    });

    const importer = new PartnerHierarchyImporter(context);
    const result = await importer.import();

    expect(logger.warning).not.toHaveBeenCalledWith(
      'associated_museums id=4 has no project_id, assigning default legacy project ISL',
      undefined
    );
    expect(attachPartnerToCollectionWithLevelMock).toHaveBeenCalledWith(
      'abc-collection-uuid',
      'partner-uuid',
      'project',
      'associated_partner'
    );
    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
    expect(result.skipped).toBe(0);
    expect(result.warnings).toEqual([]);
  });

  it('keeps the failure explicit when the default ISL project cannot be resolved', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.associated_museums')) {
        return [
          {
            associated_id: 5,
            partner_id: null,
            project_id: null,
            museum_id: 'Mus01',
            country_id: 'jo',
          },
        ];
      }

      return [];
    });

    const importer = new PartnerHierarchyImporter(context);
    const result = await importer.import();

    expect(logger.warning).toHaveBeenNthCalledWith(
      1,
      'associated_museums id=5 has no project_id, assigning default legacy project ISL',
      undefined
    );
    expect(logger.warning).toHaveBeenNthCalledWith(
      2,
      'Collection not found for project ISL, skipping partner hierarchy entry',
      undefined
    );
    expect(attachPartnerToCollectionWithLevelMock).not.toHaveBeenCalled();
    expect(result.success).toBe(true);
    expect(result.imported).toBe(0);
    expect(result.skipped).toBe(1);
    expect(result.warnings).toEqual([
      'associated_museums id=5 has no project_id, assigning default legacy project ISL',
    ]);
  });
});