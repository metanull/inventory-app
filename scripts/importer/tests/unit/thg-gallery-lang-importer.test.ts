import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ThgGalleryLangImporter } from '../../src/importers/phase-10/thg-gallery-lang-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ThgGalleryLangImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeCollectionTranslationMock: ReturnType<typeof vi.fn>;

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
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3_thematic_gallery:thg_gallery:42', 'collection-uuid-42', 'collection');
    tracker.set('mwnf3_thematic_gallery:thg_gallery:42', 'context-uuid-42', 'context');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_thematic_gallery.thg_gallery_lang')) {
        return [
          {
            gallery_id: 42,
            lang: 'en',
            title: 'The Gallery',
            long_title: 'The Extended Title',
            short_text: 'A short description.',
          },
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

    writeCollectionTranslationMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeCollectionTranslation: writeCollectionTranslationMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('queries long_title and short_text — not subtitle or description', async () => {
    const importer = new ThgGalleryLangImporter(context);
    await importer.import();

    const sql: string = queryMock.mock.calls[0][0];
    expect(sql).toContain('long_title');
    expect(sql).toContain('short_text');
    expect(sql).not.toContain('subtitle');
    expect(sql).not.toContain('description');
  });

  it('writes a collection translation combining long_title and short_text as description', async () => {
    const importer = new ThgGalleryLangImporter(context);
    const result = await importer.import();

    expect(writeCollectionTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({
        collection_id: 'collection-uuid-42',
        language_id: 'eng',
        context_id: 'context-uuid-42',
        title: 'The Gallery',
        description: 'The Extended Title\n\nA short description.',
        backward_compatibility: 'mwnf3_thematic_gallery:thg_gallery_lang:42:en',
      })
    );
    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
    expect(result.errors).toHaveLength(0);
  });

  it('skips a row when the gallery collection is not in the tracker', async () => {
    tracker = new UnifiedTracker();
    tracker.set('en', 'eng', 'language');
    // no collection registered for gallery 42

    context = { ...context, tracker };
    const importer = new ThgGalleryLangImporter(context);
    const result = await importer.import();

    expect(writeCollectionTranslationMock).not.toHaveBeenCalled();
    expect(result.skipped).toBeGreaterThan(0);
  });

  it('skips a row when the language code is unknown', async () => {
    queryMock = vi.fn(async () => [
      {
        gallery_id: 42,
        lang: 'xx',
        title: 'Unknown lang',
        long_title: null,
        short_text: null,
      },
    ]);
    legacyDb = { ...legacyDb, query: queryMock as ILegacyDatabase['query'] };
    context = { ...context, legacyDb };

    const importer = new ThgGalleryLangImporter(context);
    const result = await importer.import();

    expect(writeCollectionTranslationMock).not.toHaveBeenCalled();
    expect(result.skipped).toBeGreaterThan(0);
  });
});
