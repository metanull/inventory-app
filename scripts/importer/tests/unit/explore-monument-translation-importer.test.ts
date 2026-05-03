import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ExploreMonumentTranslationImporter } from '../../src/importers/phase-06/explore-monument-translation-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('ExploreMonumentTranslationImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeItemTranslationMock: ReturnType<typeof vi.fn>;

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
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3_travels:monument:IAM:pt:1:I:1:b', 'canonical-travel-item-uuid', 'item');

    queryMock = vi.fn(async (sql: string) => {
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

      if (sql.includes('FROM mwnf3_explore.exploremonumentext')) {
        return [
          {
            monumentId: 150,
            langId: 'en',
            name: 'Travel-linked monument',
            description: 'Explore description',
            related_bibliography: null,
            date: null,
            styles: null,
            prepared_by: null,
            how_to_reach: null,
            info: null,
            contact: null,
            history: null,
            note: null,
            abstract: null,
            further_reading: null,
            url_prog_pdf: null,
            pdf_text: null,
            url_prog_doc: null,
            institution: null,
            address: null,
            phone: null,
            fax: null,
            email: null,
            website: null,
          },
        ];
      }

      if (sql.includes('FROM mwnf3_explore.exploremonument_further_reading')) {
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

    writeItemTranslationMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeItemTranslation: writeItemTranslationMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('writes Explore-context text onto the resolved source item for referenced monuments', async () => {
    const importer = new ExploreMonumentTranslationImporter(context);
    const result = await importer.import();

    expect(writeItemTranslationMock).toHaveBeenCalledWith(
      expect.objectContaining({
        item_id: 'canonical-travel-item-uuid',
        context_id: 'explore-context-uuid',
        backward_compatibility: 'mwnf3_explore:monument:150:translation:eng',
        name: 'Travel-linked monument',
      })
    );
    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
  });
});