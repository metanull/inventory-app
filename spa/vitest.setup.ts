/**
 * Vitest setup file for test utilities and environment setup
 */

// Node.js 22+ ships a built-in localStorage that shadows jsdom's proper
// Web Storage implementation.  The built-in object lacks getItem/setItem/etc.,
// which causes "localStorage.getItem is not a function" errors.
// Replace it with a spec-compliant in-memory shim when the methods are missing.
if (typeof localStorage === 'undefined' || typeof localStorage.getItem !== 'function') {
  const store = new Map<string, string>()
  const storage = {
    getItem: (key: string) => store.get(key) ?? null,
    setItem: (key: string, value: string) => store.set(key, String(value)),
    removeItem: (key: string) => store.delete(key),
    clear: () => store.clear(),
    get length() {
      return store.size
    },
    key: (index: number) => [...store.keys()][index] ?? null,
  }
  Object.defineProperty(globalThis, 'localStorage', { value: storage, writable: true })
}

export {}
