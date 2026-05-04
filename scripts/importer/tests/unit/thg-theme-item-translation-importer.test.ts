import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ThgThemeItemTranslationImporter } from '../../src/importers/phase-10/thg-theme-item-translation-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ThgThemeItemTranslationImporter — source_bc_by_language', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let getCollectionItemExtraMock: ReturnType<typeof vi.fn>;
  let setCollectionItemExtraMock: ReturnType<typeof vi.fn>;

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

  const GALLERY_ID = 3;
  const THEME_ID = 5;
  const ITEM_ID = 100;
  // mwnf3 object BC keys used by resolveItemReference
  const OBJ_PROJECT = 'ISL';
  const OBJ_COUNTRY = 'MAR';
  const OBJ_PARTNER = 'MUS001';
  const ITEM_BC = `mwnf3:objects:${OBJ_PROJECT}:${OBJ_COUNTRY}:${OBJ_PARTNER}:${ITEM_ID}`;

  const BASE_THEME_ITEM = {
    gallery_id: GALLERY_ID,
    theme_id: THEME_ID,
    item_id: ITEM_ID,
    mwnf3_object_project_id: OBJ_PROJECT,
    mwnf3_object_country_id: OBJ_COUNTRY,
    mwnf3_object_partner_id: OBJ_PARTNER,
    mwnf3_object_item_id: ITEM_ID,
    mwnf3_monument_project_id: null,
    mwnf3_monument_country_id: null,
    mwnf3_monument_partner_id: null,
    mwnf3_monument_item_id: null,
    mwnf3_monument_detail_project_id: null,
    mwnf3_monument_detail_country_id: null,
    mwnf3_monument_detail_partner_id: null,
    mwnf3_monument_detail_item_id: null,
    mwnf3_monument_detail_detail_id: null,
  };

  beforeEach(() => {
    vi.clearAllMocks();

    tracker = new UnifiedTracker();
    tracker.set('en', 'eng', 'language');
    tracker.set('fr', 'fra', 'language');
    // theme collection
    tracker.set(
      `mwnf3_thematic_gallery:theme:${GALLERY_ID}:${THEME_ID}`,
      'theme-collection-uuid',
      'collection'
    );
    // item registered with the correct BC key format
    tracker.set(ITEM_BC, 'item-uuid', 'item');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_thematic_gallery.theme_item_i18n')) {
        return [
          {
            gallery_id: GALLERY_ID,
            theme_id: THEME_ID,
            item_id: ITEM_ID,
            language_id: 'en',
            contextual_description: 'This item is special in English.',
          },
          {
            gallery_id: GALLERY_ID,
            theme_id: THEME_ID,
            item_id: ITEM_ID,
            language_id: 'fr',
            contextual_description: 'Cet objet est special en français.',
          },
        ];
      }
      if (sql.includes('FROM mwnf3_thematic_gallery.theme_item')) {
        return [BASE_THEME_ITEM];
      }
      return [];
    });

    legacyDb = {
      query: queryMock as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    getCollectionItemExtraMock = vi.fn().mockResolvedValue(null);
    setCollectionItemExtraMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      getCollectionItemExtra: getCollectionItemExtraMock,
      setCollectionItemExtra: setCollectionItemExtraMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('stores source_bc_by_language alongside contextual_descriptions in collection_item.extra', async () => {
    const importer = new ThgThemeItemTranslationImporter(context);
    const result = await importer.import();

    expect(setCollectionItemExtraMock).toHaveBeenCalled();
    const extraArg: string = setCollectionItemExtraMock.mock.calls[0][2] as string;
    const extra = JSON.parse(extraArg) as Record<string, unknown>;

    // Both maps should exist
    expect(extra.contextual_descriptions).toBeDefined();
    expect(extra.source_bc_by_language).toBeDefined();

    const bcs = extra.source_bc_by_language as Record<string, string>;
    expect(bcs['eng']).toBe(
      `mwnf3_thematic_gallery:theme_item_i18n:${GALLERY_ID}:${THEME_ID}:${ITEM_ID}:en`
    );
    expect(bcs['fra']).toBe(
      `mwnf3_thematic_gallery:theme_item_i18n:${GALLERY_ID}:${THEME_ID}:${ITEM_ID}:fr`
    );

    expect(result.errors).toHaveLength(0);
  });

  it('source_bc_by_language only contains languages with non-empty contextual_description', async () => {
    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_thematic_gallery.theme_item_i18n')) {
        return [
          {
            gallery_id: GALLERY_ID,
            theme_id: THEME_ID,
            item_id: ITEM_ID,
            language_id: 'en',
            contextual_description: 'Has text',
          },
          {
            gallery_id: GALLERY_ID,
            theme_id: THEME_ID,
            item_id: ITEM_ID,
            language_id: 'fr',
            contextual_description: null, // no text — should not appear in BC map
          },
        ];
      }
      if (sql.includes('FROM mwnf3_thematic_gallery.theme_item')) {
        return [BASE_THEME_ITEM];
      }
      return [];
    });
    context = { ...context, legacyDb: { ...legacyDb, query: queryMock as ILegacyDatabase['query'] } };

    const importer = new ThgThemeItemTranslationImporter(context);
    await importer.import();

    const extraArg: string = setCollectionItemExtraMock.mock.calls[0][2] as string;
    const extra = JSON.parse(extraArg) as Record<string, unknown>;

    const bcs = extra.source_bc_by_language as Record<string, string>;
    expect(Object.keys(bcs)).toEqual(['eng']);
    expect(bcs['fra']).toBeUndefined();
  });
});
