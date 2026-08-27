import { beforeEach, describe, expect, it, vi } from 'vitest';

import { MuseumProjectLinkBackfillImporter } from '../../src/importers/phase-11/museum-project-link-backfill-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

/**
 * The two legacy museums that make MWNF-384 visible on carpets: created under
 * DCA, holding nothing, listed by legacy anyway.
 */
const CARPETS_ORPHANS = [
  { museum_id: 'Mus31', country: 'jo', project_id: 'DCA' },
  { museum_id: 'Mus31', country: 'pt', project_id: 'DCA' },
];

describe('MuseumProjectLinkBackfillImporter', () => {
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let findMock: ReturnType<typeof vi.fn>;
  let setMock: ReturnType<typeof vi.fn>;

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

  /** Resolves every mwnf3 backward_compatibility key to `<table>-<pk...>`. */
  const resolveEverything = async (table: string, bc: string) => `${table}:${bc}`;

  beforeEach(() => {
    vi.clearAllMocks();

    queryMock = vi.fn().mockResolvedValue([]);
    findMock = vi.fn(resolveEverything);
    setMock = vi.fn().mockResolvedValue(1);

    context = {
      legacyDb: {
        query: queryMock,
        execute: vi.fn(),
        connect: vi.fn(),
        disconnect: vi.fn(),
      } as unknown as ILegacyDatabase,
      strategy: {
        findByBackwardCompatibility: findMock,
        setPartnerProjectIdIfUnset: setMock,
      } as unknown as IWriteStrategy,
      tracker: new UnifiedTracker(),
      logger,
      dryRun: false,
    };
  });

  it('links the two DCA museums that hold nothing — the MWNF-384 rows', async () => {
    queryMock.mockResolvedValue(CARPETS_ORPHANS);

    const importer = new MuseumProjectLinkBackfillImporter(context);
    const result = await importer.import();

    expect(setMock).toHaveBeenCalledTimes(2);
    expect(setMock).toHaveBeenNthCalledWith(
      1,
      'partners:mwnf3:museums:Mus31:jo',
      'projects:mwnf3:projects:DCA'
    );
    expect(setMock).toHaveBeenNthCalledWith(
      2,
      'partners:mwnf3:museums:Mus31:pt',
      'projects:mwnf3:projects:DCA'
    );
    expect(result.imported).toBe(2);
    expect(result.success).toBe(true);
  });

  it('resolves each legacy project code once, however many museums share it', async () => {
    queryMock.mockResolvedValue([
      ...CARPETS_ORPHANS,
      { museum_id: 'Mus01', country: 'uk', project_id: 'DCA' },
      { museum_id: 'Mus02', country: 'es', project_id: 'ISL' },
    ]);

    const importer = new MuseumProjectLinkBackfillImporter(context);
    await importer.import();

    const projectLookups = findMock.mock.calls.filter(([table]) => table === 'projects');
    expect(projectLookups).toHaveLength(2);
  });

  it("skips the empty project code — legacy's museums.project_id is NOT NULL DEFAULT ''", async () => {
    queryMock.mockResolvedValue([
      { museum_id: 'Mus01', country: 'uk', project_id: '' },
      { museum_id: 'Mus02', country: 'uk', project_id: '   ' },
    ]);

    const importer = new MuseumProjectLinkBackfillImporter(context);
    const result = await importer.import();

    expect(findMock).not.toHaveBeenCalled();
    expect(setMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(2);
    expect(result.success).toBe(true);
  });

  it('warns and skips when the project was never imported, without failing the run', async () => {
    queryMock.mockResolvedValue([{ museum_id: 'Mus31', country: 'jo', project_id: 'GONE' }]);
    findMock.mockImplementation(async (table: string, bc: string) =>
      table === 'projects' ? null : `${table}:${bc}`
    );

    const importer = new MuseumProjectLinkBackfillImporter(context);
    const result = await importer.import();

    expect(setMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
    expect(result.errors).toHaveLength(0);
    expect(result.warnings).toHaveLength(1);
    expect(result.warnings[0]).toContain('GONE');
    expect(result.success).toBe(true);
  });

  it('warns and skips when the partner is missing — PartnerImporter has not run', async () => {
    queryMock.mockResolvedValue([{ museum_id: 'Mus31', country: 'jo', project_id: 'DCA' }]);
    findMock.mockImplementation(async (table: string, bc: string) =>
      table === 'partners' ? null : `${table}:${bc}`
    );

    const importer = new MuseumProjectLinkBackfillImporter(context);
    const result = await importer.import();

    expect(setMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
    expect(result.warnings[0]).toContain('mwnf3:museums:Mus31:jo');
    expect(result.success).toBe(true);
  });

  it('counts an already-linked partner as skipped — the update is where-null', async () => {
    queryMock.mockResolvedValue(CARPETS_ORPHANS);
    setMock.mockResolvedValue(0);

    const importer = new MuseumProjectLinkBackfillImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(0);
    expect(result.skipped).toBe(2);
    expect(result.success).toBe(true);
  });

  it('writes nothing in dry-run mode but still reports what it would change', async () => {
    queryMock.mockResolvedValue(CARPETS_ORPHANS);

    const importer = new MuseumProjectLinkBackfillImporter({ ...context, dryRun: true });
    const result = await importer.import();

    expect(setMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(2);
    expect(result.success).toBe(true);
  });

  it('reports a per-row failure without aborting the remaining rows', async () => {
    queryMock.mockResolvedValue(CARPETS_ORPHANS);
    setMock.mockImplementation(async (partnerId: string) => {
      if (partnerId.endsWith(':jo')) throw new Error('write failed');
      return 1;
    });

    const importer = new MuseumProjectLinkBackfillImporter(context);
    const result = await importer.import();

    expect(setMock).toHaveBeenCalledTimes(2);
    expect(result.imported).toBe(1);
    expect(result.errors).toHaveLength(1);
    expect(result.errors[0]).toContain('Mus31:jo');
    expect(result.success).toBe(false);
  });
});
