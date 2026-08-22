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
