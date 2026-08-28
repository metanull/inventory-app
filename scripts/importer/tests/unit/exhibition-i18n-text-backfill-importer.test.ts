import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ExhibitionI18nTextBackfillImporter } from '../../src/importers/phase-11/exhibition-i18n-text-backfill-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

/**
 * The Colours rows: English is published and carries all three texts, German is
 * not published and carries none. The pair is what makes the backfill worth
 * having — an exhibition package cannot render the sub-title, the banner
 * headline and the About page from the single joined `description` the original
 * import wrote, because `about` contains the same blank-line separator the join
 * used.
 */
const COLOURS_ROWS = [
  {
    gallery_id: 47,
    language_id: 'de',
    subtitle: null,
    heading: null,
    about: null,
  },
  {
    gallery_id: 47,
    language_id: 'en',
    subtitle: 'About Techniques, Symbolism and Meanings',
    heading: '<i><b>This is the demo exhibition.</i></b>',
    about: '',
  },
];

describe('ExhibitionI18nTextBackfillImporter', () => {
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let getExtraMock: ReturnType<typeof vi.fn>;
  let setExtraMock: ReturnType<typeof vi.fn>;

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

  /** Resolves every backward_compatibility key to `<table>:<bc>`. */
  const resolveEverything = async (table: string, bc: string) => `${table}:${bc}`;

  const writtenExtra = (call = 0) =>
    JSON.parse(setExtraMock.mock.calls[call]![2] as string) as Record<string, unknown>;

  beforeEach(() => {
    vi.clearAllMocks();

    queryMock = vi.fn().mockResolvedValue([]);
    getExtraMock = vi.fn().mockResolvedValue(null);
    setExtraMock = vi.fn().mockResolvedValue(undefined);

    context = {
      legacyDb: {
        query: queryMock,
        execute: vi.fn(),
        connect: vi.fn(),
        disconnect: vi.fn(),
      } as unknown as ILegacyDatabase,
      strategy: {
        findByBackwardCompatibility: vi.fn(resolveEverything),
        getCollectionTranslationExtra: getExtraMock,
        setCollectionTranslationExtra: setExtraMock,
      } as unknown as IWriteStrategy,
      tracker: new UnifiedTracker(),
      logger,
      dryRun: false,
    };
  });

  it('writes the non-empty texts and skips a row that has none', async () => {
    queryMock.mockResolvedValue(COLOURS_ROWS);

    const importer = new ExhibitionI18nTextBackfillImporter(context);
    const result = await importer.import();

    // German contributes nothing; only the English row is written.
    expect(setExtraMock).toHaveBeenCalledTimes(1);
    expect(result.imported).toBe(1);
    expect(result.skipped).toBe(1);
    expect(result.errors).toHaveLength(0);

    const extra = writtenExtra().exhibition_i18n as Record<string, unknown>;
    expect(extra['subtitle']).toBe('About Techniques, Symbolism and Meanings');
    expect(extra['heading']).toBe('<i><b>This is the demo exhibition.</i></b>');
    // `about` is the empty string on this exhibition — an empty value is not a
    // value, and writing it would make the exporter believe the split fields
    // are present when the other two might not be.
    expect(extra).not.toHaveProperty('about');
  });

  /**
   * The block already holds `enabled`, `popup_logo` and friends from the
   * original import, and `thg_gallery` sits beside it. A backfill that replaced
   * `extra` instead of merging into it would silently delete the chrome every
   * exporter reads.
   */
  it('merges into the existing extra rather than replacing it', async () => {
    queryMock.mockResolvedValue([COLOURS_ROWS[1]]);
    getExtraMock.mockResolvedValue({
      thg_gallery: { status: 'A', has_timeline: true },
      exhibition_i18n: { enabled: 'Y', popup_logo_show: 'Y' },
    });

    await new ExhibitionI18nTextBackfillImporter(context).import();

    const extra = writtenExtra();
    expect(extra['thg_gallery']).toEqual({ status: 'A', has_timeline: true });
    expect(extra['exhibition_i18n']).toEqual({
      enabled: 'Y',
      popup_logo_show: 'Y',
      subtitle: 'About Techniques, Symbolism and Meanings',
      heading: '<i><b>This is the demo exhibition.</i></b>',
    });
  });

  /**
   * The step has to be safe to run on a database that was imported after the
   * fix, and safe to run twice — otherwise it is a hazard rather than a repair.
   */
  it('is a no-op when the texts are already there', async () => {
    queryMock.mockResolvedValue([COLOURS_ROWS[1]]);
    getExtraMock.mockResolvedValue({
      exhibition_i18n: {
        enabled: 'Y',
        subtitle: 'About Techniques, Symbolism and Meanings',
        heading: '<i><b>This is the demo exhibition.</i></b>',
      },
    });

    const result = await new ExhibitionI18nTextBackfillImporter(context).import();

    expect(setExtraMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
    expect(result.imported).toBe(0);
  });

  it('fills only the fields that are missing', async () => {
    queryMock.mockResolvedValue([COLOURS_ROWS[1]]);
    getExtraMock.mockResolvedValue({
      exhibition_i18n: { enabled: 'Y', subtitle: 'A subtitle somebody already wrote' },
    });

    await new ExhibitionI18nTextBackfillImporter(context).import();

    const extra = writtenExtra().exhibition_i18n as Record<string, unknown>;
    // The existing value wins — the backfill repairs gaps, it does not restate
    // the legacy row over whatever is there.
    expect(extra['subtitle']).toBe('A subtitle somebody already wrote');
    expect(extra['heading']).toBe('<i><b>This is the demo exhibition.</i></b>');
  });

  it('writes nothing in dry-run mode but still counts the row', async () => {
    queryMock.mockResolvedValue([COLOURS_ROWS[1]]);
    context = { ...context, dryRun: true };

    const result = await new ExhibitionI18nTextBackfillImporter(context).import();

    expect(setExtraMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(1);
  });
});
