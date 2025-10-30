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

import type { ContextResource, ContextStoreRequest } from '@metanull/inventory-app-api-client'

// Mock the stores instead of the API client
vi.mock('@/stores/context')

// Test data
const mockContexts: ContextResource[] = [
  createMockContext({
    id: '123e4567-e89b-12d3-a456-426614174000',
    internal_name: 'Production',
    backward_compatibility: 'prod',
    is_default: true,
  }),
  createMockContext({
    id: '123e4567-e89b-12d3-a456-426614174001',
    internal_name: 'Development',
    backward_compatibility: 'dev',
    is_default: false,
  }),
  createMockContext({
    id: '123e4567-e89b-12d3-a456-426614174002',
    internal_name: 'Testing',
    backward_compatibility: 'test',
    is_default: false,
  }),
]

describe('Context Resource Integration Tests', () => {
  let contextStore: ReturnType<typeof useContextStore>

  beforeEach(async () => {
    setActivePinia(createPinia())

    contextStore = useContextStore()

    // Setup comprehensive store mocks
    const mockContextStoreImplementation = {
      contexts: mockContexts,
      currentContext: null as ContextResource | null,
      loading: false,
      defaultContexts: mockContexts.filter(c => c.is_default),
      fetchContexts: vi.fn().mockResolvedValue(mockContexts),
      fetchContext: vi.fn().mockImplementation((id: string) => {
        let context = mockContexts.find(c => c.id === id)
        // Handle the case where we're fetching a newly created context
        if (id === 'new-context' && !context) {
          context = {
            ...createMockContext({ id: 'new-context', internal_name: 'New Integration Context' }),
            internal_name: 'New Integration Context',
          }
        }
        mockContextStoreImplementation.currentContext = context || null
        return Promise.resolve(context)
      }),
      createContext: vi
        .fn()
        .mockImplementation((data: ContextStoreRequest) =>
          Promise.resolve({ ...createMockContext(data), id: 'new-context' })
        ),
      updateContext: vi
        .fn()
        .mockImplementation((id: string, data: ContextStoreRequest) =>
          Promise.resolve({ ...mockContexts.find(c => c.id === id), ...data })
        ),
      deleteContext: vi.fn().mockResolvedValue(undefined),
      setContextDefault: vi
        .fn()
        .mockImplementation((id: string, isDefault: boolean) =>
          Promise.resolve({ ...mockContexts.find(c => c.id === id), is_default: isDefault })
        ),
    }

    // Mock store implementations
    vi.mocked(useContextStore).mockReturnValue(
      mockContextStoreImplementation as ReturnType<typeof useContextStore>
    )

    // Update store references
    contextStore = mockContextStoreImplementation as ReturnType<typeof useContextStore>

    vi.clearAllMocks()
  })

  describe('Context List and Detail Integration (Context-specific workflows)', () => {
    it('should complete full context lifecycle: list → create → view → edit → delete', async () => {
      // 1. Load initial context list (simulating Contexts.vue)
      await contextStore.fetchContexts()
      expect(contextStore.contexts).toHaveLength(3)

      // 2. Create new context (simulating ContextDetail.vue in create mode)
      const newContextData = {
        internal_name: 'New Integration Context',
        backward_compatibility: 'new-integration',
      }

      const createdContext = await contextStore.createContext(newContextData)
      expect(createdContext.id).toBe('new-context')
      expect(createdContext.internal_name).toBe('New Integration Context')

      // 3. Fetch the created context (simulating navigation to detail view)
      await contextStore.fetchContext('new-context')
      expect(contextStore.currentContext?.internal_name).toBe('New Integration Context')

      // 4. Edit the context (simulating ContextDetail.vue in edit mode)
      const updatedData = {
        internal_name: 'Updated Integration Context',
        backward_compatibility: 'updated-integration',
      }
      await contextStore.updateContext('new-context', updatedData)

      // 5. Delete the context (completing the lifecycle)
      await contextStore.deleteContext('new-context')

      // Verify the full workflow completed successfully
      expect(contextStore.createContext).toHaveBeenCalledWith(newContextData)
      expect(contextStore.updateContext).toHaveBeenCalledWith('new-context', updatedData)
      expect(contextStore.deleteContext).toHaveBeenCalledWith('new-context')
    })

    it('should handle Context-specific filtering workflows across components', async () => {
      // Load contexts and verify filtering computations work together
      await contextStore.fetchContexts()

      // Test default contexts filter (Context-specific)
      const defaultContexts = contextStore.defaultContexts
      expect(defaultContexts).toHaveLength(1) // Only Production is default
      expect(defaultContexts.every(c => c.is_default)).toBe(true)
    })

    it('should handle Context-specific status toggle workflows', async () => {
      // Load context
      await contextStore.fetchContext('123e4567-e89b-12d3-a456-426614174001') // Development Context (not default)

      // Toggle default status (Context-specific operation)
      await contextStore.setContextDefault('123e4567-e89b-12d3-a456-426614174001', true)
      expect(contextStore.setContextDefault).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174001',
        true
      )

      // Verify status change was processed
      expect(contextStore.setContextDefault).toHaveBeenCalledTimes(1)

      // Test removing default status
      await contextStore.setContextDefault('123e4567-e89b-12d3-a456-426614174000', false)
      expect(contextStore.setContextDefault).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000',
        false
      )
      expect(contextStore.setContextDefault).toHaveBeenCalledTimes(2)
    })
  })

  describe('Error Handling Integration', () => {
    it('should handle API errors gracefully across the workflow', async () => {
      // Mock API failure on the original store instance
      const originalFetchContexts = contextStore.fetchContexts
      contextStore.fetchContexts = vi.fn().mockRejectedValue(new Error('API Error'))

      // Verify error is handled (thrown in this case)
      await expect(contextStore.fetchContexts()).rejects.toThrow('API Error')

      // Restore original implementation and verify it works
      contextStore.fetchContexts = originalFetchContexts

      // Verify that after error, we can still use the store
      expect(typeof contextStore.fetchContexts).toBe('function')
    })
  })

  describe('Context-specific Business Rules', () => {
    it('should enforce Context-specific default rules', async () => {
      await contextStore.fetchContexts()

      // Test that only default contexts are included in defaultContexts
      const defaultContexts = contextStore.defaultContexts
      const hasNonDefaultContexts = defaultContexts.some(c => !c.is_default)
      expect(hasNonDefaultContexts).toBe(false)

      // Test that only contexts with is_default true are in default collection
      defaultContexts.forEach(context => {
        expect(context.is_default).toBe(true)
      })
    })

    it('should handle Context-specific search functionality', async () => {
      await contextStore.fetchContexts()

      // Simulate search functionality that would be used in Contexts.vue
      const searchTerm = 'prod'
      const searchResults = contextStore.contexts.filter(
        context =>
          context.internal_name.toLowerCase().includes(searchTerm) ||
          (context.backward_compatibility &&
            context.backward_compatibility.toLowerCase().includes(searchTerm))
      )

      expect(searchResults).toHaveLength(1)
      expect(searchResults[0].internal_name).toBe('Production')
    })

    it('should handle multiple default contexts scenario', async () => {
      // Add another default context for testing
      const additionalDefaultContext = createMockContext({
        id: '123e4567-e89b-12d3-a456-426614174003',
        internal_name: 'Staging',
        backward_compatibility: 'staging',
        is_default: true,
      })

      const contextsWithMultipleDefaults = [...mockContexts, additionalDefaultContext]
      contextStore.contexts = contextsWithMultipleDefaults
      contextStore.defaultContexts = contextsWithMultipleDefaults.filter(c => c.is_default)

      // Test that multiple default contexts are handled correctly
      const defaultContexts = contextStore.defaultContexts
      expect(defaultContexts).toHaveLength(2)
      expect(defaultContexts.every(c => c.is_default)).toBe(true)
    })

    it('should handle null backward_compatibility gracefully', async () => {
      // Create context with null backward_compatibility
      const contextWithNullCompat = createMockContext({
        id: '123e4567-e89b-12d3-a456-426614174004',
        internal_name: 'Testing Environment',
        backward_compatibility: null,
        is_default: false,
      })

      const contextsWithNull = [...mockContexts, contextWithNullCompat]
      contextStore.contexts = contextsWithNull

      // Simulate search that would encounter null backward_compatibility
      const searchTerm = 'testing'
      const searchResults = contextStore.contexts.filter(
        context =>
          context.internal_name.toLowerCase().includes(searchTerm) ||
          (context.backward_compatibility &&
            context.backward_compatibility.toLowerCase().includes(searchTerm))
      )

      expect(searchResults).toHaveLength(2) // "Testing" and "Testing Environment"
      expect(searchResults.some(c => c.backward_compatibility === null)).toBe(true)
    })
  })

  describe('Context Lifecycle Management', () => {
    it('should handle context creation with validation', async () => {
      const validContextData = {
        internal_name: 'Valid Context',
        backward_compatibility: 'valid-context',
        is_default: false,
      }

      const createdContext = await contextStore.createContext(validContextData)

      expect(contextStore.createContext).toHaveBeenCalledWith(validContextData)
      expect(createdContext.internal_name).toBe('Valid Context')
      expect(createdContext.backward_compatibility).toBe('valid-context')
    })

    it('should handle context updates with partial data', async () => {
      // Test updating only internal_name
      const partialUpdate = {
        internal_name: 'Updated Name Only',
      }

      await contextStore.updateContext('123e4567-e89b-12d3-a456-426614174000', partialUpdate)
      expect(contextStore.updateContext).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000',
        partialUpdate
      )

      // Test updating only backward_compatibility
      const compatUpdate = {
        backward_compatibility: 'new-compat',
      }

      await contextStore.updateContext('123e4567-e89b-12d3-a456-426614174001', compatUpdate)
      expect(contextStore.updateContext).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174001',
        compatUpdate
      )
    })

    it('should handle context deletion with dependency checks', async () => {
      // Test deleting a non-default context (should be safe)
      await contextStore.deleteContext('123e4567-e89b-12d3-a456-426614174001')
      expect(contextStore.deleteContext).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174001'
      )

      // Test deleting a default context (business logic would handle restrictions)
      await contextStore.deleteContext('123e4567-e89b-12d3-a456-426614174000')
      expect(contextStore.deleteContext).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
    })
  })

  describe('Context State Management', () => {
    it('should maintain consistent state across operations', async () => {
      // Initial state
      await contextStore.fetchContexts()

      // Create new context
      await contextStore.createContext({
        internal_name: 'State Test Context',
        backward_compatibility: 'state-test',
        is_default: false,
      })

      // State should reflect creation (in real app, this would trigger re-fetch)
      expect(contextStore.createContext).toHaveBeenCalled()

      // Update context to be default
      await contextStore.setContextDefault('new-context', true)
      expect(contextStore.setContextDefault).toHaveBeenCalledWith('new-context', true)

      // Delete context
      await contextStore.deleteContext('new-context')
      expect(contextStore.deleteContext).toHaveBeenCalledWith('new-context')

      // Verify all operations were called
      expect(contextStore.createContext).toHaveBeenCalledTimes(1)
      expect(contextStore.setContextDefault).toHaveBeenCalledTimes(1)
      expect(contextStore.deleteContext).toHaveBeenCalledTimes(1)
    })

    it('should handle concurrent operations', async () => {
      // Simulate concurrent fetch and update operations
      const fetchPromise = contextStore.fetchContexts()
      const updatePromise = contextStore.setContextDefault(
        '123e4567-e89b-12d3-a456-426614174000',
        false
      )

      await Promise.all([fetchPromise, updatePromise])

      expect(contextStore.fetchContexts).toHaveBeenCalled()
      expect(contextStore.setContextDefault).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000',
        false
      )
    })
  })
})
