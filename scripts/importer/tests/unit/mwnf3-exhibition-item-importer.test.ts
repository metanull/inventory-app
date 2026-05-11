import { beforeEach, describe, expect, it, vi } from 'vitest';

import { Mwnf3ExhibitionItemImporter } from '../../src/importers/phase-01/mwnf3-exhibition-item-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

// ===========================================================================
// Test constants
// ===========================================================================

const PAGE_ID = 7;
const PAGE_COLLECTION_BC = `mwnf3:exhibition_pages:${PAGE_ID}`;

const EXHIBITION_ID = 10;
const EXHIBITION_COLLECTION_BC = `mwnf3:exhibitions:${EXHIBITION_ID}`;

const ARTINTRO_PAGE_ID = 3;
const ARTINTRO_PAGE_COLLECTION_BC = `mwnf3:artintro_pages:${ARTINTRO_PAGE_ID}`;

const IMAGE_ID = 42;
const ITEM_BC = 'mwnf3:objects:isl:jo:Mus01:8';

// ===========================================================================
// Shared fixtures
// ===========================================================================

/** A page image row referencing item 'O;ISL;jo;1;8'. */
const PAGE_IMAGE_ROW = {
  image_id: IMAGE_ID,
  page_id: PAGE_ID,
  n: 1,
  n2: 0,
  ref_item: 'O;ISL;jo;1;8',
  picture: 'images/item.jpg',
};

/** An exhibition-level image row referencing the same item. */
const EXHIBITION_IMAGE_ROW = {
  image_id: IMAGE_ID,
  exhibition_id: EXHIBITION_ID,
  n: 1,
  n2: 0,
  ref_item: 'O;ISL;jo;1;8',
  picture: 'images/exh.jpg',
};

/** An artintro page image row referencing the same item. */
const ARTINTRO_IMAGE_ROW = {
  image_id: IMAGE_ID,
  page_id: ARTINTRO_PAGE_ID,
  n: 1,
  n2: 0,
  ref_item: 'O;ISL;jo;1;8',
  picture: 'images/artintro.jpg',
};

// ===========================================================================
// Logger stub
// ===========================================================================

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

// ===========================================================================
// Helpers
// ===========================================================================

/**
 * Build a query mock for page-image tests.
 * Supplies exhibition_page_images + exhibition_page_images_fields EAV,
 * and returns empty arrays for all other tables.
 */
function makePageImageQueryMock(
  pageImages: typeof PAGE_IMAGE_ROW[],
  eavRows: Array<{ entity_id: number; lang_id: string; field: string; value: string }>,
  detailRows: Array<Record<string, unknown>> = [],
  detailEavRows: Array<{ entity_id: number; lang_id: string; field: string; value: string }> = []
): ReturnType<typeof vi.fn> {
  return vi.fn(async (sql: string) => {
    if (sql.includes('exhibition_page_images_fields')) return eavRows;
    if (sql.includes('exhibition_page_image_details_fields')) return detailEavRows;
    if (sql.includes('exhibition_page_image_details') && !sql.includes('_fields')) return detailRows;
    if (sql.includes('exhibition_page_images') && !sql.includes('_fields') && !sql.includes('details')) return pageImages;
    // exhibition_images and artintro_page_images return empty
    return [];
  });
}

/**
 * Build a query mock for exhibition-level image tests.
 */
function makeExhibitionImageQueryMock(
  exhibitionImages: typeof EXHIBITION_IMAGE_ROW[],
  eavRows: Array<{ entity_id: number; lang_id: string; field: string; value: string }>
): ReturnType<typeof vi.fn> {
  return vi.fn(async (sql: string) => {
    if (sql.includes('exhibition_images_fields')) return eavRows;
    if (sql.includes('exhibition_images') && !sql.includes('_fields') && !sql.includes('page')) return exhibitionImages;
    // page images and artintro return empty
    return [];
  });
}

/**
 * Build a query mock for artintro page image tests.
 */
function makeArtintroImageQueryMock(
  artintroImages: typeof ARTINTRO_IMAGE_ROW[],
  eavRows: Array<{ entity_id: number; lang_id: string; field: string; value: string }>
): ReturnType<typeof vi.fn> {
  return vi.fn(async (sql: string) => {
    if (sql.includes('artintro_page_images_fields')) return eavRows;
    if (sql.includes('artintro_page_images') && !sql.includes('_fields')) return artintroImages;
    // page images and exhibition_images return empty
    return [];
  });
}

function makeContext(
  queryMock: ReturnType<typeof vi.fn>,
  tracker: UnifiedTracker,
  writeCollectionItemMock: ReturnType<typeof vi.fn>
): ImportContext {
  const legacyDb: ILegacyDatabase = {
    query: queryMock as ILegacyDatabase['query'],
    execute: vi.fn(),
    connect: vi.fn(),
    disconnect: vi.fn(),
  };

  const strategy = {
    exists: vi.fn().mockResolvedValue(false),
    findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
    writeCollectionItem: writeCollectionItemMock,
    writeCollectionImage: vi.fn().mockResolvedValue(undefined),
  } as unknown as IWriteStrategy;

  return {
    legacyDb,
    strategy,
    tracker,
    logger,
    dryRun: false,
  };
}

// ===========================================================================
// Tests: story #1225 — exhibition_page_images_fields extended fields
// ===========================================================================

describe('Mwnf3ExhibitionItemImporter — story #1225: page image EAV fields', () => {
  let tracker: UnifiedTracker;
  let writeCollectionItemMock: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    vi.clearAllMocks();
    tracker = new UnifiedTracker();
    tracker.set(PAGE_COLLECTION_BC, 'page-collection-uuid', 'collection');
    tracker.set(ITEM_BC, 'item-uuid', 'item');
    writeCollectionItemMock = vi.fn().mockResolvedValue(undefined);
  });

  it('stores item_name, item_date, item_dynasty, item_location on collection_item.extra', async () => {
    const eavRows = [
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_name', value: 'Golden Vase' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_date', value: '12th century' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_dynasty', value: 'Abbasid' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_location', value: 'Baghdad Museum' },
    ];

    const ctx = makeContext(
      makePageImageQueryMock([PAGE_IMAGE_ROW], eavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    expect(writeCollectionItemMock).toHaveBeenCalledTimes(1);
    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.item_name).toEqual({ en: 'Golden Vase' });
    expect(extra.item_date).toEqual({ en: '12th century' });
    expect(extra.item_dynasty).toEqual({ en: 'Abbasid' });
    expect(extra.item_location).toEqual({ en: 'Baghdad Museum' });
  });

  it('stores item_artist, item_material, item_museum on collection_item.extra', async () => {
    const eavRows = [
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_artist', value: 'Unknown Artist' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_material', value: 'Ceramic' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_museum', value: 'Cairo Museum' },
    ];

    const ctx = makeContext(
      makePageImageQueryMock([PAGE_IMAGE_ROW], eavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.item_artist).toEqual({ en: 'Unknown Artist' });
    expect(extra.item_material).toEqual({ en: 'Ceramic' });
    expect(extra.item_museum).toEqual({ en: 'Cairo Museum' });
  });

  it('regression: detail_justification still lands in collection_item.extra', async () => {
    const eavRows = [
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'detail_justification', value: 'The detail text.' },
    ];

    const ctx = makeContext(
      makePageImageQueryMock([PAGE_IMAGE_ROW], eavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.detail_justification).toEqual({ en: 'The detail text.' });
  });

  it('preserves n, n2, picture, and details alongside new EAV fields', async () => {
    const eavRows = [
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_name', value: 'Vessel' },
    ];

    const ctx = makeContext(
      makePageImageQueryMock([PAGE_IMAGE_ROW], eavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.n).toBe(1);
    expect(extra.n2).toBe(0);
    expect(extra.picture).toBe('images/item.jpg');
  });

  it('stores multi-language values as separate lang keys', async () => {
    const eavRows = [
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_name', value: 'Golden Vase' },
      { entity_id: IMAGE_ID, lang_id: 'de', field: 'item_name', value: 'Goldene Vase' },
    ];

    const ctx = makeContext(
      makePageImageQueryMock([PAGE_IMAGE_ROW], eavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.item_name).toEqual({ en: 'Golden Vase', de: 'Goldene Vase' });
  });

  it('omits fields whose source values are empty or whitespace-only', async () => {
    const eavRows = [
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_name', value: '' },
    ];

    const ctx = makeContext(
      makePageImageQueryMock([PAGE_IMAGE_ROW], eavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.item_name).toBeUndefined();
  });
});

// ===========================================================================
// Tests: story #1222 — exhibition_page_image_details_fields extended fields
// ===========================================================================

describe('Mwnf3ExhibitionItemImporter — story #1222: page image detail annotation EAV fields', () => {
  let tracker: UnifiedTracker;
  let writeCollectionItemMock: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    vi.clearAllMocks();
    tracker = new UnifiedTracker();
    tracker.set(PAGE_COLLECTION_BC, 'page-collection-uuid', 'collection');
    tracker.set(ITEM_BC, 'item-uuid', 'item');
    writeCollectionItemMock = vi.fn().mockResolvedValue(undefined);
  });

  it('stores item_name, item_date, item_dynasty, item_location on nested detail annotation', async () => {
    const DETAIL_ID = 99;
    const detailRows = [
      { detail_id: DETAIL_ID, image_id: IMAGE_ID, n: 1, n2: 0, ref_detail_item: null, picture_details: null },
    ];
    const detailEavRows = [
      { entity_id: DETAIL_ID, lang_id: 'en', field: 'item_name', value: 'Fragment' },
      { entity_id: DETAIL_ID, lang_id: 'en', field: 'item_date', value: '10th century' },
      { entity_id: DETAIL_ID, lang_id: 'en', field: 'item_dynasty', value: 'Fatimid' },
      { entity_id: DETAIL_ID, lang_id: 'en', field: 'item_location', value: 'Cairo' },
    ];

    const ctx = makeContext(
      makePageImageQueryMock([PAGE_IMAGE_ROW], [], detailRows, detailEavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    const details = extra.details as Array<Record<string, unknown>>;
    expect(details).toHaveLength(1);
    expect(details[0].item_name).toEqual({ en: 'Fragment' });
    expect(details[0].item_date).toEqual({ en: '10th century' });
    expect(details[0].item_dynasty).toEqual({ en: 'Fatimid' });
    expect(details[0].item_location).toEqual({ en: 'Cairo' });
  });

  it('regression: detail_name and detail_justification remain present on nested annotations', async () => {
    const DETAIL_ID = 100;
    const detailRows = [
      { detail_id: DETAIL_ID, image_id: IMAGE_ID, n: 1, n2: 0, ref_detail_item: null, picture_details: null },
    ];
    const detailEavRows = [
      { entity_id: DETAIL_ID, lang_id: 'en', field: 'detail_name', value: 'Close-up' },
      { entity_id: DETAIL_ID, lang_id: 'en', field: 'detail_justification', value: 'Shows craftsmanship.' },
    ];

    const ctx = makeContext(
      makePageImageQueryMock([PAGE_IMAGE_ROW], [], detailRows, detailEavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    const details = extra.details as Array<Record<string, unknown>>;
    expect(details[0].detail_name).toEqual({ en: 'Close-up' });
    expect(details[0].detail_justification).toEqual({ en: 'Shows craftsmanship.' });
  });

  it('preserves n, n2, ref_detail_item, and picture_details on annotation', async () => {
    const DETAIL_ID = 101;
    const detailRows = [
      {
        detail_id: DETAIL_ID,
        image_id: IMAGE_ID,
        n: 2,
        n2: 1,
        ref_detail_item: 'O;ISL;jo;1;9',
        picture_details: 'detail.jpg',
      },
    ];

    const ctx = makeContext(
      makePageImageQueryMock([PAGE_IMAGE_ROW], [], detailRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    const details = extra.details as Array<Record<string, unknown>>;
    expect(details[0].n).toBe(2);
    expect(details[0].n2).toBe(1);
    expect(details[0].ref_detail_item).toBe('O;ISL;jo;1;9');
    expect(details[0].picture_details).toBe('detail.jpg');
  });
});

// ===========================================================================
// Tests: story #1224 — exhibition_images_fields
// ===========================================================================

describe('Mwnf3ExhibitionItemImporter — story #1224: exhibition-level image EAV fields', () => {
  let tracker: UnifiedTracker;
  let writeCollectionItemMock: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    vi.clearAllMocks();
    tracker = new UnifiedTracker();
    tracker.set(EXHIBITION_COLLECTION_BC, 'exh-collection-uuid', 'collection');
    tracker.set(ITEM_BC, 'item-uuid', 'item');
    writeCollectionItemMock = vi.fn().mockResolvedValue(undefined);
  });

  it('stores item_name, item_date, item_dynasty, item_location on collection_item.extra', async () => {
    const eavRows = [
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_name', value: 'Bronze Bowl' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_date', value: '9th century' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_dynasty', value: 'Umayyad' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_location', value: 'Damascus' },
    ];

    const ctx = makeContext(
      makeExhibitionImageQueryMock([EXHIBITION_IMAGE_ROW], eavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    expect(writeCollectionItemMock).toHaveBeenCalledTimes(1);
    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.item_name).toEqual({ en: 'Bronze Bowl' });
    expect(extra.item_date).toEqual({ en: '9th century' });
    expect(extra.item_dynasty).toEqual({ en: 'Umayyad' });
    expect(extra.item_location).toEqual({ en: 'Damascus' });
  });

  it('stores item_description and item_museum on collection_item.extra', async () => {
    const eavRows = [
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_description', value: 'A rare example.' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_museum', value: 'National Museum' },
    ];

    const ctx = makeContext(
      makeExhibitionImageQueryMock([EXHIBITION_IMAGE_ROW], eavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.item_description).toEqual({ en: 'A rare example.' });
    expect(extra.item_museum).toEqual({ en: 'National Museum' });
  });

  it('regression: n, n2, and picture are still written to extra', async () => {
    const ctx = makeContext(
      makeExhibitionImageQueryMock([EXHIBITION_IMAGE_ROW], []),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.n).toBe(1);
    expect(extra.n2).toBe(0);
    expect(extra.picture).toBe('images/exh.jpg');
  });
});

// ===========================================================================
// Tests: story #1223 — artintro_page_images_fields
// ===========================================================================

describe('Mwnf3ExhibitionItemImporter — story #1223: artintro page image EAV fields', () => {
  let tracker: UnifiedTracker;
  let writeCollectionItemMock: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    vi.clearAllMocks();
    tracker = new UnifiedTracker();
    tracker.set(ARTINTRO_PAGE_COLLECTION_BC, 'artintro-page-uuid', 'collection');
    tracker.set(ITEM_BC, 'item-uuid', 'item');
    writeCollectionItemMock = vi.fn().mockResolvedValue(undefined);
  });

  it('stores detail_justification, item_name, item_date, item_location on collection_item.extra', async () => {
    const eavRows = [
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'detail_justification', value: 'Context note.' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_name', value: 'Marble Slab' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_date', value: '11th century' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_location', value: 'Jordan' },
    ];

    const ctx = makeContext(
      makeArtintroImageQueryMock([ARTINTRO_IMAGE_ROW], eavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    expect(writeCollectionItemMock).toHaveBeenCalledTimes(1);
    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.detail_justification).toEqual({ en: 'Context note.' });
    expect(extra.item_name).toEqual({ en: 'Marble Slab' });
    expect(extra.item_date).toEqual({ en: '11th century' });
    expect(extra.item_location).toEqual({ en: 'Jordan' });
  });

  it('stores detail_name, item_dynasty, item_museum on collection_item.extra', async () => {
    const eavRows = [
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'detail_name', value: 'Detail shot' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_dynasty', value: 'Ayyubid' },
      { entity_id: IMAGE_ID, lang_id: 'en', field: 'item_museum', value: 'Amman Museum' },
    ];

    const ctx = makeContext(
      makeArtintroImageQueryMock([ARTINTRO_IMAGE_ROW], eavRows),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.detail_name).toEqual({ en: 'Detail shot' });
    expect(extra.item_dynasty).toEqual({ en: 'Ayyubid' });
    expect(extra.item_museum).toEqual({ en: 'Amman Museum' });
  });

  it('regression: n, n2, and picture are still written to extra', async () => {
    const ctx = makeContext(
      makeArtintroImageQueryMock([ARTINTRO_IMAGE_ROW], []),
      tracker,
      writeCollectionItemMock
    );

    const importer = new Mwnf3ExhibitionItemImporter(ctx);
    await importer.import();

    const extra = writeCollectionItemMock.mock.calls[0][0].extra as Record<string, unknown>;
    expect(extra.n).toBe(1);
    expect(extra.n2).toBe(0);
    expect(extra.picture).toBe('images/artintro.jpg');
  });
});
