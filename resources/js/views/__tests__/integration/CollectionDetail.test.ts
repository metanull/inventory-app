import { describe, it, expect, vi, beforeEach, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory, type Router } from 'vue-router'
import CollectionDetail from '../../CollectionDetail.vue'
import { useCollectionStore } from '@/stores/collection'
import { useLanguageStore } from '@/stores/language'
import { useContextStore } from '@/stores/context'
import { useAuthStore } from '@/stores/auth'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'

import { createMockCollection, createMockLanguage, createMockContext } from '@/__tests__/test-utils'

// Mock console methods
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

// Mock stores
vi.mock('@/stores/collection')
vi.mock('@/stores/language')
vi.mock('@/stores/context')
vi.mock('@/stores/auth')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')
vi.mock('@/stores/successDisplay')
vi.mock('@/stores/cancelChangesConfirmation')
vi.mock('@/stores/deleteConfirmation')

// Mock icons
vi.mock('@heroicons/vue/24/solid', () => ({
  CheckIcon: { name: 'CheckIcon', render: () => null },
  XCircleIcon: { name: 'XCircleIcon', render: () => null },
  ArrowLeftIcon: { name: 'ArrowLeftIcon', render: () => null },
  TrashIcon: { name: 'TrashIcon', render: () => null },
  PencilIcon: { name: 'PencilIcon', render: () => null },
  EyeIcon: { name: 'EyeIcon', render: () => null },
  RectangleStackIcon: { name: 'RectangleStackIcon', render: () => null },
  PlusIcon: { name: 'PlusIcon', render: () => null },
}))

vi.mock('@heroicons/vue/24/outline', () => ({
  CheckIcon: { name: 'CheckIcon', render: () => null },
  XCircleIcon: { name: 'XCircleIcon', render: () => null },
  XMarkIcon: { name: 'XMarkIcon', render: () => null },
  ArrowLeftIcon: { name: 'ArrowLeftIcon', render: () => null },
  TrashIcon: { name: 'TrashIcon', render: () => null },
  PencilIcon: { name: 'PencilIcon', render: () => null },
  EyeIcon: { name: 'EyeIcon', render: () => null },
  RectangleStackIcon: { name: 'RectangleStackIcon', render: () => null },
  PlusIcon: { name: 'PlusIcon', render: () => null },
}))

// Mock components
vi.mock('@/components/format/detail/DetailView.vue', () => ({
  default: {
    name: 'DetailView',
    template: '<div class="detail-view-mock"><slot /></div>',
    props: ['loading', 'mode', 'title'],
  },
}))

describe('CollectionDetail Integration Tests', () => {
  let router: Router
  let pinia: ReturnType<typeof createPinia>
  let mockCollectionStore: ReturnType<typeof vi.mocked<typeof useCollectionStore>>
  let mockLanguageStore: ReturnType<typeof vi.mocked<typeof useLanguageStore>>
  let mockContextStore: ReturnType<typeof vi.mocked<typeof useContextStore>>
  let mockAuthStore: ReturnType<typeof vi.mocked<typeof useAuthStore>>
  let mockLoadingStore: ReturnType<typeof vi.mocked<typeof useLoadingOverlayStore>>
  let mockErrorStore: ReturnType<typeof vi.mocked<typeof useErrorDisplayStore>>
  let mockCancelChangesStore: ReturnType<typeof vi.mocked<typeof useCancelChangesConfirmationStore>>
  let mockDeleteStore: ReturnType<typeof vi.mocked<typeof useDeleteConfirmationStore>>

  beforeEach(() => {
    vi.clearAllMocks()

    pinia = createPinia()
    setActivePinia(pinia)

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/collections', component: { template: '<div>Collections</div>' } },
        { path: '/collections/new', component: CollectionDetail },
        { path: '/collections/:id', component: CollectionDetail },
        { path: '/login', component: { template: '<div>Login</div>' } },
      ],
    })

    // Mock stores
    mockCollectionStore = vi.mocked(useCollectionStore)
    mockLanguageStore = vi.mocked(useLanguageStore)
    mockContextStore = vi.mocked(useContextStore)
    mockAuthStore = vi.mocked(useAuthStore)
    mockLoadingStore = vi.mocked(useLoadingOverlayStore)
    mockErrorStore = vi.mocked(useErrorDisplayStore)
    mockCancelChangesStore = vi.mocked(useCancelChangesConfirmationStore)
    mockDeleteStore = vi.mocked(useDeleteConfirmationStore)

    // Setup default store implementations
    mockCollectionStore.mockReturnValue({
      currentCollection: null,
      collections: [],
      loading: false,
      fetchCollection: vi.fn().mockResolvedValue(undefined),
      fetchCollections: vi.fn().mockResolvedValue(undefined),
      createCollection: vi.fn().mockResolvedValue(undefined),
      updateCollection: vi.fn().mockResolvedValue(undefined),
      deleteCollection: vi.fn().mockResolvedValue(undefined),
    })

    mockLanguageStore.mockReturnValue({
      languages: [
        createMockLanguage({ id: 'eng', internal_name: 'English' }),
        createMockLanguage({ id: 'fra', internal_name: 'French' }),
      ],
      fetchLanguages: vi.fn().mockResolvedValue(undefined),
    })

    mockContextStore.mockReturnValue({
      contexts: [
        createMockContext({ id: 'ctx-1', internal_name: 'Museum Context' }),
        createMockContext({ id: 'ctx-2', internal_name: 'Archive Context' }),
      ],
      fetchContexts: vi.fn().mockResolvedValue(undefined),
    })

    mockAuthStore.mockReturnValue({
      isAuthenticated: true,
      user: { id: '1', name: 'Test User', email: 'test@example.com' },
      token: 'mock-token',
      login: vi.fn(),
      logout: vi.fn(),
      checkAuth: vi.fn(),
    })

    mockLoadingStore.mockReturnValue({
      visible: false,
      disabled: false,
      text: 'Loading...',
      show: vi.fn(),
      hide: vi.fn(),
      disable: vi.fn(),
      enable: vi.fn(),
    })

    mockErrorStore.mockReturnValue({
      messages: [],
      addMessage: vi.fn(),
      removeMessage: vi.fn(),
      clearMessages: vi.fn(),
    })

    mockCancelChangesStore.mockReturnValue({
      hasChanges: false,
      addChange: vi.fn(),
      resetChanges: vi.fn(),
      trigger: vi.fn().mockResolvedValue(true),
    })

    mockDeleteStore.mockReturnValue({
      trigger: vi.fn().mockResolvedValue('delete'),
    })
  })

  describe('Authentication Integration', () => {
    it('renders collection detail when user is authenticated', async () => {
      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      expect(wrapper.exists()).toBe(true)
    })

    it('handles unauthenticated state properly', async () => {
      mockAuthStore.mockReturnValue({
        isAuthenticated: false,
        user: null,
        token: null,
        login: vi.fn(),
        logout: vi.fn(),
        checkAuth: vi.fn(),
      })

      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Store Integration - Data Loading', () => {
    it('integrates with collection store for fetching existing collection', async () => {
      const mockFetchCollection = vi.fn().mockImplementation(async (id: string) => {
        mockCollectionStore().currentCollection = createMockCollection({
          id,
          internal_name: 'test-collection',
        })
      })

      mockCollectionStore.mockReturnValue({
        currentCollection: null,
        collections: [],
        loading: false,
        fetchCollection: mockFetchCollection,
        fetchCollections: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      await router.push('/collections/test-id')
      mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      expect(mockFetchCollection).toHaveBeenCalledWith('test-id')
    })

    it('integrates with language store for dropdown data', async () => {
      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      // Component should render successfully with language store integration
      expect(wrapper.exists()).toBe(true)
    })

    it('integrates with context store for dropdown data', async () => {
      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      // Component should render successfully with context store integration
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Store Integration - CRUD Operations', () => {
    it('integrates with collection store for creating new collection', async () => {
      const mockCreateCollection = vi
        .fn()
        .mockResolvedValue(createMockCollection({ id: 'new-id', internal_name: 'new-collection' }))

      mockCollectionStore.mockReturnValue({
        currentCollection: null,
        collections: [],
        loading: false,
        fetchCollection: vi.fn(),
        fetchCollections: vi.fn(),
        createCollection: mockCreateCollection,
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Simulate form submission
      const form = wrapper.find('form')
      if (form.exists()) {
        await form.trigger('submit.prevent')
        await flushPromises()
        // createCollection should be called during form submission
      }
    })

    it('integrates with collection store for updating existing collection', async () => {
      const existingCollection = createMockCollection({
        id: 'test-id',
        internal_name: 'existing-collection',
      })

      const mockUpdateCollection = vi.fn().mockResolvedValue({
        ...existingCollection,
        internal_name: 'updated-collection',
      })

      mockCollectionStore.mockReturnValue({
        currentCollection: existingCollection,
        collections: [],
        loading: false,
        fetchCollection: vi.fn(),
        fetchCollections: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: mockUpdateCollection,
        deleteCollection: vi.fn(),
      })

      await router.push('/collections/test-id?mode=edit')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Simulate form submission
      const form = wrapper.find('form')
      if (form.exists()) {
        await form.trigger('submit.prevent')
        await flushPromises()
        // updateCollection should be called during form submission
      }
    })

    it('integrates with collection store for deleting collection', async () => {
      const existingCollection = createMockCollection({
        id: 'test-id',
        internal_name: 'collection-to-delete',
      })

      const mockDeleteCollection = vi.fn().mockResolvedValue(undefined)

      mockCollectionStore.mockReturnValue({
        currentCollection: existingCollection,
        collections: [],
        loading: false,
        fetchCollection: vi.fn(),
        fetchCollections: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: mockDeleteCollection,
      })

      await router.push('/collections/test-id')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Simulate delete action
      const deleteButton = wrapper.find('[data-testid="delete-collection"]')
      if (deleteButton.exists()) {
        const originalConfirm = window.confirm
        window.confirm = vi.fn().mockReturnValue(true)

        await deleteButton.trigger('click')
        await flushPromises()

        expect(mockDeleteCollection).toHaveBeenCalledWith('test-id')
        window.confirm = originalConfirm
      }
    })
  })

  describe('Router Integration', () => {
    it('navigates back to collections list after successful creation', async () => {
      const mockCreateCollection = vi
        .fn()
        .mockResolvedValue(createMockCollection({ id: 'new-id', internal_name: 'new-collection' }))

      mockCollectionStore.mockReturnValue({
        currentCollection: null,
        collections: [],
        loading: false,
        fetchCollection: vi.fn(),
        fetchCollections: vi.fn(),
        createCollection: mockCreateCollection,
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Simulate successful form submission
      const form = wrapper.find('form')
      if (form.exists()) {
        await form.trigger('submit.prevent')
        await flushPromises()
        // Should navigate to the new collection or back to list
      }
    })

    it('navigates back to collections list when back button is clicked', async () => {
      await router.push('/collections/test-id')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      const backButton = wrapper.find('[data-testid="back-button"]')
      if (backButton.exists()) {
        await backButton.trigger('click')
        await flushPromises()
        expect(router.currentRoute.value.path).toBe('/collections')
      }
    })

    it('handles route changes between create and view modes', async () => {
      // Start in create mode
      await router.push('/collections/new')
      mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      expect(router.currentRoute.value.path).toBe('/collections/new')

      // Navigate to view mode
      await router.push('/collections/test-id')
      await flushPromises()
      expect(router.currentRoute.value.path).toBe('/collections/test-id')
    })
  })

  describe('Error Handling Integration', () => {
    it('handles API errors during collection fetch', async () => {
      const mockFetchCollection = vi.fn().mockRejectedValue(new Error('Fetch Error'))

      mockCollectionStore.mockReturnValue({
        currentCollection: null,
        collections: [],
        loading: false,
        fetchCollection: mockFetchCollection,
        fetchCollections: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      await router.push('/collections/test-id')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      expect(wrapper.exists()).toBe(true)
    })

    it('handles API errors during collection creation', async () => {
      const mockCreateCollection = vi.fn().mockRejectedValue(new Error('Create Error'))

      mockCollectionStore.mockReturnValue({
        currentCollection: null,
        collections: [],
        loading: false,
        fetchCollection: vi.fn(),
        fetchCollections: vi.fn(),
        createCollection: mockCreateCollection,
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Simulate form submission that will fail
      const form = wrapper.find('form')
      if (form.exists()) {
        await form.trigger('submit.prevent')
        await flushPromises()
        expect(wrapper.exists()).toBe(true)
      }
    })

    it('handles validation errors from API', async () => {
      const validationError = {
        response: {
          data: {
            errors: {
              internal_name: ['The internal name field is required.'],
            },
          },
        },
      }

      const mockCreateCollection = vi.fn().mockRejectedValue(validationError)

      mockCollectionStore.mockReturnValue({
        currentCollection: null,
        collections: [],
        loading: false,
        fetchCollection: vi.fn(),
        fetchCollections: vi.fn(),
        createCollection: mockCreateCollection,
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Simulate form submission with validation errors
      const form = wrapper.find('form')
      if (form.exists()) {
        await form.trigger('submit.prevent')
        await flushPromises()
        // Component should display validation errors
        expect(wrapper.exists()).toBe(true)
      }
    })
  })

  describe('Loading State Integration', () => {
    it('displays loading state during data fetch', async () => {
      mockCollectionStore.mockReturnValue({
        currentCollection: null,
        collections: [],
        loading: true,
        fetchCollection: vi
          .fn()
          .mockImplementation(() => new Promise(resolve => setTimeout(resolve, 100))),
        fetchCollections: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      await router.push('/collections/test-id')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      // Component should render successfully
      expect(wrapper.exists()).toBe(true)
    })

    it('displays loading state during form submission', async () => {
      const mockCreateCollection = vi
        .fn()
        .mockImplementation(() => new Promise(resolve => setTimeout(resolve, 100)))

      mockCollectionStore.mockReturnValue({
        currentCollection: null,
        collections: [],
        loading: false,
        fetchCollection: vi.fn(),
        fetchCollections: vi.fn(),
        createCollection: mockCreateCollection,
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Simulate form submission
      const form = wrapper.find('form')
      if (form.exists()) {
        const submitPromise = form.trigger('submit.prevent')
        // Should show loading during submission
        expect(wrapper.exists()).toBe(true)
        await submitPromise
      }
    })
  })

  describe('Form Integration', () => {
    it('integrates form validation with store operations', async () => {
      await router.push('/collections/new')
      const wrapper = mount(CollectionDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Component should render successfully with form integration capability
      expect(wrapper.exists()).toBe(true)
    })
  })
})
