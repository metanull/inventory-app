import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCollectionStore } from '../collection'
import { createMockCollection } from '../../__tests__/test-utils'
import type { CollectionResource } from '@metanull/inventory-app-api-client'

// Mock the API client
const mockCollectionApi = {
  collectionIndex: vi.fn(),
  collectionShow: vi.fn(),
  collectionStore: vi.fn(),
  collectionUpdate: vi.fn(),
  collectionDestroy: vi.fn(),
}

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: () => ({
    createCollectionApi: () => mockCollectionApi,
  }),
}))

const mockCollections: CollectionResource[] = [
  createMockCollection({
    id: '123e4567-e89b-12d3-a456-426614174000',
    internal_name: 'Test Collection 1',
  }),
  createMockCollection({
    id: '123e4567-e89b-12d3-a456-426614174001',
    internal_name: 'Test Collection 2',
  }),
]

describe('Collection Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  it('should initialize with empty state', () => {
    const store = useCollectionStore()

    expect(store.collections).toEqual([])
    expect(store.currentCollection).toBeNull()
    expect(store.loading).toBe(false)
  })

  it('should clear current collection', () => {
    const store = useCollectionStore()

    store.currentCollection = mockCollections[0]
    store.clearCurrentCollection()

    expect(store.currentCollection).toBeNull()
  })

  it('should handle fetchCollections success', async () => {
    const store = useCollectionStore()

    mockCollectionApi.collectionIndex.mockResolvedValue({
      data: { data: mockCollections },
    })

    await store.fetchCollections()

    expect(mockCollectionApi.collectionIndex).toHaveBeenCalledWith({
      params: { page: 1, per_page: 20 },
      __storeMethod: {
        needsPagination: true,
        supportsInclude: true,
      },
    })
    expect(store.collections).toEqual(mockCollections)
    expect(store.loading).toBe(false)
  })

  it('should handle fetchCollections error', async () => {
    const store = useCollectionStore()
    const error = new Error('Network error')

    mockCollectionApi.collectionIndex.mockRejectedValue(error)

    await expect(store.fetchCollections()).rejects.toThrow('Network error')

    expect(store.loading).toBe(false)
  })

  it('should handle fetchCollection success', async () => {
    const store = useCollectionStore()
    const collection = mockCollections[0]

    mockCollectionApi.collectionShow.mockResolvedValue({
      data: { data: collection },
    })

    await store.fetchCollection('123e4567-e89b-12d3-a456-426614174000')

    expect(mockCollectionApi.collectionShow).toHaveBeenCalledWith(
      '123e4567-e89b-12d3-a456-426614174000',
      {
        __storeMethod: {
          needsPagination: false,
          supportsInclude: true,
        },
      }
    )
    expect(store.currentCollection).toEqual(collection)
  })
})
