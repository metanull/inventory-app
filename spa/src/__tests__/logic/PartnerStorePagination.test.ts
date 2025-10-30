import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { usePartnerStore } from '@/stores/partner'
import { createMockPartner } from '@/__tests__/test-utils'

vi.mock('@/composables/useApiClient')

describe('PartnerStore - pagination and includes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('uses default include ["country"] when none provided', async () => {
    const partners = [createMockPartner({ id: 'par-1' })]
    let lastPage: number | undefined
    let lastPerPage: number | undefined
    let lastInclude: string | undefined

    vi.mocked(await import('@/composables/useApiClient')).useApiClient.mockReturnValue({
      createPartnerApi: () => ({
        partnerIndex: (page?: number, perPage?: number, include?: string) => {
          lastPage = page
          lastPerPage = perPage
          lastInclude = include
          return Promise.resolve({
            data: { data: partners, meta: { total: 1, current_page: 1, per_page: 20 } },
          })
        },
      }),
    } as unknown as {
      createPartnerApi: () => {
        partnerIndex: (
          page?: number,
          perPage?: number,
          include?: string
        ) => Promise<{ data: { data: unknown; meta: unknown } }>
      }
    })

    const store = usePartnerStore()
    await store.fetchPartners()

    expect(lastPage).toBe(1)
    expect(lastPerPage).toBe(20)
    expect(lastInclude).toBe('country')
  })

  it('applies pagination meta updates', async () => {
    const partners = [createMockPartner({ id: 'par-2' })]
    const meta = { total: 42, current_page: 3, per_page: 15 }

    vi.mocked(await import('@/composables/useApiClient')).useApiClient.mockReturnValue({
      createPartnerApi: () => ({
        partnerIndex: (_page?: number, _perPage?: number, _include?: string) =>
          Promise.resolve({ data: { data: partners, meta } }),
      }),
    } as unknown as {
      createPartnerApi: () => {
        partnerIndex: (
          page?: number,
          perPage?: number,
          include?: string
        ) => Promise<{ data: { data: unknown; meta: unknown } }>
      }
    })

    const store = usePartnerStore()
    await store.fetchPartners({ page: 3, perPage: 15 })
    expect(store.page).toBe(3)
    expect(store.perPage).toBe(15)
    expect(store.total).toBe(42)
  })
})
