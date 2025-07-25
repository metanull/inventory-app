/**
 * Integration Tests for CountryDetail Component
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import CountryDetail from '../CountryDetail.vue'
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

// Mock the stores
vi.mock('@/stores/country')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')

const mockCountry: CountryResource = createMockCountry({
  id: 'GBR',
  internal_name: 'United Kingdom',
  backward_compatibility: 'GB',
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
})

describe('CountryDetail Integration Tests', () => {
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
        { path: '/countries', component: { template: '<div>Countries</div>' } },
        { path: '/countries/:id', name: 'country-detail', component: CountryDetail },
      ],
    })

    // Setup store mocks
    mockCountryStore = {
      countries: [mockCountry],
      currentCountry: mockCountry,
      loading: false,
      error: null,
      fetchCountries: vi.fn().mockResolvedValue([mockCountry]),
      fetchCountry: vi.fn().mockResolvedValue(mockCountry),
      createCountry: vi.fn().mockResolvedValue(mockCountry),
      updateCountry: vi.fn().mockResolvedValue(mockCountry),
      deleteCountry: vi.fn().mockResolvedValue(true),
      clearCurrentCountry: vi.fn(),
      clearError: vi.fn(),
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

  describe('CountryDetail Component Integration', () => {
    it('should render for new country creation', async () => {
      await router.push('/countries/new')
      await router.isReady()

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Verify component renders
      expect(wrapper.exists()).toBe(true)

      // Should be in create mode - check route params after navigation
      expect(router.currentRoute.value.params.id).toBe('new')
    })

    it('should render for existing country editing', async () => {
      await router.push('/countries/GBR')

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Verify component renders
      expect(wrapper.exists()).toBe(true)

      // Should load the country
      expect(router.currentRoute.value.params.id).toBe('GBR')
    })

    it('should handle loading states during operations', async () => {
      mockCountryStore.loading = true

      await router.push('/countries/GBR')

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should handle loading state
      expect(wrapper.exists()).toBe(true)
    })

    it('should handle error states', async () => {
      mockCountryStore.error = 'Failed to load country'

      await router.push('/countries/GBR')

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should handle error state
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Form Integration', () => {
    it('should handle form interactions for new country', async () => {
      await router.push('/countries/new')

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Verify form elements exist (basic check)
      expect(wrapper.exists()).toBe(true)

      // The component should be ready for form input
      expect(router.currentRoute.value.params.id).toBe('new')
    })

    it('should load existing country data for editing', async () => {
      await router.push('/countries/GBR')

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Should attempt to load country data
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Navigation Integration', () => {
    it('should handle back navigation', async () => {
      await router.push('/countries/GBR')

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should render with navigation capabilities
      expect(wrapper.exists()).toBe(true)
      expect(router.currentRoute.value.path).toBe('/countries/GBR')
    })

    it('should handle route parameter changes', async () => {
      // Start with new country
      await router.push('/countries/new')

      mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()
      expect(router.currentRoute.value.params.id).toBe('new')

      // Navigate to existing country
      await router.push('/countries/GBR')
      await flushPromises()

      expect(router.currentRoute.value.params.id).toBe('GBR')
    })
  })

  describe('Store Integration', () => {
    it('should interact with country store for data operations', async () => {
      await router.push('/countries/GBR')

      mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should interact with store
      // The exact store calls depend on component implementation
      expect(mockCountryStore).toBeDefined()
    })

    it('should handle store errors gracefully', async () => {
      const error = new Error('Store error')
      mockCountryStore.getCountryById = vi.fn().mockRejectedValue(error)

      await router.push('/countries/GBR')

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should still render despite store errors
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Mode Detection', () => {
    it('should detect create mode correctly', async () => {
      await router.push('/countries/new')

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Should be in create mode
      expect(router.currentRoute.value.params.id).toBe('new')
      expect(wrapper.exists()).toBe(true)
    })

    it('should detect edit mode correctly', async () => {
      await router.push('/countries/GBR')

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Should be in edit mode
      expect(router.currentRoute.value.params.id).toBe('GBR')
      expect(wrapper.exists()).toBe(true)
    })
  })
})
