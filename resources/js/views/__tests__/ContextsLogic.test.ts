/**
 * Unit Tests for Contexts Component Business Logic
 *
 * These tests focus on the core functionality and business logic
 * of the Contexts component without dealing with complex UI rendering.
 *
 * Tests cover:
 * - Context filtering (All, Default) - Context-specific features
 * - Search functionality across internal_name and backward_compatibility
 * - Sorting functionality
 * - Store interactions and data fetching
 * - Error handling
 * - Status updates (default/non-default) - Context-specific features
 * - Delete operations
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useContextStore } from '@/stores/context'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
import { createMockContext } from '@/__tests__/test-utils'
import type { ContextResource } from '@metanull/inventory-app-api-client'

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
vi.mock('@/stores/deleteConfirmation')

// Test data - covering different context states for comprehensive testing
const mockContexts: ContextResource[] = [
  createMockContext({
    id: '123e4567-e89b-12d3-a456-426614174000',
    internal_name: 'Production',
    backward_compatibility: 'prod',
    is_default: true,
    created_at: '2023-01-01T00:00:00Z',
  }),
  createMockContext({
    id: '123e4567-e89b-12d3-a456-426614174001',
    internal_name: 'Development',
    backward_compatibility: 'dev',
    is_default: false,
    created_at: '2023-02-01T00:00:00Z',
  }),
  createMockContext({
    id: '123e4567-e89b-12d3-a456-426614174002',
    internal_name: 'Testing',
    backward_compatibility: null,
    is_default: false,
    created_at: '2023-03-01T00:00:00Z',
  }),
]

// Simulate the business logic functions from the component
class ContextsLogic {
  public filterMode: 'all' | 'default' = 'all'
  public searchQuery = ''
  public sortKey = 'internal_name'
  public sortDirection: 'asc' | 'desc' = 'asc'

  constructor(
    private contextStore: ReturnType<typeof useContextStore>,
    private loadingStore: ReturnType<typeof useLoadingOverlayStore>,
    private errorStore: ReturnType<typeof useErrorDisplayStore>,
    private deleteStore: ReturnType<typeof useDeleteConfirmationStore>
  ) {}

  get contexts() {
    return this.contextStore.contexts
  }

  get defaultContexts() {
    return this.contextStore.defaultContexts
  }

  get filteredContexts() {
    let list: ContextResource[]

    switch (this.filterMode) {
      case 'default':
        list = this.defaultContexts
        break
      default:
        list = this.contexts
    }

    // Apply search filter
    const query = this.searchQuery.trim().toLowerCase()
    if (query.length > 0) {
      list = list.filter(context => {
        const name = context.internal_name?.toLowerCase() ?? ''
        const compat = context.backward_compatibility?.toLowerCase() ?? ''
        return name.includes(query) || compat.includes(query)
      })
    }

    // Apply sorting
    return [...list].sort((a, b) => {
      const key = this.sortKey
      let valA: unknown
      let valB: unknown

      if (key === 'internal_name') {
        valA = a.internal_name ?? ''
        valB = b.internal_name ?? ''
      } else {
        valA = (a as unknown as Record<string, unknown>)[key]
        valB = (b as unknown as Record<string, unknown>)[key]
      }

      if (valA == null && valB == null) return 0
      if (valA == null) return 1
      if (valB == null) return -1
      if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1
      if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1
      return 0
    })
  }

  handleSort(key: string) {
    if (this.sortKey === key) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc'
    } else {
      this.sortKey = key
      this.sortDirection = 'asc'
    }
  }

  async updateContextStatus(context: ContextResource, field: string, value: boolean) {
    try {
      this.loadingStore.show('Updating...')

      if (field === 'is_default') {
        await this.contextStore.setContextDefault(context.id, value)
        this.errorStore.addMessage(
          'info',
          `Context ${value ? 'set as default' : 'removed from default'} successfully.`
        )
      }
    } catch (error) {
      this.errorStore.addMessage('error', 'Failed to update context status. Please try again.')
      throw error
    } finally {
      this.loadingStore.hide()
    }
  }

  async handleDeleteContext(contextToDelete: ContextResource) {
    const result = await this.deleteStore.trigger(
      'Delete Context',
      `Are you sure you want to delete "${contextToDelete.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        this.loadingStore.show('Deleting...')
        await this.contextStore.deleteContext(contextToDelete.id)
        this.errorStore.addMessage('info', 'Context deleted successfully.')
      } catch (error) {
        this.errorStore.addMessage('error', 'Failed to delete context. Please try again.')
        throw error
      } finally {
        this.loadingStore.hide()
      }
    }
  }

  async fetchContexts() {
    try {
      this.loadingStore.show()
      await this.contextStore.fetchContexts()
      this.errorStore.addMessage('info', 'Contexts refreshed successfully.')
    } catch (error) {
      this.errorStore.addMessage('error', 'Failed to refresh contexts. Please try again.')
      throw error
    } finally {
      this.loadingStore.hide()
    }
  }
}

describe('Contexts Component Business Logic', () => {
  let mockContextStore: ReturnType<typeof useContextStore>
  let mockLoadingStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorStore: ReturnType<typeof useErrorDisplayStore>
  let mockDeleteStore: ReturnType<typeof useDeleteConfirmationStore>
  let contextsLogic: ContextsLogic

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup store mocks
    mockContextStore = {
      contexts: mockContexts,
      defaultContexts: [mockContexts[0]], // Only contexts with is_default: true
      fetchContexts: vi.fn().mockResolvedValue(mockContexts),
      setContextDefault: vi.fn(),
      deleteContext: vi.fn(),
    } as ReturnType<typeof useContextStore>

    mockLoadingStore = {
      show: vi.fn(),
      hide: vi.fn(),
    } as ReturnType<typeof useLoadingOverlayStore>

    mockErrorStore = {
      addMessage: vi.fn(),
    } as ReturnType<typeof useErrorDisplayStore>

    mockDeleteStore = {
      trigger: vi.fn().mockResolvedValue('cancel'),
    } as ReturnType<typeof useDeleteConfirmationStore>

    // Mock store implementations
    vi.mocked(useContextStore).mockReturnValue(mockContextStore)
    vi.mocked(useLoadingOverlayStore).mockReturnValue(mockLoadingStore)
    vi.mocked(useErrorDisplayStore).mockReturnValue(mockErrorStore)
    vi.mocked(useDeleteConfirmationStore).mockReturnValue(mockDeleteStore)

    contextsLogic = new ContextsLogic(
      mockContextStore,
      mockLoadingStore,
      mockErrorStore,
      mockDeleteStore
    )

    vi.clearAllMocks()
  })

  describe('Context-Specific Filtering Features', () => {
    describe('All Contexts Filter', () => {
      it('should show all contexts when filter is set to "all"', () => {
        contextsLogic.filterMode = 'all'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(3)
        // Check that all contexts are included (regardless of order)
        expect(filteredContexts.map(c => c.id).sort()).toEqual(mockContexts.map(c => c.id).sort())
      })
    })

    describe('Default Contexts Filter (Context-specific)', () => {
      it('should show only default contexts when filter is set to "default"', () => {
        contextsLogic.filterMode = 'default'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(1)
        expect(filteredContexts[0].is_default).toBe(true)
        expect(filteredContexts[0].internal_name).toBe('Production')
      })

      it('should return empty array when no default contexts exist', () => {
        mockContextStore.defaultContexts = []
        contextsLogic.filterMode = 'default'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts).toEqual([])
      })

      it('should include contexts with is_default: true in default filter', () => {
        const additionalDefaultContext = createMockContext({
          id: '123e4567-e89b-12d3-a456-426614174003',
          internal_name: 'Another Default',
          backward_compatibility: 'another-default',
          is_default: true,
          created_at: '2023-04-01T00:00:00Z',
        })

        mockContextStore.defaultContexts = [mockContexts[0], additionalDefaultContext]
        contextsLogic.filterMode = 'default'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(2)
        expect(filteredContexts.every(c => c.is_default)).toBe(true)
      })
    })
  })

  describe('Search Functionality', () => {
    describe('Basic Search', () => {
      it('should search by internal_name', () => {
        contextsLogic.searchQuery = 'prod'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(1)
        expect(filteredContexts[0].internal_name).toBe('Production')
      })

      it('should search by backward_compatibility', () => {
        contextsLogic.searchQuery = 'dev'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(1)
        expect(filteredContexts[0].backward_compatibility).toBe('dev')
      })

      it('should be case insensitive', () => {
        contextsLogic.searchQuery = 'PRODUCTION'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(1)
        expect(filteredContexts[0].internal_name).toBe('Production')
      })

      it('should return empty array when no matches found', () => {
        contextsLogic.searchQuery = 'nonexistent'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts).toEqual([])
      })

      it('should handle partial matches', () => {
        contextsLogic.searchQuery = 'test'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(1)
        expect(filteredContexts[0].internal_name).toBe('Testing')
      })
    })

    describe('Search Edge Cases', () => {
      it('should handle empty search query', () => {
        contextsLogic.searchQuery = ''

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(3)
        // Check that all contexts are included (regardless of order)
        expect(filteredContexts.map(c => c.id).sort()).toEqual(mockContexts.map(c => c.id).sort())
      })

      it('should handle whitespace-only search query', () => {
        contextsLogic.searchQuery = '   '

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(3)
        // Check that all contexts are included (regardless of order)
        expect(filteredContexts.map(c => c.id).sort()).toEqual(mockContexts.map(c => c.id).sort())
      })

      it('should handle null backward_compatibility gracefully', () => {
        contextsLogic.searchQuery = 'testing'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(1)
        expect(filteredContexts[0].internal_name).toBe('Testing')
        expect(filteredContexts[0].backward_compatibility).toBeNull()
      })
    })

    describe('Search with Filtering', () => {
      it('should combine search with default filter', () => {
        contextsLogic.filterMode = 'default'
        contextsLogic.searchQuery = 'prod'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(1)
        expect(filteredContexts[0].internal_name).toBe('Production')
        expect(filteredContexts[0].is_default).toBe(true)
      })

      it('should return empty when search does not match filtered contexts', () => {
        contextsLogic.filterMode = 'default'
        contextsLogic.searchQuery = 'dev'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts).toEqual([])
      })
    })
  })

  describe('Sorting Functionality', () => {
    describe('Sort by Internal Name', () => {
      it('should sort contexts by internal_name in ascending order', () => {
        contextsLogic.sortKey = 'internal_name'
        contextsLogic.sortDirection = 'asc'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts[0].internal_name).toBe('Development')
        expect(filteredContexts[1].internal_name).toBe('Production')
        expect(filteredContexts[2].internal_name).toBe('Testing')
      })

      it('should sort contexts by internal_name in descending order', () => {
        contextsLogic.sortKey = 'internal_name'
        contextsLogic.sortDirection = 'desc'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts[0].internal_name).toBe('Testing')
        expect(filteredContexts[1].internal_name).toBe('Production')
        expect(filteredContexts[2].internal_name).toBe('Development')
      })
    })

    describe('Sort by Other Fields', () => {
      it('should sort contexts by created_at date', () => {
        contextsLogic.sortKey = 'created_at'
        contextsLogic.sortDirection = 'asc'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts[0].created_at).toBe('2023-01-01T00:00:00Z')
        expect(filteredContexts[1].created_at).toBe('2023-02-01T00:00:00Z')
        expect(filteredContexts[2].created_at).toBe('2023-03-01T00:00:00Z')
      })

      it('should handle null values when sorting', () => {
        // Create a context with null internal_name
        const nullContext = createMockContext({
          id: '123e4567-e89b-12d3-a456-426614174003',
          internal_name: null as any,
          backward_compatibility: 'null-context',
          is_default: false,
          created_at: '2023-04-01T00:00:00Z',
        })

        const contextsWithNulls = [...mockContexts, nullContext]
        mockContextStore.contexts = contextsWithNulls

        contextsLogic.sortKey = 'internal_name'
        contextsLogic.sortDirection = 'asc'

        const filteredContexts = contextsLogic.filteredContexts

        // Verify that the null context is included in the results
        const hasNullContext = filteredContexts.some(c => c.internal_name === null)
        expect(hasNullContext).toBe(true)

        // Verify total count includes the null context
        expect(filteredContexts.length).toBe(4)
      })
    })

    describe('Sort Direction Toggle', () => {
      it('should toggle sort direction when sorting by the same field', () => {
        // Reset to ensure we start from a known state
        contextsLogic.sortKey = 'created_at'
        contextsLogic.sortDirection = 'asc'

        contextsLogic.handleSort('internal_name')
        expect(contextsLogic.sortKey).toBe('internal_name')
        expect(contextsLogic.sortDirection).toBe('asc')

        contextsLogic.handleSort('internal_name')
        expect(contextsLogic.sortKey).toBe('internal_name')
        expect(contextsLogic.sortDirection).toBe('desc')
      })

      it('should reset to ascending when sorting by a different field', () => {
        contextsLogic.sortKey = 'internal_name'
        contextsLogic.sortDirection = 'desc'

        contextsLogic.handleSort('created_at')
        expect(contextsLogic.sortKey).toBe('created_at')
        expect(contextsLogic.sortDirection).toBe('asc')
      })
    })
  })

  describe('Context Status Operations', () => {
    describe('Update Default Status', () => {
      it('should successfully update context default status', async () => {
        const context = mockContexts[1]
        mockContextStore.setContextDefault = vi.fn().mockResolvedValue(undefined)

        await contextsLogic.updateContextStatus(context, 'is_default', true)

        expect(mockLoadingStore.show).toHaveBeenCalledWith('Updating...')
        expect(mockContextStore.setContextDefault).toHaveBeenCalledWith(context.id, true)
        expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
          'info',
          'Context set as default successfully.'
        )
        expect(mockLoadingStore.hide).toHaveBeenCalled()
      })

      it('should handle removing default status', async () => {
        const context = mockContexts[0]
        mockContextStore.setContextDefault = vi.fn().mockResolvedValue(undefined)

        await contextsLogic.updateContextStatus(context, 'is_default', false)

        expect(mockContextStore.setContextDefault).toHaveBeenCalledWith(context.id, false)
        expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
          'info',
          'Context removed from default successfully.'
        )
      })

      it('should handle update errors gracefully', async () => {
        const context = mockContexts[1]
        const updateError = new Error('Update failed')
        mockContextStore.setContextDefault = vi.fn().mockRejectedValue(updateError)

        await expect(
          contextsLogic.updateContextStatus(context, 'is_default', true)
        ).rejects.toThrow('Update failed')

        expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
          'error',
          'Failed to update context status. Please try again.'
        )
        expect(mockLoadingStore.hide).toHaveBeenCalled()
      })
    })
  })

  describe('Context Deletion', () => {
    describe('Successful Deletion', () => {
      it('should successfully delete context when user confirms', async () => {
        const contextToDelete = mockContexts[1]
        mockDeleteStore.trigger = vi.fn().mockResolvedValue('delete')
        mockContextStore.deleteContext = vi.fn().mockResolvedValue(undefined)

        await contextsLogic.handleDeleteContext(contextToDelete)

        expect(mockDeleteStore.trigger).toHaveBeenCalledWith(
          'Delete Context',
          `Are you sure you want to delete "${contextToDelete.internal_name}"? This action cannot be undone.`
        )
        expect(mockLoadingStore.show).toHaveBeenCalledWith('Deleting...')
        expect(mockContextStore.deleteContext).toHaveBeenCalledWith(contextToDelete.id)
        expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
          'info',
          'Context deleted successfully.'
        )
        expect(mockLoadingStore.hide).toHaveBeenCalled()
      })

      it('should not delete context when user cancels', async () => {
        const contextToDelete = mockContexts[1]
        mockDeleteStore.trigger = vi.fn().mockResolvedValue('cancel')

        await contextsLogic.handleDeleteContext(contextToDelete)

        expect(mockDeleteStore.trigger).toHaveBeenCalled()
        expect(mockContextStore.deleteContext).not.toHaveBeenCalled()
        expect(mockLoadingStore.show).not.toHaveBeenCalled()
      })
    })

    describe('Deletion Error Handling', () => {
      it('should handle deletion errors gracefully', async () => {
        const contextToDelete = mockContexts[1]
        const deleteError = new Error('Delete failed')
        mockDeleteStore.trigger = vi.fn().mockResolvedValue('delete')
        mockContextStore.deleteContext = vi.fn().mockRejectedValue(deleteError)

        await expect(contextsLogic.handleDeleteContext(contextToDelete)).rejects.toThrow(
          'Delete failed'
        )

        expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
          'error',
          'Failed to delete context. Please try again.'
        )
        expect(mockLoadingStore.hide).toHaveBeenCalled()
      })
    })
  })

  describe('Data Fetching Operations', () => {
    describe('Fetch Contexts', () => {
      it('should successfully fetch contexts', async () => {
        mockContextStore.fetchContexts = vi.fn().mockResolvedValue(mockContexts)

        await contextsLogic.fetchContexts()

        expect(mockLoadingStore.show).toHaveBeenCalled()
        expect(mockContextStore.fetchContexts).toHaveBeenCalled()
        expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
          'info',
          'Contexts refreshed successfully.'
        )
        expect(mockLoadingStore.hide).toHaveBeenCalled()
      })

      it('should handle fetch errors gracefully', async () => {
        const fetchError = new Error('Fetch failed')
        mockContextStore.fetchContexts = vi.fn().mockRejectedValue(fetchError)

        await expect(contextsLogic.fetchContexts()).rejects.toThrow('Fetch failed')

        expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
          'error',
          'Failed to refresh contexts. Please try again.'
        )
        expect(mockLoadingStore.hide).toHaveBeenCalled()
      })
    })
  })

  describe('Integration Tests', () => {
    describe('Filter + Search + Sort Integration', () => {
      it('should properly combine filtering, searching, and sorting', () => {
        // Add another default context for better testing
        const anotherDefaultContext = createMockContext({
          id: '123e4567-e89b-12d3-a456-426614174003',
          internal_name: 'Alpha Production',
          backward_compatibility: 'alpha-prod',
          is_default: true,
          created_at: '2023-04-01T00:00:00Z',
        })
        mockContextStore.defaultContexts = [mockContexts[0], anotherDefaultContext]

        contextsLogic.filterMode = 'default'
        contextsLogic.searchQuery = 'prod'
        contextsLogic.sortKey = 'internal_name'
        contextsLogic.sortDirection = 'asc'

        const filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(2)
        expect(filteredContexts[0].internal_name).toBe('Alpha Production')
        expect(filteredContexts[1].internal_name).toBe('Production')
        expect(filteredContexts.every(c => c.is_default)).toBe(true)
      })
    })

    describe('Real-world Usage Scenarios', () => {
      it('should handle context management workflow', async () => {
        // Step 1: Fetch contexts
        await contextsLogic.fetchContexts()
        expect(mockContextStore.fetchContexts).toHaveBeenCalled()

        // Step 2: Filter to show only defaults
        contextsLogic.filterMode = 'default'
        let filteredContexts = contextsLogic.filteredContexts
        expect(filteredContexts.length).toBe(1)

        // Step 3: Update a context to be default
        const nonDefaultContext = mockContexts[1]
        await contextsLogic.updateContextStatus(nonDefaultContext, 'is_default', true)
        expect(mockContextStore.setContextDefault).toHaveBeenCalledWith(nonDefaultContext.id, true)

        // Step 4: Search for specific context
        contextsLogic.searchQuery = 'dev'
        filteredContexts = contextsLogic.filteredContexts
        // This would depend on updated store state in real app
      })
    })
  })
})
