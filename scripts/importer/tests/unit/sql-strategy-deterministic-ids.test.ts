/**
 * Tests for SqlWriteStrategy – deterministic (UUIDv5) primary key generation
 *
 * These tests verify that re-running the importer against a freshly wiped
 * database (the documented workflow, see scripts/importer/README.md)
 * produces IDENTICAL primary keys for the same legacy record, and that
 * entities which legitimately share a backward_compatibility value (the
 * Context/Collection/Project trio) or a legacy path (dual item_images rows
 * for a "first image") never collide on the same UUID.
 */

import { describe, it, expect, vi } from 'vitest';
import { SqlWriteStrategy } from '../../src/strategies/sql-strategy.js';
import type {
  ItemData,
  ItemTranslationData,
  ItemImageData,
  ContextData,
  CollectionData,
  ProjectData,
} from '../../src/core/types.js';
import type { ITracker } from '../../src/core/tracker.js';

function makeMockDb() {
  const calls: { sql: string; values: unknown[] }[] = [];
  return {
    calls,
    db: {
      execute: vi.fn(async (sql: string, values?: unknown) => {
        calls.push({ sql, values: (values ?? []) as unknown[] });
        return [{}, []];
      }),
      end: vi.fn(async () => {}),
      beginTransaction: vi.fn(async () => {}),
      commit: vi.fn(async () => {}),
      rollback: vi.fn(async () => {}),
    },
  };
}

function makeMockTracker(): ITracker {
  return {
    register: vi.fn(),
    exists: vi.fn().mockReturnValue(false),
    getUuid: vi.fn().mockReturnValue(null),
    set: vi.fn(),
    getByType: vi.fn().mockReturnValue([]),
    getStats: vi.fn(),
    getAll: vi.fn().mockReturnValue([]),
    clear: vi.fn(),
    setMetadata: vi.fn(),
    getMetadata: vi.fn().mockReturnValue(null),
  } as unknown as ITracker;
}

/** Build a fresh strategy instance — simulates a brand new import process/run. */
function makeStrategy(): SqlWriteStrategy {
  const mock = makeMockDb();
  return new SqlWriteStrategy(
    mock.db as unknown as ConstructorParameters<typeof SqlWriteStrategy>[0],
    makeMockTracker()
  );
}

describe('SqlWriteStrategy — deterministic IDs are stable across separate runs', () => {
  it('writeItem: same backward_compatibility yields the same id across two independent strategy instances', async () => {
    const data: ItemData = {
      internal_name: 'Test Object',
      backward_compatibility: 'mwnf3:objects:WAL:eg:12:34',
      type: 'object',
      collection_id: 'col-1',
      partner_id: 'partner-1',
    };

    const idRun1 = await makeStrategy().writeItem(data);
    const idRun2 = await makeStrategy().writeItem(data);

    expect(idRun1).toBe(idRun2);
  });

  it('writeItem: different backward_compatibility yields different ids', async () => {
    const strategy = makeStrategy();
    const base: ItemData = {
      internal_name: 'Test Object',
      backward_compatibility: 'mwnf3:objects:WAL:eg:12:34',
      type: 'object',
    };

    const idA = await strategy.writeItem(base);
    const idB = await strategy.writeItem({
      ...base,
      backward_compatibility: 'mwnf3:objects:WAL:eg:12:35',
    });

    expect(idA).not.toBe(idB);
  });

  it('writeItemTranslation: different languages of the same item yield different ids', async () => {
    const strategy = makeStrategy();
    const base: ItemTranslationData = {
      item_id: 'item-1',
      language_id: 'eng',
      context_id: 'ctx-1',
      backward_compatibility: 'mwnf3:objects:WAL:eg:12:34',
      name: 'Name',
      description: 'Description',
    };

    await strategy.writeItemTranslation(base);
    await strategy.writeItemTranslation({ ...base, language_id: 'fra' });

    const mockCalls = (strategy as unknown as { db: { execute: { mock: { calls: unknown[][] } } } })
      .db.execute.mock.calls;
    const ids = mockCalls.map((call) => (call[1] as unknown[])[0] as string);
    expect(new Set(ids).size).toBe(2);
  });

  it('Context/Collection/Project sharing the same backward_compatibility get distinct ids', async () => {
    const strategy = makeStrategy();
    const sharedBc = 'mwnf3:projects:wal';

    const contextData: ContextData = {
      internal_name: 'WAL Context',
      backward_compatibility: sharedBc,
    };
    const collectionData: CollectionData = {
      internal_name: 'WAL Collection',
      backward_compatibility: sharedBc,
      context_id: 'ctx-x',
      language_id: 'eng',
    };
    const projectData: ProjectData = {
      internal_name: 'WAL Project',
      backward_compatibility: sharedBc,
      context_id: 'ctx-x',
      language_id: 'eng',
    };

    const contextId = await strategy.writeContext(contextData);
    const collectionId = await strategy.writeCollection(collectionData);
    const projectId = await strategy.writeProject(projectData);

    expect(new Set([contextId, collectionId, projectId]).size).toBe(3);
  });

  it('writeItemImage: same path attached to two different owning items yields distinct ids (first-image case)', async () => {
    const strategy = makeStrategy();
    const path = 'objects/wal/eg/museum01/34/1.jpg';

    const childImageId = await strategy.writeItemImage({
      item_id: 'picture-item-1',
      path,
      original_name: '1.jpg',
      mime_type: 'image/jpeg',
      size: 1,
      display_order: 1,
    } satisfies ItemImageData);

    const parentImageId = await strategy.writeItemImage({
      item_id: 'parent-item-1',
      path,
      original_name: '1.jpg',
      mime_type: 'image/jpeg',
      size: 1,
      display_order: 1,
    } satisfies ItemImageData);

    expect(childImageId).not.toBe(parentImageId);
  });

  it('writeItemImage: same (item_id, path) pair yields the same id across two independent strategy instances', async () => {
    const data: ItemImageData = {
      item_id: 'picture-item-1',
      path: 'objects/wal/eg/museum01/34/1.jpg',
      original_name: '1.jpg',
      mime_type: 'image/jpeg',
      size: 1,
      display_order: 1,
    };

    const idRun1 = await makeStrategy().writeItemImage(data);
    const idRun2 = await makeStrategy().writeItemImage(data);

    expect(idRun1).toBe(idRun2);
  });

  it('writeItemImage: an explicit id (e.g. preserved from AvailableImage) always wins', async () => {
    const strategy = makeStrategy();
    const id = await strategy.writeItemImage({
      id: 'explicit-fixed-id',
      item_id: 'picture-item-1',
      path: 'objects/wal/eg/museum01/34/1.jpg',
      original_name: '1.jpg',
      mime_type: 'image/jpeg',
      size: 1,
      display_order: 1,
    });

    expect(id).toBe('explicit-fixed-id');
  });
});
