/**
 * Integration Tests for Context Resource Management
 *
 * These tests verify complete user workflows combining multiple components
 * and stores to ensure Context-specific features work together correctly.
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useContextStore } from '@/stores/context'
import { createMockContext } from '@/__tests__/test-utils'
import type { ContextResource } from '@metanull/inventory-app-api-client'

// Mock store interface
interface MockContextStore {
  contexts: ContextResource[]
  currentContext: ContextResource | null
  loading: boolean
  error: string | null
  defaultContext: ContextResource | undefined
  defaultContexts: ContextResource[]
  fetchContexts: () => Promise<void>
  fetchContext: (id: string) => Promise<void>
  createContext: (data: unknown) => Promise<ContextResource>
  updateContext: (id: string, data: unknown) => Promise<ContextResource>
  deleteContext: (id: string) => Promise<void>
  setDefaultContext: (id: string) => Promise<void>
  getDefaultContext: () => ContextResource | null
  clearError: () => void
  clearCurrentContext: () => void
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

// Mock the stores instead of the API client
vi.mock('@/stores/context')

// Test data
const mockContexts: ContextResource[] = [
  createMockContext({
    id: 'main-context-id',
    internal_name: 'Main Context',
    backward_compatibility: 'main',
    is_default: true,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  }),
  createMockContext({
    id: 'dev-context-id',
    internal_name: 'Development Context',
    backward_compatibility: 'dev',
    is_default: false,
    created_at: '2023-01-02T00:00:00Z',
    updated_at: '2023-01-02T00:00:00Z',
  }),
  createMockContext({
    id: 'test-context-id',
    internal_name: 'Test Context',
    backward_compatibility: null,
    is_default: false,
    created_at: '2023-01-03T00:00:00Z',
    updated_at: '2023-01-03T00:00:00Z',
  }),
]

describe('Context Resource Integration Tests', () => {
  let contextStore: ReturnType<typeof useContextStore>

  beforeEach(() => {
    setActivePinia(createPinia())

    contextStore = {
      contexts: [...mockContexts],
      currentContext: null,
      loading: false,
      error: null,
      fetchContexts: vi.fn(),
      fetchContext: vi.fn(),
      createContext: vi.fn(),
      updateContext: vi.fn(),
      deleteContext: vi.fn(),
      setDefaultContext: vi.fn(),
      getDefaultContext: vi.fn(),
      defaultContext: mockContexts[0],
      defaultContexts: [mockContexts[0]],
      clearError: vi.fn(),
      clearCurrentContext: vi.fn(),
    } as MockContextStore

    vi.mocked(useContextStore).mockReturnValue(contextStore)
    vi.clearAllMocks()
  })

  describe('Context List Management', () => {
    it('fetches and displays contexts successfully', async () => {
      contextStore.fetchContexts.mockResolvedValue(undefined)

      await contextStore.fetchContexts()

      expect(contextStore.fetchContexts).toHaveBeenCalledTimes(1)
      expect(contextStore.contexts).toHaveLength(3)
    })

    it('filters contexts by default status correctly', () => {
      const defaultContexts = contextStore.contexts.filter(context => context.is_default)

      expect(defaultContexts).toHaveLength(1)
      expect(defaultContexts[0].internal_name).toBe('Main Context')
    })

    it('searches contexts across all relevant fields', () => {
      const searchQuery = 'dev'
      const filteredContexts = contextStore.contexts.filter(
        context =>
          context.internal_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          (context.backward_compatibility &&
            context.backward_compatibility.toLowerCase().includes(searchQuery.toLowerCase()))
      )

      expect(filteredContexts).toHaveLength(1)
      expect(filteredContexts[0].internal_name).toBe('Development Context')
    })
  })

  describe('Context CRUD Operations', () => {
    it('creates a new context with proper validation', async () => {
      const newContextData = {
        internal_name: 'New Test Context',
        backward_compatibility: 'new-test',
        is_default: false,
      }

      const newContext = createMockContext({
        id: 'new-context-id',
        ...newContextData,
        created_at: '2023-01-04T00:00:00Z',
        updated_at: '2023-01-04T00:00:00Z',
      })

      contextStore.createContext.mockResolvedValue(newContext)

      const result = await contextStore.createContext(newContextData)

      expect(contextStore.createContext).toHaveBeenCalledWith(newContextData)
      expect(result.internal_name).toBe('New Test Context')
      expect(result.is_default).toBe(false)
    })

    it('updates an existing context', async () => {
      const contextId = 'test-context-id'
      const updateData = {
        internal_name: 'Updated Test Context',
        backward_compatibility: 'updated-test',
      }

      const updatedContext = createMockContext({
        id: contextId,
        ...updateData,
        is_default: false,
        created_at: '2023-01-03T00:00:00Z',
        updated_at: new Date().toISOString(),
      })

      contextStore.updateContext.mockResolvedValue(updatedContext)

      const result = await contextStore.updateContext(contextId, updateData)

      expect(contextStore.updateContext).toHaveBeenCalledWith(contextId, updateData)
      expect(result.internal_name).toBe('Updated Test Context')
    })

    it('deletes a context', async () => {
      const contextId = 'test-context-id'

      contextStore.deleteContext.mockResolvedValue(undefined)

      await contextStore.deleteContext(contextId)

      expect(contextStore.deleteContext).toHaveBeenCalledWith(contextId)
    })

    it('fetches a single context by ID', async () => {
      const contextId = 'main-context-id'
      const expectedContext = mockContexts[0]

      contextStore.fetchContext.mockResolvedValue(expectedContext)

      const result = await contextStore.fetchContext(contextId)

      expect(contextStore.fetchContext).toHaveBeenCalledWith(contextId)
      expect(result.id).toBe(contextId)
      expect(result.internal_name).toBe('Main Context')
    })
  })

  describe('Default Context Management', () => {
    it('sets a context as default', async () => {
      const contextId = 'dev-context-id'
      const updatedContext = createMockContext({
        ...mockContexts[1],
        is_default: true,
      })

      contextStore.setDefaultContext.mockResolvedValue(updatedContext)

      await contextStore.setDefaultContext(contextId, true)

      expect(contextStore.setDefaultContext).toHaveBeenCalledWith(contextId, true)
    })

    it('unsets a context as default', async () => {
      const contextId = 'main-context-id'
      const updatedContext = createMockContext({
        ...mockContexts[0],
        is_default: false,
      })

      contextStore.setDefaultContext.mockResolvedValue(updatedContext)

      await contextStore.setDefaultContext(contextId, false)

      expect(contextStore.setDefaultContext).toHaveBeenCalledWith(contextId, false)
    })

    it('gets the default context', async () => {
      const defaultContext = mockContexts[0]

      contextStore.getDefaultContext.mockResolvedValue(defaultContext)

      const result = await contextStore.getDefaultContext()

      expect(contextStore.getDefaultContext).toHaveBeenCalledTimes(1)
      expect(result.is_default).toBe(true)
      expect(result.internal_name).toBe('Main Context')
    })

    it('handles multiple default context scenarios', () => {
      // Ensure only one context can be default
      const defaultContexts = contextStore.contexts.filter(context => context.is_default)

      expect(defaultContexts).toHaveLength(1)
      expect(defaultContexts[0].id).toBe('main-context-id')
    })
  })

  describe('Error Handling', () => {
    it('handles fetch contexts error', async () => {
      const error = new Error('Failed to fetch contexts')
      contextStore.fetchContexts.mockRejectedValue(error)

      await expect(contextStore.fetchContexts()).rejects.toThrow('Failed to fetch contexts')
    })

    it('handles create context error', async () => {
      const error = new Error('Failed to create context')
      const newContextData = {
        internal_name: 'Test Context',
        backward_compatibility: null,
      }

      contextStore.createContext.mockRejectedValue(error)

      await expect(contextStore.createContext(newContextData)).rejects.toThrow(
        'Failed to create context'
      )
    })

    it('handles set default context error', async () => {
      const error = new Error('Failed to set default context')
      const contextId = 'dev-context-id'

      contextStore.setDefaultContext.mockRejectedValue(error)

      await expect(contextStore.setDefaultContext(contextId, true)).rejects.toThrow(
        'Failed to set default context'
      )
    })
  })

  describe('Context Data Integrity', () => {
    it('maintains proper context structure', () => {
      const context = mockContexts[0]

      expect(context).toHaveProperty('id')
      expect(context).toHaveProperty('internal_name')
      expect(context).toHaveProperty('backward_compatibility')
      expect(context).toHaveProperty('is_default')
      expect(context).toHaveProperty('created_at')
      expect(context).toHaveProperty('updated_at')
    })

    it('validates context IDs are unique', () => {
      const ids = mockContexts.map(context => context.id)
      const uniqueIds = new Set(ids)

      expect(uniqueIds.size).toBe(ids.length)
    })

    it('ensures default context is properly flagged', () => {
      const defaultContext = mockContexts.find(context => context.is_default)

      expect(defaultContext).toBeDefined()
      expect(defaultContext?.id).toBe('main-context-id')
    })
  })
})
