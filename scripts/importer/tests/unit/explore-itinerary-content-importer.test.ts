import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ExploreItineraryContentImporter } from '../../src/importers/phase-06/explore-itinerary-content-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ExploreItineraryContentImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeCollectionTranslationMock: ReturnType<typeof vi.fn>;
  let writeCollectionItemMock: ReturnType<typeof vi.fn>;

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
    tracker.set('mwnf3_explore:context', 'explore-context-uuid', 'context');
    tracker.set('mwnf3_explore:root:explore_by_itinerary', 'explore-by-itinerary-root', 'collection');
    tracker.set('mwnf3_explore:itinerary:7', 'itinerary-7-uuid', 'collection');
    tracker.set('mwnf3_explore:itinerary:8', 'itinerary-8-uuid', 'collection');
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3_travels:monument:IAM:pt:1:I:1:b', 'resolved-travel-monument-uuid', 'item');

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_explore.explore_itineraries_langs')) {
        return [
          {
            itineraries_id: 7,
            langId: 'en',
            title: 'The Seat of the Sultanate',
            introduction: '',
            duration: 'One day',
            local_team: '',
            author: null,
            introd_type: 'ET-short#-',
            et_title: 'IAM;eg;I;en;1',
            et_introduction: '',
          },
          {
            itineraries_id: 8,
            langId: 'en',
            title: null,
            introduction: '',
            duration: null,
            local_team: '',
            author: null,
            introd_type: null,
            et_title: null,
            et_introduction: null,
          },
        ];
      }

      if (sql.includes('FROM mwnf3_travels.tr_itineraries')) {
        return [
          {
            description:
              'When Salah al-Din al-Ayyubi had his Citadel built, his desire was for it to be both a defensive fortress and the seat of the Sultanate.',
          },
        ];
      }

      if (sql.includes('FROM mwnf3_explore.explore_itineraries_rel_monuments')) {
        return [
          {
            itineraries_id: 7,
            monumentId: 150,
            mn_order: 6,
            desc_types: null,
            explore_mn_desc: null,
            tr_mn_desc: null,
            vm_mn_desc: null,
          },
        ];
      }

      if (sql.includes('FROM mwnf3_explore.exploremonument_tr')) {
        return [
          {
            monumentId: 150,
            REF_tr_monuments_project_id: 'IAM',
            REF_tr_monuments_country: 'pt',
            REF_tr_monuments_itinerary_id: 'I',
            REF_tr_monuments_location_id: '1',
            REF_tr_monuments_number: 'b',
            REF_tr_monuments_trail_id: 1,
          },
        ];
      }

      if (sql.includes('FROM mwnf3_explore.exploremonument_vm')) {
        return [];
      }

      if (sql.includes('FROM mwnf3_explore.exploremonument_sh')) {
        return [];
      }

      if (sql.includes('FROM mwnf3_explore.exploremonument')) {
        return [
          {
            monumentId: 150,
            REF_tr_monuments_project_id: null,
            REF_tr_monuments_country: null,
            REF_tr_monuments_itinerary_id: null,
            REF_tr_monuments_location_id: null,
            REF_tr_monuments_number: null,
            REF_tr_monuments_lang: null,
            REF_tr_monuments_trail_id: null,
            REF_monuments_project_id: null,
            REF_monuments_country: null,
            REF_monuments_institution_id: null,
            REF_monuments_number: null,
            REF_monuments_lang: null,
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
    writeCollectionItemMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeCollectionTranslation: writeCollectionTranslationMock,
      writeCollectionItem: writeCollectionItemMock,
      getCollectionTranslationExtra: vi.fn().mockResolvedValue(null),
      setCollectionTranslationExtra: vi.fn().mockResolvedValue(undefined),
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('imports the real itinerary title and falls back to the linked Travels description when Explore introduction is empty', async () => {
    const importer = new ExploreItineraryContentImporter(context);
    const result = await importer.import();

    expect(writeCollectionTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({
        collection_id: 'itinerary-7-uuid',
        title: 'The Seat of the Sultanate',
        description:
          'When Salah al-Din al-Ayyubi had his Citadel built, his desire was for it to be both a defensive fortress and the seat of the Sultanate.',
        extra: expect.stringContaining('One day'),
      })
    );
    expect(logger.warning).toHaveBeenCalledWith(
      'Explore itinerary mwnf3_explore:itinerary:8 is missing a required source title for en, skipping translation',
      undefined
    );
    expect(result.success).toBe(true);
  });

  it('uses the resolved source item for itinerary membership instead of an Explore shell item', async () => {
    const importer = new ExploreItineraryContentImporter(context);
    await importer.import();

    expect(writeCollectionItemMock).toHaveBeenCalledWith(
      expect.objectContaining({
        collection_id: 'itinerary-7-uuid',
        item_id: 'resolved-travel-monument-uuid',
        backward_compatibility: 'mwnf3_explore:itinerary_monument:7:150',
        display_order: 6,
      })
    );
  });
});