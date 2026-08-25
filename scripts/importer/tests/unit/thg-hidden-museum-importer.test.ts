/**
 * Unit tests for ThgHiddenMuseumImporter (gap E6).
 *
 * exhibition_hidden_mwnf3_museums lists museums the curators suppressed from an
 * exhibition's partner pages. Partner lists are derived from membership at
 * export time, so without these exclusions a rebuilt site would show museums
 * that legacy deliberately hides.
 */

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ThgHiddenMuseumImporter } from '../../src/importers/phase-10/thg-hidden-museum-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ThgHiddenMuseumImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let getCollectionExtraMock: ReturnType<typeof vi.fn>;
  let setCollectionExtraMock: ReturnType<typeof vi.fn>;

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
    tracker.set('mwnf3_thematic_gallery:thg_gallery:54', 'collection-54', 'collection');
    tracker.set('mwnf3_thematic_gallery:thg_gallery:56', 'collection-56', 'collection');
    tracker.set('mwnf3:museums:Mus52:uk', 'partner-mus52-uk', 'partner');
    tracker.set('mwnf3:museums:Mus21:us', 'partner-mus21-us', 'partner');
    tracker.set('mwnf3:museums:Mus81:dz', 'partner-mus81-dz', 'partner');

    queryMock = vi.fn(async () => [
      { gallery_id: 54, museum_id: 'Mus52', country_id: 'uk' },
      { gallery_id: 54, museum_id: 'Mus21', country_id: 'us' },
      { gallery_id: 56, museum_id: 'Mus81', country_id: 'dz' },
    ]);

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    getCollectionExtraMock = vi.fn().mockResolvedValue(null);
    setCollectionExtraMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      getCollectionExtra: getCollectionExtraMock,
      setCollectionExtra: setCollectionExtraMock,
    } as unknown as IWriteStrategy;

    context = { legacyDb, strategy, tracker, logger, dryRun: false };
  });

  it('stores one exclusion list per exhibition, resolved to partners', async () => {
    const importer = new ThgHiddenMuseumImporter(context);
    const result = await importer.import();

    expect(result.imported).toBe(3);
    expect(setCollectionExtraMock).toHaveBeenCalledTimes(2);

    const written = Object.fromEntries(
      setCollectionExtraMock.mock.calls.map((call: unknown[]) => [
        call[0] as string,
        JSON.parse(call[1] as string) as Record<string, Record<string, unknown>>,
      ])
    );

    expect(written['collection-54'].thg_gallery.hidden_partners).toEqual([
      { backward_compatibility: 'mwnf3:museums:Mus52:uk', partner_id: 'partner-mus52-uk' },
      { backward_compatibility: 'mwnf3:museums:Mus21:us', partner_id: 'partner-mus21-us' },
    ]);
    expect(written['collection-56'].thg_gallery.hidden_partners).toEqual([
      { backward_compatibility: 'mwnf3:museums:Mus81:dz', partner_id: 'partner-mus81-dz' },
    ]);
  });

  it('preserves the gallery anchor already stored in extra', async () => {
    getCollectionExtraMock.mockResolvedValue({
      thg_gallery: { mwnf3_project_id: 'EXAID', slug: 'arts_in_dialogue' },
    });

    const importer = new ThgHiddenMuseumImporter(context);
    await importer.import();

    const extra = JSON.parse(setCollectionExtraMock.mock.calls[0][1] as string) as Record<
      string,
      Record<string, unknown>
    >;

    expect(extra.thg_gallery.slug).toBe('arts_in_dialogue');
    expect(extra.thg_gallery.hidden_partners).toBeDefined();
  });

  it('keeps an exclusion whose museum never resolved to a partner', async () => {
    queryMock.mockResolvedValue([{ gallery_id: 54, museum_id: 'Mus99', country_id: 'zz' }]);

    const importer = new ThgHiddenMuseumImporter(context);
    const result = await importer.import();

    const extra = JSON.parse(setCollectionExtraMock.mock.calls[0][1] as string) as Record<
      string,
      Record<string, unknown>
    >;

    expect(extra.thg_gallery.hidden_partners).toEqual([
      { backward_compatibility: 'mwnf3:museums:Mus99:zz', partner_id: null },
    ]);
    expect(result.warnings).toEqual([expect.stringContaining('mwnf3:museums:Mus99:zz')]);
  });

  it('warns and skips a gallery whose collection is missing', async () => {
    tracker = new UnifiedTracker();
    context = { ...context, tracker };

    const importer = new ThgHiddenMuseumImporter(context);
    const result = await importer.import();

    expect(setCollectionExtraMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(3);
  });

  it('reads only the mwnf3 hidden-museum table', async () => {
    const importer = new ThgHiddenMuseumImporter(context);
    await importer.import();

    const sql = queryMock.mock.calls[0][0] as string;
    expect(sql).toContain('exhibition_hidden_mwnf3_museums');
    expect(sql).not.toContain('exhibition_hidden_sh_museums');
  });

  it('writes nothing in dry-run mode', async () => {
    context.dryRun = true;

    const importer = new ThgHiddenMuseumImporter(context);
    await importer.import();

    expect(setCollectionExtraMock).not.toHaveBeenCalled();
  });
});
