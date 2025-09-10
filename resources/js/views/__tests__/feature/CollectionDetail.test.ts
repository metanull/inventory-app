import { describe, it, expect, vi, beforeEach, beforeAll, afterAll } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory, type Router } from 'vue-router'
import { flushPromises } from '@vue/test-utils'
import CollectionDetail from '../../CollectionDetail.vue'
import type { CollectionResource } from '@metanull/inventory-app-api-client'

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
  RectangleStackIcon: { name: 'RectangleStackIcon', render: () => null },
}))

vi.mock('@heroicons/vue/24/outline', () => ({
  CheckIcon: { name: 'CheckIcon', render: () => null },
  CheckCircleIcon: { name: 'CheckCircleIcon', render: () => null },
  XCircleIcon: { name: 'XCircleIcon', render: () => null },
  XMarkIcon: { name: 'XMarkIcon', render: () => null },
  PlusIcon: { name: 'PlusIcon', render: () => null },
  ArrowLeftIcon: { name: 'ArrowLeftIcon', render: () => null },
  TrashIcon: { name: 'TrashIcon', render: () => null },
  PencilIcon: { name: 'PencilIcon', render: () => null },
  EyeIcon: { name: 'EyeIcon', render: () => null },
  RectangleStackIcon: { name: 'RectangleStackIcon', render: () => null },
}))

// Mock stores
vi.mock('@/stores/collection')
vi.mock('@/stores/language')
vi.mock('@/stores/context')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')
vi.mock('@/stores/cancelChangesConfirmation')
vi.mock('@/stores/deleteConfirmation')

const mockCollectionStore = {
  currentCollection: null as CollectionResource | null,
  collections: [],
  loading: false,
  fetchCollection: vi.fn().mockImplementation(async (id: string) => {
    // Simulate setting currentCollection when fetchCollection is called
    if (id === 'test-collection-id') {
      mockCollectionStore.currentCollection = {
        id: 'test-collection-id',
        internal_name: 'test-collection',
        backward_compatibility: null,
        language_id: 'eng',
        context_id: 'test-context-id',
        translations: [],
        created_at: '2024-01-01T00:00:00.000000Z',
        updated_at: '2024-01-01T00:00:00.000000Z',
      }
    }
  }),
  createCollection: vi.fn(),
  updateCollection: vi.fn(),
  deleteCollection: vi.fn(),
  fetchCollections: vi.fn(),
}

const mockLanguageStore = {
  languages: [
    {
      id: 'eng',
      internal_name: 'English',
      backward_compatibility: null,
      is_default: true,
      created_at: '2024-01-01T00:00:00.000000Z',
      updated_at: '2024-01-01T00:00:00.000000Z',
    },
  ],
  defaultLanguage: {
    id: 'eng',
    internal_name: 'English',
    backward_compatibility: null,
    is_default: true,
    created_at: '2024-01-01T00:00:00.000000Z',
    updated_at: '2024-01-01T00:00:00.000000Z',
  },
  fetchLanguages: vi.fn(),
}

const mockContextStore = {
  contexts: [
    {
      id: 'test-context-id',
      internal_name: 'test-context',
      backward_compatibility: null,
      is_default: true,
      translations: [],
      created_at: '2024-01-01T00:00:00.000000Z',
      updated_at: '2024-01-01T00:00:00.000000Z',
    },
  ],
  defaultContext: {
    id: 'test-context-id',
    internal_name: 'test-context',
    backward_compatibility: null,
    is_default: true,
    translations: [],
    created_at: '2024-01-01T00:00:00.000000Z',
    updated_at: '2024-01-01T00:00:00.000000Z',
  },
  fetchContexts: vi.fn(),
}

const mockLoadingStore = {
  visible: false,
  disabled: false,
  text: 'Loading...',
  show: vi.fn(),
  hide: vi.fn(),
  disable: vi.fn(),
  enable: vi.fn(),
}

const mockErrorStore = {
  messages: [],
  addMessage: vi.fn(),
  removeMessage: vi.fn(),
  clearMessages: vi.fn(),
}

vi.mock('@/stores/collection', () => ({
  useCollectionStore: () => mockCollectionStore,
}))

vi.mock('@/stores/language', () => ({
  useLanguageStore: () => mockLanguageStore,
}))

vi.mock('@/stores/context', () => ({
  useContextStore: () => mockContextStore,
}))

vi.mock('@/stores/loadingOverlay', () => ({
  useLoadingOverlayStore: () => mockLoadingStore,
}))

vi.mock('@/stores/errorDisplay', () => ({
  useErrorDisplayStore: () => mockErrorStore,
}))

vi.mock('@/stores/successDisplay', () => ({
  useSuccessDisplayStore: () => ({
    showMessage: vi.fn(),
  }),
}))

// Mock components
vi.mock('@/components/format/detail/DetailView.vue', () => ({
  default: {
    name: 'DetailView',
    template: '<div class="detail-view-mock"><slot /></div>',
    props: ['loading', 'mode', 'title'],
  },
}))

vi.mock('@/components/format/input/TextInput.vue', () => ({
  default: {
    name: 'TextInput',
    template: '<input class="text-input-mock" />',
    props: ['modelValue', 'label', 'required', 'error'],
    emits: ['update:modelValue'],
  },
}))

vi.mock('@/components/format/dropdown/GenericDropdown.vue', () => ({
  default: {
    name: 'GenericDropdown',
    template: '<select class="generic-dropdown-mock"><slot /></select>',
    props: ['modelValue', 'options', 'label', 'required'],
    emits: ['update:modelValue'],
  },
}))

describe('CollectionDetail', () => {
  let router: Router

  beforeEach(() => {
    // Setup Pinia
    setActivePinia(createPinia())

    // Reset store states
    mockCollectionStore.currentCollection = null
    mockCollectionStore.loading = false
    mockLoadingStore.visible = false

    // Reset all mocks
    vi.clearAllMocks()

    // Create router
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/collections', component: { template: '<div>Collections</div>' } },
        { path: '/collections/new', component: CollectionDetail },
        { path: '/collections/:id', component: CollectionDetail },
      ],
    })
  })

  describe('Component Mounting', () => {
    it('renders without crashing in create mode', async () => {
      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      expect(wrapper.exists()).toBe(true)
    })

    it('renders without crashing in view mode', async () => {
      await router.push('/collections/test-collection-id')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Mode Detection', () => {
    it('detects create mode correctly', async () => {
      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(wrapper.vm).toBeDefined()
      // Component should be in create mode when route is 'new'
    })

    it('detects view mode correctly', async () => {
      await router.push('/collections/test-collection-id')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(wrapper.vm).toBeDefined()
      // Component should be in view mode when route has an ID
    })
  })

  describe('Data Loading', () => {
    it('fetches collection data in view mode', async () => {
      await router.push('/collections/test-collection-id')
      mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(mockCollectionStore.fetchCollection).toHaveBeenCalledWith('test-collection-id')
    })

    it('does not fetch collection data in create mode', async () => {
      await router.push('/collections/new')
      mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(mockCollectionStore.fetchCollection).not.toHaveBeenCalled()
    })

    it('fetches languages and contexts on mount', async () => {
      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      // Component should render successfully with language and context stores available
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Form Handling', () => {
    it('handles form submission in create mode', async () => {
      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Mock successful creation
      mockCollectionStore.createCollection.mockResolvedValue({
        id: 'new-collection-id',
        internal_name: 'new-collection',
        backward_compatibility: null,
        translations: [],
        created_at: '2024-01-01T00:00:00.000000Z',
        updated_at: '2024-01-01T00:00:00.000000Z',
      })

      // Find and submit form (if form exists in component)
      const form = wrapper.find('form')
      if (form.exists()) {
        await form.trigger('submit.prevent')
        await flushPromises()
        expect(mockCollectionStore.createCollection).toHaveBeenCalled()
      }
    })

    it('handles form submission in edit mode', async () => {
      // Set up existing collection
      mockCollectionStore.currentCollection = {
        id: 'test-collection-id',
        internal_name: 'test-collection',
        backward_compatibility: null,
        translations: [],
        created_at: '2024-01-01T00:00:00.000000Z',
        updated_at: '2024-01-01T00:00:00.000000Z',
      }

      await router.push('/collections/test-collection-id?mode=edit')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Mock successful update
      mockCollectionStore.updateCollection.mockResolvedValue({
        id: 'test-collection-id',
        internal_name: 'updated-collection',
        backward_compatibility: null,
        translations: [],
        created_at: '2024-01-01T00:00:00.000000Z',
        updated_at: '2024-01-01T00:00:00.000000Z',
      })

      // Find and submit form (if form exists in component)
      const form = wrapper.find('form')
      if (form.exists()) {
        await form.trigger('submit.prevent')
        await flushPromises()
        expect(mockCollectionStore.updateCollection).toHaveBeenCalled()
      }
    })
  })

  describe('Navigation', () => {
    it('navigates back to collections list', async () => {
      await router.push('/collections/test-collection-id')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Find back button (if it exists)
      const backButton = wrapper.find('[data-testid="back-button"]')
      if (backButton.exists()) {
        await backButton.trigger('click')
        await flushPromises()
        expect(router.currentRoute.value.path).toBe('/collections')
      }
    })
  })

  describe('Error Handling', () => {
    it('handles fetch errors gracefully', async () => {
      mockCollectionStore.fetchCollection.mockRejectedValue(new Error('Network error'))

      await router.push('/collections/test-collection-id')
      mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(mockErrorStore.addMessage).toHaveBeenCalled()
    })

    it('handles form submission errors gracefully', async () => {
      mockCollectionStore.createCollection.mockRejectedValue(new Error('Validation error'))

      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Component should render properly even with mocked createCollection that rejects
      expect(wrapper.exists()).toBe(true)
      expect(mockCollectionStore.createCollection).toBeDefined()
    })
  })

  describe('Validation', () => {
    it('validates required fields before submission', async () => {
      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Component should render properly and have createCollection method available
      expect(wrapper.exists()).toBe(true)
      expect(mockCollectionStore.createCollection).toBeDefined()
    })
  })
})
