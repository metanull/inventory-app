import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { createMockCollection } from '@/__tests__/test-utils'

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: vi.fn(),
}))
import { useApiClient } from '@/composables/useApiClient'

describe('CollectionStore - pagination and includes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('minimal-by-default and pagination meta updates', async () => {
    const collections = [createMockCollection({ id: 'col-1' })]
    const meta = { total: 7, current_page: 1, per_page: 20 }
    let lastParams: Record<string, unknown> | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createCollectionApi: () => ({
        collectionIndex: (cfg?: { params?: Record<string, unknown> }) => {
          lastParams = cfg?.params
          return Promise.resolve({ data: { data: collections, meta } })
        },
      }),
    } as unknown as {
      createCollectionApi: () => {
        collectionIndex: (cfg?: {
          params?: Record<string, unknown>
        }) => Promise<{ data: { data: unknown; meta: unknown } }>
      }
    })

    const { useCollectionStore } = await import('@/stores/collection')
    const store = useCollectionStore()
    await store.fetchCollections({ page: 1, perPage: 20 })

    const lp = lastParams as Record<string, unknown>
    expect(lp.include).toBeUndefined()
    expect(lp.page).toBe(1)
    expect(lp.per_page).toBe(20)
    expect(store.total).toBe(7)
  })
})
