import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ExhibitionLogoExtraBackfillImporter } from '../../src/importers/phase-11/exhibition-logo-extra-backfill-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

/**
 * Gallery 47's sole sponsor logo. The base row carries the short internal
 * label "UNAOC"; the i18n row carries the display string the live API serves.
 */
const LOGO_ROW = {
  logo_id: 3,
  gallery_id: 47,
  category_id: 2,
  logo: 'thematic_gallery/thg_galleries/47/logos/unaoc.png',
  label: 'UNAOC',
  alt: null,
  link: 'https://www.unaoc.org/',
  visible: 'Y',
  further_reading: null,
};

const I18N_ROW = {
  logo_id: 3,
  language_id: 'en',
  label: 'United Nations Alliance of Civilizations',
  alt: null,
  further_reading: null,
};

const CATEGORY_ROWS = [
  { category_id: 0, name: 'Header', description: null },
  { category_id: 2, name: 'Footer 2', description: null },
];

/** The payload a fresh import writes — the backfill must reproduce it exactly. */
const EXPECTED_EXTRA = {
  link: 'https://www.unaoc.org/',
  category_id: 2,
  category_name: 'Footer 2',
  visible: true,
  labels: { eng: 'United Nations Alliance of Civilizations' },
};

describe('ExhibitionLogoExtraBackfillImporter', () => {
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let imageExistsMock: ReturnType<typeof vi.fn>;
  let getExtraMock: ReturnType<typeof vi.fn>;
  let setExtraMock: ReturnType<typeof vi.fn>;
  let attachTagsMock: ReturnType<typeof vi.fn>;

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

  const writtenExtra = (call = 0) =>
    JSON.parse(setExtraMock.mock.calls[call]![1] as string) as Record<string, unknown>;

  beforeEach(() => {
    vi.clearAllMocks();

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_thematic_gallery.exhibition_logo_i18n')) return [I18N_ROW];
      if (sql.includes('FROM mwnf3_thematic_gallery.exhibition_logo_category')) {
        return CATEGORY_ROWS;
      }
      if (sql.includes('FROM mwnf3_thematic_gallery.exhibition_logo')) return [LOGO_ROW];
      return [];
    });

    imageExistsMock = vi.fn().mockResolvedValue('collection-image-uuid');
    getExtraMock = vi.fn().mockResolvedValue(null);
    setExtraMock = vi.fn().mockResolvedValue(undefined);
    attachTagsMock = vi.fn().mockResolvedValue(undefined);

    const tracker = new UnifiedTracker();
    tracker.set('en', 'eng', 'language');
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('mwnf3_thematic_gallery:thg_gallery:47', 'collection-uuid-47', 'collection');

    context = {
      legacyDb: {
        query: queryMock,
        execute: vi.fn(),
        connect: vi.fn(),
        disconnect: vi.fn(),
      } as unknown as ILegacyDatabase,
      strategy: {
        findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
        writeTag: vi.fn().mockResolvedValue('logo-tag-uuid'),
        imageExists: imageExistsMock,
        getCollectionImageExtra: getExtraMock,
        setCollectionImageExtra: setExtraMock,
        attachTagsToCollectionImage: attachTagsMock,
      } as unknown as IWriteStrategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('writes the payload a fresh import would have written, and tags the image', async () => {
    const result = await new ExhibitionLogoExtraBackfillImporter(context).import();

    // Identity is (collection_id, legacy path) — collection_images has no
    // backward_compatibility column.
    expect(imageExistsMock).toHaveBeenCalledWith(
      'collection_images',
      'collection-uuid-47',
      LOGO_ROW.logo
    );
    expect(writtenExtra()).toEqual(EXPECTED_EXTRA);
    expect(attachTagsMock).toHaveBeenCalledWith('collection-image-uuid', ['logo-tag-uuid']);
    expect(result.imported).toBe(1);
    expect(result.errors).toHaveLength(0);
  });

  /**
   * Whatever else is on the row stays; the backfill repairs gaps rather than
   * restating the legacy row over what is already there.
   */
  it('merges into an existing extra and never overwrites a set key', async () => {
    getExtraMock.mockResolvedValue({
      link: 'https://somebody-already-fixed-this.example',
      curated: { note: 'hand-written' },
    });

    await new ExhibitionLogoExtraBackfillImporter(context).import();

    const extra = writtenExtra();
    expect(extra['link']).toBe('https://somebody-already-fixed-this.example');
    expect(extra['curated']).toEqual({ note: 'hand-written' });
    expect(extra['category_name']).toBe('Footer 2');
    expect(extra['labels']).toEqual({ eng: 'United Nations Alliance of Civilizations' });
  });

  /** Safe on a fresh import, and safe to run twice. */
  it('is a no-op when the payload is already there', async () => {
    getExtraMock.mockResolvedValue({ ...EXPECTED_EXTRA });

    const result = await new ExhibitionLogoExtraBackfillImporter(context).import();

    expect(setExtraMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(0);
    expect(result.skipped).toBe(1);
    expect(result.errors).toHaveLength(0);
  });

  /**
   * The backfill repairs `extra`; it does not create images. An image the main
   * importer has not written yet is a warning, not an error.
   */
  it('skips a logo whose collection image has not been imported', async () => {
    imageExistsMock.mockResolvedValue(null);

    const result = await new ExhibitionLogoExtraBackfillImporter(context).import();

    expect(setExtraMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
    expect(result.warnings).toHaveLength(1);
    expect(result.errors).toHaveLength(0);
  });

  it('writes nothing in dry-run mode but still counts the row', async () => {
    context = { ...context, dryRun: true };

    const result = await new ExhibitionLogoExtraBackfillImporter(context).import();

    expect(setExtraMock).not.toHaveBeenCalled();
    expect(attachTagsMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(1);
  });
});
