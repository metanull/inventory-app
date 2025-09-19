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
    let lastParams: Record<string, unknown> | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createDetailApi: () => ({
        detailIndex: (cfg?: { params?: Record<string, unknown> }) => {
          lastParams = cfg?.params
          return Promise.resolve({ data: { data: details, meta } })
        },
      }),
    } as unknown as ReturnType<typeof useApiClient>)

    const { useDetailStore } = await import('@/stores/detail')
    const store = useDetailStore()
    await store.fetchDetails()

    const lp = lastParams as Record<string, unknown>
    expect(lp.page).toBe(1)
    expect(lp.per_page).toBe(20)
    expect(store.details).toEqual(details)
    expect(store.total).toBe(2)
    expect(store.page).toBe(1)
    expect(store.perPage).toBe(20)
  })

  it('should filter details by item ID', async () => {
    const details = [
      createMockDetail({ id: 'detail-3', item_id: 'item-123' }),
      createMockDetail({ id: 'detail-4', item_id: 'item-123' }),
    ]
    const meta = { total: 2, current_page: 1, per_page: 20 }
    let lastParams: Record<string, unknown> | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createDetailApi: () => ({
        detailIndex: (cfg?: { params?: Record<string, unknown> }) => {
          lastParams = cfg?.params
          return Promise.resolve({ data: { data: details, meta } })
        },
      }),
    } as unknown as ReturnType<typeof useApiClient>)

    const { useDetailStore } = await import('@/stores/detail')
    const store = useDetailStore()
    await store.fetchDetails({ itemId: 'item-123' })

    const lp = lastParams as Record<string, unknown>
    expect(lp.item_id).toBe('item-123')
    expect(store.details).toEqual(details)
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
