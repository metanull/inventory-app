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
    let lastPage: number | undefined
    let lastPerPage: number | undefined
    let lastInclude: string | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createCollectionApi: () => ({
        collectionIndex: (page?: number, perPage?: number, include?: string) => {
          lastPage = page
          lastPerPage = perPage
          lastInclude = include
          return Promise.resolve({ data: { data: collections, meta } })
        },
      }),
    } as unknown as ReturnType<typeof useApiClient>)

    const { useCollectionStore } = await import('@/stores/collection')
    const store = useCollectionStore()
    await store.fetchCollections({ page: 1, perPage: 20 })

    expect(lastInclude).toBeUndefined()
    expect(lastPage).toBe(1)
    expect(lastPerPage).toBe(20)
    expect(store.total).toBe(7)
  })
})
