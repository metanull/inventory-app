import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAvailableImageStore } from '../availableImage'
import { createMockAvailableImage } from '../../__tests__/test-utils'
import type { AvailableImageResource } from '@metanull/inventory-app-api-client'

// Mock the API client
const mockAvailableImageApi = {
  availableImageIndex: vi.fn(),
  availableImageShow: vi.fn(),
  availableImageStore: vi.fn(),
  availableImageUpdate: vi.fn(),
  availableImageDestroy: vi.fn(),
}

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: () => ({
    createAvailableImageApi: () => mockAvailableImageApi,
  }),
}))

const mockAvailableImages: AvailableImageResource[] = [
  createMockAvailableImage({
    id: '123e4567-e89b-12d3-a456-426614174000',
    path: 'images/test1.jpg',
  }),
  createMockAvailableImage({
    id: '123e4567-e89b-12d3-a456-426614174001',
    path: 'images/test2.jpg',
  }),
]

describe('Available Image Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  it('should initialize with empty state', () => {
    const store = useAvailableImageStore()

    expect(store.availableImages).toEqual([])
    expect(store.currentAvailableImage).toBeNull()
    expect(store.loading).toBe(false)
  })

  it('should clear current available image', () => {
    const store = useAvailableImageStore()

    store.currentAvailableImage = mockAvailableImages[0]
    store.clearCurrentAvailableImage()

    expect(store.currentAvailableImage).toBeNull()
  })

  it('should handle fetchAvailableImages success', async () => {
    const store = useAvailableImageStore()

    mockAvailableImageApi.availableImageIndex.mockResolvedValue({
      data: { data: mockAvailableImages },
    })

    await store.fetchAvailableImages()

    expect(mockAvailableImageApi.availableImageIndex).toHaveBeenCalledWith({
      params: { page: 1, per_page: 20 },
      __storeMethod: {
        needsPagination: true,
      },
    })
    expect(store.availableImages).toEqual(mockAvailableImages)
    expect(store.loading).toBe(false)
  })

  it('should handle fetchAvailableImages error', async () => {
    const store = useAvailableImageStore()
    const error = new Error('Network error')

    mockAvailableImageApi.availableImageIndex.mockRejectedValue(error)

    await expect(store.fetchAvailableImages()).rejects.toThrow('Network error')

    expect(store.loading).toBe(false)
  })

  it('should handle fetchAvailableImage success', async () => {
    const store = useAvailableImageStore()
    const availableImage = mockAvailableImages[0]

    mockAvailableImageApi.availableImageShow.mockResolvedValue({
      data: { data: availableImage },
    })

    await store.fetchAvailableImage('123e4567-e89b-12d3-a456-426614174000')

    expect(mockAvailableImageApi.availableImageShow).toHaveBeenCalledWith(
      '123e4567-e89b-12d3-a456-426614174000',
      {
        __storeMethod: {
          needsPagination: false,
        },
      }
    )
    expect(store.currentAvailableImage).toEqual(availableImage)
  })
})
