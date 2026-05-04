/**
 * Tests for SqlWriteStrategy – provenance persistence
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import { SqlWriteStrategy } from '../../src/strategies/sql-strategy.js';
import type { ItemTranslationData } from '../../src/core/types.js';
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
    },
  };
}

function makeMockTracker(): ITracker {
  return {
    set: vi.fn(),
    get: vi.fn().mockReturnValue(null),
    has: vi.fn().mockReturnValue(false),
    resolve: vi.fn().mockResolvedValue(null),
  } as unknown as ITracker;
}

describe('SqlWriteStrategy.writeItemTranslation – provenance', () => {
  let mock: ReturnType<typeof makeMockDb>;
  let strategy: SqlWriteStrategy;

  beforeEach(() => {
    mock = makeMockDb();
    strategy = new SqlWriteStrategy(
      mock.db as unknown as ConstructorParameters<typeof SqlWriteStrategy>[0],
      makeMockTracker()
    );
  });

  it('persists provenance to item_translations.provenance', async () => {
    const data: ItemTranslationData = {
      item_id: 'item-uuid-001',
      language_id: 'eng',
      context_id: 'ctx-uuid-001',
      backward_compatibility: 'mwnf3:objects:ISL:eg:Mus01:1',
      name: 'Test Object',
      description: 'A test description',
      method_for_provenance: 'Purchased at auction.',
      provenance: 'Egypt, probably Cairo.',
    };

    await strategy.writeItemTranslation(data);

    expect(mock.calls.length).toBe(1);
    const call = mock.calls[0]!;

    // Verify provenance column is present in the SQL
    expect(call.sql).toContain('provenance');

    // Verify the provenance column comes after method_for_provenance
    const provenanceIdx = call.sql.indexOf('provenance');
    const methodIdx = call.sql.indexOf('method_for_provenance');
    expect(provenanceIdx).toBeGreaterThan(methodIdx);

    // Verify the value array contains the provenance text
    expect(call.values).toContain('Egypt, probably Cairo.');
    expect(call.values).toContain('Purchased at auction.');

    // Verify the value for provenance appears after method_for_provenance in the values array
    const values = call.values as (string | null)[];
    const methodForProvenancePos = values.indexOf('Purchased at auction.');
    const provenancePos = values.indexOf('Egypt, probably Cairo.');
    expect(provenancePos).toBe(methodForProvenancePos + 1);
  });

  it('writes null for provenance when not provided', async () => {
    const data: ItemTranslationData = {
      item_id: 'item-uuid-002',
      language_id: 'eng',
      context_id: 'ctx-uuid-002',
      backward_compatibility: 'mwnf3:objects:ISL:eg:Mus01:2',
      name: 'No Provenance Object',
      description: 'Description without provenance',
    };

    await strategy.writeItemTranslation(data);

    expect(mock.calls.length).toBe(1);
    const call = mock.calls[0]!;
    expect(call.sql).toContain('provenance');
  });

  it('keeps method_for_provenance unchanged when provenance is also set', async () => {
    const data: ItemTranslationData = {
      item_id: 'item-uuid-003',
      language_id: 'eng',
      context_id: 'ctx-uuid-003',
      backward_compatibility: 'mwnf3:objects:ISL:eg:Mus01:3',
      name: 'Object With Both',
      description: 'Has both method and provenance',
      method_for_provenance: 'Gift from donor.',
      provenance: 'Egypt, probably Cairo.',
    };

    await strategy.writeItemTranslation(data);

    const values = mock.calls[0]!.values as (string | null)[];
    expect(values).toContain('Gift from donor.');
    expect(values).toContain('Egypt, probably Cairo.');
  });
});
