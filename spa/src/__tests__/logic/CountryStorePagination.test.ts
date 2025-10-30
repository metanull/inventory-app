import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCountryStore } from '@/stores/country'
import { createMockCountry } from '@/__tests__/test-utils'

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: vi.fn(),
}))
import { useApiClient } from '@/composables/useApiClient'

describe('CountryStore - pagination and includes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('minimal-by-default (no include) and pagination meta fallback when meta absent', async () => {
    const countries = [createMockCountry({ id: 'usa' })]
    let lastPage: number | undefined
    let lastPerPage: number | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createCountryApi: () => ({
        countryIndex: (page?: number, perPage?: number) => {
          lastPage = page
          lastPerPage = perPage
          // No meta returned to test fallback behavior
          return Promise.resolve({ data: { data: countries } })
        },
      }),
    } as unknown as ReturnType<typeof useApiClient>)

    const store = useCountryStore()
    await store.fetchCountries({ page: 5, perPage: 25 })

    // countryIndex now supports pagination parameters
    expect(lastPage).toBe(5)
    expect(lastPerPage).toBe(25)

    // Fallback to requested values if meta is missing
    expect(store.page).toBe(5)
    expect(store.perPage).toBe(25)
    expect(store.total).toBeNull()
  })
})
