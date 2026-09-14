<?php

namespace App\Support\Importer;

use Ramsey\Uuid\Uuid;

/**
 * PHP mirror of the Node importer's deterministic UUID generation.
 *
 * The importer (scripts/importer/src/utils/deterministic-uuid.ts) derives every
 * legacy-sourced entity's UUID from `uuidv5(name, IMPORTER_UUID_NAMESPACE)`, so
 * that re-running a full import produces identical primary keys instead of
 * fresh random ones. For a Collection specifically, the `name` hashed is
 * `"collection:" . strtolower(backward_compatibility)`
 * (see scripts/importer/src/strategies/sql-strategy.ts, `writeCollection()`).
 *
 * CRITICAL — this class must stay in lockstep with the TypeScript importer.
 * Never change NAMESPACE, and never change how `forCollection()` builds its
 * input string, without changing scripts/importer/src/utils/deterministic-uuid.ts
 * and scripts/importer/src/strategies/sql-strategy.ts in the same change — doing
 * either alone reassigns every collection's UUID on the next import while this
 * class keeps computing the old one (or vice versa).
 *
 * Out of scope for this class: it is read-only tooling for the artisan lookup
 * commands (`importer:find-collection`, `importer:list-collections`). It must
 * never be used to assign an id at write time — the importer alone owns that.
 */
class DeterministicUuid
{
    /**
     * Frozen namespace UUID, copied verbatim from
     * scripts/importer/src/utils/deterministic-uuid.ts::IMPORTER_UUID_NAMESPACE.
     */
    public const NAMESPACE = 'bc112041-0784-4475-80b2-0c96425ac5ea';

    /**
     * Derive a deterministic UUID from an arbitrary natural-key string, exactly
     * as the importer's `deterministicUuid()` does.
     */
    public static function forName(string $name): string
    {
        return Uuid::uuid5(self::NAMESPACE, $name)->toString();
    }

    /**
     * Derive the UUID the importer assigns to a Collection with the given
     * `backward_compatibility` value.
     */
    public static function forCollection(string $backwardCompatibility): string
    {
        return self::forName('collection:'.mb_strtolower($backwardCompatibility));
    }
}
