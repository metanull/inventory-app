import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ExtraBitBufferBackfillImporter } from '../../src/importers/phase-11/extra-bit-buffer-backfill-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ExtraBitBufferBackfillImporter', () => {
  let context: ImportContext;
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

  const legacyDb: ILegacyDatabase = {
    query: vi.fn().mockResolvedValue([]) as ILegacyDatabase['query'],
    execute: vi.fn(),
    connect: vi.fn(),
    disconnect: vi.fn(),
  };

  beforeEach(() => {
    vi.clearAllMocks();

    findMock = vi.fn().mockResolvedValue([]);
    setMock = vi.fn().mockResolvedValue(undefined);

    context = {
      legacyDb,
      strategy: {
        findCollectionTranslationsWithSerializedBuffers: findMock,
        setCollectionTranslationExtraById: setMock,
      } as unknown as IWriteStrategy,
      tracker: new UnifiedTracker(),
      logger,
      dryRun: false,
    };
  });

  it('rewrites the gallery 47 shape as JSON booleans', async () => {
    findMock.mockResolvedValue([
      {
        id: 'translation-uuid-47-en',
        extra: {
          thg_gallery: {
            link: 'the_use_of_colours_in_art',
            status: 'A',
            has_timeline: { data: [1], type: 'Buffer' },
            has_country_timeline: { data: [0], type: 'Buffer' },
          },
          exhibition_i18n: { enabled: 'Y' },
        },
      },
    ]);

    const importer = new ExtraBitBufferBackfillImporter(context);
    const result = await importer.import();

    expect(setMock).toHaveBeenCalledTimes(1);
    const [id, serialized] = setMock.mock.calls[0] as [string, string];
    expect(id).toBe('translation-uuid-47-en');
    expect(serialized).not.toContain('Buffer');

    expect(JSON.parse(serialized)).toEqual({
      thg_gallery: {
        link: 'the_use_of_colours_in_art',
        status: 'A',
        has_timeline: true,
        has_country_timeline: false,
      },
      exhibition_i18n: { enabled: 'Y' },
    });

    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
    expect(result.errors).toHaveLength(0);
  });

  it('leaves a candidate row alone when the JSON_SEARCH hit was not a bit Buffer', async () => {
    findMock.mockResolvedValue([
      { id: 'decoy-uuid', extra: { thg_gallery_lang: { keywords: 'Buffer, ring buffer' } } },
    ]);

    const importer = new ExtraBitBufferBackfillImporter(context);
    const result = await importer.import();

    expect(setMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(0);
    expect(result.skipped).toBe(1);
    expect(result.success).toBe(true);
  });

  it('is a no-op when no row holds a serialized Buffer', async () => {
    const importer = new ExtraBitBufferBackfillImporter(context);
    const result = await importer.import();

    expect(setMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(0);
    expect(result.success).toBe(true);
  });

  it('writes nothing in dry-run mode but still reports what it would change', async () => {
    findMock.mockResolvedValue([
      { id: 'translation-uuid', extra: { thg_gallery: { has_timeline: { data: [1], type: 'Buffer' } } } },
    ]);

    const importer = new ExtraBitBufferBackfillImporter({ ...context, dryRun: true });
    const result = await importer.import();

    expect(setMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(1);
    expect(result.success).toBe(true);
  });

  it('reports a per-row failure without aborting the remaining rows', async () => {
    findMock.mockResolvedValue([
      { id: 'bad-uuid', extra: { thg_gallery: { has_timeline: { data: [1], type: 'Buffer' } } } },
      { id: 'good-uuid', extra: { thg_gallery: { has_timeline: { data: [0], type: 'Buffer' } } } },
    ]);
    setMock.mockImplementation(async (id: string) => {
      if (id === 'bad-uuid') throw new Error('write failed');
    });

    const importer = new ExtraBitBufferBackfillImporter(context);
    const result = await importer.import();

    expect(setMock).toHaveBeenCalledTimes(2);
    expect(result.imported).toBe(1);
    expect(result.errors).toHaveLength(1);
    expect(result.errors[0]).toContain('bad-uuid');
    expect(result.success).toBe(false);
  });

  it('is idempotent — a second pass over already-normalised rows changes nothing', async () => {
    findMock.mockResolvedValue([]);

    const importer = new ExtraBitBufferBackfillImporter(context);
    await importer.import();
    const result = await importer.import();

    expect(setMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(0);
  });
});
