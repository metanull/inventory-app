import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ThgGalleryContentImporter } from '../../src/importers/phase-10/thg-gallery-content-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ThgGalleryContentImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeCollectionImageMock: ReturnType<typeof vi.fn>;
  let writeCollectionMediaMock: ReturnType<typeof vi.fn>;

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
    tracker.set('mwnf3_thematic_gallery:thg_gallery:5', 'collection-uuid-5', 'collection');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_thematic_gallery.exhibition_logo')) {
        return [
          {
            logo_id: 1,
            gallery_id: 5,
            logo: 'logos/gallery5_header.png',
            display_order: 1,
          },
        ];
      }
      if (sql.includes('FROM mwnf3_thematic_gallery.exhibition_related_content_i18n')) {
        return [
          {
            related_content_id: 20,
            language_id: 'en',
            title: 'A Bibliography Entry',
            description: null,
          },
        ];
      }
      if (sql.includes('FROM mwnf3_thematic_gallery.exhibition_related_content')) {
        return [
          {
            related_content_id: 20,
            gallery_id: 5,
            type_resource: 'document',
            link: 'https://example.com/doc.pdf',
            display_order: 1,
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

    writeCollectionImageMock = vi.fn().mockResolvedValue(undefined);
    writeCollectionMediaMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeCollectionImage: writeCollectionImageMock,
      writeCollectionMedia: writeCollectionMediaMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  describe('exhibition logos', () => {
    it('logo query selects logo column — not path', async () => {
      const importer = new ThgGalleryContentImporter(context);
      await importer.import();

      const logoCall = queryMock.mock.calls.find((args: unknown[]) =>
        (args[0] as string).includes('FROM mwnf3_thematic_gallery.exhibition_logo')
      );
      expect(logoCall).toBeDefined();
      const sql: string = logoCall![0] as string;
      expect(sql).toContain('logo');
      expect(sql).not.toContain('path');
    });

    it('writes a collection image using the logo column value as path', async () => {
      const importer = new ThgGalleryContentImporter(context);
      const result = await importer.import();

      expect(writeCollectionImageMock).toHaveBeenCalledWith(
        expect.objectContaining({
          collection_id: 'collection-uuid-5',
          path: 'logos/gallery5_header.png',
          original_name: 'gallery5_header.png',
          mime_type: 'image/png',
          display_order: 1,
        })
      );
      expect(result.errors).toHaveLength(0);
    });
  });

  describe('exhibition related content', () => {
    it('related content query uses related_content_id, type_resource, link — not content_id, type, url', async () => {
      const importer = new ThgGalleryContentImporter(context);
      await importer.import();

      const contentCall = queryMock.mock.calls.find(
        (args: unknown[]) =>
          (args[0] as string).includes('FROM mwnf3_thematic_gallery.exhibition_related_content') &&
          !(args[0] as string).includes('_i18n')
      );
      expect(contentCall).toBeDefined();
      const sql: string = contentCall![0] as string;
      expect(sql).toContain('related_content_id');
      expect(sql).toContain('type_resource');
      expect(sql).toContain('link');
      expect(sql).not.toMatch(/\bcontent_id\b/);
      expect(sql).not.toMatch(/\btype\b/);
      expect(sql).not.toMatch(/\burl\b/);
    });

    it('i18n query uses related_content_id and language_id — not content_id and lang', async () => {
      const importer = new ThgGalleryContentImporter(context);
      await importer.import();

      const i18nCall = queryMock.mock.calls.find((args: unknown[]) =>
        (args[0] as string).includes('FROM mwnf3_thematic_gallery.exhibition_related_content_i18n')
      );
      expect(i18nCall).toBeDefined();
      const sql: string = i18nCall![0] as string;
      expect(sql).toContain('related_content_id');
      expect(sql).toContain('language_id');
      expect(sql).not.toMatch(/\bcontent_id\b/);
      expect(sql).not.toMatch(/\blang\b(?!uage_id)/);
    });

    it('writes collection media with the correct url and type from link and type_resource', async () => {
      const importer = new ThgGalleryContentImporter(context);
      const result = await importer.import();

      expect(writeCollectionMediaMock).toHaveBeenCalledWith(
        expect.objectContaining({
          collection_id: 'collection-uuid-5',
          language_id: 'eng',
          type: 'document',
          title: 'A Bibliography Entry',
          url: 'https://example.com/doc.pdf',
          backward_compatibility: 'mwnf3_thematic_gallery:exhibition_related_content:20:en',
        })
      );
      expect(result.errors).toHaveLength(0);
    });
  });
});
