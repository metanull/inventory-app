import { beforeEach, describe, expect, it, vi } from 'vitest';

import { MonumentPictureImporter } from '../../src/importers/phase-02/monument-picture-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('MonumentPictureImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeItemMock: ReturnType<typeof vi.fn>;
  let writeItemTranslationMock: ReturnType<typeof vi.fn>;
  let writeItemImageMock: ReturnType<typeof vi.fn>;
  let findOrCreateArtistMock: ReturnType<typeof vi.fn>;

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

  // A minimal picture row with caption (caption → name)
  const rowWithCaption = {
    project_id: 'BAR',
    country: 'hr',
    institution_id: 'Mon11',
    number: 30,
    lang: 'en',
    type: '',
    image_number: 1,
    path: 'bar/hr/mon11/30/1.jpg',
    caption: 'Interior view',
    photographer: null,
    copyright: null,
  };

  // A metadata-only row (photographer, no caption) — name = parent title + number
  const rowMetadataOnly = {
    project_id: 'BAR',
    country: 'hr',
    institution_id: 'Mon11',
    number: 30,
    lang: 'en',
    type: '',
    image_number: 2,
    path: 'bar/hr/mon11/30/2.jpg',
    caption: null,
    photographer: 'Jane Smith',
    copyright: null,
  };

  // An empty row (no caption, photographer, or copyright) — translation should be skipped
  const rowEmpty = {
    project_id: 'BAR',
    country: 'hr',
    institution_id: 'Mon11',
    number: 30,
    lang: 'fr',
    type: '',
    image_number: 1,
    path: 'bar/hr/mon11/30/1.jpg',
    caption: null,
    photographer: null,
    copyright: null,
  };

  // A copyright-only row
  const rowCopyrightOnly = {
    project_id: 'BAR',
    country: 'hr',
    institution_id: 'Mon11',
    number: 30,
    lang: 'en',
    type: '',
    image_number: 3,
    path: 'bar/hr/mon11/30/3.jpg',
    caption: null,
    photographer: null,
    copyright: '2023 BAR Museum',
  };

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.setMetadata('default_context_id', 'default-ctx-uuid');

    // Parent monument Item (needed to link picture to monument)
    tracker.set('mwnf3:monuments:BAR:hr:Mon11:30', 'parent-item-uuid', 'item');
    // Context (project → context/collection/project)
    tracker.set('mwnf3:projects:BAR', 'context-uuid', 'context');
    tracker.set('mwnf3:projects:BAR', 'collection-uuid', 'collection');
    tracker.set('mwnf3:projects:BAR', 'project-uuid', 'project');
    // Partner (institution → partner)
    tracker.set('mwnf3:institutions:Mon11:hr', 'partner-uuid', 'partner');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3.monuments_pictures')) {
        return [rowWithCaption, rowEmpty];
      }
      if (sql.includes('FROM mwnf3.monuments')) {
        return [{ name: 'Hellenbach Manor' }];
      }
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
    findOrCreateArtistMock = vi.fn().mockResolvedValue('artist-uuid');

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeItem: writeItemMock,
      writeItemTranslation: writeItemTranslationMock,
      writeItemImage: writeItemImageMock,
      writeArtist: vi.fn().mockResolvedValue('artist-uuid'),
      attachArtistsToItem: vi.fn().mockResolvedValue(undefined),
      findArtistByInternalName: findOrCreateArtistMock,
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
      if (sql.includes('FROM mwnf3.monuments_pictures')) return [rowWithCaption];
      return [];
    });
    const importer = new MonumentPictureImporter(context);
    await importer.import();

    expect(writeItemTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({ name: 'Interior view' })
    );
  });

  it('creates translation with parent title and number for metadata-only (photographer) rows', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.monuments_pictures')) return [rowMetadataOnly];
      if (sql.includes('FROM mwnf3.monuments')) return [{ name: 'Hellenbach Manor' }];
      return [];
    });
    const importer = new MonumentPictureImporter(context);
    await importer.import();

    expect(writeItemTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({ name: 'Hellenbach Manor (2)' })
    );
  });

  it('creates translation with parent title and number for copyright-only rows', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.monuments_pictures')) return [rowCopyrightOnly];
      if (sql.includes('FROM mwnf3.monuments')) return [{ name: 'Hellenbach Manor' }];
      return [];
    });
    const importer = new MonumentPictureImporter(context);
    await importer.import();

    expect(writeItemTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({
        name: 'Hellenbach Manor (3)',
        extra: JSON.stringify({ copyright: '2023 BAR Museum' }),
      })
    );
  });

  it('skips translation when no caption, photographer, or copyright', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.monuments_pictures')) return [rowEmpty];
      return [];
    });
    const importer = new MonumentPictureImporter(context);
    await importer.import();

    expect(writeItemTranslationMock).not.toHaveBeenCalled();
  });

  it('does not create Image N placeholder names', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.monuments_pictures')) return [rowEmpty];
      return [];
    });
    const importer = new MonumentPictureImporter(context);
    await importer.import();

    const calls = writeItemTranslationMock.mock.calls.flat();
    for (const arg of calls) {
      if (arg && typeof arg === 'object' && 'name' in arg) {
        expect((arg as { name: string }).name).not.toMatch(/^Image \d+/);
      }
    }
  });

  it('reports error (not silent fallback) when parent monument not found for metadata-only row', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.monuments_pictures')) return [rowMetadataOnly];
      if (sql.includes('FROM mwnf3.monuments')) return []; // parent not found
      return [];
    });
    const importer = new MonumentPictureImporter(context);
    const result = await importer.import();

    // The failure must be surfaced (either errors or warnings) – NOT silently swallowed.
    const surfaced = [...(result.errors ?? []), ...(result.warnings ?? [])];
    expect(surfaced.length).toBeGreaterThan(0);
    expect(writeItemTranslationMock).not.toHaveBeenCalled();
  });

  it('parent name query does NOT include type column', async () => {
    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3.monuments_pictures')) return [rowMetadataOnly];
      if (sql.includes('FROM mwnf3.monuments')) return [{ name: 'Hellenbach Manor' }];
      return [];
    });
    const importer = new MonumentPictureImporter(context);
    await importer.import();

    const monumentQuery = queryMock.mock.calls.find(
      ([sql]: [string]) =>
        typeof sql === 'string' && sql.includes('FROM mwnf3.monuments') && !sql.includes('pictures')
    );
    expect(monumentQuery).toBeDefined();
    const [sql] = monumentQuery as [string];
    expect(sql).not.toContain('AND type');
  });

  it('returns success=false when the main query throws', async () => {
    queryMock.mockRejectedValue(new Error('DB connection lost'));
    const importer = new MonumentPictureImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(false);
    expect(result.errors[0]).toContain('monument pictures');
  });
});
