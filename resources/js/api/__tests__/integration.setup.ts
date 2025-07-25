// Integration test setup
// This file is loaded before integration tests run

/// <reference lib="dom" />

// Export to make this file a module
export {}

// Interface to match the localStorage API
interface LocalStorageInterface {
  readonly length: number
  clear(): void
  getItem(key: string): string | null
  key(index: number): string | null
  removeItem(key: string): void
  setItem(key: string, value: string): void
}

// Mock localStorage for Node.js environment
if (typeof globalThis.localStorage === 'undefined') {
  const localStorageMock: LocalStorageInterface & { storage: Map<string, string> } = {
    storage: new Map<string, string>(),
    getItem(key: string): string | null {
      return this.storage.get(key) || null
    },
    setItem(key: string, value: string): void {
      this.storage.set(key, value)
    },
    removeItem(key: string): void {
      this.storage.delete(key)
    },
    clear(): void {
      this.storage.clear()
    },
    get length(): number {
      return this.storage.size
    },
    key(index: number): string | null {
      const keys: string[] = Array.from(this.storage.keys())
      const key = keys[index]
      return key !== undefined ? key : null
    },
  }

  globalThis.localStorage = localStorageMock as LocalStorageInterface
}

// Reduce console noise during integration tests
// Store original console methods
const originalConsoleError = console.error

// Override console.error to filter out expected API errors
console.error = (...args: unknown[]) => {
  const message = args.join(' ')

  // Skip logging full error objects for expected API errors in integration tests
  if (
    message.includes('Error occurred:') &&
    (message.includes('Request failed with status code 422') ||
      message.includes('Request failed with status code 404') ||
      message.includes('Request failed with status code 401') ||
      message.includes('Request failed with status code 500') ||
      message.includes('UNIQUE constraint failed') ||
      message.includes('Integrity constraint violation'))
  ) {
    // Log a concise version instead
    if (message.includes('status code 500') && message.includes('UNIQUE constraint')) {
      console.error('Expected duplicate constraint error (500)')
    } else if (message.includes('status code 422')) {
      console.error('Expected validation error (422)')
    } else if (message.includes('status code 404')) {
      console.error('Expected not found error (404)')
    } else if (message.includes('status code 401')) {
      console.error('Expected authentication error (401)')
    }
    return // Skip logging the full error
  }

  // Log other errors normally
  originalConsoleError.apply(console, args)
}

// Set test environment variables
globalThis.process = globalThis.process || {}
globalThis.process.env = globalThis.process.env || {}

// Set default API base URL for integration tests
if (!globalThis.process.env.VITE_API_BASE_URL) {
  globalThis.process.env.VITE_API_BASE_URL = 'http://127.0.0.1:8000/api'
}

// Set default test credentials for integration tests
if (!globalThis.process.env.VITE_TEST_USER_EMAIL) {
  globalThis.process.env.VITE_TEST_USER_EMAIL = 'user@example.com'
}

if (!globalThis.process.env.VITE_TEST_USER_PASSWORD) {
  globalThis.process.env.VITE_TEST_USER_PASSWORD = 'password'
}

console.log('Integration test setup complete')
console.log('API Base URL:', globalThis.process.env.VITE_API_BASE_URL)
console.log('Test User Email:', globalThis.process.env.VITE_TEST_USER_EMAIL)

// Helper functions for integration tests
export const getTestCredentials = () => ({
  email: globalThis.process.env.VITE_TEST_USER_EMAIL || 'user@example.com',
  password: globalThis.process.env.VITE_TEST_USER_PASSWORD || 'password',
})

export const getApiBaseUrl = () => {
  return globalThis.process.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'
}
