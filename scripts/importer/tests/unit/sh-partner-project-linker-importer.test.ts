import { beforeEach, describe, expect, it, vi } from 'vitest';

import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import { ShPartnerProjectLinkerImporter } from '../../src/importers/phase-11/sh-partner-project-linker-importer.js';

describe('ShPartnerProjectLinkerImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let attachMock: ReturnType<typeof vi.fn>;

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

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_projects')) {
        // The WHERE clause keeps only public SP projects — AWE.
        return [{ project_id: 'AWE' }];
      }
      if (sql.includes('FROM mwnf3_sharing_history.sh_partners ')) {
        return [
          { partners_id: 'AT_01' },
          { partners_id: 'AT_01_A' },
          { partners_id: 'US_01_B' },
        ];
      }
      if (sql.includes('FROM mwnf3_sharing_history.sh_partner_associated')) {
        return [
          { partners_id: 'AT_01_A', project_id: 'AWE' },
          { partners_id: 'US_01_B', project_id: 'AWE' },
        ];
      }
      if (sql.includes('FROM mwnf3_sharing_history.sh_partner_further_associated')) {
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

    attachMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      attachPartnerToCollectionWithLevel: attachMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };

    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'awe-collection-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_partners:at_01', 'at01-uuid', 'partner');
    tracker.set('mwnf3_sharing_history:sh_partners:at_01_a', 'at01a-uuid', 'partner');
    tracker.set('mwnf3_sharing_history:sh_partners:us_01_b', 'us01b-uuid', 'partner');
  });

  it('links every partner to the AWE project collection with tier-derived levels', async () => {
    const importer = new ShPartnerProjectLinkerImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(attachMock).toHaveBeenCalledTimes(3);
    expect(attachMock).toHaveBeenCalledWith(
      'awe-collection-uuid',
      'at01-uuid',
      'project',
      'partner'
    );
    expect(attachMock).toHaveBeenCalledWith(
      'awe-collection-uuid',
      'at01a-uuid',
      'project',
      'associated_partner'
    );
    expect(attachMock).toHaveBeenCalledWith(
      'awe-collection-uuid',
      'us01b-uuid',
      'project',
      'associated_partner'
    );
    expect(result.imported).toBe(3);
  });

  it('maps further-associated partners to minor_contributor', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_projects')) return [{ project_id: 'AWE' }];
      if (sql.includes('FROM mwnf3_sharing_history.sh_partners '))
        return [{ partners_id: 'AT_01' }];
      if (sql.includes('FROM mwnf3_sharing_history.sh_partner_further_associated'))
        return [{ partners_id: 'AT_01', project_id: 'AWE' }];
      return [];
    });

    const importer = new ShPartnerProjectLinkerImporter(context);
    await importer.import();

    expect(attachMock).toHaveBeenCalledWith(
      'awe-collection-uuid',
      'at01-uuid',
      'project',
      'minor_contributor'
    );
  });

  it('filters projects to public SP ones in SQL', async () => {
    const importer = new ShPartnerProjectLinkerImporter(context);
    await importer.import();

    const projectSql = queryMock.mock.calls
      .map((c) => c[0] as string)
      .find((sql) => sql.includes('sh_projects'));
    expect(projectSql).toContain(`\`show\` = 'Y'`);
    expect(projectSql).toContain(`category = 'SP'`);
  });

  it('ignores tier rows belonging to another project', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_projects')) return [{ project_id: 'AWE' }];
      if (sql.includes('FROM mwnf3_sharing_history.sh_partners '))
        return [{ partners_id: 'AT_01' }];
      if (sql.includes('FROM mwnf3_sharing_history.sh_partner_associated'))
        return [{ partners_id: 'AT_01', project_id: 'RUS' }];
      return [];
    });

    const importer = new ShPartnerProjectLinkerImporter(context);
    await importer.import();

    expect(attachMock).toHaveBeenCalledWith(
      'awe-collection-uuid',
      'at01-uuid',
      'project',
      'partner'
    );
  });

  it('warns and skips unknown partners', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_projects')) return [{ project_id: 'AWE' }];
      if (sql.includes('FROM mwnf3_sharing_history.sh_partners '))
        return [{ partners_id: 'ZZ_99' }];
      return [];
    });

    const importer = new ShPartnerProjectLinkerImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('mwnf3_sharing_history:sh_partners:zz_99'),
      undefined
    );
    expect(attachMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
  });

  it('warns and skips when the project collection is missing', async () => {
    tracker = new UnifiedTracker();
    context = { ...context, tracker };

    const importer = new ShPartnerProjectLinkerImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('mwnf3_sharing_history:sh_projects:awe'),
      undefined
    );
    expect(attachMock).not.toHaveBeenCalled();
  });

  it('performs no writes in dry-run mode', async () => {
    context = { ...context, dryRun: true };

    const importer = new ShPartnerProjectLinkerImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(attachMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(3);
  });
});
