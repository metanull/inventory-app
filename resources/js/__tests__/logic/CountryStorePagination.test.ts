import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCountryStore } from '@/stores/country'
import { createMockCountry } from '@/__tests__/test-utils'

vi.mock('@/composables/useApiClient')

describe('CountryStore - pagination and includes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('minimal-by-default (no include) and pagination meta fallback when meta absent', async () => {
    const countries = [createMockCountry({ id: 'usa' })]
    let lastParams: Record<string, unknown> | undefined

    vi.mocked(await import('@/composables/useApiClient')).useApiClient.mockReturnValue({
      createCountryApi: () => ({
        countryIndex: (cfg?: { params?: Record<string, unknown> }) => {
          lastParams = cfg?.params
          // No meta returned to test fallback behavior
          return Promise.resolve({ data: { data: countries } })
        },
      }),
    } as unknown as {
      createCountryApi: () => {
        countryIndex: (cfg?: {
          params?: Record<string, unknown>
        }) => Promise<{ data: { data: unknown } }>
      }
    })

    const store = useCountryStore()
    await store.fetchCountries({ page: 5, perPage: 25 })

    expect(lastParams).toBeDefined()
    const lp = lastParams as Record<string, unknown>
    expect(lp.include).toBeUndefined()
    expect(lp.page).toBe(5)
    expect(lp.per_page).toBe(25)

    // Fallback to requested values if meta is missing
    expect(store.page).toBe(5)
    expect(store.perPage).toBe(25)
    expect(store.total).toBeNull()
  })
})
