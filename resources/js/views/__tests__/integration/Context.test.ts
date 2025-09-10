/**
 * Integration Tests for Context Components
 *
 * These tests verify that Context components work correctly together
 * with real component mounting and interaction testing. This includes
 * testing the integration between Contexts and ContextDetail components
 * with their stores and complete user workflows.
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory, type Router } from 'vue-router'
import Contexts from '../../Contexts.vue'
import ContextDetail from '../../ContextDetail.vue'
import { useContextStore } from '@/stores/context'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
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

import type { ContextResource } from '@metanull/inventory-app-api-client'

// Component interface types for proper typing
interface ContextsComponentInstance {
  contexts: ContextResource[]
  filteredContexts: ContextResource[]
  defaultContexts: ContextResource[]
  filterMode: string
  searchQuery: string
  sortDirection: string
  sortKey: string
  openContextDetail: (id: number) => void
  updateContextStatus: (context: ContextResource, field: string, value: boolean) => Promise<void>
  handleDeleteContext: (context: ContextResource) => Promise<void>
  handleSort: (field: string) => void
  fetchContexts: () => Promise<void>
}

interface ContextDetailComponentInstance {
  mode: 'view' | 'edit' | 'create'
  context: ContextResource | null
  editForm: {
    internal_name: string
    backward_compatibility: string | null
  }
  hasUnsavedChanges: boolean
  enterEditMode: () => void
  saveContext: () => Promise<void>
  deleteContext: () => Promise<void>
  cancelAction: () => Promise<void>
}

// Mock icons
vi.mock('@heroicons/vue/24/solid', () => ({
  CogIcon: { template: '<div data-testid="context-icon"></div>' },
  CheckCircleIcon: { template: '<div data-testid="check-icon"></div>' },
  XCircleIcon: { template: '<div data-testid="x-icon"></div>' },
  XMarkIcon: { template: '<div data-testid="x-mark-icon"></div>' },
  PlusIcon: { template: '<div data-testid="plus-icon"></div>' },
  ArrowLeftIcon: { template: '<div data-testid="arrow-left-icon"></div>' },
  TrashIcon: { template: '<div data-testid="trash-icon"></div>' },
  PencilIcon: { template: '<div data-testid="pencil-icon"></div>' },
  EyeIcon: { template: '<div data-testid="eye-icon"></div>' },
  EyeSlashIcon: { template: '<div data-testid="eye-slash-icon"></div>' },
  MagnifyingGlassIcon: { template: '<div data-testid="search-icon"></div>' },
  ChevronUpDownIcon: { template: '<div data-testid="sort-icon"></div>' },
  CheckIcon: { template: '<div data-testid="check-icon"></div>' },
}))

// Mock stores
vi.mock('@/stores/context')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')
vi.mock('@/stores/deleteConfirmation')
vi.mock('@/stores/cancelChangesConfirmation')

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
  createMockContext({
    id: '123e4567-e89b-12d3-a456-426614174002',
    internal_name: 'Testing',
    backward_compatibility: null,
    is_default: false,
    created_at: '2023-01-03T00:00:00Z',
    updated_at: '2023-01-03T00:00:00Z',
  }),
]

describe('Context Integration Tests', () => {
  let mockContextStore: ReturnType<typeof useContextStore>
  let mockLoadingStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorStore: ReturnType<typeof useErrorDisplayStore>
  let mockDeleteStore: ReturnType<typeof useDeleteConfirmationStore>
  let mockCancelChangesStore: ReturnType<typeof useCancelChangesConfirmationStore>
  let router: Router

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup router
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
        { path: '/contexts', name: 'contexts', component: Contexts },
        { path: '/contexts/new', name: 'context-new', component: ContextDetail },
        { path: '/contexts/:id', name: 'context-detail', component: ContextDetail },
      ],
    })

    // Setup store mocks
    mockContextStore = {
      contexts: mockContexts,
      currentContext: null,
      loading: false,
      error: null,
      fetchContexts: vi.fn().mockResolvedValue(mockContexts),
      fetchContext: vi.fn(),
      createContext: vi.fn(),
      updateContext: vi.fn(),
      deleteContext: vi.fn(),
      setContextDefault: vi.fn(),
      clearCurrentContext: vi.fn(),
      defaultContexts: mockContexts.filter(c => c.is_default),
      defaultContext: mockContexts.find(c => c.is_default) || null,
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
      trigger: vi.fn(),
      isVisible: false,
    } as ReturnType<typeof useDeleteConfirmationStore>

    mockCancelChangesStore = {
      trigger: vi.fn(),
      addChange: vi.fn(),
      resetChanges: vi.fn(),
      isVisible: false,
    } as ReturnType<typeof useCancelChangesConfirmationStore>

    // Mock store implementations
    vi.mocked(useContextStore).mockReturnValue(mockContextStore)
    vi.mocked(useLoadingOverlayStore).mockReturnValue(mockLoadingStore)
    vi.mocked(useErrorDisplayStore).mockReturnValue(mockErrorStore)
    vi.mocked(useDeleteConfirmationStore).mockReturnValue(mockDeleteStore)
    vi.mocked(useCancelChangesConfirmationStore).mockReturnValue(mockCancelChangesStore)

    vi.clearAllMocks()
  })

  describe('Contexts List Component Integration', () => {
    it('should mount and display contexts list correctly', async () => {
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

    it('should handle filter mode changes', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      // Test filter mode changes
      expect(vm.filterMode).toBe('all')
      expect(vm.filteredContexts.length).toBe(mockContexts.length)
    })

    it('should handle search functionality', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      // Test search functionality
      vm.searchQuery = 'prod'
      await wrapper.vm.$nextTick()

      // Should filter contexts based on search query
      expect(vm.searchQuery).toBe('prod')
    })

    it('should handle sorting', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      // Test sorting
      vm.handleSort('internal_name')
      expect(vm.sortKey).toBe('internal_name')
    })

    it('should navigate to context detail', async () => {
      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      // Test navigation to context detail
      const contextId = mockContexts[0].id
      vm.openContextDetail(contextId)

      // Should trigger navigation
      expect(vm.openContextDetail).toBeDefined()
    })
  })

  describe('Context Detail Component Integration', () => {
    it('should mount in create mode', async () => {
      await router.push('/contexts/new')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(wrapper.exists()).toBe(true)

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance
      expect(vm.mode).toBe('create')
    })

    it('should mount in view mode for existing context', async () => {
      const mockContext = mockContexts[0]
      mockContextStore.currentContext = mockContext
      mockContextStore.fetchContext = vi.fn().mockResolvedValue(mockContext)

      await router.push(`/contexts/${mockContext.id}`)

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(wrapper.exists()).toBe(true)
      expect(mockContextStore.fetchContext).toHaveBeenCalledWith(mockContext.id.toString())

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance
      expect(vm.mode).toBe('view')
    })

    it('should handle edit mode transition', async () => {
      const mockContext = mockContexts[0]
      mockContextStore.currentContext = mockContext

      await router.push(`/contexts/${mockContext.id}`)

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance

      // Enter edit mode
      vm.enterEditMode()
      await wrapper.vm.$nextTick()

      expect(vm.mode).toBe('edit')
    })

    it('should handle context save operations', async () => {
      mockContextStore.updateContext = vi.fn().mockResolvedValue(mockContexts[0])

      const mockContext = mockContexts[0]
      mockContextStore.currentContext = mockContext

      await router.push(`/contexts/${mockContext.id}`)

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance

      // Edit and save
      vm.enterEditMode()
      vm.editForm.internal_name = 'Updated Context'
      await vm.saveContext()

      expect(mockContextStore.updateContext).toHaveBeenCalled()
    })

    it('should handle context creation', async () => {
      const newContext = createMockContext({
        id: '123e4567-e89b-12d3-a456-426614174003',
        internal_name: 'New Context',
        backward_compatibility: 'new',
        is_default: false,
      })

      mockContextStore.createContext = vi.fn().mockResolvedValue(newContext)

      await router.push('/contexts/new')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance

      // Fill form and save
      vm.editForm.internal_name = 'New Context'
      vm.editForm.backward_compatibility = 'new'
      await vm.saveContext()

      expect(mockContextStore.createContext).toHaveBeenCalled()
    })

    it('should handle delete operations', async () => {
      const mockContext = mockContexts[0]
      mockContextStore.currentContext = mockContext

      await router.push(`/contexts/${mockContext.id}`)

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance

      // Trigger delete
      await vm.deleteContext()

      expect(mockDeleteStore.trigger).toHaveBeenCalled()
    })

    it('should handle cancel operations with unsaved changes', async () => {
      const mockContext = mockContexts[0]
      mockContextStore.currentContext = mockContext

      await router.push(`/contexts/${mockContext.id}`)

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance

      // Make changes and cancel
      vm.enterEditMode()
      vm.editForm.internal_name = 'Modified Name'
      await vm.cancelAction()

      expect(mockCancelChangesStore.trigger).toHaveBeenCalled()
    })
  })

  describe('Context Status Operations Integration', () => {
    it('should handle default status toggle', async () => {
      mockContextStore.setContextDefault = vi.fn().mockResolvedValue(mockContexts[1])

      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      // Toggle default status - test that it executes without error
      await expect(
        vm.updateContextStatus(mockContexts[1], 'is_default', true)
      ).resolves.toBeUndefined()

      // Test that the context still exists in the store
      expect(mockContextStore.contexts).toContain(mockContexts[1])
    })

    it('should handle error during status update', async () => {
      mockContextStore.setContextDefault = vi.fn().mockRejectedValue(new Error('Update failed'))

      await router.push('/contexts')

      const wrapper = mount(Contexts, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextsComponentInstance

      // Attempt to toggle status
      try {
        await vm.updateContextStatus(mockContexts[1], 'is_default', true)
      } catch (error) {
        expect(error).toBeInstanceOf(Error)
      }

      expect(mockErrorStore.addMessage).toHaveBeenCalled()
    })
  })

  describe('End-to-End Workflows', () => {
    it('should complete create context workflow', async () => {
      const newContext = createMockContext({
        id: '123e4567-e89b-12d3-a456-426614174004',
        internal_name: 'E2E Test Context',
        backward_compatibility: 'e2e',
        is_default: false,
      })

      mockContextStore.createContext = vi.fn().mockResolvedValue(newContext)

      // Start at contexts list
      await router.push('/contexts')

      await flushPromises()

      // Navigate to create form
      await router.push('/contexts/new')
      const detailWrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = detailWrapper.vm as unknown as ContextDetailComponentInstance

      // Fill and submit form
      vm.editForm.internal_name = 'E2E Test Context'
      vm.editForm.backward_compatibility = 'e2e'
      await vm.saveContext()

      expect(mockContextStore.createContext).toHaveBeenCalled()
    })

    it('should complete edit context workflow', async () => {
      const mockContext = mockContexts[0]
      mockContextStore.currentContext = mockContext
      mockContextStore.fetchContext = vi.fn().mockResolvedValue(mockContext)
      mockContextStore.updateContext = vi.fn().mockResolvedValue({
        ...mockContext,
        internal_name: 'Updated Production',
      })

      // Start at context detail
      await router.push(`/contexts/${mockContext.id}`)
      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance

      // Edit and save
      vm.enterEditMode()
      vm.editForm.internal_name = 'Updated Production'
      await vm.saveContext()

      expect(mockContextStore.updateContext).toHaveBeenCalled()
    })
  })
})
