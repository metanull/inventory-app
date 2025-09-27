import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { createMockLanguage } from '@/__tests__/test-utils'

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: vi.fn(),
}))
import { useApiClient } from '@/composables/useApiClient'

describe('LanguageStore - pagination and includes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('minimal-by-default and pagination meta updates', async () => {
    const languages = [createMockLanguage({ id: 'eng' })]
    const meta = { total: 12, current_page: 2, per_page: 5 }
    let lastPage: number | undefined
    let lastPerPage: number | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createLanguageApi: () => ({
        languageIndex: (page?: number, perPage?: number) => {
          lastPage = page
          lastPerPage = perPage
          return Promise.resolve({ data: { data: languages, meta } })
        },
      }),
    } as unknown as {
      createLanguageApi: () => {
        languageIndex: (page?: number, perPage?: number) => Promise<{ data: { data: unknown; meta: unknown } }>
      }
    })

    const { useLanguageStore } = await import('@/stores/language')
    const store = useLanguageStore()
    await store.fetchLanguages({ page: 2, perPage: 5 })

    expect(lastPage).toBe(2)
    expect(lastPerPage).toBe(5)
    expect(store.page).toBe(2)
    expect(store.perPage).toBe(5)
    expect(store.total).toBe(12)
  })
})
