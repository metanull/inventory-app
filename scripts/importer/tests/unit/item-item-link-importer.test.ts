import { beforeEach, describe, expect, it, vi } from 'vitest';

import { ItemItemLinkImporter } from '../../src/importers/phase-01/item-item-link-importer.js';
import { ThgItemRelatedTranslationImporter } from '../../src/importers/phase-10/thg-item-related-translation-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

describe('Item-item link translation idempotency', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeItemItemLinkTranslationMock: ReturnType<typeof vi.fn>;

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
    tracker.setMetadata('default_context_id', 'default-context-id');
    tracker.set('fr', 'fra', 'language');
    tracker.set(
      'mwnf3:link:object_object:EPM:eg:cairo:1:EPM:eg:cairo:2',
      'link-uuid',
      'item_item_link'
    );

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3.objects_objects_justification')) {
        return [
          {
            relation_id: 5312,
            lang_id: 'fr',
            justification: 'Existing justification',
          },
        ];
      }

      if (sql.includes('FROM mwnf3.objects_objects') && !sql.includes('ORDER BY id')) {
        return [
          {
            id: 5312,
            o1_project_id: 'EPM',
            o1_country_id: 'eg',
            o1_museum_id: 'cairo',
            o1_number: 1,
            o2_project_id: 'EPM',
            o2_country_id: 'eg',
            o2_museum_id: 'cairo',
            o2_number: 2,
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

    writeItemItemLinkTranslationMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeItemItemLinkTranslation: writeItemItemLinkTranslationMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('skips existing justification translations before insert', async () => {
    tracker.set(
      'mwnf3:link:object_object:EPM:eg:cairo:1:EPM:eg:cairo:2:justification:fr',
      'existing-translation-id',
      'item_item_link_translation'
    );

    const importer = new ItemItemLinkImporter(context);
    const result = await importer.import();

    expect(writeItemItemLinkTranslationMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
    expect(result.errors).toHaveLength(0);
    expect(result.success).toBe(true);
  });

  it('skips existing THG item-link translations before insert', async () => {
    tracker.set(
      'mwnf3_thematic_gallery:theme_item_related:10:20:30:40',
      'thg-link-uuid',
      'item_item_link'
    );
    tracker.set('en', 'eng', 'language');
    tracker.set(
      'mwnf3_thematic_gallery:theme_item_related_i18n:10:20:30:40:en',
      'existing-thg-translation-id',
      'item_item_link_translation'
    );

    queryMock.mockImplementation(async (sql: string) => {
      if (sql.includes('FROM mwnf3_thematic_gallery.theme_item_related_i18n')) {
        return [
          {
            gallery_id: 10,
            theme_id: 20,
            item_id: 30,
            related_gallery_id: 11,
            related_theme_id: 21,
            related_item_id: 40,
            language_id: 'en',
            contextual_description: 'Context text',
            reciprocal_description: 'Reverse text',
          },
        ];
      }

      return [];
    });

    const importer = new ThgItemRelatedTranslationImporter(context);
    const result = await importer.import();

    expect(writeItemItemLinkTranslationMock).not.toHaveBeenCalled();
    expect(result.skipped).toBe(1);
    expect(result.errors).toHaveLength(0);
    expect(result.success).toBe(true);
  });
});
