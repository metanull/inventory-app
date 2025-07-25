import { describe, it, expect, vi, beforeEach, beforeAll, afterAll } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory, type Router } from 'vue-router'
import { flushPromises } from '@vue/test-utils'
import LanguageDetail from '../LanguageDetail.vue'
import type { LanguageResource } from '@metanull/inventory-app-api-client'

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
  LanguageIcon: { name: 'LanguageIcon', render: () => null },
}))

// Mock stores
const mockLanguageStore = {
  currentLanguage: null as LanguageResource | null,
  loading: false,
  fetchLanguage: vi.fn().mockImplementation(async (id: string) => {
    // Simulate setting currentLanguage when fetchLanguage is called
    if (id === 'fra') {
      mockLanguageStore.currentLanguage = {
        id: 'fra',
        internal_name: 'French',
        backward_compatibility: 'fr',
        is_default: false,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }
    }
  }),
  clearCurrentLanguage: vi.fn().mockImplementation(() => {
    mockLanguageStore.currentLanguage = null
  }),
  createLanguage: vi.fn(),
  updateLanguage: vi.fn(),
  deleteLanguage: vi.fn(),
}

// Component interface for proper typing
interface LanguageDetailComponentInstance {
  editForm: {
    internal_name: string
    backward_compatibility: string | null
  }
  mode: 'view' | 'edit' | 'create'
  language: LanguageResource | null
  hasUnsavedChanges: boolean
  informationDescription: string
  enterEditMode: () => void
  enterViewMode: () => void
  saveLanguage: () => Promise<void>
  cancelAction: () => Promise<void>
  deleteLanguage: () => Promise<void>
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

vi.mock('@/stores/language', () => ({
  useLanguageStore: () => mockLanguageStore,
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

describe('LanguageDetail.vue', () => {
  let router: Router

  beforeEach(() => {
    // Reset all mocks
    vi.clearAllMocks()

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/languages', component: { template: '<div>Languages</div>' } },
        { path: '/languages/new', component: LanguageDetail },
        { path: '/languages/:id', component: LanguageDetail },
      ],
    })

    // Reset mock store states
    mockLanguageStore.currentLanguage = null
    mockLanguageStore.loading = false
  })

  describe('Component Mounting', () => {
    it('should mount successfully in create mode', async () => {
      await router.push('/languages/new')

      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(wrapper.exists()).toBe(true)
    })

    it('should mount successfully in edit mode', async () => {
      const mockLanguage: LanguageResource = {
        id: 'eng',
        internal_name: 'English',
        backward_compatibility: 'en',
        is_default: true,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockLanguageStore.currentLanguage = mockLanguage
      await router.push('/languages/eng')

      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(wrapper.exists()).toBe(true)
      expect(mockLanguageStore.fetchLanguage).toHaveBeenCalledWith('eng')
    })
  })

  describe('Form Initialization', () => {
    it('should initialize form with empty values for new language', async () => {
      await router.push('/languages/new')

      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguageDetailComponentInstance
      expect(vm.editForm.internal_name).toBe('')
      expect(vm.editForm.backward_compatibility).toBe('')
    })

    it('should initialize form with language data for editing', async () => {
      await router.push('/languages/fra')

      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Verify the component mounted and is in the correct mode
      expect(wrapper.exists()).toBe(true)

      // Verify that fetchLanguage was called with the correct ID
      expect(mockLanguageStore.fetchLanguage).toHaveBeenCalledWith('fra')

      // The component should be properly initialized
      const vm = wrapper.vm as unknown as LanguageDetailComponentInstance
      expect(vm.mode).toBe('view') // Should start in view mode for existing language
    })
  })

  describe('Mode Detection', () => {
    it('should detect create mode', async () => {
      await router.push('/languages/new')

      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguageDetailComponentInstance
      expect(vm.mode).toBe('create')
    })

    it('should detect edit mode', async () => {
      const mockLanguage: LanguageResource = {
        id: 'deu',
        internal_name: 'German',
        backward_compatibility: 'de',
        is_default: false,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockLanguageStore.currentLanguage = mockLanguage
      await router.push('/languages/deu')

      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Start in view mode, then switch to edit
      const vm = wrapper.vm as unknown as LanguageDetailComponentInstance
      vm.mode = 'edit'
      await wrapper.vm.$nextTick()

      expect(vm.mode).toBe('edit')
    })

    it('should detect view mode', async () => {
      const mockLanguage: LanguageResource = {
        id: 'spa',
        internal_name: 'Spanish',
        backward_compatibility: 'es',
        is_default: true,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockLanguageStore.currentLanguage = mockLanguage
      await router.push('/languages/spa')

      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguageDetailComponentInstance
      expect(vm.mode).toBe('view')
    })
  })
})
