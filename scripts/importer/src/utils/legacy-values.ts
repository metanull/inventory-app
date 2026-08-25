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
