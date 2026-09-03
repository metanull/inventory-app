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
  let getExtraMock: ReturnType<typeof vi.fn>;
  let setExtraMock: ReturnType<typeof vi.fn>;
  let existsForContextMock: ReturnType<typeof vi.fn>;

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
    getExtraMock = vi.fn().mockResolvedValue(null);
    setExtraMock = vi.fn().mockResolvedValue(undefined);
    existsForContextMock = vi.fn().mockResolvedValue(false);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeItemTranslation: writeItemTranslationMock,
      getItemTranslationExtra: getExtraMock,
      setItemTranslationExtra: setExtraMock,
      itemTranslationExistsForContext: existsForContextMock,
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

  describe('when the translation row already exists', () => {
    beforeEach(() => {
      // writeItemTranslation is a plain INSERT — a second run must not call
      // it for a row that's already there. entityExistsAsync checks the
      // tracker first, so seeding it here is what makes `alreadyExists` true.
      tracker.set(
        'mwnf3_explore:monument:150:translation:eng',
        'existing-translation-uuid',
        'item_translation'
      );
      queryMock.mockImplementation(async (sql: string) => {
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
              history: 'See more<br/>here',
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
        return [];
      });
    });

    it('refreshes extra instead of skipping, converting HTML the earlier write missed', async () => {
      getExtraMock.mockResolvedValue({ history: 'See more<br/>here' });

      const importer = new ExploreMonumentTranslationImporter(context);
      const result = await importer.import();

      expect(writeItemTranslationMock).not.toHaveBeenCalled();
      expect(setExtraMock).toHaveBeenCalledWith(
        'canonical-travel-item-uuid',
        'eng',
        JSON.stringify({ history: 'See more  \nhere' })
      );
      expect(result.success).toBe(true);
      expect(result.imported).toBe(1);
    });

    it('is a no-op when the stored extra already matches what would be written', async () => {
      getExtraMock.mockResolvedValue({ history: 'See more  \nhere' });

      const importer = new ExploreMonumentTranslationImporter(context);
      const result = await importer.import();

      expect(writeItemTranslationMock).not.toHaveBeenCalled();
      expect(setExtraMock).not.toHaveBeenCalled();
      expect(result.success).toBe(true);
      expect(result.skipped).toBe(1);
    });

    it('performs no writes in dry-run mode', async () => {
      getExtraMock.mockResolvedValue({ history: 'See more<br/>here' });
      context = { ...context, dryRun: true };

      const importer = new ExploreMonumentTranslationImporter(context);
      const result = await importer.import();

      expect(writeItemTranslationMock).not.toHaveBeenCalled();
      expect(setExtraMock).not.toHaveBeenCalled();
      expect(result.imported).toBe(1);
    });
  });

  describe('when a different monumentId already resolved to the same item/language/context', () => {
    // ExploreMonumentResolver can legitimately map several distinct legacy
    // monumentIds onto the same target item (vm/travels/sharing-history
    // cross-references) — this monumentId's own BC never existed, but the
    // database's real uniqueness constraint (item, language, context) is
    // already taken by whichever monumentId got there first.
    it('skips cleanly instead of colliding on the database constraint', async () => {
      existsForContextMock.mockResolvedValue(true);

      const importer = new ExploreMonumentTranslationImporter(context);
      const result = await importer.import();

      expect(existsForContextMock).toHaveBeenCalledWith(
        'canonical-travel-item-uuid',
        'eng',
        'explore-context-uuid'
      );
      expect(writeItemTranslationMock).not.toHaveBeenCalled();
      expect(result.success).toBe(true);
      expect(result.skipped).toBe(1);
      expect(result.imported).toBe(0);
    });
  });

  describe('resolvedCandidates (one legacy monumentId, several possible target items)', () => {
    // monumentId 200 has no unambiguous target: a 'vm' reference and a
    // 'travels' reference both resolve to a real (but different) item, so
    // ExploreMonumentResolver reports mode 'resolvedCandidates' and every
    // candidate gets its own write attempt.
    beforeEach(() => {
      tracker.set('mwnf3:monuments:BAR:at:Mon5:9', 'vm-candidate-item-uuid', 'item');
      tracker.set(
        'mwnf3_travels:monument:IAM:pt:2:I:1:c',
        'travels-candidate-item-uuid',
        'item'
      );

      queryMock.mockImplementation(async (sql: string) => {
        if (sql.includes('FROM mwnf3_explore.exploremonumentext')) {
          return [
            {
              monumentId: 200,
              langId: 'en',
              name: 'Ambiguous monument',
              description: null,
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
        if (sql.includes('FROM mwnf3_explore.exploremonument_vm')) {
          return [
            {
              monumentId: 200,
              REF_monuments_project_id: 'BAR',
              REF_monuments_country: 'at',
              REF_monuments_institution_id: 'Mon5',
              REF_monuments_number: 9,
            },
          ];
        }
        if (sql.includes('FROM mwnf3_explore.exploremonument_tr')) {
          return [
            {
              monumentId: 200,
              REF_tr_monuments_project_id: 'IAM',
              REF_tr_monuments_country: 'pt',
              REF_tr_monuments_itinerary_id: 'I',
              REF_tr_monuments_location_id: '1',
              REF_tr_monuments_number: 'c',
              REF_tr_monuments_trail_id: 2,
            },
          ];
        }
        return [];
      });
    });

    it('skips a candidate whose target already has a translation for this language/context', async () => {
      // Simulates: the 'vm' candidate item was already covered (by this run
      // or an earlier one, via any source) — only the still-uncovered
      // 'travels' candidate should be written.
      existsForContextMock.mockImplementation(
        async (itemId: string) => itemId === 'vm-candidate-item-uuid'
      );

      const importer = new ExploreMonumentTranslationImporter(context);
      await importer.import();

      expect(writeItemTranslationMock).toHaveBeenCalledTimes(1);
      expect(writeItemTranslationMock).toHaveBeenCalledWith(
        expect.objectContaining({ item_id: 'travels-candidate-item-uuid' })
      );
    });

    it('writes every candidate when none is covered yet', async () => {
      const importer = new ExploreMonumentTranslationImporter(context);
      await importer.import();

      expect(writeItemTranslationMock).toHaveBeenCalledTimes(2);
    });
  });
});