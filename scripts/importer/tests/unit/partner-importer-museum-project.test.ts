import { beforeEach, describe, expect, it, vi } from 'vitest';

import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import type { PartnerData } from '../../src/core/types.js';
import { PartnerImporter } from '../../src/importers/phase-01/partner-importer.js';

/**
 * PartnerImporter carries `mwnf3.museums.project_id` onto `partners.project_id`
 * — the museum→project link legacy's MWNF-384 partner branch selects on.
 */
describe('PartnerImporter — museum project link', () => {
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writePartnerMock: ReturnType<typeof vi.fn>;

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

  const museum = (museum_id: string, country: string, project_id: string) => ({
    museum_id,
    country,
    name: `${museum_id} ${country}`,
    project_id,
  });

  /** Legacy rows served per query; overridden per test. */
  let museums: ReturnType<typeof museum>[];

  const writtenProjectIds = () =>
    writePartnerMock.mock.calls.map((call) => (call[0] as PartnerData).project_id ?? null);

  beforeEach(() => {
    vi.clearAllMocks();
    museums = [];

    const tracker = new UnifiedTracker();
    tracker.setMetadata('default_context_id', 'context-uuid');
    tracker.set('mwnf3:projects:DCA', 'project-dca-uuid', 'project');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3.museums')) return museums;
      return [];
    });

    writePartnerMock = vi.fn(async () => 'partner-uuid');

    context = {
      legacyDb: {
        query: queryMock,
        execute: vi.fn(),
        connect: vi.fn(),
        disconnect: vi.fn(),
      } as unknown as ILegacyDatabase,
      strategy: {
        exists: vi.fn().mockResolvedValue(false),
        findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
        writePartner: writePartnerMock,
        writePartnerTranslation: vi.fn().mockResolvedValue(undefined),
      } as unknown as IWriteStrategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('writes the creating project onto the partner', async () => {
    museums = [museum('Mus31', 'jo', 'DCA'), museum('Mus31', 'pt', 'DCA')];

    const result = await new PartnerImporter(context).import();

    expect(writtenProjectIds()).toEqual(['project-dca-uuid', 'project-dca-uuid']);
    expect(result.success).toBe(true);
  });

  it("leaves project_id null for the empty legacy code (NOT NULL DEFAULT '')", async () => {
    museums = [museum('Mus01', 'uk', ''), museum('Mus02', 'uk', '   ')];

    await new PartnerImporter(context).import();

    expect(writtenProjectIds()).toEqual([null, null]);
  });

  it('warns and leaves project_id null when the project was never imported', async () => {
    museums = [museum('Mus31', 'jo', 'GONE')];

    const result = await new PartnerImporter(context).import();

    expect(writtenProjectIds()).toEqual([null]);
    expect(result.warnings.length).toBe(0); // a missing project is logged, not a row warning
    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('project GONE not found'),
      undefined
    );
    expect(result.success).toBe(true);
  });
});
