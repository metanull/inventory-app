/**
 * Helpers for normalising raw legacy column values before they are stored in
 * the new model.
 */

/**
 * Normalise a MySQL `bit(1)` column to a JSON boolean.
 *
 * mysql2 returns `bit` columns as Node Buffers. Storing one in an `extra` JSON
 * field serialises it as `{"type":"Buffer","data":[0]}` instead of `false`,
 * which is unusable downstream — `extra` must hold proper JSON values.
 *
 * Accepts the other shapes a driver or a test fixture may produce (number,
 * boolean, numeric string). Returns null when the source value is null or
 * undefined, so "unset" stays distinguishable from `false`.
 */
export function bitToBoolean(value: unknown): boolean | null {
  if (value === null || value === undefined) {
    return null;
  }

  if (typeof value === 'boolean') {
    return value;
  }

  if (typeof value === 'number') {
    return value !== 0;
  }

  // mysql2 returns bit(1) as a Buffer holding a single byte.
  if (Buffer.isBuffer(value)) {
    return value.length > 0 && value[0] !== 0;
  }

  if (typeof value === 'string') {
    if (value === '') {
      return null;
    }
    // Covers both the textual forms ("0" / "1") and the raw single-byte string
    // a driver may hand back, whose first char code is 0 or 1.
    return value !== '0' && value.charCodeAt(0) !== 0;
  }

  return null;
}

/**
 * The JSON shape `JSON.stringify` produces for a Node Buffer.
 *
 * An importer that stored a mysql2 `bit(1)` value without running it through
 * bitToBoolean leaves `{"type":"Buffer","data":[1]}` in the `extra` column
 * instead of a usable boolean.
 */
interface SerializedBuffer {
  type: 'Buffer';
  data: number[];
}

function isSerializedBuffer(value: unknown): value is SerializedBuffer {
  if (typeof value !== 'object' || value === null || Array.isArray(value)) {
    return false;
  }
  const candidate = value as Record<string, unknown>;
  return (
    candidate.type === 'Buffer' &&
    Array.isArray(candidate.data) &&
    candidate.data.every((byte) => typeof byte === 'number')
  );
}

/**
 * Recursively replace serialized single-byte Buffers with JSON booleans.
 *
 * Only single-byte payloads are converted: those are the `bit(1)` columns that
 * should have been booleans all along. A longer Buffer is real binary data and
 * is left untouched, so this is safe to run over an arbitrary `extra` object.
 *
 * Structurally shares unchanged branches — the returned value is the input
 * itself when nothing needed converting, which callers can use to skip a write.
 */
export function normalizeSerializedBitBuffers(value: unknown): unknown {
  if (isSerializedBuffer(value)) {
    return value.data.length === 1 ? bitToBoolean(Buffer.from(value.data)) : value;
  }

  if (Array.isArray(value)) {
    let changed = false;
    const next = value.map((entry) => {
      const normalized = normalizeSerializedBitBuffers(entry);
      if (normalized !== entry) changed = true;
      return normalized;
    });
    return changed ? next : value;
  }

  if (typeof value === 'object' && value !== null) {
    let changed = false;
    const next: Record<string, unknown> = {};
    for (const [key, entry] of Object.entries(value as Record<string, unknown>)) {
      const normalized = normalizeSerializedBitBuffers(entry);
      if (normalized !== entry) changed = true;
      next[key] = normalized;
    }
    return changed ? next : value;
  }

  return value;
}
