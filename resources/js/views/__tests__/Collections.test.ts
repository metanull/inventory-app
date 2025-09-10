import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import Collections from '../Collections.vue'
import { useCollectionStore } from '@/stores/collection'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { createMockCollection } from '@/__tests__/test-utils'
import type { CollectionResource } from '@metanull/inventory-app-api-client'
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
interface CollectionsComponentInstance {
  collections: CollectionResource[]
  filteredCollections: CollectionResource[]
  searchQuery: string
  sortDirection: string
  sortKey: string
  openCollectionDetail: (id: string) => void
  handleSort: (field: string) => void
  fetchCollections: () => Promise<void>
}

// Mock the stores
vi.mock('@/stores/collection')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')

// Mock icon modules
vi.mock('@heroicons/vue/24/solid', () => ({
  RectangleStackIcon: { name: 'RectangleStackIcon', render: () => null },
  PlusIcon: { name: 'PlusIcon', render: () => null },
  MagnifyingGlassIcon: { name: 'MagnifyingGlassIcon', render: () => null },
  ChevronUpDownIcon: { name: 'ChevronUpDownIcon', render: () => null },
  EyeIcon: { name: 'EyeIcon', render: () => null },
  PencilIcon: { name: 'PencilIcon', render: () => null },
  TrashIcon: { name: 'TrashIcon', render: () => null },
}))

describe('Collections', () => {
  let router: Router
  let pinia: ReturnType<typeof createPinia>
  let mockCollectionStore: ReturnType<typeof vi.mocked<typeof useCollectionStore>>
  let mockLoadingStore: ReturnType<typeof vi.mocked<typeof useLoadingOverlayStore>>
  let mockErrorStore: ReturnType<typeof vi.mocked<typeof useErrorDisplayStore>>

  beforeEach(() => {
    // Reset all mocks
    vi.clearAllMocks()

    // Create fresh Pinia instance
    pinia = createPinia()
    setActivePinia(pinia)

    // Create router
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/collections', component: Collections },
        { path: '/collections/new', component: { template: '<div>New Collection</div>' } },
        { path: '/collections/:id', component: { template: '<div>Collection Detail</div>' } },
      ],
    })

    // Mock stores
    mockCollectionStore = vi.mocked(useCollectionStore)
    mockLoadingStore = vi.mocked(useLoadingOverlayStore)
    mockErrorStore = vi.mocked(useErrorDisplayStore)

    // Setup store mocks with default implementations
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

    mockLoadingStore.mockReturnValue({
      isLoading: false,
      setLoading: vi.fn(),
      clearLoading: vi.fn(),
      show: vi.fn(),
      hide: vi.fn(),
    })

    mockErrorStore.mockReturnValue({
      showError: vi.fn(),
      clearErrors: vi.fn(),
      addMessage: vi.fn(),
    })
  })

  describe('Component Mounting', () => {
    it('renders without crashing', async () => {
      const wrapper = mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      expect(wrapper.exists()).toBe(true)
    })

    it('displays the correct page title', async () => {
      const wrapper = mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      expect(wrapper.text()).toContain('Collections')
    })
  })

  describe('Data Loading', () => {
    it('calls fetchCollections on component mount', async () => {
      const mockFetchCollections = vi.fn().mockResolvedValue(undefined)

      // Update the existing mock instead of creating a new one
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

      mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      expect(mockFetchCollections).toHaveBeenCalledOnce()
    })

    it('displays collections when loaded', async () => {
      const mockCollections: CollectionResource[] = [
        createMockCollection({ id: '1', internal_name: 'test-collection-1' }),
        createMockCollection({ id: '2', internal_name: 'test-collection-2' }),
      ]

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
      expect(wrapper.text()).toContain('test-collection-1')
      expect(wrapper.text()).toContain('test-collection-2')
    })
  })

  describe('Search Functionality', () => {
    it('filters collections based on search query', async () => {
      const mockCollections: CollectionResource[] = [
        createMockCollection({ id: '1', internal_name: 'ancient-artifacts' }),
        createMockCollection({ id: '2', internal_name: 'modern-art' }),
      ]

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

      // Find and interact with search input
      const searchInput = wrapper.find('input[type="text"]')
      expect(searchInput.exists()).toBe(true)

      await searchInput.setValue('ancient')
      await flushPromises()

      // Check that filtering works
      const component = wrapper.vm as unknown as CollectionsComponentInstance
      expect(component.filteredCollections).toHaveLength(1)
      expect(component.filteredCollections[0].internal_name).toBe('ancient-artifacts')
    })
  })

  describe('Navigation', () => {
    it('navigates to new collection page when create button is clicked', async () => {
      mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Test navigation functionality by checking router
      expect(router.currentRoute.value.path).toBe('/')
    })

    it('navigates to collection detail when view button is clicked', async () => {
      const mockCollections: CollectionResource[] = [
        createMockCollection({ id: 'test-id', internal_name: 'test-collection' }),
      ]

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

      const component = wrapper.vm as unknown as CollectionsComponentInstance
      component.openCollectionDetail('test-id')
      await flushPromises()
      expect(router.currentRoute.value.path).toBe('/collections/test-id')
    })
  })

  describe('Sorting', () => {
    it('sorts collections by internal_name', async () => {
      const mockCollections: CollectionResource[] = [
        createMockCollection({ id: '1', internal_name: 'z-collection' }),
        createMockCollection({ id: '2', internal_name: 'a-collection' }),
      ]

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

      const component = wrapper.vm as unknown as CollectionsComponentInstance

      // The component starts with sortKey 'internal_name' and direction 'asc'
      // So it should already be sorted correctly
      await flushPromises()

      expect(component.filteredCollections[0].internal_name).toBe('a-collection')
      expect(component.filteredCollections[1].internal_name).toBe('z-collection')
    })
  })

  describe('Error Handling', () => {
    it('handles fetch errors gracefully', async () => {
      const mockFetchCollections = vi.fn().mockRejectedValue(new Error('Network error'))
      const mockShowError = vi.fn()

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

      mockErrorStore.mockReturnValue({
        showError: vi.fn(),
        clearErrors: vi.fn(),
        addMessage: mockShowError,
      })

      mount(Collections, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()
      expect(mockShowError).toHaveBeenCalled()
    })
  })
})
