import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useItemStore } from '../item'
import { createMockItem } from '../../__tests__/test-utils'
import type { ItemResource } from '@metanull/inventory-app-api-client'

// Mock the API client
const mockItemApi = {
  itemIndex: vi.fn(),
  itemShow: vi.fn(),
  itemStore: vi.fn(),
  itemUpdate: vi.fn(),
  itemDestroy: vi.fn(),
}

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: () => ({
    createItemApi: () => mockItemApi,
  }),
}))

const mockItems: ItemResource[] = [
  createMockItem({ id: '123e4567-e89b-12d3-a456-426614174000', internal_name: 'Test Item 1' }),
  createMockItem({ id: '123e4567-e89b-12d3-a456-426614174001', internal_name: 'Test Item 2' }),
]

describe('Item Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  it('should initialize with empty state', () => {
    const store = useItemStore()

    expect(store.items).toEqual([])
    expect(store.currentItem).toBeNull()
    expect(store.loading).toBe(false)
  })

  it('should clear current item', () => {
    const store = useItemStore()

    store.currentItem = mockItems[0]
    store.clearCurrentItem()

    expect(store.currentItem).toBeNull()
  })

  it('should handle fetchItems success', async () => {
    const store = useItemStore()

    mockItemApi.itemIndex.mockResolvedValue({
      data: { data: mockItems },
    })

    await store.fetchItems()

    expect(mockItemApi.itemIndex).toHaveBeenCalledWith({
      params: { page: 1, per_page: 20 },
      __storeMethod: {
        needsPagination: true,
      },
    })
    expect(store.items).toEqual(mockItems)
    expect(store.loading).toBe(false)
  })

  it('should handle fetchItems error', async () => {
    const store = useItemStore()
    const error = new Error('Network error')

    mockItemApi.itemIndex.mockRejectedValue(error)

    await expect(store.fetchItems()).rejects.toThrow('Network error')

    expect(store.loading).toBe(false)
  })

  it('should handle fetchItem success', async () => {
    const store = useItemStore()
    const item = mockItems[0]

    mockItemApi.itemShow.mockResolvedValue({
      data: { data: item },
    })

    const result = await store.fetchItem('123e4567-e89b-12d3-a456-426614174000')

    expect(mockItemApi.itemShow).toHaveBeenCalledWith('123e4567-e89b-12d3-a456-426614174000', {
      params: { include: 'pictures,details,partner' },
      __storeMethod: {
        needsPagination: false,
        supportsInclude: true,
      },
    })
    expect(store.currentItem).toEqual(item)
    expect(result).toEqual(item)
  })
})
