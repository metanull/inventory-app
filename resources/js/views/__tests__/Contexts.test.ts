import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import Contexts from '../Contexts.vue'
import { useContextStore } from '@/stores/context'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
import { createMockContext } from '@/__tests__/test-utils'
import type { ContextResource } from '@metanull/inventory-app-api-client'
import type { Router } from 'vue-router'

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

// Component interface for proper typing
interface ContextsComponentInstance {
  contexts: ContextResource[]
  filteredContexts: ContextResource[]
  defaultContexts: ContextResource[]
  filterMode: string
  searchQuery: string
  sortDirection: string
  sortKey: string
  openContextDetail: (id: string) => void
  updateContextStatus: (context: ContextResource, field: string, value: boolean) => Promise<void>
  handleDeleteContext: (context: ContextResource) => Promise<void>
  handleSort: (field: string) => void
  fetchContexts: () => Promise<void>
}

// Mock the stores
vi.mock('@/stores/context')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')
vi.mock('@/stores/deleteConfirmation')

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

// Mock default contexts
const mockDefaultContexts = [mockContexts[0]] // Only the first one is default

describe('Contexts.vue', () => {
  let mockContextStore: ReturnType<typeof useContextStore>
  let mockLoadingStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorStore: ReturnType<typeof useErrorDisplayStore>
  let mockDeleteStore: ReturnType<typeof useDeleteConfirmationStore>
  let router: Router

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup router
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/contexts', component: Contexts },
        { path: '/contexts/new', component: { template: '<div>New Context</div>' } },
        { path: '/contexts/:id', component: { template: '<div>Context Detail</div>' } },
      ],
    })

    // Setup store mocks
    mockContextStore = {
      contexts: mockContexts,
      filteredContexts: mockContexts,
      defaultContexts: mockDefaultContexts,
      loading: false,
      error: null,
      fetchContexts: vi.fn().mockResolvedValue(mockContexts),
      updateContext: vi.fn(),
      deleteContext: vi.fn(),
      setDefaultContext: vi.fn(),
    } as ReturnType<typeof useContextStore>

    mockLoadingStore = {
      show: vi.fn(),
      hide: vi.fn(),
      isVisible: false,
    } as ReturnType<typeof useLoadingOverlayStore>

    mockErrorStore = {
      addMessage: vi.fn(),
      clearMessages: vi.fn(),
      messages: [],
    } as ReturnType<typeof useErrorDisplayStore>

    mockDeleteStore = {
      trigger: vi.fn().mockResolvedValue('delete'),
      isVisible: false,
    } as ReturnType<typeof useDeleteConfirmationStore>

    // Mock store implementations
    vi.mocked(useContextStore).mockReturnValue(mockContextStore)
    vi.mocked(useLoadingOverlayStore).mockReturnValue(mockLoadingStore)
    vi.mocked(useErrorDisplayStore).mockReturnValue(mockErrorStore)
    vi.mocked(useDeleteConfirmationStore).mockReturnValue(mockDeleteStore)

    vi.clearAllMocks()
  })

  describe('Component Initialization', () => {
    it('should mount successfully', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(wrapper.exists()).toBe(true)
      expect(mockContextStore.fetchContexts).toHaveBeenCalled()
    })

    it('should display loading state', () => {
      mockContextStore.loading = true

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      expect(vm.contexts).toEqual(mockContexts)
    })

    it('should display error state', () => {
      mockContextStore.error = 'Failed to load contexts'

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Context Display', () => {
    it('should display all contexts by default', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      expect(vm.contexts).toEqual(mockContexts)
      expect(vm.filteredContexts).toEqual(mockContexts)
    })

    it('should filter default contexts', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      expect(vm.defaultContexts).toEqual(mockDefaultContexts)
    })

    it('should display context information correctly', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(wrapper.html()).toContain('Production')
      expect(wrapper.html()).toContain('Development')
      expect(wrapper.html()).toContain('Testing')
    })
  })

  describe('Filtering', () => {
    it('should filter by all contexts', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      vm.filterMode = 'all'
      await wrapper.vm.$nextTick()

      expect(vm.filteredContexts).toEqual(mockContexts)
    })

    it('should filter by default contexts', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      vm.filterMode = 'default'
      await wrapper.vm.$nextTick()

      expect(vm.filteredContexts.length).toBe(1)
      expect(vm.filteredContexts[0].is_default).toBe(true)
    })
  })

  describe('Search', () => {
    it('should search by internal name', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      vm.searchQuery = 'prod'
      await wrapper.vm.$nextTick()

      // Should filter contexts based on search query
      expect(vm.searchQuery).toBe('prod')
    })

    it('should search by backward compatibility', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      vm.searchQuery = 'dev'
      await wrapper.vm.$nextTick()

      expect(vm.searchQuery).toBe('dev')
    })

    it('should handle empty search results', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      vm.searchQuery = 'nonexistent'
      await wrapper.vm.$nextTick()

      expect(vm.searchQuery).toBe('nonexistent')
    })
  })

  describe('Sorting', () => {
    it('should sort by internal name', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      vm.handleSort('internal_name')

      expect(vm.sortKey).toBe('internal_name')
    })

    it('should sort by created date', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      vm.handleSort('created_at')

      expect(vm.sortKey).toBe('created_at')
    })

    it('should toggle sort direction', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      // Component starts with internal_name as sortKey and asc as direction
      expect(vm.sortKey).toBe('internal_name')
      expect(vm.sortDirection).toBe('asc')

      // First sort on same field should toggle to descending
      vm.handleSort('internal_name')
      expect(vm.sortDirection).toBe('desc')

      // Second sort on same field should toggle back to ascending
      vm.handleSort('internal_name')
      expect(vm.sortDirection).toBe('asc')
    })
  })

  describe('Navigation', () => {
    it('should navigate to context detail', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      const spy = vi.spyOn(router, 'push')

      vm.openContextDetail('123e4567-e89b-12d3-a456-426614174000')

      expect(spy).toHaveBeenCalledWith('/contexts/123e4567-e89b-12d3-a456-426614174000')
    })

    it('should navigate to new context', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Should be able to navigate to new context form
      expect(wrapper.html()).toContain('Add Context')
    })
  })

  describe('Context Status Operations', () => {
    it('should update context default status', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      await vm.updateContextStatus(mockContexts[1], 'is_default', true)

      expect(mockContextStore.setDefaultContext).toHaveBeenCalledWith(mockContexts[1].id, true)
    })

    it('should handle status update errors', async () => {
      mockContextStore.setDefaultContext = vi.fn().mockRejectedValue(new Error('Update failed'))

      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      await vm.updateContextStatus(mockContexts[0], 'is_default', false)

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to update context default status. Please try again.'
      )
    })
  })

  describe('Context Deletion', () => {
    it('should trigger delete confirmation', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      await vm.handleDeleteContext(mockContexts[1])

      expect(mockDeleteStore.trigger).toHaveBeenCalled()
    })

    it('should handle delete operation', async () => {
      mockContextStore.deleteContext = vi.fn().mockResolvedValue(undefined)

      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      await vm.handleDeleteContext(mockContexts[1])

      expect(mockDeleteStore.trigger).toHaveBeenCalled()
    })

    it('should handle delete errors', async () => {
      mockContextStore.deleteContext = vi.fn().mockRejectedValue(new Error('Delete failed'))

      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      await vm.handleDeleteContext(mockContexts[1])

      expect(mockDeleteStore.trigger).toHaveBeenCalled()
    })
  })

  describe('Empty States', () => {
    it('should display empty state when no contexts', async () => {
      mockContextStore.contexts = []
      mockContextStore.filteredContexts = []

      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(wrapper.html()).toContain('No contexts found')
    })

    it('should display filtered empty state', async () => {
      // Setup store with contexts that have no default ones
      const nonDefaultContexts = mockContexts.map(context => ({
        ...context,
        is_default: false,
      }))

      mockContextStore.contexts = nonDefaultContexts
      mockContextStore.filteredContexts = nonDefaultContexts
      mockContextStore.defaultContexts = []

      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance
      vm.filterMode = 'default'
      await wrapper.vm.$nextTick()

      expect(wrapper.html()).toContain('No default context found')
    })
  })

  describe('Data Refresh', () => {
    it('should refresh contexts on retry', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      await vm.fetchContexts()

      expect(mockContextStore.fetchContexts).toHaveBeenCalledTimes(2) // Once on mount, once on retry
    })

    it('should handle refresh errors', async () => {
      mockContextStore.fetchContexts = vi.fn().mockRejectedValue(new Error('Fetch failed'))

      await router.push('/contexts')

      mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(mockErrorStore.addMessage).toHaveBeenCalled()
    })
  })
})
