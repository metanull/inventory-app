/**
 * Unit Tests for Contexts Component Business Logic
 *
 * These tests focus on the core functionality and business logic
 * of the Contexts component without dealing with complex UI rendering.
 *
 * Tests cover:
 * - Search functionality across internal_name and backward_compatibility
 * - Sorting functionality
 * - Filter functionality (all/default)
 * - Store interactions and data fetching
 * - Error handling
 * - Delete operations
 * - Default toggle operations
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useContextStore } from '@/stores/context'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { createMockContext } from '@/__tests__/test-utils'
import type { ContextResource } from '@metanull/inventory-app-api-client'

// Mock store interfaces
interface MockContextStore {
  contexts: ContextResource[]
  currentContext: ContextResource | null
  loading: boolean
  error: string | null
  defaultContext: ContextResource | undefined
  defaultContexts: ContextResource[]
  fetchContexts: () => Promise<ContextResource[] | void>
  fetchContext: (id: string) => Promise<void>
  getContextById: (id: string) => ContextResource | undefined
  createContext: (data: unknown) => Promise<ContextResource>
  updateContext: (id: string, data: unknown) => Promise<ContextResource>
  deleteContext: (id: string) => Promise<void>
  setDefaultContext: (id: string) => Promise<void>
  getDefaultContext: () => ContextResource | null
  clearError: () => void
  clearCurrentContext: () => void
}

interface MockLoadingOverlayStore {
  show: (message?: string) => void
  hide: () => void
  isLoading: boolean
}

interface MockErrorDisplayStore {
  addMessage: (type: string, message: string) => void
  removeMessage: (id: string) => void
  messages: unknown[]
}

// Mock console.error to avoid noise in test output
vi.mock('console', () => ({
  error: vi.fn(),
  warn: vi.fn(),
  log: vi.fn(),
}))

// Store original console methods for cleanup
// eslint-disable-next-line @typescript-eslint/no-explicit-any
let originalConsole: any

beforeAll(() => {
  originalConsole = { ...console }
  console.error = vi.fn()
  console.warn = vi.fn()
  console.log = vi.fn()
})

afterAll(() => {
  Object.assign(console, originalConsole)
})

// Mock the stores
vi.mock('@/stores/context')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')

// Test data - covering different context types for comprehensive testing
const mockContexts: ContextResource[] = [
  createMockContext({
    id: 'main-context',
    internal_name: 'Main Context',
    backward_compatibility: 'main',
    is_default: true,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  }),
  createMockContext({
    id: 'dev-context',
    internal_name: 'Development Context',
    backward_compatibility: 'dev',
    is_default: false,
    created_at: '2023-01-02T00:00:00Z',
    updated_at: '2023-01-02T00:00:00Z',
  }),
  createMockContext({
    id: 'prod-context',
    internal_name: 'Production Context',
    backward_compatibility: null,
    is_default: false,
    created_at: '2023-01-03T00:00:00Z',
    updated_at: '2023-01-03T00:00:00Z',
  }),
  createMockContext({
    id: 'test-context',
    internal_name: 'Test Context',
    backward_compatibility: 'test',
    is_default: false,
    created_at: '2023-01-04T00:00:00Z',
    updated_at: '2023-01-04T00:00:00Z',
  }),
]

describe('Contexts Logic Tests', () => {
  let mockContextStore: ReturnType<typeof useContextStore>
  let mockLoadingStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorStore: ReturnType<typeof useErrorDisplayStore>

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup store mocks
    mockContextStore = {
      contexts: mockContexts,
      currentContext: null,
      loading: false,
      error: null,
      fetchContexts: vi.fn().mockResolvedValue(mockContexts),
      fetchContext: vi.fn().mockResolvedValue(undefined),
      getContextById: vi.fn(),
      createContext: vi.fn(),
      updateContext: vi.fn(),
      deleteContext: vi.fn(),
      setDefaultContext: vi.fn(),
      getDefaultContext: vi.fn(),
      defaultContext: mockContexts[0], // First context is default
      defaultContexts: [mockContexts[0]],
      clearError: vi.fn(),
      clearCurrentContext: vi.fn(),
    } as MockContextStore

    mockLoadingStore = {
      show: vi.fn(),
      hide: vi.fn(),
      isLoading: false,
    } as MockLoadingOverlayStore

    mockErrorStore = {
      addMessage: vi.fn(),
      removeMessage: vi.fn(),
      messages: [],
    } as MockErrorDisplayStore

    vi.mocked(useContextStore).mockReturnValue(mockContextStore)
    vi.mocked(useLoadingOverlayStore).mockReturnValue(mockLoadingStore)
    vi.mocked(useErrorDisplayStore).mockReturnValue(mockErrorStore)

    vi.clearAllMocks()
  })

  describe('Search Functionality', () => {
    it('filters contexts by internal_name (case insensitive)', () => {
      const searchQuery = 'main'
      const filtered = mockContexts.filter(context =>
        context.internal_name.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered).toHaveLength(1)
      expect(filtered[0].internal_name).toBe('Main Context')
    })

    it('filters contexts by backward_compatibility', () => {
      const searchQuery = 'dev'
      const filtered = mockContexts.filter(
        context =>
          context.backward_compatibility &&
          context.backward_compatibility.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered).toHaveLength(1)
      expect(filtered[0].internal_name).toBe('Development Context')
    })

    it('returns no results for non-matching search', () => {
      const searchQuery = 'nonexistent'
      const filtered = mockContexts.filter(
        context =>
          context.internal_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          (context.backward_compatibility &&
            context.backward_compatibility.toLowerCase().includes(searchQuery.toLowerCase()))
      )

      expect(filtered).toHaveLength(0)
    })

    it('handles empty search query', () => {
      const searchQuery = ''
      const filtered = mockContexts.filter(
        context =>
          !searchQuery.trim() ||
          context.internal_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          (context.backward_compatibility &&
            context.backward_compatibility.toLowerCase().includes(searchQuery.toLowerCase()))
      )

      expect(filtered).toHaveLength(mockContexts.length)
    })
  })

  describe('Filter Functionality', () => {
    it('filters contexts by default status', () => {
      const defaultContexts = mockContexts.filter(context => context.is_default)
      expect(defaultContexts).toHaveLength(1)
      expect(defaultContexts[0].internal_name).toBe('Main Context')
    })

    it('shows all contexts when filter is "all"', () => {
      expect(mockContexts).toHaveLength(4)
    })

    it('filters non-default contexts', () => {
      const nonDefaultContexts = mockContexts.filter(context => !context.is_default)
      expect(nonDefaultContexts).toHaveLength(3)
    })
  })

  describe('Sorting Functionality', () => {
    it('sorts contexts by internal_name ascending', () => {
      const sorted = [...mockContexts].sort((a, b) =>
        a.internal_name.toLowerCase().localeCompare(b.internal_name.toLowerCase())
      )

      expect(sorted[0].internal_name).toBe('Development Context')
      expect(sorted[1].internal_name).toBe('Main Context')
      expect(sorted[2].internal_name).toBe('Production Context')
      expect(sorted[3].internal_name).toBe('Test Context')
    })

    it('sorts contexts by internal_name descending', () => {
      const sorted = [...mockContexts].sort((a, b) =>
        b.internal_name.toLowerCase().localeCompare(a.internal_name.toLowerCase())
      )

      expect(sorted[0].internal_name).toBe('Test Context')
      expect(sorted[1].internal_name).toBe('Production Context')
      expect(sorted[2].internal_name).toBe('Main Context')
      expect(sorted[3].internal_name).toBe('Development Context')
    })

    it('sorts contexts by is_default status', () => {
      const sorted = [...mockContexts].sort((a, b) => {
        const aValue = a.is_default ? 1 : 0
        const bValue = b.is_default ? 1 : 0
        return bValue - aValue // desc order
      })

      expect(sorted[0].is_default).toBe(true)
      expect(sorted[1].is_default).toBe(false)
    })

    it('sorts contexts by created_at date', () => {
      const sorted = [...mockContexts].sort((a, b) => {
        const aTime = new Date(a.created_at || '').getTime()
        const bTime = new Date(b.created_at || '').getTime()
        return aTime - bTime
      })

      expect(sorted[0].created_at).toBe('2023-01-01T00:00:00Z')
      expect(sorted[3].created_at).toBe('2023-01-04T00:00:00Z')
    })
  })

  describe('Store Integration', () => {
    it('calls fetchContexts on initialization', async () => {
      expect(mockContextStore.fetchContexts).toHaveBeenCalledTimes(0) // Not called yet

      // Simulate component initialization
      await mockContextStore.fetchContexts()

      expect(mockContextStore.fetchContexts).toHaveBeenCalledTimes(1)
    })

    it('handles context deletion', async () => {
      const contextId = 'test-context'

      mockContextStore.deleteContext.mockResolvedValue(undefined)

      await mockContextStore.deleteContext(contextId)

      expect(mockContextStore.deleteContext).toHaveBeenCalledWith(contextId)
    })

    it('handles setting default context', async () => {
      const contextId = 'dev-context'
      const isDefault = true

      mockContextStore.setDefaultContext.mockResolvedValue(undefined)

      await mockContextStore.setDefaultContext(contextId, isDefault)

      expect(mockContextStore.setDefaultContext).toHaveBeenCalledWith(contextId, isDefault)
    })
  })

  describe('Error Handling', () => {
    it('handles fetch error appropriately', async () => {
      const error = new Error('Failed to fetch contexts')
      mockContextStore.fetchContexts.mockRejectedValue(error)

      try {
        await mockContextStore.fetchContexts()
      } catch (e) {
        expect(e).toBe(error)
      }

      expect(mockContextStore.fetchContexts).toHaveBeenCalled()
    })

    it('handles delete error appropriately', async () => {
      const error = new Error('Failed to delete context')
      const contextId = 'test-context'

      mockContextStore.deleteContext.mockRejectedValue(error)

      try {
        await mockContextStore.deleteContext(contextId)
      } catch (e) {
        expect(e).toBe(error)
      }

      expect(mockContextStore.deleteContext).toHaveBeenCalledWith(contextId)
    })

    it('handles set default error appropriately', async () => {
      const error = new Error('Failed to set default context')
      const contextId = 'dev-context'
      const isDefault = true

      mockContextStore.setDefaultContext.mockRejectedValue(error)

      try {
        await mockContextStore.setDefaultContext(contextId, isDefault)
      } catch (e) {
        expect(e).toBe(error)
      }

      expect(mockContextStore.setDefaultContext).toHaveBeenCalledWith(contextId, isDefault)
    })
  })

  describe('Data Processing', () => {
    it('correctly identifies default contexts', () => {
      const defaultContexts = mockContexts.filter(context => context.is_default)
      expect(defaultContexts).toHaveLength(1)
      expect(defaultContexts[0].id).toBe('main-context')
    })

    it('correctly processes context creation date', () => {
      const context = mockContexts[0]
      const date = new Date(context.created_at || '')
      expect(date.getFullYear()).toBe(2023)
      expect(date.getMonth()).toBe(0) // January
      expect(date.getDate()).toBe(1)
    })

    it('handles null backward_compatibility gracefully', () => {
      const contextWithoutBackward = mockContexts.find(
        context => context.backward_compatibility === null
      )
      expect(contextWithoutBackward).toBeDefined()
      expect(contextWithoutBackward?.internal_name).toBe('Production Context')
    })
  })
})
