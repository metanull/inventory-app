import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useDetailStore } from '../detail'
import { createMockDetail } from '../../__tests__/test-utils'
import type { DetailResource } from '@metanull/inventory-app-api-client'

// Mock the API client
const mockDetailApi = {
  detailIndex: vi.fn(),
  detailShow: vi.fn(),
  detailStore: vi.fn(),
  detailUpdate: vi.fn(),
  detailDestroy: vi.fn(),
}

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: () => ({
    createDetailApi: () => mockDetailApi,
  }),
}))

const mockDetails: DetailResource[] = [
  createMockDetail({ id: '123e4567-e89b-12d3-a456-426614174000', internal_name: 'Test Detail 1' }),
  createMockDetail({ id: '123e4567-e89b-12d3-a456-426614174001', internal_name: 'Test Detail 2' }),
]

describe('Detail Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  it('should initialize with empty state', () => {
    const store = useDetailStore()

    expect(store.details).toEqual([])
    expect(store.currentDetail).toBeNull()
    expect(store.loading).toBe(false)
  })

  it('should clear current detail', () => {
    const store = useDetailStore()

    store.currentDetail = mockDetails[0]
    store.clearCurrentDetail()

    expect(store.currentDetail).toBeNull()
  })

  it('should handle fetchDetails success', async () => {
    const store = useDetailStore()

    mockDetailApi.detailIndex.mockResolvedValue({
      data: { data: mockDetails },
    })

    await store.fetchDetails()

    expect(mockDetailApi.detailIndex).toHaveBeenCalledWith({
      params: { page: 1, per_page: 20, include: 'item' },
      __storeMethod: {
        needsPagination: true,
        supportsInclude: true,
      },
    })
    expect(store.details).toEqual(mockDetails)
    expect(store.loading).toBe(false)
  })

  it('should handle fetchDetails error', async () => {
    const store = useDetailStore()
    const error = new Error('Network error')

    mockDetailApi.detailIndex.mockRejectedValue(error)

    await expect(store.fetchDetails()).rejects.toThrow('Network error')

    expect(store.loading).toBe(false)
  })

  it('should handle fetchDetail success', async () => {
    const store = useDetailStore()
    const detail = mockDetails[0]

    mockDetailApi.detailShow.mockResolvedValue({
      data: { data: detail },
    })

    await store.fetchDetail('123e4567-e89b-12d3-a456-426614174000')

    expect(mockDetailApi.detailShow).toHaveBeenCalledWith('123e4567-e89b-12d3-a456-426614174000', {
      params: { include: 'item' },
      __storeMethod: {
        needsPagination: false,
        supportsInclude: true,
      },
    })
    expect(store.currentDetail).toEqual(detail)
  })
})
