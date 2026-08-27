import { beforeEach, describe, expect, it, vi } from 'vitest';

import { AuthorImporter } from '../../src/importers/phase-01/author-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

// Covers the SH author-item assignment key format: sh_authors_objects /
// sh_authors_monuments have NO museum/institution column and store the
// project id uppercase ('AWE'), while SH items were imported with lowercase
// 3-part keys (sh_objects:awe:gr:1). The importer historically built
// 4-part keys with an undefined museum segment, so every SH credit
// assignment silently failed to resolve (issue found 2026-08-22).
describe('AuthorImporter — SH author-item assignments', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let updateFkMock: ReturnType<typeof vi.fn>;

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

  // Legacy fixture rows exactly as the SH junction tables provide them:
  // uppercase project id, no museum/institution column.
  const shAuthorObjects = [
    { author_id: 4, project_id: 'AWE', country: 'gr', number: 1, lang: 'en', type: 'writer', priority: 0 },
    { author_id: 7, project_id: 'AWE', country: 'gr', number: 1, lang: 'en', type: 'translator', priority: 0 },
  ];
  const shAuthorMonuments = [
    { author_id: 5, project_id: 'AWE', country: 'eg', number: 1, lang: 'en', type: 'writer', priority: 0 },
  ];

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3_sharing_history:sh_authors:4', 'author-4', 'author');
    tracker.set('mwnf3_sharing_history:sh_authors:5', 'author-5', 'author');
    tracker.set('mwnf3_sharing_history:sh_authors:7', 'author-7', 'author');
    // Items exist under their real imported keys: lowercase, 3-part.
    tracker.set('mwnf3_sharing_history:sh_objects:awe:gr:1', 'item-obj-gr1', 'item');
    tracker.set('mwnf3_sharing_history:sh_monuments:awe:eg:1', 'item-mon-eg1', 'item');

    const queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('sh_authors_objects')) return shAuthorObjects;
      if (sql.includes('sh_authors_monuments')) return shAuthorMonuments;
      // Every other author table (mwnf3 + THG authors, CVs, mwnf3
      // assignments, dynasties, bridge tables) is empty in this fixture.
      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    updateFkMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      updateItemTranslationAuthorFk: updateFkMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('resolves SH object assignments with lowercase 3-part item keys', async () => {
    const importer = new AuthorImporter(context);
    const result = await importer.import();

    expect(updateFkMock).toHaveBeenCalledWith('item-obj-gr1', 'eng', 'author_id', 'author-4');
    expect(updateFkMock).toHaveBeenCalledWith('item-obj-gr1', 'eng', 'translator_id', 'author-7');
    expect(result.success).toBe(true);
  });

  it('resolves SH monument assignments with lowercase 3-part item keys', async () => {
    const importer = new AuthorImporter(context);
    const result = await importer.import();

    expect(updateFkMock).toHaveBeenCalledWith('item-mon-eg1', 'eng', 'author_id', 'author-5');
    expect(result.success).toBe(true);
    // All three fixture assignments resolved — nothing skipped for key reasons.
    expect(updateFkMock).toHaveBeenCalledTimes(3);
  });

  it('performs no writes in dry-run mode', async () => {
    context.dryRun = true;
    const importer = new AuthorImporter(context);
    const result = await importer.import();

    expect(updateFkMock).not.toHaveBeenCalled();
    expect(result.success).toBe(true);
  });
});

// Covers the mwnf3 author-item assignment key format. Unlike the SH junction
// tables, mwnf3.authors_objects / authors_monuments / authors_dynasties use
// *_id column names (country_id, museum_id, object_id, monument_id, lang_id).
// The importer read them as country/number/lang, so every lookup key contained
// the string "undefined" and every mwnf3 credit silently failed to resolve —
// leaving EPM item sheets with no byline on any language but the one that
// happened to carry the denormalised objects.preparedby free text.
describe('AuthorImporter — mwnf3 author-item assignments', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let updateFkMock: ReturnType<typeof vi.fn>;
  let updateDynastyFkMock: ReturnType<typeof vi.fn>;
  let queryMock: ReturnType<typeof vi.fn>;

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

  // Real legacy rows for mwnf3:objects:EPM:at:Mus22:51 — the object that
  // exposed this bug. Note there is no 'ar' row: the Arabic credit the
  // importer used to produce came from objects.preparedby, not from here.
  const authorObjects = [
    { author_id: 456, project_id: 'EPM', country_id: 'at', museum_id: 'Mus22', object_id: 51, lang_id: 'en', type: 'writer', priority: 0 },
    { author_id: 527, project_id: 'EPM', country_id: 'at', museum_id: 'Mus22', object_id: 51, lang_id: 'en', type: 'copyEditor', priority: 0 },
    { author_id: 536, project_id: 'EPM', country_id: 'at', museum_id: 'Mus22', object_id: 51, lang_id: 'de', type: 'writer', priority: 1 },
    { author_id: 535, project_id: 'EPM', country_id: 'at', museum_id: 'Mus22', object_id: 51, lang_id: 'de', type: 'writer', priority: 2 },
  ];
  const authorMonuments = [
    { author_id: 456, project_id: 'ISL', country_id: 'eg', institution_id: 'Ins01', monument_id: 7, lang_id: 'en', type: 'writer', priority: 0 },
  ];
  const authorDynasties = [
    { dynasty_id: 3, author_id: 456, lang_id: 'en', type: 'writer', priority: null },
  ];

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.set('en', 'eng', 'language');
    tracker.set('de', 'deu', 'language');
    tracker.set('mwnf3:authors:456', 'author-456', 'author');
    tracker.set('mwnf3:authors:527', 'author-527', 'author');
    tracker.set('mwnf3:authors:535', 'author-535', 'author');
    tracker.set('mwnf3:authors:536', 'author-536', 'author');
    tracker.set('mwnf3:objects:EPM:at:Mus22:51', 'item-epm-51', 'item');
    tracker.set('mwnf3:monuments:ISL:eg:Ins01:7', 'item-isl-mon-7', 'item');
    tracker.set('mwnf3:dynasties:3', 'dynasty-3', 'dynasty');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('mwnf3.authors_objects')) return authorObjects;
      if (sql.includes('mwnf3.authors_monuments')) return authorMonuments;
      if (sql.includes('mwnf3.authors_dynasties')) return authorDynasties;
      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    updateFkMock = vi.fn().mockResolvedValue(undefined);
    updateDynastyFkMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      updateItemTranslationAuthorFk: updateFkMock,
      updateDynastyTranslationAuthorFk: updateDynastyFkMock,
    } as unknown as IWriteStrategy;

    context = { legacyDb, strategy, tracker, logger, dryRun: false };
  });

  it('credits the language the legacy junction names, not just the free-text one', async () => {
    const result = await new AuthorImporter(context).import();

    expect(updateFkMock).toHaveBeenCalledWith('item-epm-51', 'eng', 'author_id', 'author-456');
    expect(updateFkMock).toHaveBeenCalledWith(
      'item-epm-51',
      'eng',
      'text_copy_editor_id',
      'author-527'
    );
    expect(updateFkMock).toHaveBeenCalledWith('item-epm-51', 'deu', 'author_id', 'author-536');
    expect(result.success).toBe(true);
  });

  it('never builds an item key containing an undefined segment', async () => {
    await new AuthorImporter(context).import();

    for (const [itemId] of updateFkMock.mock.calls) {
      expect(itemId).not.toContain('undefined');
    }
    expect(updateFkMock).toHaveBeenCalled();
  });

  it('resolves mwnf3 monument assignments with the 4-part institution key', async () => {
    await new AuthorImporter(context).import();

    expect(updateFkMock).toHaveBeenCalledWith('item-isl-mon-7', 'eng', 'author_id', 'author-456');
  });

  it('orders junction rows by legacy priority so the lowest-priority author wins', async () => {
    await new AuthorImporter(context).import();

    // item_translations holds one FK per role and the strategy only fills a
    // NULL column, so the first row offered for a (item, language, role) is the
    // one that sticks. That order has to come from the query, matching
    // legacy's ORDER BY NVL(priority, 0) ASC.
    const objectQuery = queryMock.mock.calls
      .map(([sql]) => sql as string)
      .find((sql) => sql.includes('mwnf3.authors_objects'));

    expect(objectQuery).toMatch(/ORDER BY IFNULL\(priority, 0\) ASC, author_id ASC/);
  });

  it('resolves dynasty assignments using the lang_id column', async () => {
    await new AuthorImporter(context).import();

    expect(updateDynastyFkMock).toHaveBeenCalledWith('dynasty-3', 'eng', 'author_id', 'author-456');
  });
});
