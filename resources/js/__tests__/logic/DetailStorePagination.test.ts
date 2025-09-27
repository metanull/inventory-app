import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: vi.fn(),
}))
import { useApiClient } from '@/composables/useApiClient'

// Create a simple mock Detail interface
interface MockDetail {
  id: string
  internal_name: string
  backward_compatibility?: string | null
  item_id: string
  created_at: string | null
  updated_at: string | null
}

const createMockDetail = (overrides: Partial<MockDetail> = {}): MockDetail => ({
  id: '123e4567-e89b-12d3-a456-426614174020',
  internal_name: 'Test Detail',
  backward_compatibility: null,
  item_id: '123e4567-e89b-12d3-a456-426614174002',
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

describe('DetailStore - pagination and item relationship', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('should fetch details with default pagination', async () => {
    const details = [
      createMockDetail({ id: 'detail-1', internal_name: 'Detail 1' }),
      createMockDetail({ id: 'detail-2', internal_name: 'Detail 2' }),
    ]
    const meta = { total: 2, current_page: 1, per_page: 20 }
    let lastPage: number | undefined
    let lastPerPage: number | undefined
    let lastInclude: string | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createDetailApi: () => ({
        detailIndex: (page?: number, perPage?: number, include?: string) => {
          lastPage = page
          lastPerPage = perPage
          lastInclude = include
          return Promise.resolve({ data: { data: details, meta } })
        },
      }),
    } as unknown as ReturnType<typeof useApiClient>)

    const { useDetailStore } = await import('@/stores/detail')
    const store = useDetailStore()
    await store.fetchDetails()

    expect(lastPage).toBe(1)
    expect(lastPerPage).toBe(20)
    expect(lastInclude).toBe('item') // Default include for details
    expect(store.details).toEqual(details)
    expect(store.total).toBe(2)
    expect(store.page).toBe(1)
    expect(store.perPage).toBe(20)
  })

  it('should filter details by item ID', async () => {
    // Mock details with item relationship - some match, some don't
    const allDetails = [
      createMockDetail({ id: 'detail-3', item: { id: 'item-123', internal_name: 'Item 123' } }),
      createMockDetail({ id: 'detail-4', item: { id: 'item-123', internal_name: 'Item 123' } }),
      createMockDetail({ id: 'detail-5', item: { id: 'item-456', internal_name: 'Item 456' } }),
    ]
    const filteredDetails = allDetails.filter(d => d.item?.id === 'item-123')
    const meta = { total: 3, current_page: 1, per_page: 20 }
    let lastPage: number | undefined
    let lastPerPage: number | undefined
    let lastInclude: string | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createDetailApi: () => ({
        detailIndex: (page?: number, perPage?: number, include?: string) => {
          lastPage = page
          lastPerPage = perPage
          lastInclude = include
          return Promise.resolve({ data: { data: allDetails, meta } })
        },
      }),
    } as unknown as ReturnType<typeof useApiClient>)

    const { useDetailStore } = await import('@/stores/detail')
    const store = useDetailStore()
    await store.fetchDetails({ itemId: 'item-123' })

    // API is called without itemId filtering (client-side filtering)
    expect(lastPage).toBe(1)
    expect(lastPerPage).toBe(20)
    expect(lastInclude).toBe('item')
    // Store should contain filtered results
    expect(store.details).toEqual(filteredDetails)
  })

  it('should handle loading states correctly', async () => {
    vi.mocked(useApiClient).mockReturnValue({
      createDetailApi: () => ({
        detailIndex: () =>
          new Promise(resolve => setTimeout(() => resolve({ data: { data: [] } }), 100)),
      }),
    } as unknown as ReturnType<typeof useApiClient>)

    const { useDetailStore } = await import('@/stores/detail')
    const store = useDetailStore()

    expect(store.loading).toBe(false)
    const fetchPromise = store.fetchDetails()
    expect(store.loading).toBe(true)

    await fetchPromise
    expect(store.loading).toBe(false)
  })
})
