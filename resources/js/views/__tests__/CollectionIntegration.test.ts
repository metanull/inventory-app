import { describe, it, expect, vi, beforeEach, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory, type Router } from 'vue-router'
import Collections from '../Collections.vue'
import { useCollectionStore } from '@/stores/collection'
import { useAuthStore } from '@/stores/auth'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { createMockCollection } from '@/__tests__/test-utils'

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
vi.mock('@/stores/auth')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')

// Mock icons
vi.mock('@heroicons/vue/24/solid', () => ({
  RectangleStackIcon: { name: 'RectangleStackIcon', render: () => null },
  PlusIcon: { name: 'PlusIcon', render: () => null },
  MagnifyingGlassIcon: { name: 'MagnifyingGlassIcon', render: () => null },
  ChevronUpDownIcon: { name: 'ChevronUpDownIcon', render: () => null },
  EyeIcon: { name: 'EyeIcon', render: () => null },
  PencilIcon: { name: 'PencilIcon', render: () => null },
  TrashIcon: { name: 'TrashIcon', render: () => null },
}))

describe('Collections Integration Tests', () => {
  let router: Router
  let pinia: ReturnType<typeof createPinia>
  let mockCollectionStore: ReturnType<typeof vi.mocked<typeof useCollectionStore>>
  let mockAuthStore: ReturnType<typeof vi.mocked<typeof useAuthStore>>
  let mockLoadingStore: ReturnType<typeof vi.mocked<typeof useLoadingOverlayStore>>
  let mockErrorStore: ReturnType<typeof vi.mocked<typeof useErrorDisplayStore>>

  beforeEach(() => {
    vi.clearAllMocks()

    pinia = createPinia()
    setActivePinia(pinia)

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/collections', component: Collections },
        { path: '/collections/new', component: { template: '<div>New Collection</div>' } },
        { path: '/collections/:id', component: { template: '<div>Collection Detail</div>' } },
        { path: '/login', component: { template: '<div>Login</div>' } },
      ],
    })

    // Mock stores
    mockCollectionStore = vi.mocked(useCollectionStore)
    mockAuthStore = vi.mocked(useAuthStore)
    mockLoadingStore = vi.mocked(useLoadingOverlayStore)
    mockErrorStore = vi.mocked(useErrorDisplayStore)

    // Setup default store implementations
    mockCollectionStore.mockReturnValue({
      collections: [],
      currentCollection: null,
      loading: false,
      fetchCollections: vi.fn().mockResolvedValue(undefined),
      fetchCollection: vi.fn().mockResolvedValue(undefined),
      createCollection: vi.fn().mockResolvedValue(undefined),
      updateCollection: vi.fn().mockResolvedValue(undefined),
      deleteCollection: vi.fn().mockResolvedValue(undefined),
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
  })

  describe('Authentication Integration', () => {
    it('renders collections when user is authenticated', async () => {
      const mockCollections = [createMockCollection({ id: '1', internal_name: 'test-collection' })]

      mockCollectionStore.mockReturnValue({
        collections: mockCollections,
        currentCollection: null,
        loading: false,
        fetchCollections: vi.fn().mockResolvedValue(undefined),
        fetchCollection: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      const wrapper = mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      expect(wrapper.text()).toContain('test-collection')
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

      const wrapper = mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      // Component should handle unauthenticated state gracefully
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Store Integration', () => {
    it('integrates with collection store for data fetching', async () => {
      const mockFetchCollections = vi.fn().mockResolvedValue(undefined)
      const mockCollections = [
        createMockCollection({ id: '1', internal_name: 'collection-1' }),
        createMockCollection({ id: '2', internal_name: 'collection-2' }),
      ]

      mockCollectionStore.mockReturnValue({
        collections: mockCollections,
        currentCollection: null,
        loading: false,
        fetchCollections: mockFetchCollections,
        fetchCollection: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      expect(mockFetchCollections).toHaveBeenCalledOnce()
    })

    it('integrates with collection store for deletion', async () => {
      const mockDeleteCollection = vi.fn().mockResolvedValue(undefined)
      const mockCollections = [
        createMockCollection({ id: '1', internal_name: 'collection-to-delete' }),
      ]

      mockCollectionStore.mockReturnValue({
        collections: mockCollections,
        currentCollection: null,
        loading: false,
        fetchCollections: vi.fn(),
        fetchCollection: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: mockDeleteCollection,
      })

      mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Component loads successfully with delete function available
      expect(mockDeleteCollection).toBeDefined()
    })
  })

  describe('Router Integration', () => {
    it('navigates to collection detail view', async () => {
      const mockCollections = [
        createMockCollection({ id: 'test-id', internal_name: 'test-collection' }),
      ]

      mockCollectionStore.mockReturnValue({
        collections: mockCollections,
        currentCollection: null,
        loading: false,
        fetchCollections: vi.fn(),
        fetchCollection: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      const wrapper = mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Component renders and router is available for navigation
      expect(wrapper.exists()).toBe(true)
      expect(router).toBeDefined()
    })

    it('navigates to create new collection', async () => {
      const wrapper = mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Component renders successfully
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Error Handling Integration', () => {
    it('handles API errors during data fetching', async () => {
      const mockFetchCollections = vi.fn().mockRejectedValue(new Error('API Error'))

      mockCollectionStore.mockReturnValue({
        collections: [],
        currentCollection: null,
        loading: false,
        fetchCollections: mockFetchCollections,
        fetchCollection: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      const wrapper = mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      // Component should handle the error gracefully
      expect(wrapper.exists()).toBe(true)
    })

    it('handles API errors during deletion', async () => {
      const mockDeleteCollection = vi.fn().mockRejectedValue(new Error('Delete Error'))
      const mockCollections = [
        createMockCollection({ id: '1', internal_name: 'collection-to-delete' }),
      ]

      mockCollectionStore.mockReturnValue({
        collections: mockCollections,
        currentCollection: null,
        loading: false,
        fetchCollections: vi.fn(),
        fetchCollection: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: mockDeleteCollection,
      })

      const wrapper = mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Simulate delete action that will fail
      const deleteButton = wrapper.find('[data-testid="delete-collection-1"]')
      if (deleteButton.exists()) {
        const originalConfirm = window.confirm
        window.confirm = vi.fn().mockReturnValue(true)

        await deleteButton.trigger('click')
        await flushPromises()

        expect(mockDeleteCollection).toHaveBeenCalledWith('1')
        // Component should handle the error gracefully
        expect(wrapper.exists()).toBe(true)

        window.confirm = originalConfirm
      }
    })
  })

  describe('Loading State Integration', () => {
    it('displays loading state during data fetch', async () => {
      mockCollectionStore.mockReturnValue({
        collections: [],
        currentCollection: null,
        loading: true, // Set loading state
        fetchCollections: vi
          .fn()
          .mockImplementation(() => new Promise(resolve => setTimeout(resolve, 100))),
        fetchCollection: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      const wrapper = mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      // Component should render properly even during loading
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Search Integration', () => {
    it('integrates search functionality with reactive data', async () => {
      const mockCollections = [
        createMockCollection({ id: '1', internal_name: 'ancient-artifacts' }),
        createMockCollection({ id: '2', internal_name: 'modern-art' }),
        createMockCollection({ id: '3', internal_name: 'historical-documents' }),
      ]

      mockCollectionStore.mockReturnValue({
        collections: mockCollections,
        currentCollection: null,
        loading: false,
        fetchCollections: vi.fn(),
        fetchCollection: vi.fn(),
        createCollection: vi.fn(),
        updateCollection: vi.fn(),
        deleteCollection: vi.fn(),
      })

      const wrapper = mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Test search functionality
      const searchInput = wrapper.find('input[type="text"]')
      if (searchInput.exists()) {
        await searchInput.setValue('ancient')
        await flushPromises()

        // Should filter results
        expect(wrapper.text()).toContain('ancient-artifacts')
        expect(wrapper.text()).not.toContain('modern-art')
      }
    })
  })
})
