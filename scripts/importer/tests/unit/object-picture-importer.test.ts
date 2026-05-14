import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ObjectPictureImporter } from '../../src/importers/phase-02/object-picture-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ObjectPictureImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeItemMock: ReturnType<typeof vi.fn>;
  let writeItemTranslationMock: ReturnType<typeof vi.fn>;
  let writeItemImageMock: ReturnType<typeof vi.fn>;

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

  // Caption row — name = caption
  const rowWithCaption = {
    project_id: 'EPM',
    country: 'qt',
    museum_id: 'Mus21',
    number: 19,
    lang: 'en',
    type: '',
    image_number: 1,
    path: 'epm/qt/mus21/19/1.jpg',
    caption: 'Gold amulet',
    photographer: null,
    copyright: null,
  };

  // Copyright-only row — name = parent title + number
  const rowCopyrightOnly = {
    project_id: 'EPM',
    country: 'qt',
    museum_id: 'Mus21',
    number: 19,
    lang: 'en',
    type: '',
    image_number: 1,
    path: 'epm/qt/mus21/19/1.jpg',
    caption: null,
    photographer: null,
    copyright: '2023 EPM',
  };

  // Empty row — translation should be skipped
  const rowEmpty = {
    project_id: 'EPM',
    country: 'qt',
    museum_id: 'Mus21',
    number: 19,
    lang: 'fr',
    type: '',
    image_number: 1,
    path: 'epm/qt/mus21/19/1.jpg',
    caption: null,
    photographer: null,
    copyright: null,
  };

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.setMetadata('default_context_id', 'default-ctx-uuid');

    // Parent object Item
    tracker.set('mwnf3:objects:EPM:qt:Mus21:19', 'parent-item-uuid', 'item');
    // Context / collection / project
    tracker.set('mwnf3:projects:EPM', 'context-uuid', 'context');
    tracker.set('mwnf3:projects:EPM', 'collection-uuid', 'collection');
    tracker.set('mwnf3:projects:EPM', 'project-uuid', 'project');
    // Partner (museum)
    tracker.set('mwnf3:museums:Mus21:qt', 'partner-uuid', 'partner');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3.objects_pictures')) return [rowWithCaption];
      if (sql.includes('FROM mwnf3.objects')) return [{ name: 'Museum Object Title' }];
      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    writeItemMock = vi.fn().mockResolvedValue('new-picture-item-uuid');
    writeItemTranslationMock = vi.fn().mockResolvedValue(undefined);
    writeItemImageMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeItem: writeItemMock,
      writeItemTranslation: writeItemTranslationMock,
      writeItemImage: writeItemImageMock,
      writeArtist: vi.fn().mockResolvedValue('artist-uuid'),
      attachArtistsToItem: vi.fn().mockResolvedValue(undefined),
      findArtistByInternalName: vi.fn().mockResolvedValue('artist-uuid'),
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('creates translation with caption as name when caption is present', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.objects_pictures')) return [rowWithCaption];
      return [];
    });
    const importer = new ObjectPictureImporter(context);
    await importer.import();

    expect(writeItemTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({ name: 'Gold amulet' })
    );
  });

  it('creates translation with parent title and number for copyright-only rows', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.objects_pictures')) return [rowCopyrightOnly];
      if (sql.includes('FROM mwnf3.objects')) return [{ name: 'Museum Object Title' }];
      return [];
    });
    const importer = new ObjectPictureImporter(context);
    await importer.import();

    expect(writeItemTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({
        name: 'Museum Object Title (1)',
        extra: JSON.stringify({ copyright: '2023 EPM' }),
      })
    );
  });

  it('skips translation when no caption, photographer, or copyright', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.objects_pictures')) return [rowEmpty];
      return [];
    });
    const importer = new ObjectPictureImporter(context);
    await importer.import();

    expect(writeItemTranslationMock).not.toHaveBeenCalled();
  });

  it('does not create Image N placeholder names', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.objects_pictures')) return [rowEmpty];
      return [];
    });
    const importer = new ObjectPictureImporter(context);
    await importer.import();

    const calls = writeItemTranslationMock.mock.calls.flat();
    for (const arg of calls) {
      if (arg && typeof arg === 'object' && 'name' in arg) {
        expect((arg as { name: string }).name).not.toMatch(/^Image \d+/);
      }
    }
  });

  it('reports error when parent object not found for metadata-only row', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.objects_pictures')) return [rowCopyrightOnly];
      if (sql.includes('FROM mwnf3.objects')) return []; // parent not found
      return [];
    });
    const importer = new ObjectPictureImporter(context);
    const result = await importer.import();

    // The failure must be surfaced (either errors or warnings) – NOT silently swallowed.
    const surfaced = [...(result.errors ?? []), ...(result.warnings ?? [])];
    expect(surfaced.length).toBeGreaterThan(0);
    expect(writeItemTranslationMock).not.toHaveBeenCalled();
  });
});
