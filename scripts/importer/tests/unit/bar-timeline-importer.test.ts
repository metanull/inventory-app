import { beforeEach, describe, expect, it, vi } from 'vitest';

import { TimelineImporter } from '../../src/importers/phase-05/timeline-importer.js';
import type { BarDedupItem } from '../../src/importers/phase-05/timeline-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import type { LegacyHcr } from '../../src/domain/types/index.js';

// ---------------------------------------------------------------------------
// Shared test fixtures
// ---------------------------------------------------------------------------

const HCR_PT_1500_1800: LegacyHcr = {
  hcr_id: 101,
  country_id: 'pt',
  name: 'Portugal 1500-1800',
  from_ad: 1500,
  to_ad: 1800,
  from_ah: null,
  to_ah: null,
};

const HCR_PT_1000_1300: LegacyHcr = {
  hcr_id: 102,
  country_id: 'pt',
  name: 'Portugal 1000-1300',
  from_ad: 1000,
  to_ad: 1300,
  from_ah: null,
  to_ah: null,
};

const BAR_OBJECT_ROW = {
  project_id: 'BAR',
  country: 'pt',
  museum_id: 'Mus11_A',
  number: '13',
  start_date: '1600',
  end_date: '1700',
};

const BAR_MONUMENT_ROW = {
  project_id: 'BAR',
  country: 'pt',
  institution_id: 'Mon11',
  number: '23',
  start_date: '1550',
  end_date: '1750',
};

// ---------------------------------------------------------------------------
// Helper: build a minimal ImportContext
// ---------------------------------------------------------------------------

function buildContext(overrides?: {
  legacyDb?: Partial<ILegacyDatabase>;
  strategy?: Partial<IWriteStrategy>;
  trackerSetup?: (tracker: UnifiedTracker) => void;
}): ImportContext {
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

  const tracker = new UnifiedTracker();
  tracker.setMetadata('default_language_id', 'eng');
  tracker.set('en', 'eng', 'language');
  tracker.set('pt', 'por', 'language');
  tracker.set('fr', 'fra', 'language');
  // BAR collection registered (simulates phase-01 having run)
  tracker.set('mwnf3:projects:BAR', 'bar-collection-uuid', 'collection');

  if (overrides?.trackerSetup) {
    overrides.trackerSetup(tracker);
  }

  const legacyDb: ILegacyDatabase = {
    query: vi.fn().mockResolvedValue([]),
    execute: vi.fn(),
    connect: vi.fn(),
    disconnect: vi.fn(),
    ...(overrides?.legacyDb ?? {}),
  } as unknown as ILegacyDatabase;

  const strategy: IWriteStrategy = {
    writeTimeline: vi.fn().mockResolvedValue('bar-timeline-uuid'),
    writeTimelineEvent: vi.fn().mockResolvedValue('bar-event-uuid'),
    writeTimelineEventTranslation: vi.fn().mockResolvedValue(undefined),
    writeTimelineEventItem: vi.fn().mockResolvedValue(undefined),
    writeTimelineEventImage: vi.fn().mockResolvedValue('bar-image-uuid'),
    updateTimelineExtra: vi.fn().mockResolvedValue(undefined),
    exists: vi.fn().mockResolvedValue(false),
    findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
  } as unknown as IWriteStrategy;

  const merged = {
    ...strategy,
    ...(overrides?.strategy ?? {}),
  } as IWriteStrategy;

  return {
    legacyDb,
    strategy: merged,
    tracker,
    logger,
    dryRun: false,
  };
}

// ---------------------------------------------------------------------------
// Unit tests: deduplicateBarItems
// ---------------------------------------------------------------------------

describe('TimelineImporter.deduplicateBarItems', () => {
  it('deduplicates object rows with the same PK keeping the first row', () => {
    const ctx = buildContext();
    const importer = new TimelineImporter(ctx);

    const rows = [
      { project_id: 'BAR', country: 'pt', museum_id: 'Mus1', number: '1', start_date: '1600', end_date: '1700' },
      // duplicate — different dates (second language row)
      { project_id: 'BAR', country: 'pt', museum_id: 'Mus1', number: '1', start_date: '1601', end_date: '1701' },
      { project_id: 'BAR', country: 'pt', museum_id: 'Mus2', number: '2', start_date: '1400', end_date: '1500' },
    ];

    const result = importer.deduplicateBarItems(rows, []);
    expect(result).toHaveLength(2);
    expect(result[0]!.backwardCompatibility).toBe('mwnf3:objects:BAR:pt:Mus1:1');
    expect(result[0]!.startDate).toBe(1600); // first row kept
    expect(result[1]!.backwardCompatibility).toBe('mwnf3:objects:BAR:pt:Mus2:2');
  });

  it('deduplicates monument rows with the same PK', () => {
    const ctx = buildContext();
    const importer = new TimelineImporter(ctx);

    const rows = [
      { project_id: 'BAR', country: 'pt', institution_id: 'Mon1', number: '10', start_date: '1550', end_date: '1750' },
      { project_id: 'BAR', country: 'pt', institution_id: 'Mon1', number: '10', start_date: '1551', end_date: '1751' },
    ];

    const result = importer.deduplicateBarItems([], rows);
    expect(result).toHaveLength(1);
    expect(result[0]!.backwardCompatibility).toBe('mwnf3:monuments:BAR:pt:Mon1:10');
    expect(result[0]!.startDate).toBe(1550);
  });

  it('handles null dates gracefully', () => {
    const ctx = buildContext();
    const importer = new TimelineImporter(ctx);

    const rows = [
      { project_id: 'BAR', country: 'es', museum_id: 'Mus3', number: '3', start_date: null, end_date: null },
    ];

    const result = importer.deduplicateBarItems(rows, []);
    expect(result).toHaveLength(1);
    expect(result[0]!.startDate).toBeNull();
    expect(result[0]!.endDate).toBeNull();
  });

  it('combines objects and monuments without collision', () => {
    const ctx = buildContext();
    const importer = new TimelineImporter(ctx);

    const objects = [{ project_id: 'BAR', country: 'pt', museum_id: 'Mus1', number: '1', start_date: '1600', end_date: '1700' }];
    const monuments = [{ project_id: 'BAR', country: 'pt', institution_id: 'Mon1', number: '1', start_date: '1550', end_date: '1750' }];

    const result = importer.deduplicateBarItems(objects, monuments);
    expect(result).toHaveLength(2);
    const bcs = result.map((r) => r.backwardCompatibility);
    expect(bcs).toContain('mwnf3:objects:BAR:pt:Mus1:1');
    expect(bcs).toContain('mwnf3:monuments:BAR:pt:Mon1:1');
  });
});

// ---------------------------------------------------------------------------
// Unit tests: barItemMatchesHcrEvent
// ---------------------------------------------------------------------------

describe('TimelineImporter.barItemMatchesHcrEvent', () => {
  let importer: TimelineImporter;

  beforeEach(() => {
    importer = new TimelineImporter(buildContext());
  });

  it('returns true when item dates are strictly contained within HCR range', () => {
    const item: BarDedupItem = { backwardCompatibility: 'test', country: 'pt', startDate: 1600, endDate: 1700 };
    expect(importer.barItemMatchesHcrEvent(item, HCR_PT_1500_1800)).toBe(true);
  });

  it('returns true when item start equals HCR from_ad (boundary)', () => {
    const item: BarDedupItem = { backwardCompatibility: 'test', country: 'pt', startDate: 1500, endDate: 1700 };
    expect(importer.barItemMatchesHcrEvent(item, HCR_PT_1500_1800)).toBe(true);
  });

  it('returns true when item end equals HCR to_ad (boundary)', () => {
    const item: BarDedupItem = { backwardCompatibility: 'test', country: 'pt', startDate: 1600, endDate: 1800 };
    expect(importer.barItemMatchesHcrEvent(item, HCR_PT_1500_1800)).toBe(true);
  });

  it('returns false when item start_date is before HCR from_ad', () => {
    const item: BarDedupItem = { backwardCompatibility: 'test', country: 'pt', startDate: 1400, endDate: 1700 };
    expect(importer.barItemMatchesHcrEvent(item, HCR_PT_1500_1800)).toBe(false);
  });

  it('returns false when item end_date is after HCR to_ad', () => {
    const item: BarDedupItem = { backwardCompatibility: 'test', country: 'pt', startDate: 1600, endDate: 1900 };
    expect(importer.barItemMatchesHcrEvent(item, HCR_PT_1500_1800)).toBe(false);
  });

  it('returns false when item start_date is null', () => {
    const item: BarDedupItem = { backwardCompatibility: 'test', country: 'pt', startDate: null, endDate: 1700 };
    expect(importer.barItemMatchesHcrEvent(item, HCR_PT_1500_1800)).toBe(false);
  });

  it('returns false when item end_date is null', () => {
    const item: BarDedupItem = { backwardCompatibility: 'test', country: 'pt', startDate: 1600, endDate: null };
    expect(importer.barItemMatchesHcrEvent(item, HCR_PT_1500_1800)).toBe(false);
  });

  it('returns false when item dates are entirely outside HCR range', () => {
    const item: BarDedupItem = { backwardCompatibility: 'test', country: 'pt', startDate: 1850, endDate: 1950 };
    expect(importer.barItemMatchesHcrEvent(item, HCR_PT_1500_1800)).toBe(false);
  });

  it('returns false when item predates HCR range (for different HCR event)', () => {
    const item: BarDedupItem = { backwardCompatibility: 'test', country: 'pt', startDate: 1600, endDate: 1700 };
    expect(importer.barItemMatchesHcrEvent(item, HCR_PT_1000_1300)).toBe(false);
  });
});

// ---------------------------------------------------------------------------
// Integration tests: importBarHcr (via importer.import())
// ---------------------------------------------------------------------------

describe('TimelineImporter BAR timeline import (Step 5)', () => {
  it('skips BAR import gracefully when BAR collection is not found', async () => {
    const tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    // BAR collection NOT registered

    const legacyDb: ILegacyDatabase = {
      query: vi.fn().mockResolvedValue([]),
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    } as unknown as ILegacyDatabase;

    const strategy = {
      writeTimeline: vi.fn(),
      writeTimelineEvent: vi.fn(),
      writeTimelineEventTranslation: vi.fn(),
      writeTimelineEventItem: vi.fn(),
      writeTimelineEventImage: vi.fn().mockResolvedValue('x'),
      updateTimelineExtra: vi.fn(),
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
    } as unknown as IWriteStrategy;

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

    const ctx: ImportContext = { legacyDb, strategy, tracker, logger, dryRun: false };
    const importer = new TimelineImporter(ctx);
    const result = await importer.import();

    // No timeline should be created
    expect(strategy.writeTimeline).not.toHaveBeenCalled();
    // Import should still succeed overall
    expect(result.success).toBe(true);
  });

  it('creates a BAR timeline bound to the BAR collection for a country with HCR rows', async () => {
    const tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3:projects:BAR', 'bar-collection-uuid', 'collection');
    tracker.set('mwnf3:objects:BAR:pt:Mus11_A:13', 'item-uuid-obj', 'item');

    const legacyDb: ILegacyDatabase = {
      query: vi.fn(async (sql: string) => {
        // Generic HCR rows (Step 1)
        if (sql.includes('FROM mwnf3.hcr ORDER BY') && !sql.includes('BAR')) {
          return [HCR_PT_1500_1800];
        }
        if (sql.includes('FROM mwnf3.hcr_events')) {
          return [];
        }
        // SH tables (Steps 2, 3, 4)
        if (sql.includes('mwnf3_sharing_history')) {
          return [];
        }
        // BAR objects (Step 5)
        if (sql.includes("project_id = 'BAR'") && sql.includes('museum_id')) {
          return [BAR_OBJECT_ROW];
        }
        // BAR monuments (Step 5)
        if (sql.includes("project_id = 'BAR'") && sql.includes('institution_id')) {
          return [BAR_MONUMENT_ROW];
        }
        return [];
      }) as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    const strategy = {
      writeTimeline: vi.fn().mockResolvedValue('bar-timeline-uuid'),
      writeTimelineEvent: vi.fn().mockResolvedValue('bar-event-uuid'),
      writeTimelineEventTranslation: vi.fn().mockResolvedValue(undefined),
      writeTimelineEventItem: vi.fn().mockResolvedValue(undefined),
      writeTimelineEventImage: vi.fn().mockResolvedValue('img-uuid'),
      updateTimelineExtra: vi.fn().mockResolvedValue(undefined),
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
    } as unknown as IWriteStrategy;

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

    const ctx: ImportContext = { legacyDb, strategy, tracker, logger, dryRun: false };
    const importer = new TimelineImporter(ctx);
    await importer.import();

    // BAR timeline should be created
    expect(strategy.writeTimeline).toHaveBeenCalledWith(
      expect.objectContaining({
        backward_compatibility: 'mwnf3:hcr:bar:country:pt',
        collection_id: 'bar-collection-uuid',
        internal_name: 'pt — Baroque Art',
      })
    );

    // BAR event should be created with BAR-specific BC (not the generic mwnf3:hcr:101)
    expect(strategy.writeTimelineEvent).toHaveBeenCalledWith(
      expect.objectContaining({
        backward_compatibility: 'mwnf3:hcr:bar:101',
        timeline_id: 'bar-timeline-uuid',
      })
    );
  });

  it('creates item-to-event pivots for BAR items matching the date containment rule', async () => {
    const tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3:projects:BAR', 'bar-collection-uuid', 'collection');
    // Register the BAR object (simulates phase-01 having run)
    tracker.set('mwnf3:objects:BAR:pt:Mus11_A:13', 'item-uuid-obj', 'item');
    // BAR monument — outside range (start_date=1200, end_date=1400) → should NOT be linked
    tracker.set('mwnf3:monuments:BAR:pt:Mon11:23', 'item-uuid-mon', 'item');

    const barObjectInRange = { ...BAR_OBJECT_ROW }; // 1600-1700, within 1500-1800 ✓
    const barMonumentOutOfRange = {
      project_id: 'BAR',
      country: 'pt',
      institution_id: 'Mon11',
      number: '23',
      start_date: '1200',
      end_date: '1400',
    }; // outside 1500-1800 ✗

    const legacyDb: ILegacyDatabase = {
      query: vi.fn(async (sql: string) => {
        if (sql.includes('FROM mwnf3.hcr ORDER BY') && !sql.includes('BAR')) {
          return [HCR_PT_1500_1800];
        }
        if (sql.includes('FROM mwnf3.hcr_events')) return [];
        if (sql.includes('mwnf3_sharing_history')) return [];
        if (sql.includes("project_id = 'BAR'") && sql.includes('museum_id')) {
          return [barObjectInRange];
        }
        if (sql.includes("project_id = 'BAR'") && sql.includes('institution_id')) {
          return [barMonumentOutOfRange];
        }
        return [];
      }) as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    const strategy = {
      writeTimeline: vi.fn().mockResolvedValue('bar-timeline-uuid'),
      writeTimelineEvent: vi.fn().mockResolvedValue('bar-event-uuid'),
      writeTimelineEventTranslation: vi.fn().mockResolvedValue(undefined),
      writeTimelineEventItem: vi.fn().mockResolvedValue(undefined),
      writeTimelineEventImage: vi.fn().mockResolvedValue('img-uuid'),
      updateTimelineExtra: vi.fn().mockResolvedValue(undefined),
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
    } as unknown as IWriteStrategy;

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

    const ctx: ImportContext = { legacyDb, strategy, tracker, logger, dryRun: false };
    const importer = new TimelineImporter(ctx);
    await importer.import();

    // Only the in-range object should be associated
    expect(strategy.writeTimelineEventItem).toHaveBeenCalledTimes(1);
    expect(strategy.writeTimelineEventItem).toHaveBeenCalledWith(
      expect.objectContaining({
        timeline_event_id: 'bar-event-uuid',
        item_id: 'item-uuid-obj',
      })
    );
  });

  it('does not create item pivot when item UUID is not found in tracker or DB', async () => {
    const tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3:projects:BAR', 'bar-collection-uuid', 'collection');
    // Item NOT registered in tracker

    const legacyDb: ILegacyDatabase = {
      query: vi.fn(async (sql: string) => {
        if (sql.includes('FROM mwnf3.hcr ORDER BY') && !sql.includes('BAR')) {
          return [HCR_PT_1500_1800];
        }
        if (sql.includes('FROM mwnf3.hcr_events')) return [];
        if (sql.includes('mwnf3_sharing_history')) return [];
        if (sql.includes("project_id = 'BAR'") && sql.includes('museum_id')) {
          return [BAR_OBJECT_ROW]; // item is in-range but UUID not found
        }
        if (sql.includes("project_id = 'BAR'") && sql.includes('institution_id')) {
          return [];
        }
        return [];
      }) as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    const strategy = {
      writeTimeline: vi.fn().mockResolvedValue('bar-timeline-uuid'),
      writeTimelineEvent: vi.fn().mockResolvedValue('bar-event-uuid'),
      writeTimelineEventTranslation: vi.fn().mockResolvedValue(undefined),
      writeTimelineEventItem: vi.fn().mockResolvedValue(undefined),
      writeTimelineEventImage: vi.fn().mockResolvedValue('img-uuid'),
      updateTimelineExtra: vi.fn().mockResolvedValue(undefined),
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null), // item not in DB either
    } as unknown as IWriteStrategy;

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

    const ctx: ImportContext = { legacyDb, strategy, tracker, logger, dryRun: false };
    const importer = new TimelineImporter(ctx);
    await importer.import();

    // No item pivot should be written when item UUID cannot be resolved
    expect(strategy.writeTimelineEventItem).not.toHaveBeenCalled();
    // A warning should be logged (logWarning passes undefined as second arg when details omitted)
    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('Item not found for backward_compatibility'),
      undefined
    );
  });

  it('skips timeline creation in dry-run mode without writing to DB', async () => {
    const tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3:projects:BAR', 'bar-collection-uuid', 'collection');
    tracker.set('mwnf3:objects:BAR:pt:Mus11_A:13', 'item-uuid-obj', 'item');

    const legacyDb: ILegacyDatabase = {
      query: vi.fn(async (sql: string) => {
        if (sql.includes('FROM mwnf3.hcr ORDER BY') && !sql.includes('BAR')) {
          return [HCR_PT_1500_1800];
        }
        if (sql.includes('FROM mwnf3.hcr_events')) return [];
        if (sql.includes('mwnf3_sharing_history')) return [];
        if (sql.includes("project_id = 'BAR'") && sql.includes('museum_id')) {
          return [BAR_OBJECT_ROW];
        }
        if (sql.includes("project_id = 'BAR'") && sql.includes('institution_id')) return [];
        return [];
      }) as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    const strategy = {
      writeTimeline: vi.fn(),
      writeTimelineEvent: vi.fn(),
      writeTimelineEventTranslation: vi.fn(),
      writeTimelineEventItem: vi.fn(),
      writeTimelineEventImage: vi.fn().mockResolvedValue('img-uuid'),
      updateTimelineExtra: vi.fn(),
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
    } as unknown as IWriteStrategy;

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

    const ctx: ImportContext = { legacyDb, strategy, tracker, logger, dryRun: true };
    const importer = new TimelineImporter(ctx);
    const result = await importer.import();

    // In dry-run, write methods should not be called
    expect(strategy.writeTimeline).not.toHaveBeenCalled();
    expect(strategy.writeTimelineEvent).not.toHaveBeenCalled();
    expect(strategy.writeTimelineEventItem).not.toHaveBeenCalled();
    // But counts should still be non-zero
    expect(result.imported).toBeGreaterThan(0);
    expect(result.success).toBe(true);
  });

  it('skips BAR timeline for country when already imported (idempotent)', async () => {
    const tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3:projects:BAR', 'bar-collection-uuid', 'collection');
    // Mark BAR pt timeline as already imported
    tracker.set('mwnf3:hcr:bar:country:pt', 'existing-timeline-uuid', 'timeline');

    const legacyDb: ILegacyDatabase = {
      query: vi.fn(async (sql: string) => {
        if (sql.includes('FROM mwnf3.hcr ORDER BY') && !sql.includes('BAR')) {
          return [HCR_PT_1500_1800];
        }
        if (sql.includes('FROM mwnf3.hcr_events')) return [];
        if (sql.includes('mwnf3_sharing_history')) return [];
        if (sql.includes("project_id = 'BAR'") && sql.includes('museum_id')) {
          return [BAR_OBJECT_ROW];
        }
        if (sql.includes("project_id = 'BAR'") && sql.includes('institution_id')) return [];
        return [];
      }) as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    const strategy = {
      writeTimeline: vi.fn(),
      writeTimelineEvent: vi.fn(),
      writeTimelineEventTranslation: vi.fn(),
      writeTimelineEventItem: vi.fn(),
      writeTimelineEventImage: vi.fn().mockResolvedValue('img-uuid'),
      updateTimelineExtra: vi.fn(),
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
    } as unknown as IWriteStrategy;

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

    const ctx: ImportContext = { legacyDb, strategy, tracker, logger, dryRun: false };
    const importer = new TimelineImporter(ctx);
    await importer.import();

    // Timeline should NOT be re-created
    expect(strategy.writeTimeline).not.toHaveBeenCalledWith(
      expect.objectContaining({ backward_compatibility: 'mwnf3:hcr:bar:country:pt' })
    );
  });

  it('creates BAR event translations when hcr_events rows exist', async () => {
    const tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('en', 'eng', 'language');
    tracker.set('pt', 'por', 'language');
    tracker.set('mwnf3:projects:BAR', 'bar-collection-uuid', 'collection');

    const hcrEventTranslation = {
      hcr_id: 101,
      lang_id: 'en',
      name: 'Portugal Renaissance',
      description: 'Baroque art period',
      datedesc_ah: null,
      datedesc_ad: '1500-1800',
    };

    const legacyDb: ILegacyDatabase = {
      query: vi.fn(async (sql: string) => {
        if (sql.includes('FROM mwnf3.hcr ORDER BY') && !sql.includes('BAR')) {
          return [HCR_PT_1500_1800];
        }
        if (sql.includes('FROM mwnf3.hcr_events')) {
          return [hcrEventTranslation];
        }
        if (sql.includes('mwnf3_sharing_history')) return [];
        if (sql.includes("project_id = 'BAR'") && sql.includes('museum_id')) return [];
        if (sql.includes("project_id = 'BAR'") && sql.includes('institution_id')) return [];
        return [];
      }) as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    const strategy = {
      writeTimeline: vi.fn().mockResolvedValue('bar-timeline-uuid'),
      writeTimelineEvent: vi.fn().mockResolvedValue('bar-event-uuid'),
      writeTimelineEventTranslation: vi.fn().mockResolvedValue(undefined),
      writeTimelineEventItem: vi.fn().mockResolvedValue(undefined),
      writeTimelineEventImage: vi.fn().mockResolvedValue('img-uuid'),
      updateTimelineExtra: vi.fn().mockResolvedValue(undefined),
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
    } as unknown as IWriteStrategy;

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

    const ctx: ImportContext = { legacyDb, strategy, tracker, logger, dryRun: false };
    const importer = new TimelineImporter(ctx);
    await importer.import();

    expect(strategy.writeTimelineEventTranslation).toHaveBeenCalledWith(
      expect.objectContaining({
        timeline_event_id: 'bar-event-uuid',
        language_id: 'eng',
        name: 'Portugal Renaissance',
      })
    );
  });

  it('existing mwnf3 generic timelines (Step 1) are not affected by BAR import', async () => {
    const tracker = new UnifiedTracker();
    tracker.setMetadata('default_language_id', 'eng');
    tracker.set('en', 'eng', 'language');
    tracker.set('mwnf3:projects:BAR', 'bar-collection-uuid', 'collection');

    const writeTimelineCalls: unknown[] = [];

    const legacyDb: ILegacyDatabase = {
      query: vi.fn(async (sql: string) => {
        if (sql.includes('FROM mwnf3.hcr ORDER BY') && !sql.includes('BAR')) {
          return [HCR_PT_1500_1800];
        }
        if (sql.includes('FROM mwnf3.hcr_events')) return [];
        if (sql.includes('mwnf3_sharing_history')) return [];
        // Return a BAR object so barCountries is non-empty and BAR timeline gets created
        if (sql.includes("project_id = 'BAR'") && sql.includes('museum_id')) {
          return [BAR_OBJECT_ROW];
        }
        if (sql.includes("project_id = 'BAR'") && sql.includes('institution_id')) return [];
        return [];
      }) as ILegacyDatabase['query'],
      execute: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    const strategy = {
      writeTimeline: vi.fn(async (data: { backward_compatibility: string }) => {
        writeTimelineCalls.push(data.backward_compatibility);
        return 'some-timeline-uuid';
      }),
      writeTimelineEvent: vi.fn().mockResolvedValue('some-event-uuid'),
      writeTimelineEventTranslation: vi.fn().mockResolvedValue(undefined),
      writeTimelineEventItem: vi.fn().mockResolvedValue(undefined),
      writeTimelineEventImage: vi.fn().mockResolvedValue('img-uuid'),
      updateTimelineExtra: vi.fn().mockResolvedValue(undefined),
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
    } as unknown as IWriteStrategy;

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

    const ctx: ImportContext = { legacyDb, strategy, tracker, logger, dryRun: false };
    const importer = new TimelineImporter(ctx);
    await importer.import();

    // Both the generic mwnf3 timeline and the BAR timeline should be created separately
    expect(writeTimelineCalls).toContain('mwnf3:hcr:country:pt');
    expect(writeTimelineCalls).toContain('mwnf3:hcr:bar:country:pt');
  });
});
