import { beforeEach, describe, expect, it, vi } from 'vitest';

import { TimelineImporter } from '../../src/importers/phase-05/timeline-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import type { ShLegacyHcrImage, ShLegacyHcrImageText } from '../../src/domain/types/index.js';

describe('TimelineImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let imageRows: ShLegacyHcrImage[];
  let imageTextRows: ShLegacyHcrImageText[];

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
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('en', 'eng', 'language');
    tracker.set('fr', 'fra', 'language');
    tracker.set('mwnf3_sharing_history:sh_hcr:101', 'timeline-event-uuid', 'timeline_event');

    imageRows = [
      {
        hcr_img_id: 495,
        hcr_id: 101,
        ref_item: '',
        item_type: 'obj',
        picture: 'custom/sharinghistory/14.jpg',
        sort_order: 7,
      },
    ];

    imageTextRows = [];

    legacyDb = {
      query: vi.fn(async (sql: string) => {
        if (sql.includes('FROM mwnf3.hcr_events')) {
          return [];
        }

        if (sql.includes('FROM mwnf3.hcr ORDER BY')) {
          return [];
        }

        if (sql.includes('FROM mwnf3_sharing_history.sh_hcr_events')) {
          return [];
        }

        if (sql.includes('FROM mwnf3_sharing_history.sh_hcr ORDER BY')) {
          return [];
        }

        if (sql.includes('FROM mwnf3_sharing_history.sh_hcr_images')) {
          return imageRows;
        }

        if (sql.includes('FROM mwnf3_sharing_history.sh_hcr_image_texts')) {
          return imageTextRows;
        }

        if (sql.includes('FROM mwnf3_sharing_history.rel_sh_bibliography_hcr_country')) {
          return [];
        }

        return [];
      }) as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    strategy = {
      writeTimelineEventImage: vi.fn().mockResolvedValue('timeline-image-uuid'),
      writeTimelineEventItem: vi.fn().mockResolvedValue(undefined),
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      updateTimelineExtra: vi.fn().mockResolvedValue(undefined),
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };
  });

  it('writes standalone timeline event images with default-language alt text', async () => {
    imageTextRows = [
      {
        hcr_img_id: 495,
        lang: 'fr',
        name: 'Legende francaise',
        sname: '',
        name_detail: '',
        detail_justification: '',
        date: '',
        dynasty: '',
        museum: '',
        location: '',
        artist: '',
        material: '',
      },
      {
        hcr_img_id: 495,
        lang: 'en',
        name: 'English caption',
        sname: '',
        name_detail: '',
        detail_justification: '',
        date: '',
        dynasty: '',
        museum: '',
        location: '',
        artist: '',
        material: '',
      },
    ];

    const importer = new TimelineImporter(context);
    const result = await importer.import();

    expect(strategy.writeTimelineEventImage).toHaveBeenCalledWith({
      timeline_event_id: 'timeline-event-uuid',
      path: 'custom/sharinghistory/14.jpg',
      original_name: '14.jpg',
      mime_type: 'image/jpeg',
      size: 1,
      alt_text: 'English caption',
      display_order: 7,
    });
    expect(result.success).toBe(true);
    expect(result.imported).toBe(1);
    expect(result.skipped).toBe(0);
    expect(result.errors).toHaveLength(0);
  });

  it('falls back to the first non-empty standalone image text when default language is absent', async () => {
    imageTextRows = [
      {
        hcr_img_id: 495,
        lang: 'fr',
        name: '',
        sname: '',
        name_detail: '',
        detail_justification: '',
        date: '',
        dynasty: '',
        museum: 'Musee du test',
        location: '',
        artist: '',
        material: '',
      },
    ];

    const importer = new TimelineImporter(context);
    await importer.import();

    expect(strategy.writeTimelineEventImage).toHaveBeenCalledWith(
      expect.objectContaining({ alt_text: 'Musee du test' })
    );
  });
});
