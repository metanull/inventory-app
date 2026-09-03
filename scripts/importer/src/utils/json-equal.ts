/**
 * Deep, key-order-independent equality for values read from a MySQL JSON
 * column.
 *
 * MySQL's JSON type does not preserve the key order a value was inserted
 * with — reading it back yields keys in MySQL's own canonical order, not
 * insertion order. `JSON.stringify(a) === JSON.stringify(b)` is therefore
 * unusable to detect "nothing changed" against a freshly-built JS object:
 * it very rarely matches even when every value is identical, which means a
 * refresh/idempotency check built on it never converges — it rewrites the
 * same content, in the same shape, on every single run.
 */
export function jsonValuesEqual(a: unknown, b: unknown): boolean {
  if (a === b) return true;
  if (typeof a !== typeof b) return false;
  if (a === null || b === null) return false;
  if (typeof a !== 'object') return false;

  if (Array.isArray(a) || Array.isArray(b)) {
    if (!Array.isArray(a) || !Array.isArray(b)) return false;
    if (a.length !== b.length) return false;
    return a.every((item, i) => jsonValuesEqual(item, b[i]));
  }

  const aKeys = Object.keys(a as Record<string, unknown>);
  const bKeys = Object.keys(b as Record<string, unknown>);
  if (aKeys.length !== bKeys.length) return false;

  return aKeys.every((key) =>
    jsonValuesEqual(
      (a as Record<string, unknown>)[key],
      (b as Record<string, unknown>)[key]
    )
  );
}
