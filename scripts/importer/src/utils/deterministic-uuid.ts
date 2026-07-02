/**
 * Deterministic UUID Generation (UUIDv5)
 *
 * Legacy records must receive the SAME UUID every time they are imported,
 * so that re-running a full import (which is normally preceded by a DB wipe,
 * see scripts/importer/README.md) produces identical primary keys instead of
 * fresh random ones. This lets downstream consumers (viewer, exporter,
 * synced image files) treat re-imports as idempotent rather than as a
 * wholesale identity change.
 *
 * We use RFC 4122 UUIDv5 (namespace + name, SHA-1 based) instead of UUIDv4
 * (random) for any entity that has a stable natural key available at
 * creation time — almost always the same `backward_compatibility` string
 * (or, for legacy image files, the legacy relative path) that the importer
 * already computes for deduplication.
 *
 * CRITICAL — DO NOT CHANGE `IMPORTER_UUID_NAMESPACE` ONCE ADOPTED.
 * Every UUID this module has ever produced is derived from
 * SHA1(namespace + name). Changing the namespace constant reassigns every
 * legacy entity's ID on the next import, defeating the entire purpose of
 * this module. Treat it as a frozen, versioned constant — same category of
 * change as a database schema migration.
 *
 * CRITICAL — the exact string passed as `name` is part of the same
 * contract. Any change to how callers build that string (e.g. a change to
 * `formatBackwardCompatibility()`'s separator, field order, or casing)
 * silently reassigns IDs for every affected entity on the next import.
 */

import { v5 as uuidv5 } from 'uuid';

/**
 * Fixed namespace UUID for this importer's deterministic ID generation.
 * Generated once via `uuidv4()` — an arbitrary, non-secret constant that
 * merely partitions this application's UUIDv5 hash space from any other
 * use of UUIDv5 elsewhere. It is NOT a credential; committing it is safe
 * and required (all import runs must use the same namespace to agree on
 * generated IDs).
 */
export const IMPORTER_UUID_NAMESPACE = 'bc112041-0784-4475-80b2-0c96425ac5ea';

/**
 * Derive a deterministic UUID from a natural-key string.
 *
 * The same `name` always produces the same UUID (within this module's
 * namespace). Callers are responsible for building a `name` that is
 * globally unique across all entities that could ever be hashed by this
 * function — in practice this means prefixing with an entity-type/table
 * qualifier before the natural key itself, since some legacy natural keys
 * (e.g. a project's backward_compatibility) are deliberately reused across
 * multiple different target entities (Context, Collection, Project).
 */
export function deterministicUuid(name: string): string {
  return uuidv5(name, IMPORTER_UUID_NAMESPACE);
}
