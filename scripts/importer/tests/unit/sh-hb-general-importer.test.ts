import { beforeEach, describe, expect, it, vi } from 'vitest';

import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import { ShHbGeneralImporter } from '../../src/importers/phase-11/sh-hb-general-importer.js';

describe('ShHbGeneralImporter', () => {
  let tracker: UnifiedTracker;
  let legacyDb: ILegacyDatabase;
  let strategy: IWriteStrategy;
  let context: ImportContext;
  let queryMock: ReturnType<typeof vi.fn>;
  let writeCollectionMock: ReturnType<typeof vi.fn>;
  let writeTranslationMock: ReturnType<typeof vi.fn>;

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

    queryMock = vi.fn(async (sql: string) => {
      if (sql.includes('FROM mwnf3_sharing_history.sh_projects ')) {
        // The WHERE clause keeps only public SP projects — AWE.
        return [{ project_id: 'AWE' }];
      }
      if (sql.includes('FROM mwnf3_sharing_history.sh_project_about_historical_background_texts')) {
        return [
          { page_id: 1, lang: 'en', title: 'Arab Perspective', desc: '<b>Arab</b> text' },
          { page_id: 2, lang: 'en', title: 'Ottoman Perspective', desc: 'Ottoman text' },
          { page_id: 3, lang: 'en', title: 'European Perspective', desc: 'European text' },
        ];
      }
      if (sql.includes('FROM mwnf3_sharing_history.sh_project_about_historical_background ')) {
        return [{ page_id: 1 }, { page_id: 2 }, { page_id: 3 }];
      }
      if (sql.includes('FROM mwnf3_sharing_history.sh_project_about_topics_texts')) {
        return [
          { topic_id: 1, lang: 'en', title: 'The Rise of Nationalism', desc: 'Topic text 1' },
          { topic_id: 2, lang: 'en', title: 'The Young Turks', desc: 'Topic text 2' },
        ];
      }
      if (sql.includes('FROM mwnf3_sharing_history.sh_project_about_topics ')) {
        return [
          { topic_id: 1, sort_order: 1 },
          { topic_id: 2, sort_order: 2 },
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

    let nextId = 0;
    writeCollectionMock = vi.fn(async () => `col-uuid-${++nextId}`);
    writeTranslationMock = vi.fn().mockResolvedValue(undefined);

    strategy = {
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
      writeCollection: writeCollectionMock,
      writeCollectionTranslation: writeTranslationMock,
    } as unknown as IWriteStrategy;

    context = {
      legacyDb,
      strategy,
      tracker,
      logger,
      dryRun: false,
    };

    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'awe-collection-uuid', 'collection');
    tracker.set('mwnf3_sharing_history:sh_projects:awe', 'awe-context-uuid', 'context');
    tracker.set('en', 'eng', 'language');
    tracker.setMetadata('default_language_id', 'eng');
  });

  it('creates the marker, perspective pages, topics root and topics with the AWE hierarchy', async () => {
    const importer = new ShHbGeneralImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    // 1 HB root + 3 perspectives + 1 topics root + 2 topics
    expect(writeCollectionMock).toHaveBeenCalledTimes(7);
    expect(result.imported).toBe(7);

    const byBc = new Map(
      writeCollectionMock.mock.calls.map((c) => [c[0].backward_compatibility, c[0]])
    );

    const root = byBc.get('mwnf3_sharing_history:sh_project_about_historical_background:root:awe');
    expect(root).toBeDefined();
    expect(root.parent_id).toBe('awe-collection-uuid');
    expect(root.context_id).toBe('awe-context-uuid');

    const rootUuid = tracker.getUuid(
      'mwnf3_sharing_history:sh_project_about_historical_background:root:awe',
      'collection'
    );
    const page1 = byBc.get('mwnf3_sharing_history:sh_project_about_historical_background:awe:1');
    expect(page1.parent_id).toBe(rootUuid);
    expect(page1.display_order).toBe(1);

    const topicsRoot = byBc.get('mwnf3_sharing_history:sh_project_about_topics:root:awe');
    expect(topicsRoot.parent_id).toBe(rootUuid);

    const topicsRootUuid = tracker.getUuid(
      'mwnf3_sharing_history:sh_project_about_topics:root:awe',
      'collection'
    );
    const topic2 = byBc.get('mwnf3_sharing_history:sh_project_about_topics:awe:2');
    expect(topic2.parent_id).toBe(topicsRootUuid);
    expect(topic2.display_order).toBe(2);
  });

  it('writes translations with HTML converted to Markdown and typed extra', async () => {
    const importer = new ShHbGeneralImporter(context);
    await importer.import();

    const perspectiveCall = writeTranslationMock.mock.calls
      .map((c) => c[0])
      .find(
        (t) =>
          t.backward_compatibility ===
          'mwnf3_sharing_history:sh_project_about_historical_background:awe:1:en'
      );
    expect(perspectiveCall).toBeDefined();
    expect(perspectiveCall.title).toBe('Arab Perspective');
    expect(perspectiveCall.description).toContain('**Arab**');
    expect(JSON.parse(perspectiveCall.extra)).toEqual({ hb_general_type: 'perspective' });
    expect(perspectiveCall.language_id).toBe('eng');

    const topicCall = writeTranslationMock.mock.calls
      .map((c) => c[0])
      .find(
        (t) =>
          t.backward_compatibility === 'mwnf3_sharing_history:sh_project_about_topics:awe:1:en'
      );
    expect(JSON.parse(topicCall.extra)).toEqual({ hb_general_type: 'topic' });
  });

  it('scopes legacy queries to the project and to public SP projects', async () => {
    const importer = new ShHbGeneralImporter(context);
    await importer.import();

    const sqls = queryMock.mock.calls.map((c) => c[0] as string);
    expect(sqls.find((s) => s.includes('sh_projects '))).toContain(`\`show\` = 'Y'`);
    expect(sqls.find((s) => s.includes('sh_project_about_topics '))).toContain(
      `project_id = 'AWE'`
    );
    expect(sqls.find((s) => s.includes('sh_project_about_historical_background '))).toContain(
      `project_id = 'AWE'`
    );
  });

  it('is idempotent — existing collections are skipped, children still resolve', async () => {
    tracker.set(
      'mwnf3_sharing_history:sh_project_about_historical_background:root:awe',
      'existing-root-uuid',
      'collection'
    );

    const importer = new ShHbGeneralImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(result.skipped).toBe(1);
    // Root skipped, the other 6 still created under the existing root.
    expect(writeCollectionMock).toHaveBeenCalledTimes(6);
    const page1 = writeCollectionMock.mock.calls
      .map((c) => c[0])
      .find(
        (s) =>
          s.backward_compatibility ===
          'mwnf3_sharing_history:sh_project_about_historical_background:awe:1'
      );
    expect(page1.parent_id).toBe('existing-root-uuid');
  });

  it('performs no writes in dry-run mode but walks the whole subtree', async () => {
    context = { ...context, dryRun: true };

    const importer = new ShHbGeneralImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(writeCollectionMock).not.toHaveBeenCalled();
    expect(writeTranslationMock).not.toHaveBeenCalled();
    expect(result.imported).toBe(7);
  });

  it('skips gracefully when the project root collection is missing', async () => {
    tracker = new UnifiedTracker();
    tracker.set('en', 'eng', 'language');
    tracker.setMetadata('default_language_id', 'eng');
    context = { ...context, tracker };

    const importer = new ShHbGeneralImporter(context);
    const result = await importer.import();

    expect(result.success).toBe(true);
    expect(writeCollectionMock).not.toHaveBeenCalled();
    expect(logger.warning).toHaveBeenCalledWith(
      expect.stringContaining('mwnf3_sharing_history:sh_projects:awe'),
      undefined
    );
  });
});
