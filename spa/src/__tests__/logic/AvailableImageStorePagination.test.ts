import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { createMockAvailableImage } from '../test-utils'

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: vi.fn(),
}))
import { useApiClient } from '@/composables/useApiClient'

describe('AvailableImageStore - pagination and API integration', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('should fetch available images with default pagination', async () => {
    const availableImages = [
      createMockAvailableImage({ id: 'img-1', path: 'images/test1.jpg' }),
      createMockAvailableImage({ id: 'img-2', path: 'images/test2.jpg' }),
    ]
    const meta = { total: 2, current_page: 1, per_page: 20 }
    let lastPage: number | undefined
    let lastPerPage: number | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createAvailableImageApi: () => ({
        availableImageIndex: (page?: number, perPage?: number) => {
          lastPage = page
          lastPerPage = perPage
          return Promise.resolve({ data: { data: availableImages, meta } })
        },
      }),
    } as unknown as ReturnType<typeof useApiClient>)

    const { useAvailableImageStore } = await import('@/stores/availableImage')
    const store = useAvailableImageStore()
    await store.fetchAvailableImages()

    expect(lastPage).toBe(1)
    expect(lastPerPage).toBe(20)
    expect(store.availableImages).toEqual(availableImages)
    expect(store.total).toBe(2)
    expect(store.page).toBe(1)
    expect(store.perPage).toBe(20)
  })

  it('should handle custom pagination parameters', async () => {
    const availableImages = [createMockAvailableImage({ id: 'img-3' })]
    const meta = { total: 10, current_page: 3, per_page: 5 }
    let lastPage: number | undefined
    let lastPerPage: number | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createAvailableImageApi: () => ({
        availableImageIndex: (page?: number, perPage?: number) => {
          lastPage = page
          lastPerPage = perPage
          return Promise.resolve({ data: { data: availableImages, meta } })
        },
      }),
    } as unknown as ReturnType<typeof useApiClient>)

    const { useAvailableImageStore } = await import('@/stores/availableImage')
    const store = useAvailableImageStore()
    await store.fetchAvailableImages({ page: 3, perPage: 5 })

    expect(lastPage).toBe(3)
    expect(lastPerPage).toBe(5)
    expect(store.total).toBe(10)
    expect(store.page).toBe(3)
    expect(store.perPage).toBe(5)
  })

  it('should handle loading states correctly', async () => {
    vi.mocked(useApiClient).mockReturnValue({
      createAvailableImageApi: () => ({
        availableImageIndex: () =>
          new Promise(resolve => setTimeout(() => resolve({ data: { data: [] } }), 100)),
      }),
    } as unknown as ReturnType<typeof useApiClient>)

    const { useAvailableImageStore } = await import('@/stores/availableImage')
    const store = useAvailableImageStore()

    expect(store.loading).toBe(false)
    const fetchPromise = store.fetchAvailableImages()
    expect(store.loading).toBe(true)

    await fetchPromise
    expect(store.loading).toBe(false)
  })
})
