import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { createMockContext } from '@/__tests__/test-utils'

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: vi.fn(),
}))
import { useApiClient } from '@/composables/useApiClient'

describe('ContextStore - pagination and includes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('minimal-by-default and pagination meta absent fallback', async () => {
    const contexts = [createMockContext({ id: 'ctx-1' })]
    let lastPage: number | undefined
    let lastPerPage: number | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createContextApi: () => ({
        contextIndex: (page?: number, perPage?: number) => {
          lastPage = page
          lastPerPage = perPage
          return Promise.resolve({ data: { data: contexts } })
        },
      }),
    } as unknown as ReturnType<typeof useApiClient>)

    const { useContextStore } = await import('@/stores/context')
    const store = useContextStore()
    await store.fetchContexts({ page: 4, perPage: 10 })

    // contextIndex now supports pagination parameters
    expect(lastPage).toBe(4)
    expect(lastPerPage).toBe(10)
    expect(store.page).toBe(4)
    expect(store.perPage).toBe(10)
    expect(store.total).toBeNull()
  })
})
