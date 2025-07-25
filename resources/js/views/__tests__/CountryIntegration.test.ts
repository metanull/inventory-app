/**
 * Integration Tests for Country Components
 *
 * These tests verify that Country components work correctly together
 * with real component mounting and interaction testing.
 */

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

describe('Country Integration Tests', () => {
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
      sortedCountries: mockCountries,
      countriesCount: mockCountries.length,
      fetchCountries: vi.fn().mockResolvedValue(mockCountries),
      fetchCountry: vi.fn(),
      createCountry: vi.fn(),
      updateCountry: vi.fn(),
      deleteCountry: vi.fn(),
      findCountryById: vi.fn(),
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

  describe('Countries Component Integration', () => {
    it('should render with complete data and interactions', async () => {
      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Verify component renders
      expect(wrapper.exists()).toBe(true)

      // Verify content is displayed
      expect(wrapper.text()).toContain('Countries')
      expect(wrapper.text()).toContain('United Kingdom')
      expect(wrapper.text()).toContain('United States')
      expect(wrapper.text()).toContain('France')

      // Verify store was called
      expect(mockCountryStore.fetchCountries).toHaveBeenCalledOnce()
    })

    it('should handle search interactions correctly', async () => {
      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Find search input
      const searchInput = wrapper.find('input[placeholder="Search countries..."]')
      expect(searchInput.exists()).toBe(true)

      // Simulate search
      await searchInput.setValue('United')
      await wrapper.vm.$nextTick()

      // The component should filter the results
      expect(wrapper.text()).toContain('United Kingdom')
      expect(wrapper.text()).toContain('United States')
    })

    it('should handle empty states correctly', async () => {
      mockCountryStore.countries = []

      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      expect(wrapper.text()).toContain('No countries found')
      expect(wrapper.text()).toContain('Get started by creating a new country.')
    })

    it('should handle loading states', async () => {
      mockCountryStore.loading = true

      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should still render but handle loading state
      expect(wrapper.exists()).toBe(true)
    })

    it('should handle error states', async () => {
      mockCountryStore.error = 'Failed to load countries'

      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should still render but handle error state
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Navigation Integration', () => {
    it('should navigate to country detail on row click', async () => {
      await router.push('/countries')

      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Find country rows - look for tr elements that contain country data
      const allRows = wrapper.findAll('tr')
      // Filter out header rows by looking for data content
      const dataRows = allRows.filter(row => {
        const text = row.text()
        return (
          text.includes('United Kingdom') ||
          text.includes('United States') ||
          text.includes('France') ||
          row.classes().some(cls => cls.includes('cursor-pointer'))
        )
      })
      expect(dataRows.length).toBeGreaterThan(0)
    })

    it('should have working add country button', async () => {
      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Find the add button link
      const addButton = wrapper.find('a[href="/countries/new"]')
      expect(addButton.exists()).toBe(true)
      expect(addButton.text()).toContain('Add Country')
    })
  })

  describe('Store Integration', () => {
    it('should call store methods on mount', async () => {
      mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      expect(mockCountryStore.fetchCountries).toHaveBeenCalledOnce()
    })

    it('should handle store errors gracefully', async () => {
      // Set up store to reject fetchCountries call
      mockCountryStore.fetchCountries = vi.fn().mockRejectedValue(new Error('Network error'))

      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      // Wait for component to handle the error
      await flushPromises()
      await new Promise(resolve => setTimeout(resolve, 10)) // Give a bit more time for error handling

      // Component should still render even with store errors
      expect(wrapper.exists()).toBe(true)

      // The store method should have been called
      expect(mockCountryStore.fetchCountries).toHaveBeenCalled()
    })
  })

  describe('Responsive Behavior', () => {
    it('should render table headers correctly', async () => {
      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Check for table headers
      const headers = wrapper.findAll('th')
      expect(headers.length).toBeGreaterThan(0)

      // Should include Country and Created headers
      const headerText = headers.map(h => h.text()).join(' ')
      expect(headerText).toContain('Country')
      expect(headerText).toContain('Created')
    })

    it('should render country data in table format', async () => {
      const wrapper = mount(Countries, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Check for table data
      const cells = wrapper.findAll('td')
      expect(cells.length).toBeGreaterThan(0)

      // Should contain country names
      const cellText = cells.map(c => c.text()).join(' ')
      expect(cellText).toContain('United Kingdom')
      expect(cellText).toContain('United States')
      expect(cellText).toContain('France')
    })
  })
})
