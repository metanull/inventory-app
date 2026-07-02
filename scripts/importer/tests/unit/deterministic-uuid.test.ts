/**
 * Tests for deterministic UUID (UUIDv5) generation
 */

import { describe, it, expect } from 'vitest';
import { validate as validateUuid, version as uuidVersion } from 'uuid';
import { deterministicUuid, IMPORTER_UUID_NAMESPACE } from '../../src/utils/deterministic-uuid.js';

describe('deterministicUuid', () => {
  it('produces a valid RFC 4122 UUID', () => {
    const id = deterministicUuid('item:mwnf3:objects:WAL:eg:12:34');
    expect(validateUuid(id)).toBe(true);
  });

  it('produces a UUIDv5 (not v4)', () => {
    const id = deterministicUuid('item:mwnf3:objects:WAL:eg:12:34');
    expect(uuidVersion(id)).toBe(5);
  });

  it('is deterministic: same name always yields the same UUID', () => {
    const name = 'item:mwnf3:objects:WAL:eg:12:34';
    const first = deterministicUuid(name);
    const second = deterministicUuid(name);
    const third = deterministicUuid(name);
    expect(first).toBe(second);
    expect(second).toBe(third);
  });

  it('produces different UUIDs for different names', () => {
    const a = deterministicUuid('item:mwnf3:objects:WAL:eg:12:34');
    const b = deterministicUuid('item:mwnf3:objects:WAL:eg:12:35');
    expect(a).not.toBe(b);
  });

  it('is sensitive to an entity-type prefix even when the rest of the name is identical', () => {
    // Regression guard: Context/Collection/Project intentionally share the same
    // backward_compatibility value for a given legacy project. Without an
    // entity-type qualifier in the hashed name, all three would collide on
    // the same UUID.
    const sharedKey = 'mwnf3:projects:wal';
    const contextId = deterministicUuid(`context:${sharedKey}`);
    const collectionId = deterministicUuid(`collection:${sharedKey}`);
    const projectId = deterministicUuid(`project:${sharedKey}`);

    expect(new Set([contextId, collectionId, projectId]).size).toBe(3);
  });

  it('is sensitive to a language/context qualifier for translation rows', () => {
    // Regression guard: translation rows (item_translations, etc.) reuse the
    // parent's backward_compatibility across every language row. Without
    // folding in language_id (and context_id), every language variant of the
    // same item's translation would collide on the same UUID.
    const bc = 'mwnf3:objects:WAL:eg:12:34';
    const en = deterministicUuid(`item_translation:${bc}:eng:ctx-1`);
    const fr = deterministicUuid(`item_translation:${bc}:fra:ctx-1`);
    const enEpm = deterministicUuid(`item_translation:${bc}:eng:ctx-2`);

    expect(new Set([en, fr, enEpm]).size).toBe(3);
  });

  it('is sensitive to the owning entity for image rows sharing the same legacy path', () => {
    // Regression guard: object-picture-importer writes the SAME legacy path
    // to two different item_images rows (the child "picture" Item and, for
    // the first image, the parent Item too). Path alone would collide on the
    // UUID primary key.
    const path = 'mwnf3/objects/wal/eg/12/34/1.jpg';
    const childImageId = deterministicUuid(`image:child-item-uuid:${path.toLowerCase()}`);
    const parentImageId = deterministicUuid(`image:parent-item-uuid:${path.toLowerCase()}`);

    expect(childImageId).not.toBe(parentImageId);
  });

  it('is case-insensitive when callers lowercase the natural key (matches tracker semantics)', () => {
    // The in-memory tracker normalizes backward_compatibility to lowercase
    // before comparing; callers of deterministicUuid must do the same for
    // consistent results. This test documents the expectation, not the
    // function's own behavior (it does no normalization itself).
    const lower = deterministicUuid('item:mwnf3:objects:wal');
    const upperInput = deterministicUuid('item:mwnf3:objects:WAL'.toLowerCase());
    expect(lower).toBe(upperInput);
  });

  it('uses the frozen namespace constant', () => {
    // Documents the exact namespace value so an accidental change is caught
    // by this test rather than silently reassigning every legacy ID.
    expect(IMPORTER_UUID_NAMESPACE).toBe('bc112041-0784-4475-80b2-0c96425ac5ea');
  });
});
