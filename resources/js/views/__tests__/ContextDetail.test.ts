import { describe, it, expect, vi, beforeEach, beforeAll, afterAll } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory, type Router } from 'vue-router'
import { flushPromises } from '@vue/test-utils'
import ContextDetail from '../ContextDetail.vue'
import type { ContextResource } from '@metanull/inventory-app-api-client'

// Mock console.error to avoid noise in test output
vi.mock('console', () => ({
  error: vi.fn(),
  warn: vi.fn(),
  log: vi.fn(),
}))

// Store original console methods for cleanup
let originalConsole: Record<string, unknown>

beforeAll(() => {
  originalConsole = { ...console }
  console.error = vi.fn()
  console.warn = vi.fn()
  console.log = vi.fn()
})

afterAll(() => {
  Object.assign(console, originalConsole)
})

// Mock icon modules with comprehensive exports
vi.mock('@heroicons/vue/24/solid', () => ({
  CheckIcon: { name: 'CheckIcon', render: () => null },
  CheckCircleIcon: { name: 'CheckCircleIcon', render: () => null },
  XCircleIcon: { name: 'XCircleIcon', render: () => null },
  XMarkIcon: { name: 'XMarkIcon', render: () => null },
  PlusIcon: { name: 'PlusIcon', render: () => null },
  ArrowLeftIcon: { name: 'ArrowLeftIcon', render: () => null },
  TrashIcon: { name: 'TrashIcon', render: () => null },
  PencilIcon: { name: 'PencilIcon', render: () => null },
  EyeIcon: { name: 'EyeIcon', render: () => null },
}))

vi.mock('@heroicons/vue/24/outline', () => ({
  CheckIcon: { name: 'CheckIcon', render: () => null },
  XMarkIcon: { name: 'XMarkIcon', render: () => null },
  PlusIcon: { name: 'PlusIcon', render: () => null },
  ArrowLeftIcon: { name: 'ArrowLeftIcon', render: () => null },
  TrashIcon: { name: 'TrashIcon', render: () => null },
  PencilIcon: { name: 'PencilIcon', render: () => null },
  EyeIcon: { name: 'EyeIcon', render: () => null },
  StarIcon: { name: 'StarIcon', render: () => null },
  CogIcon: { name: 'CogIcon', render: () => null },
}))

// Mock stores
const mockContextStore = {
  currentContext: null as ContextResource | null,
  loading: false,
  fetchContext: vi.fn().mockImplementation(async (id: string) => {
    // Simulate setting currentContext when fetchContext is called
    if (id === '123e4567-e89b-12d3-a456-426614174000') {
      mockContextStore.currentContext = {
        id: '123e4567-e89b-12d3-a456-426614174000',
        internal_name: 'Production',
        backward_compatibility: 'prod',
        is_default: true,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }
    }
  }),
  clearCurrentContext: vi.fn().mockImplementation(() => {
    mockContextStore.currentContext = null
  }),
  createContext: vi.fn(),
  updateContext: vi.fn(),
  deleteContext: vi.fn(),
  setDefaultContext: vi.fn(),
}

// Component interface for proper typing
interface ContextDetailComponentInstance {
  editForm: {
    internal_name: string
    backward_compatibility: string | null
  }
  mode: 'view' | 'edit' | 'create'
  context: ContextResource | null
  hasUnsavedChanges: boolean
  informationDescription: string
  enterEditMode: () => void
  enterViewMode: () => void
  saveContext: () => Promise<void>
  cancelAction: () => Promise<void>
  deleteContext: () => Promise<void>
  handleStatusToggle: (index: number) => Promise<void>
}

const mockLoadingOverlayStore = {
  show: vi.fn(),
  hide: vi.fn(),
}

const mockErrorDisplayStore = {
  addMessage: vi.fn(),
}

const mockDeleteConfirmationStore = {
  trigger: vi.fn(),
}

const mockCancelChangesConfirmationStore = {
  trigger: vi.fn(),
  addChange: vi.fn(),
  resetChanges: vi.fn(),
}

// Mock the stores
vi.mock('@/stores/context', () => ({
  useContextStore: () => mockContextStore,
}))

vi.mock('@/stores/loadingOverlay', () => ({
  useLoadingOverlayStore: () => mockLoadingOverlayStore,
}))

vi.mock('@/stores/errorDisplay', () => ({
  useErrorDisplayStore: () => mockErrorDisplayStore,
}))

vi.mock('@/stores/deleteConfirmation', () => ({
  useDeleteConfirmationStore: () => mockDeleteConfirmationStore,
}))

vi.mock('@/stores/cancelChangesConfirmation', () => ({
  useCancelChangesConfirmationStore: () => mockCancelChangesConfirmationStore,
}))

describe('ContextDetail.vue', () => {
  let router: Router

  beforeEach(() => {
    // Reset all mocks
    vi.clearAllMocks()

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/contexts', component: { template: '<div>Contexts</div>' } },
        { path: '/contexts/new', component: ContextDetail },
        { path: '/contexts/:id', component: ContextDetail },
      ],
    })

    // Reset mock store states
    mockContextStore.currentContext = null
    mockContextStore.loading = false
  })

  describe('Component Mounting', () => {
    it('should mount successfully in create mode', async () => {
      await router.push('/contexts/new')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(wrapper.exists()).toBe(true)
    })

    it('should mount successfully in edit mode', async () => {
      const mockContext: ContextResource = {
        id: '123e4567-e89b-12d3-a456-426614174000',
        internal_name: 'Production',
        backward_compatibility: 'prod',
        is_default: true,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockContextStore.currentContext = mockContext
      await router.push('/contexts/123e4567-e89b-12d3-a456-426614174000')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(wrapper.exists()).toBe(true)
      expect(mockContextStore.fetchContext).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
    })
  })

  describe('Form Initialization', () => {
    it('should initialize form with empty values for new context', async () => {
      await router.push('/contexts/new')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance
      expect(vm.editForm.internal_name).toBe('')
      expect(vm.editForm.backward_compatibility).toBe('')
    })

    it('should initialize form with context data for editing', async () => {
      await router.push('/contexts/123e4567-e89b-12d3-a456-426614174000')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Verify the component mounted and is in the correct mode
      expect(wrapper.exists()).toBe(true)

      // Verify that fetchContext was called with the correct ID
      expect(mockContextStore.fetchContext).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )

      // The component should be properly initialized
      const vm = wrapper.vm as unknown as ContextDetailComponentInstance
      expect(vm.mode).toBe('view') // Should start in view mode for existing context
    })
  })

  describe('Mode Detection', () => {
    it('should detect create mode', async () => {
      await router.push('/contexts/new')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance
      expect(vm.mode).toBe('create')
    })

    it('should detect edit mode', async () => {
      const mockContext: ContextResource = {
        id: '123e4567-e89b-12d3-a456-426614174001',
        internal_name: 'Development',
        backward_compatibility: 'dev',
        is_default: false,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockContextStore.currentContext = mockContext
      await router.push('/contexts/123e4567-e89b-12d3-a456-426614174001')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance
      expect(vm.mode).toBe('view')
    })
  })

  describe('Information Description', () => {
    it('shows correct description for view mode', async () => {
      const mockContext: ContextResource = {
        id: '123e4567-e89b-12d3-a456-426614174002',
        internal_name: 'Production',
        backward_compatibility: 'prod',
        is_default: true,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockContextStore.currentContext = mockContext
      await router.push('/contexts/123e4567-e89b-12d3-a456-426614174002')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance
      expect(vm.informationDescription).toBe('View and edit the basic properties of this context.')
    })

    it('shows correct description for create mode', async () => {
      await router.push('/contexts/new')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance
      expect(vm.informationDescription).toBe('Configure the basic properties for this new context.')
    })
  })

  describe('Status Toggle (Set Default)', () => {
    it('should toggle default status successfully', async () => {
      const mockContext = {
        id: '123e4567-e89b-12d3-a456-426614174003',
        internal_name: 'Production',
        backward_compatibility: 'prod',
        is_default: false,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockContextStore.currentContext = mockContext
      await router.push('/contexts/123e4567-e89b-12d3-a456-426614174003')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance

      // Trigger status toggle (index 0 for default status)
      await vm.handleStatusToggle(0)

      expect(mockContextStore.updateContext).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174003',
        {
          internal_name: 'Production',
          backward_compatibility: 'prod',
          is_default: true,
        }
      )
      expect(mockErrorDisplayStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Context set as default successfully.'
      )
    })

    it('should handle status toggle error', async () => {
      mockContextStore.updateContext = vi.fn().mockRejectedValue(new Error('Update failed'))

      const mockContext = {
        id: '123e4567-e89b-12d3-a456-426614174004',
        internal_name: 'Production',
        backward_compatibility: 'prod',
        is_default: false,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockContextStore.currentContext = mockContext
      await router.push('/contexts/123e4567-e89b-12d3-a456-426614174004')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance

      // Trigger status toggle
      await vm.handleStatusToggle(0)

      expect(mockErrorDisplayStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to update context status. Please try again.'
      )
    })

    it('should remove default status successfully', async () => {
      const mockContext = {
        id: '123e4567-e89b-12d3-a456-426614174005',
        internal_name: 'Production',
        backward_compatibility: 'prod',
        is_default: true,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockContextStore.currentContext = mockContext
      mockContextStore.updateContext = vi.fn().mockResolvedValue(undefined)
      await router.push('/contexts/123e4567-e89b-12d3-a456-426614174005')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance

      // Trigger status toggle to remove default
      await vm.handleStatusToggle(0)

      expect(mockContextStore.updateContext).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174005',
        {
          internal_name: 'Production',
          backward_compatibility: 'prod',
          is_default: false,
        }
      )
      expect(mockErrorDisplayStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Context removed as default successfully.'
      )
    })

    it('should not trigger toggle for invalid index', async () => {
      const mockContext = {
        id: '123e4567-e89b-12d3-a456-426614174006',
        internal_name: 'Production',
        backward_compatibility: 'prod',
        is_default: true,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockContextStore.currentContext = mockContext
      await router.push('/contexts/123e4567-e89b-12d3-a456-426614174006')

      const wrapper = mount(ContextDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as ContextDetailComponentInstance

      // Trigger status toggle with invalid index
      await vm.handleStatusToggle(1)

      expect(mockContextStore.updateContext).not.toHaveBeenCalled()
    })
  })
})
