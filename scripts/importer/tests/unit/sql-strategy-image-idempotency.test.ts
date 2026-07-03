/**
 * Tests for SqlWriteStrategy — image writes are idempotent across process
 * restarts (a non-wiped rerun of the importer).
 *
 * item_images, partner_images, partner_logos, collection_images,
 * contributor_images, and timeline_event_images have no
 * backward_compatibility column, so SqlWriteStrategy.exists() /
 * findByBackwardCompatibility() can never detect "already imported" for
 * them from a fresh process (see TABLES_WITHOUT_BC in sql-strategy.ts).
 * Instead, each write derives a deterministic id from (owner, legacy path)
 * and treats a MySQL duplicate-key error on that id as "already imported,
 * return the existing id" rather than a failure.
 */

import { describe, it, expect, vi } from 'vitest';
import { SqlWriteStrategy } from '../../src/strategies/sql-strategy.js';
import type {
  ItemImageData,
  PartnerImageData,
  PartnerLogoData,
  CollectionImageData,
  ContributorImageData,
  TimelineEventImageData,
} from '../../src/core/types.js';
import type { ITracker } from '../../src/core/tracker.js';

/** MySQL2's shape for a duplicate-key error (ER_DUP_ENTRY / errno 1062). */
function makeDuplicateKeyError(): Error & { code: string; errno: number } {
  const error = new Error("Duplicate entry 'x' for key 'PRIMARY'") as Error & {
    code: string;
    errno: number;
  };
  error.code = 'ER_DUP_ENTRY';
  error.errno = 1062;
  return error;
}

/** A DB mock whose `execute` throws a duplicate-key error on every call after the first. */
function makeMockDbFailingOnSecondInsert() {
  let callCount = 0;
  return {
    execute: vi.fn(async () => {
      callCount++;
      if (callCount > 1) {
        throw makeDuplicateKeyError();
      }
      return [{}, []];
    }),
    end: vi.fn(async () => {}),
    beginTransaction: vi.fn(async () => {}),
    commit: vi.fn(async () => {}),
    rollback: vi.fn(async () => {}),
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

describe('SqlWriteStrategy — image writes survive a duplicate-key hit (non-wiped rerun)', () => {
  it('writeItemImage: a duplicate-key error on the second write returns the same id, not a throw', async () => {
    const mockDb = makeMockDbFailingOnSecondInsert();
    const strategy = new SqlWriteStrategy(
      mockDb as unknown as ConstructorParameters<typeof SqlWriteStrategy>[0],
      makeMockTracker()
    );
    const data: ItemImageData = {
      item_id: 'picture-item-1',
      path: 'objects/wal/eg/museum01/34/1.jpg',
      original_name: '1.jpg',
      mime_type: 'image/jpeg',
      size: 1,
      display_order: 1,
    };

    const firstRunId = await strategy.writeItemImage(data);
    const secondRunId = await strategy.writeItemImage(data); // simulates a rerun's INSERT hitting the same row

    expect(secondRunId).toBe(firstRunId);
  });

  it('writeItemImage: a non-duplicate-key error still propagates', async () => {
    const mockDb = {
      execute: vi.fn(async () => {
        throw new Error('connection lost');
      }),
      end: vi.fn(async () => {}),
      beginTransaction: vi.fn(async () => {}),
      commit: vi.fn(async () => {}),
      rollback: vi.fn(async () => {}),
    };
    const strategy = new SqlWriteStrategy(
      mockDb as unknown as ConstructorParameters<typeof SqlWriteStrategy>[0],
      makeMockTracker()
    );

    await expect(
      strategy.writeItemImage({
        item_id: 'picture-item-1',
        path: 'objects/wal/eg/museum01/34/1.jpg',
        original_name: '1.jpg',
        mime_type: 'image/jpeg',
        size: 1,
        display_order: 1,
      })
    ).rejects.toThrow('connection lost');
  });

  it('writePartnerImage: idempotent across a simulated rerun', async () => {
    const mockDb = makeMockDbFailingOnSecondInsert();
    const strategy = new SqlWriteStrategy(
      mockDb as unknown as ConstructorParameters<typeof SqlWriteStrategy>[0],
      makeMockTracker()
    );
    const data: PartnerImageData = {
      partner_id: 'partner-1',
      path: 'museums/museum01/1.jpg',
      original_name: '1.jpg',
      mime_type: 'image/jpeg',
      size: 1,
      display_order: 1,
    };

    expect(await strategy.writePartnerImage(data)).toBe(await strategy.writePartnerImage(data));
  });

  it('writePartnerLogo: idempotent across a simulated rerun', async () => {
    const mockDb = makeMockDbFailingOnSecondInsert();
    const strategy = new SqlWriteStrategy(
      mockDb as unknown as ConstructorParameters<typeof SqlWriteStrategy>[0],
      makeMockTracker()
    );
    const data: PartnerLogoData = {
      partner_id: 'partner-1',
      path: 'museums/museum01/logo.jpg',
      original_name: 'logo.jpg',
      mime_type: 'image/jpeg',
      size: 1,
      display_order: 1,
    };

    expect(await strategy.writePartnerLogo(data)).toBe(await strategy.writePartnerLogo(data));
  });

  it('writeCollectionImage: idempotent across a simulated rerun', async () => {
    const mockDb = makeMockDbFailingOnSecondInsert();
    const strategy = new SqlWriteStrategy(
      mockDb as unknown as ConstructorParameters<typeof SqlWriteStrategy>[0],
      makeMockTracker()
    );
    const data: CollectionImageData = {
      collection_id: 'collection-1',
      path: 'collections/wal/1.jpg',
      original_name: '1.jpg',
      mime_type: 'image/jpeg',
      size: 1,
      display_order: 1,
    };

    expect(await strategy.writeCollectionImage(data)).toBe(
      await strategy.writeCollectionImage(data)
    );
  });

  it('writeContributorImage: idempotent across a simulated rerun', async () => {
    const mockDb = makeMockDbFailingOnSecondInsert();
    const strategy = new SqlWriteStrategy(
      mockDb as unknown as ConstructorParameters<typeof SqlWriteStrategy>[0],
      makeMockTracker()
    );
    const data: ContributorImageData = {
      contributor_id: 'contributor-1',
      path: 'contributors/1.jpg',
      original_name: '1.jpg',
      mime_type: 'image/jpeg',
      size: 1,
      display_order: 1,
    };

    expect(await strategy.writeContributorImage(data)).toBe(
      await strategy.writeContributorImage(data)
    );
  });

  it('writeTimelineEventImage: idempotent across a simulated rerun', async () => {
    const mockDb = makeMockDbFailingOnSecondInsert();
    const strategy = new SqlWriteStrategy(
      mockDb as unknown as ConstructorParameters<typeof SqlWriteStrategy>[0],
      makeMockTracker()
    );
    const data: TimelineEventImageData = {
      timeline_event_id: 'event-1',
      path: 'timeline/event1/1.jpg',
      original_name: '1.jpg',
      mime_type: 'image/jpeg',
      size: 1,
      display_order: 1,
    };

    expect(await strategy.writeTimelineEventImage(data)).toBe(
      await strategy.writeTimelineEventImage(data)
    );
  });
});
