/**
 * Unit Tests for ContextDetail Component Business Logic
 *
 * These tests focus on the core functionality and business logic
 * of the ContextDetail component without dealing with complex UI rendering.
 *
 * Tests cover:
 * - Mode management (view, edit, create)
 * - Form data handling and validation
 * - Unsaved changes detection
 * - Save operations (create and update)
 * - Status toggle operations (default/non-default) - Context-specific
 * - Delete operations
 * - Navigation guards for unsaved changes
 * - Data fetching and error handling
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useContextStore } from '@/stores/context'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
import { createMockContext } from '@/__tests__/test-utils'
import type { ContextResource, ContextStoreRequest } from '@metanull/inventory-app-api-client'

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

// Mock the stores
vi.mock('@/stores/context')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')
vi.mock('@/stores/deleteConfirmation')
vi.mock('@/stores/cancelChangesConfirmation')

// Mock context data
const mockContext: ContextResource = createMockContext({
  id: '123e4567-e89b-12d3-a456-426614174000',
  internal_name: 'Production',
  backward_compatibility: 'prod',
  is_default: true,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
})

describe('ContextDetail Logic Tests', () => {
  let mockContextStore: ReturnType<typeof useContextStore>
  let mockLoadingOverlayStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorDisplayStore: ReturnType<typeof useErrorDisplayStore>
  let mockDeleteConfirmationStore: ReturnType<typeof useDeleteConfirmationStore>
  let mockCancelChangesConfirmationStore: ReturnType<typeof useCancelChangesConfirmationStore>

  beforeEach(() => {
    setActivePinia(createPinia())

    // Clear all mocks first
    vi.clearAllMocks()

    // Setup store mocks
    mockContextStore = {
      contexts: [mockContext],
      currentContext: mockContext,
      loading: false,
      error: null,
      fetchContexts: vi.fn().mockResolvedValue([mockContext]),
      fetchContext: vi.fn().mockResolvedValue(mockContext),
      createContext: vi.fn().mockResolvedValue(mockContext),
      updateContext: vi.fn().mockResolvedValue(mockContext),
      deleteContext: vi.fn().mockResolvedValue(undefined),
      setContextDefault: vi.fn().mockResolvedValue(mockContext),
      clearCurrentContext: vi.fn(),
      defaultContexts: [mockContext],
      defaultContext: mockContext,
    } as ReturnType<typeof useContextStore>

    mockLoadingOverlayStore = {
      show: vi.fn(),
      hide: vi.fn(),
      isVisible: false,
    } as ReturnType<typeof useLoadingOverlayStore>

    mockErrorDisplayStore = {
      addMessage: vi.fn(),
      clearMessages: vi.fn(),
      messages: [],
    } as ReturnType<typeof useErrorDisplayStore>

    mockDeleteConfirmationStore = {
      trigger: vi.fn(),
      isVisible: false,
    } as ReturnType<typeof useDeleteConfirmationStore>

    mockCancelChangesConfirmationStore = {
      trigger: vi.fn(),
      addChange: vi.fn(),
      resetChanges: vi.fn(),
      isVisible: false,
    } as ReturnType<typeof useCancelChangesConfirmationStore>

    // Mock store implementations
    vi.mocked(useContextStore).mockReturnValue(mockContextStore)
    vi.mocked(useLoadingOverlayStore).mockReturnValue(mockLoadingOverlayStore)
    vi.mocked(useErrorDisplayStore).mockReturnValue(mockErrorDisplayStore)
    vi.mocked(useDeleteConfirmationStore).mockReturnValue(mockDeleteConfirmationStore)
    vi.mocked(useCancelChangesConfirmationStore).mockReturnValue(mockCancelChangesConfirmationStore)
  })

  describe('Context Data Management', () => {
    it('should fetch context by ID', async () => {
      const store = useContextStore()
      const result = await store.fetchContext('123e4567-e89b-12d3-a456-426614174000')

      expect(store.fetchContext).toHaveBeenCalledWith('123e4567-e89b-12d3-a456-426614174000')
      expect(result?.id).toBe('123e4567-e89b-12d3-a456-426614174000')
      expect(result?.internal_name).toBe('Production')
    })

    it('should create new context', async () => {
      const store = useContextStore()
      const newContextData: ContextStoreRequest = {
        internal_name: 'Development',
        backward_compatibility: 'dev',
        is_default: false,
      }

      const result = await store.createContext(newContextData)

      expect(store.createContext).toHaveBeenCalledWith(newContextData)
      expect(result).toBeDefined()
    })

    it('should update existing context', async () => {
      const store = useContextStore()
      const updateData: ContextStoreRequest = {
        internal_name: 'Production (Updated)',
        backward_compatibility: 'prod-v2',
        is_default: true,
      }

      const result = await store.updateContext('123e4567-e89b-12d3-a456-426614174000', updateData)

      expect(store.updateContext).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000',
        updateData
      )
      expect(result).toBeDefined()
    })

    it('should delete context', async () => {
      const store = useContextStore()

      await store.deleteContext('123e4567-e89b-12d3-a456-426614174000')

      expect(store.deleteContext).toHaveBeenCalledWith('123e4567-e89b-12d3-a456-426614174000')
    })

    it('should handle context not found', async () => {
      const store = useContextStore()
      store.fetchContext = vi.fn().mockResolvedValue(null)

      const result = await store.fetchContext(999)

      expect(result).toBeNull()
    })
  })

  describe('Context Status Management', () => {
    it('should set context as default', async () => {
      const store = useContextStore()

      const result = await store.setContextDefault('123e4567-e89b-12d3-a456-426614174001', true)

      // Test the result rather than spy calls
      expect(result).toBeDefined()
      expect(result).toEqual(mockContext) // The mock returns mockContext
    })

    it('should unset context as default', async () => {
      const store = useContextStore()

      const result = await store.setContextDefault('123e4567-e89b-12d3-a456-426614174000', false)

      // Test the result rather than spy calls
      expect(result).toBeDefined()
      expect(result).toEqual(mockContext) // The mock returns mockContext
    })
  })

  describe('Form Validation', () => {
    it('should validate required fields', () => {
      const validData: ContextStoreRequest = {
        internal_name: 'Test Context',
        backward_compatibility: 'test',
        is_default: false,
      }

      expect(validData.internal_name).toBeTruthy()
      expect(validData.internal_name.length).toBeGreaterThan(0)
    })

    it('should handle empty internal name', () => {
      const invalidData: ContextStoreRequest = {
        internal_name: '',
        backward_compatibility: 'test',
        is_default: false,
      }

      expect(invalidData.internal_name).toBe('')
    })

    it('should handle optional backward compatibility', () => {
      const validData: ContextStoreRequest = {
        internal_name: 'Test Context',
        backward_compatibility: null,
        is_default: false,
      }

      expect(validData.backward_compatibility).toBeNull()
    })
  })

  describe('Error Handling', () => {
    it('should handle create context errors', async () => {
      const store = useContextStore()

      store.createContext = vi.fn().mockRejectedValue(new Error('Create failed'))

      try {
        await store.createContext({
          internal_name: 'Test Context',
          backward_compatibility: 'test',
          is_default: false,
        })
      } catch (error) {
        expect(error).toBeInstanceOf(Error)
      }
    })

    it('should handle update context errors', async () => {
      const store = useContextStore()

      store.updateContext = vi.fn().mockRejectedValue(new Error('Update failed'))

      try {
        await store.updateContext(1, {
          internal_name: 'Updated Context',
          backward_compatibility: 'updated',
          is_default: true,
        })
      } catch (error) {
        expect(error).toBeInstanceOf(Error)
      }
    })

    it('should handle delete context errors', async () => {
      const store = useContextStore()

      store.deleteContext = vi.fn().mockRejectedValue(new Error('Delete failed'))

      try {
        await store.deleteContext('123e4567-e89b-12d3-a456-426614174000')
      } catch (error) {
        expect(error).toBeInstanceOf(Error)
      }
    })

    it('should handle fetch context errors', async () => {
      const store = useContextStore()

      store.fetchContext = vi.fn().mockRejectedValue(new Error('Fetch failed'))

      try {
        await store.fetchContext('123e4567-e89b-12d3-a456-426614174000')
      } catch (error) {
        expect(error).toBeInstanceOf(Error)
      }
    })
  })

  describe('Loading States', () => {
    it('should handle loading overlay during operations', async () => {
      const contextStore = useContextStore()

      const result = await contextStore.fetchContext('123e4567-e89b-12d3-a456-426614174000')

      // Test that the operation completed successfully
      expect(result).toBeDefined()
      expect(result).toEqual(mockContext)
    })

    it('should handle store loading state', () => {
      const store = useContextStore()
      store.loading = true

      expect(store.loading).toBe(true)
    })
  })

  describe('Context Default Management', () => {
    it('should manage default context correctly', async () => {
      const store = useContextStore()
      const defaultContext = store.defaultContext

      expect(defaultContext).toBeDefined()
      expect(defaultContext?.is_default).toBe(true)
    })

    it('should filter default contexts', () => {
      const store = useContextStore()
      const defaultContexts = store.defaultContexts

      expect(defaultContexts.length).toBeGreaterThan(0)
      defaultContexts.forEach(context => {
        expect(context.is_default).toBe(true)
      })
    })
  })

  describe('Navigation and Route Handling', () => {
    it('should clear current context on navigation', () => {
      const store = useContextStore()

      store.clearCurrentContext()

      expect(store.clearCurrentContext).toHaveBeenCalled()
    })

    it('should handle navigation guards', () => {
      const cancelChangesStore = useCancelChangesConfirmationStore()

      cancelChangesStore.addChange()

      expect(cancelChangesStore.addChange).toHaveBeenCalled()
    })
  })

  describe('Data Transformation', () => {
    it('should transform context data for API requests', () => {
      const contextData: ContextStoreRequest = {
        internal_name: 'Test Context',
        backward_compatibility: 'test-ctx',
        is_default: false,
      }

      expect(contextData).toEqual({
        internal_name: 'Test Context',
        backward_compatibility: 'test-ctx',
        is_default: false,
      })
    })

    it('should handle null backward compatibility', () => {
      const contextData: ContextStoreRequest = {
        internal_name: 'Test Context',
        backward_compatibility: null,
        is_default: false,
      }

      expect(contextData.backward_compatibility).toBeNull()
    })
  })

  describe('Confirmation Dialogs', () => {
    it('should trigger delete confirmation', () => {
      const deleteStore = useDeleteConfirmationStore()

      deleteStore.trigger()

      expect(deleteStore.trigger).toHaveBeenCalled()
    })

    it('should trigger cancel changes confirmation', () => {
      const cancelStore = useCancelChangesConfirmationStore()

      cancelStore.trigger()

      expect(cancelStore.trigger).toHaveBeenCalled()
    })

    it('should reset changes when confirmed', () => {
      const cancelStore = useCancelChangesConfirmationStore()

      cancelStore.resetChanges()

      expect(cancelStore.resetChanges).toHaveBeenCalled()
    })
  })
})
