import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { createMockItem } from '@/__tests__/test-utils'

// Mock useApiClient to control API behavior
vi.mock('@/composables/useApiClient', () => ({
  useApiClient: vi.fn(),
}))
import { useApiClient } from '@/composables/useApiClient'

describe('ItemStore - pagination and includes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('uses minimal-by-default (no include) and applies pagination meta', async () => {
    const items = [createMockItem({ id: 'itm-1' })]
    const meta = { total: 100, current_page: 2, per_page: 50 }
    let lastParams: Record<string, unknown> | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createItemApi: () => ({
        itemIndex: (cfg?: { params?: Record<string, unknown> }) => {
          lastParams = cfg?.params
          return Promise.resolve({ data: { data: items, meta } })
        },
      }),
    } as unknown as {
      createItemApi: () => {
        itemIndex: (cfg?: {
          params?: Record<string, unknown>
        }) => Promise<{ data: { data: unknown; meta: unknown } }>
      }
    })

    const { useItemStore } = await import('@/stores/item')
    const store = useItemStore()
    await store.fetchItems({ page: 2, perPage: 50 })

    expect(Array.isArray(store.items)).toBe(true)
    expect(store.page).toBe(2)
    expect(store.perPage).toBe(50)
    expect(store.total).toBe(100)

    expect(lastParams).toBeDefined()
    const lp = lastParams as Record<string, unknown>
    expect(lp.include).toBeUndefined()
    expect(lp.page).toBe(2)
    expect(lp.per_page).toBe(50)
  })

  it('passes include when provided', async () => {
    const items = [createMockItem({ id: 'itm-2' })]
    let lastParams: Record<string, unknown> | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createItemApi: () => ({
        itemIndex: (cfg?: { params?: Record<string, unknown> }) => {
          lastParams = cfg?.params
          return Promise.resolve({
            data: { data: items, meta: { total: 1, current_page: 1, per_page: 20 } },
          })
        },
      }),
    } as unknown as {
      createItemApi: () => {
        itemIndex: (cfg?: {
          params?: Record<string, unknown>
        }) => Promise<{ data: { data: unknown; meta: unknown } }>
      }
    })

    const { useItemStore } = await import('@/stores/item')
    const store = useItemStore()
    await store.fetchItems({ include: ['partner', 'project'] })

    expect(lastParams).toBeDefined()
    const lp = lastParams as Record<string, unknown>
    expect(lp.include).toBe('partner,project')
  })
})
