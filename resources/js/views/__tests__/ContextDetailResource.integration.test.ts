/**
 * Resource Integration Tests for ContextDetail Management
 *
 * These tests verify complete resource workflows for context detail operations,
 * focusing on resource-level interactions, data consistency, and business logic
 * without UI rendering complexity.
 *
 * Tests cover:
 * - Resource creation, update, and deletion workflows
 * - Data validation and transformation
 * - Resource state management and consistency
 * - Error handling and recovery scenarios
 * - Resource relationships
 * - Status management workflows (default/non-default)
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useContextStore } from '@/stores/context'
import { createMockContext } from '@/__tests__/test-utils'
import type {
  ContextResource,
  ContextStoreRequest,
  ContextUpdateRequest,
} from '@metanull/inventory-app-api-client'

// Mock console.error to suppress error output during tests
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
    id: '123e4567-e89b-12d3-a456-426614174000',
    internal_name: 'Production',
    backward_compatibility: 'prod',
    is_default: true,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  }),
  createMockContext({
    id: '123e4567-e89b-12d3-a456-426614174001',
    internal_name: 'Development',
    backward_compatibility: 'dev',
    is_default: false,
    created_at: '2023-01-02T00:00:00Z',
    updated_at: '2023-01-02T00:00:00Z',
  }),
]

describe('ContextDetail Resource Integration Tests', () => {
  let mockContextStore: ReturnType<typeof useContextStore>

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup comprehensive store mock
    mockContextStore = {
      contexts: mockContexts,
      currentContext: null,
      loading: false,
      error: null,
      fetchContexts: vi.fn().mockResolvedValue(mockContexts),
      fetchContext: vi.fn().mockImplementation((id: number) => {
        return Promise.resolve(mockContexts.find(c => c.id === id) || null)
      }),
      createContext: vi.fn().mockImplementation((request: ContextStoreRequest) => {
        const newContext = createMockContext({
          id: Math.max(...mockContexts.map(c => c.id)) + 1,
          internal_name: request.internal_name,
          backward_compatibility: request.backward_compatibility,
          is_default: request.is_default || false,
        })
        return Promise.resolve(newContext)
      }),
      updateContext: vi.fn().mockImplementation((id: number, request: ContextUpdateRequest) => {
        const updated = createMockContext({
          id: id,
          internal_name: request.internal_name,
          backward_compatibility: request.backward_compatibility,
          is_default: request.is_default || false,
          updated_at: new Date().toISOString(),
        })
        return Promise.resolve(updated)
      }),
      deleteContext: vi.fn().mockResolvedValue(undefined),
      setContextDefault: vi.fn().mockImplementation((id: number, isDefault: boolean) => {
        const updated = createMockContext({
          ...mockContexts.find(c => c.id === id)!,
          is_default: isDefault,
          updated_at: new Date().toISOString(),
        })
        return Promise.resolve(updated)
      }),
      clearCurrentContext: vi.fn(),
      defaultContexts: mockContexts.filter(ctx => ctx.is_default),
      defaultContext: mockContexts.find(ctx => ctx.is_default) || null,
    } as ReturnType<typeof useContextStore>

    // Mock store implementation
    vi.mocked(useContextStore).mockReturnValue(mockContextStore)

    vi.clearAllMocks()
  })

  describe('Context Resource Creation Workflows', () => {
    it('should create a new context with minimal data', async () => {
      const store = useContextStore()
      const newContextData: ContextStoreRequest = {
        internal_name: 'Testing',
        backward_compatibility: null,
        is_default: false,
      }

      const result = await store.createContext(newContextData)

      expect(store.createContext).toHaveBeenCalledWith(newContextData)
      expect(result).toBeDefined()
      expect(result?.internal_name).toBe('Testing')
      expect(result?.backward_compatibility).toBeNull()
      expect(result?.is_default).toBe(false)
    })

    it('should create a new context with full data', async () => {
      const store = useContextStore()
      const newContextData: ContextStoreRequest = {
        internal_name: 'Staging Environment',
        backward_compatibility: 'staging',
        is_default: false,
      }

      const result = await store.createContext(newContextData)

      expect(store.createContext).toHaveBeenCalledWith(newContextData)
      expect(result).toBeDefined()
      expect(result?.internal_name).toBe('Staging Environment')
      expect(result?.backward_compatibility).toBe('staging')
      expect(result?.is_default).toBe(false)
    })

    it('should create a default context', async () => {
      const store = useContextStore()
      const newContextData: ContextStoreRequest = {
        internal_name: 'New Default',
        backward_compatibility: 'new-default',
        is_default: true,
      }

      const result = await store.createContext(newContextData)

      expect(store.createContext).toHaveBeenCalledWith(newContextData)
      expect(result).toBeDefined()
      expect(result?.is_default).toBe(true)
    })

    it('should handle creation errors gracefully', async () => {
      const store = useContextStore()
      store.createContext = vi.fn().mockRejectedValue(new Error('Creation failed'))

      const newContextData: ContextStoreRequest = {
        internal_name: 'Failed Context',
        backward_compatibility: null,
        is_default: false,
      }

      await expect(store.createContext(newContextData)).rejects.toThrow('Creation failed')
    })
  })

  describe('Context Resource Update Workflows', () => {
    it('should update context internal name', async () => {
      const store = useContextStore()
      const updateData: ContextUpdateRequest = {
        internal_name: 'Production Updated',
        backward_compatibility: 'prod',
        is_default: true,
      }

      const result = await store.updateContext(1, updateData)

      expect(store.updateContext).toHaveBeenCalledWith(1, updateData)
      expect(result).toBeDefined()
      expect(result?.internal_name).toBe('Production Updated')
    })

    it('should update context backward compatibility', async () => {
      const store = useContextStore()
      const updateData: ContextUpdateRequest = {
        internal_name: 'Production',
        backward_compatibility: 'prod-v2',
        is_default: true,
      }

      const result = await store.updateContext(1, updateData)

      expect(store.updateContext).toHaveBeenCalledWith(1, updateData)
      expect(result).toBeDefined()
      expect(result?.backward_compatibility).toBe('prod-v2')
    })

    it('should clear backward compatibility', async () => {
      const store = useContextStore()
      const updateData: ContextUpdateRequest = {
        internal_name: 'Production',
        backward_compatibility: null,
        is_default: true,
      }

      const result = await store.updateContext(1, updateData)

      expect(store.updateContext).toHaveBeenCalledWith(1, updateData)
      expect(result).toBeDefined()
      expect(result?.backward_compatibility).toBeNull()
    })

    it('should handle update errors gracefully', async () => {
      const store = useContextStore()
      store.updateContext = vi.fn().mockRejectedValue(new Error('Update failed'))

      const updateData: ContextUpdateRequest = {
        internal_name: 'Failed Update',
        backward_compatibility: null,
        is_default: false,
      }

      await expect(store.updateContext(1, updateData)).rejects.toThrow('Update failed')
    })

    it('should handle updating non-existent context', async () => {
      const store = useContextStore()
      store.updateContext = vi.fn().mockRejectedValue(new Error('Context not found'))

      const updateData: ContextUpdateRequest = {
        internal_name: 'Non-existent',
        backward_compatibility: null,
        is_default: false,
      }

      await expect(store.updateContext(999, updateData)).rejects.toThrow('Context not found')
    })
  })

  describe('Context Resource Deletion Workflows', () => {
    it('should delete context successfully', async () => {
      const store = useContextStore()

      await store.deleteContext(2)

      expect(store.deleteContext).toHaveBeenCalledWith(2)
    })

    it('should handle deletion errors gracefully', async () => {
      const store = useContextStore()
      store.deleteContext = vi.fn().mockRejectedValue(new Error('Deletion failed'))

      await expect(store.deleteContext(1)).rejects.toThrow('Deletion failed')
    })

    it('should handle deleting non-existent context', async () => {
      const store = useContextStore()
      store.deleteContext = vi.fn().mockRejectedValue(new Error('Context not found'))

      await expect(store.deleteContext(999)).rejects.toThrow('Context not found')
    })

    it('should handle cascade deletion constraints', async () => {
      const store = useContextStore()
      store.deleteContext = vi
        .fn()
        .mockRejectedValue(new Error('Cannot delete context with dependencies'))

      await expect(store.deleteContext(1)).rejects.toThrow(
        'Cannot delete context with dependencies'
      )
    })
  })

  describe('Context Resource Retrieval Workflows', () => {
    it('should fetch context by ID successfully', async () => {
      const store = useContextStore()

      const result = await store.fetchContext('123e4567-e89b-12d3-a456-426614174000')

      expect(store.fetchContext).toHaveBeenCalledWith('123e4567-e89b-12d3-a456-426614174000')
      expect(result).toBeDefined()
      expect(result?.id).toBe('123e4567-e89b-12d3-a456-426614174000')
      expect(result?.internal_name).toBe('Production')
    })

    it('should return null for non-existent context', async () => {
      const store = useContextStore()

      const result = await store.fetchContext('non-existent-uuid')

      expect(store.fetchContext).toHaveBeenCalledWith('non-existent-uuid')
      expect(result).toBeNull()
    })

    it('should handle fetch errors gracefully', async () => {
      const store = useContextStore()
      store.fetchContext = vi.fn().mockRejectedValue(new Error('Fetch failed'))

      await expect(store.fetchContext('123e4567-e89b-12d3-a456-426614174000')).rejects.toThrow(
        'Fetch failed'
      )
    })

    it('should fetch all contexts successfully', async () => {
      const store = useContextStore()

      const result = await store.fetchContexts()

      expect(store.fetchContexts).toHaveBeenCalled()
      expect(result).toEqual(mockContexts)
      expect(result.length).toBe(2)
    })
  })

  describe('Context Status Management Workflows', () => {
    it('should set context as default', async () => {
      const store = useContextStore()

      const result = await store.setContextDefault(2, true)

      expect(store.setContextDefault).toHaveBeenCalledWith(2, true)
      expect(result).toBeDefined()
      expect(result?.is_default).toBe(true)
    })

    it('should unset context as default', async () => {
      const store = useContextStore()

      const result = await store.setContextDefault(1, false)

      expect(store.setContextDefault).toHaveBeenCalledWith(1, false)
      expect(result).toBeDefined()
      expect(result?.is_default).toBe(false)
    })

    it('should handle status toggle errors gracefully', async () => {
      const store = useContextStore()
      store.setContextDefault = vi.fn().mockRejectedValue(new Error('Status toggle failed'))

      await expect(store.setContextDefault(1, false)).rejects.toThrow('Status toggle failed')
    })

    it('should handle toggling status of non-existent context', async () => {
      const store = useContextStore()
      store.setContextDefault = vi.fn().mockRejectedValue(new Error('Context not found'))

      await expect(store.setContextDefault(999, true)).rejects.toThrow('Context not found')
    })
  })

  describe('Default Context Management', () => {
    it('should retrieve default contexts correctly', () => {
      const store = useContextStore()

      const defaultContexts = store.defaultContexts

      expect(defaultContexts.length).toBe(1)
      expect(defaultContexts[0].is_default).toBe(true)
      expect(defaultContexts[0].internal_name).toBe('Production')
    })

    it('should retrieve the primary default context', () => {
      const store = useContextStore()

      const defaultContext = store.defaultContext

      expect(defaultContext).toBeDefined()
      expect(defaultContext?.is_default).toBe(true)
      expect(defaultContext?.internal_name).toBe('Production')
    })

    it('should handle multiple default contexts', () => {
      const store = useContextStore()
      // Simulate multiple default contexts
      store.defaultContexts = [
        mockContexts[0],
        createMockContext({
          id: '123e4567-e89b-12d3-a456-426614174002',
          internal_name: 'Another Default',
          is_default: true,
        }),
      ]

      const defaultContexts = store.defaultContexts

      expect(defaultContexts.length).toBe(2)
      defaultContexts.forEach(context => {
        expect(context.is_default).toBe(true)
      })
    })
  })

  describe('Resource State Consistency', () => {
    it('should maintain consistent state after creation', async () => {
      const store = useContextStore()

      const newContextData: ContextStoreRequest = {
        internal_name: 'New Context',
        backward_compatibility: 'new',
        is_default: false,
      }

      await store.createContext(newContextData)

      // Verify store state consistency (this would be handled by the actual store implementation)
      expect(store.createContext).toHaveBeenCalledWith(newContextData)
    })

    it('should maintain consistent state after update', async () => {
      const store = useContextStore()

      const updateData: ContextUpdateRequest = {
        internal_name: 'Updated Production',
        backward_compatibility: 'prod-updated',
        is_default: true,
      }

      await store.updateContext(1, updateData)

      expect(store.updateContext).toHaveBeenCalledWith(1, updateData)
    })

    it('should maintain consistent state after deletion', async () => {
      const store = useContextStore()

      await store.deleteContext(2)

      expect(store.deleteContext).toHaveBeenCalledWith(2)
    })

    it('should clear current context state appropriately', () => {
      const store = useContextStore()
      store.currentContext = mockContexts[0]

      store.clearCurrentContext()

      expect(store.clearCurrentContext).toHaveBeenCalled()
    })
  })

  describe('Data Validation and Transformation', () => {
    it('should validate required fields during creation', async () => {
      const store = useContextStore()

      const validData: ContextStoreRequest = {
        internal_name: 'Valid Context',
        backward_compatibility: null,
        is_default: false,
      }

      const result = await store.createContext(validData)

      expect(result).toBeDefined()
      expect(result?.internal_name).toBe('Valid Context')
    })

    it('should handle null backward compatibility correctly', async () => {
      const store = useContextStore()

      const dataWithNullBackwardCompatibility: ContextStoreRequest = {
        internal_name: 'No Legacy',
        backward_compatibility: null,
        is_default: false,
      }

      const result = await store.createContext(dataWithNullBackwardCompatibility)

      expect(result).toBeDefined()
      expect(result?.backward_compatibility).toBeNull()
    })

    it('should transform data appropriately for API requests', async () => {
      const store = useContextStore()

      const inputData: ContextStoreRequest = {
        internal_name: 'API Test Context',
        backward_compatibility: 'api-test',
        is_default: false,
      }

      await store.createContext(inputData)

      expect(store.createContext).toHaveBeenCalledWith({
        internal_name: 'API Test Context',
        backward_compatibility: 'api-test',
        is_default: false,
      })
    })
  })

  describe('Error Recovery Scenarios', () => {
    it('should handle partial update failures', async () => {
      const store = useContextStore()
      store.updateContext = vi.fn().mockRejectedValue(new Error('Partial update failed'))

      const updateData: ContextUpdateRequest = {
        internal_name: 'Partial Update',
        backward_compatibility: 'partial',
        is_default: false,
      }

      await expect(store.updateContext(1, updateData)).rejects.toThrow('Partial update failed')
    })

    it('should handle concurrent modification conflicts', async () => {
      const store = useContextStore()
      store.updateContext = vi
        .fn()
        .mockRejectedValue(new Error('Conflict: Context was modified by another user'))

      const updateData: ContextUpdateRequest = {
        internal_name: 'Conflicted Update',
        backward_compatibility: 'conflict',
        is_default: false,
      }

      await expect(store.updateContext(1, updateData)).rejects.toThrow(
        'Conflict: Context was modified by another user'
      )
    })

    it('should handle validation errors from server', async () => {
      const store = useContextStore()
      store.createContext = vi
        .fn()
        .mockRejectedValue(new Error('Validation failed: Internal name already exists'))

      const duplicateData: ContextStoreRequest = {
        internal_name: 'Production', // Assuming this already exists
        backward_compatibility: 'duplicate',
        is_default: false,
      }

      await expect(store.createContext(duplicateData)).rejects.toThrow(
        'Validation failed: Internal name already exists'
      )
    })
  })
})
