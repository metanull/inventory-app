import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import Countries from '../Countries.vue'
import { useCountryStore } from '@/stores/country'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { createMockCountry } from '@/__tests__/test-utils'
import type { CountryResource } from '@metanull/inventory-app-api-client'
import type { Router } from 'vue-router'

// Mock console.error to avoid noise in test output
vi.mock('console', () => ({
  error: vi.fn(),
  warn: vi.fn(),
  log: vi.fn(),
}))

// Store original console methods for cleanup
// eslint-disable-next-line @typescript-eslint/no-explicit-any
let originalConsole: any

beforeAll(() => {
  originalConsole = { ...console }
  console.error = vi.fn()
  console.warn = vi.fn()
  console.log = vi.fn()
})

afterAll(() => {
  Object.assign(console, originalConsole)
})

// Component interface for proper typing
interface CountriesComponentInstance {
  countries: CountryResource[]
  filteredCountries: CountryResource[]
  searchQuery: string
  sortDirection: string
  sortKey: string
  openCountryDetail: (id: string) => void
  handleSort: (field: string) => void
  fetchCountries: () => Promise<void>
}

// Mock the stores
vi.mock('@/stores/country')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')

const mockCountries: CountryResource[] = [
  createMockCountry({
    id: 'GBR',
    internal_name: 'United Kingdom',
    backward_compatibility: 'GB',
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  }),
  createMockCountry({
    id: 'USA',
    internal_name: 'United States',
    backward_compatibility: 'US',
    created_at: '2023-01-02T00:00:00Z',
    updated_at: '2023-01-02T00:00:00Z',
  }),
  createMockCountry({
    id: 'FRA',
    internal_name: 'France',
    backward_compatibility: null,
    created_at: '2023-01-03T00:00:00Z',
    updated_at: '2023-01-03T00:00:00Z',
  }),
]

describe('Countries.vue', () => {
  let mockCountryStore: ReturnType<typeof useCountryStore>
  let mockLoadingStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorStore: ReturnType<typeof useErrorDisplayStore>
  let router: Router

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup router
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/countries', component: Countries },
        { path: '/countries/new', component: { template: '<div>New Country</div>' } },
        { path: '/countries/:id', component: { template: '<div>Country Detail</div>' } },
      ],
    })

    // Setup store mocks
    mockCountryStore = {
      countries: mockCountries,
      currentCountry: null,
      loading: false,
      error: null,
      fetchCountries: vi.fn().mockResolvedValue(mockCountries),
      getCountryById: vi.fn(),
      createCountry: vi.fn(),
      updateCountry: vi.fn(),
      deleteCountry: vi.fn(),
    } as ReturnType<typeof useCountryStore>

    mockLoadingStore = {
      show: vi.fn(),
      hide: vi.fn(),
    } as ReturnType<typeof useLoadingOverlayStore>

    mockErrorStore = {
      addMessage: vi.fn(),
    } as ReturnType<typeof useErrorDisplayStore>

    // Mock store implementations
    vi.mocked(useCountryStore).mockReturnValue(mockCountryStore)
    vi.mocked(useLoadingOverlayStore).mockReturnValue(mockLoadingStore)
    vi.mocked(useErrorDisplayStore).mockReturnValue(mockErrorStore)

    vi.clearAllMocks()
  })

  describe('Component Logic', () => {
    it('should initialize with default values', async () => {
      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()

      expect((wrapper.vm as unknown as CountriesComponentInstance).sortKey).toBe('internal_name')
      expect((wrapper.vm as unknown as CountriesComponentInstance).sortDirection).toBe('asc')
      expect((wrapper.vm as unknown as CountriesComponentInstance).searchQuery).toBe('')
    })

    it('should have access to store data', async () => {
      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()

      expect((wrapper.vm as unknown as CountriesComponentInstance).countries.length).toBe(3)
    })
  })

  describe('Search Functionality', () => {
    it('should search countries by internal name', async () => {
      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()
      ;(wrapper.vm as unknown as CountriesComponentInstance).searchQuery = 'United'
      await wrapper.vm.$nextTick()

      const filteredCountries = (wrapper.vm as unknown as CountriesComponentInstance)
        .filteredCountries
      expect(filteredCountries.length).toBe(2)
      expect(filteredCountries[0].internal_name).toContain('United')
      expect(filteredCountries[1].internal_name).toContain('United')
    })

    it('should filter countries by backward_compatibility', async () => {
      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()
      ;(wrapper.vm as unknown as CountriesComponentInstance).searchQuery = 'GB'
      await wrapper.vm.$nextTick()

      const filteredCountries = (wrapper.vm as unknown as CountriesComponentInstance)
        .filteredCountries
      expect(filteredCountries.length).toBe(1)
      expect(filteredCountries[0].backward_compatibility).toBe('GB')
    })
  })

  describe('Store Integration', () => {
    it('should call fetchCountries on mount', async () => {
      mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      expect(mockCountryStore.fetchCountries).toHaveBeenCalledOnce()
    })
  })
})
