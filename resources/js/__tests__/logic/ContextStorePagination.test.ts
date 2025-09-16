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
    let lastParams: Record<string, unknown> | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createContextApi: () => ({
        contextIndex: (cfg?: { params?: Record<string, unknown> }) => {
          lastParams = cfg?.params
          return Promise.resolve({ data: { data: contexts } })
        },
      }),
    } as unknown as {
      createContextApi: () => {
        contextIndex: (cfg?: {
          params?: Record<string, unknown>
        }) => Promise<{ data: { data: unknown } }>
      }
    })

    const { useContextStore } = await import('@/stores/context')
    const store = useContextStore()
    await store.fetchContexts({ page: 4, perPage: 10 })

    const lp = lastParams as Record<string, unknown>
    expect(lp.include).toBeUndefined()
    expect(lp.page).toBe(4)
    expect(lp.per_page).toBe(10)
    expect(store.page).toBe(4)
    expect(store.perPage).toBe(10)
    expect(store.total).toBeNull()
  })
})
