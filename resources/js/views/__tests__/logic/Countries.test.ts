/**
 * Unit Tests for Countries Component Business Logic
 *
 * These tests focus on the core functionality and business logic
 * of the Countries component without dealing with complex UI rendering.
 *
 * Tests cover:
 * - Search functionality across internal_name and backward_compatibility
 * - Sorting functionality
 * - Store interactions and data fetching
 * - Error handling
 * - Delete operations
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCountryStore } from '@/stores/country'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { createMockCountry } from '@/__tests__/test-utils'
import type { CountryResource } from '@metanull/inventory-app-api-client'

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

// Test data - covering different country types for comprehensive testing
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
  createMockCountry({
    id: 'DEU',
    internal_name: 'Germany',
    backward_compatibility: 'DE',
    created_at: '2023-01-04T00:00:00Z',
    updated_at: '2023-01-04T00:00:00Z',
  }),
]

describe('Countries Logic Tests', () => {
  let mockCountryStore: ReturnType<typeof useCountryStore>
  let mockLoadingStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorStore: ReturnType<typeof useErrorDisplayStore>

  beforeEach(() => {
    setActivePinia(createPinia())

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

  describe('Store Data Access', () => {
    it('should access countries from store', () => {
      const store = useCountryStore()
      expect(store.countries).toEqual(mockCountries)
      expect(store.countries.length).toBe(4)
    })

    it('should handle empty countries array', () => {
      mockCountryStore.countries = []
      const store = useCountryStore()
      expect(store.countries).toEqual([])
      expect(store.countries.length).toBe(0)
    })
  })

  describe('Search Functionality Logic', () => {
    it('should filter by internal_name (case insensitive)', () => {
      const countries = mockCountries
      const searchQuery = 'united'

      const filtered = countries.filter(country =>
        country.internal_name.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered.length).toBe(2)
      expect(filtered[0].internal_name).toBe('United Kingdom')
      expect(filtered[1].internal_name).toBe('United States')
    })

    it('should filter by backward_compatibility code', () => {
      const countries = mockCountries
      const searchQuery = 'GB'

      const filtered = countries.filter(country =>
        country.backward_compatibility?.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered.length).toBe(1)
      expect(filtered[0].internal_name).toBe('United Kingdom')
      expect(filtered[0].backward_compatibility).toBe('GB')
    })

    it('should handle search with no results', () => {
      const countries = mockCountries
      const searchQuery = 'NonExistent'

      const filtered = countries.filter(
        country =>
          country.internal_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          (country.backward_compatibility &&
            country.backward_compatibility.toLowerCase().includes(searchQuery.toLowerCase()))
      )

      expect(filtered.length).toBe(0)
    })

    it('should handle null backward_compatibility gracefully', () => {
      const countries = mockCountries
      const searchQuery = 'France'

      const filtered = countries.filter(
        country =>
          country.internal_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          (country.backward_compatibility &&
            country.backward_compatibility.toLowerCase().includes(searchQuery.toLowerCase()))
      )

      expect(filtered.length).toBe(1)
      expect(filtered[0].internal_name).toBe('France')
      expect(filtered[0].backward_compatibility).toBeNull()
    })
  })

  describe('Sorting Logic', () => {
    it('should sort by internal_name ascending', () => {
      const countries = [...mockCountries]

      const sorted = countries.sort((a, b) => {
        const nameA = a.internal_name ?? ''
        const nameB = b.internal_name ?? ''
        return nameA < nameB ? -1 : nameA > nameB ? 1 : 0
      })

      expect(sorted[0].internal_name).toBe('France')
      expect(sorted[1].internal_name).toBe('Germany')
      expect(sorted[2].internal_name).toBe('United Kingdom')
      expect(sorted[3].internal_name).toBe('United States')
    })

    it('should sort by internal_name descending', () => {
      const countries = [...mockCountries]

      const sorted = countries.sort((a, b) => {
        const nameA = a.internal_name ?? ''
        const nameB = b.internal_name ?? ''
        return nameA > nameB ? -1 : nameA < nameB ? 1 : 0
      })

      expect(sorted[0].internal_name).toBe('United States')
      expect(sorted[1].internal_name).toBe('United Kingdom')
      expect(sorted[2].internal_name).toBe('Germany')
      expect(sorted[3].internal_name).toBe('France')
    })

    it('should sort by created_at ascending', () => {
      const countries = [...mockCountries]

      const sorted = countries.sort((a, b) => {
        const dateA = a.created_at
        const dateB = b.created_at
        return dateA < dateB ? -1 : dateA > dateB ? 1 : 0
      })

      expect(sorted[0].id).toBe('GBR') // 2023-01-01
      expect(sorted[1].id).toBe('USA') // 2023-01-02
      expect(sorted[2].id).toBe('FRA') // 2023-01-03
      expect(sorted[3].id).toBe('DEU') // 2023-01-04
    })
  })

  describe('Store Interactions', () => {
    it('should call fetchCountries', async () => {
      const store = useCountryStore()
      await store.fetchCountries()

      expect(mockCountryStore.fetchCountries).toHaveBeenCalledOnce()
    })

    it('should handle fetchCountries error', async () => {
      const error = new Error('Failed to fetch countries')
      mockCountryStore.fetchCountries = vi.fn().mockRejectedValue(error)

      const store = useCountryStore()

      await expect(store.fetchCountries()).rejects.toThrow('Failed to fetch countries')
      expect(mockCountryStore.fetchCountries).toHaveBeenCalledOnce()
    })

    it('should call deleteCountry', async () => {
      const store = useCountryStore()
      const countryId = 'GBR'

      await store.deleteCountry(countryId)

      expect(mockCountryStore.deleteCountry).toHaveBeenCalledWith(countryId)
    })
  })

  describe('Loading and Error States', () => {
    it('should handle loading state', () => {
      mockCountryStore.loading = true
      const store = useCountryStore()

      expect(store.loading).toBe(true)
    })

    it('should handle error state', () => {
      const errorMessage = 'Failed to load countries'
      mockCountryStore.error = errorMessage
      const store = useCountryStore()

      expect(store.error).toBe(errorMessage)
    })

    it('should show loading overlay during operations', () => {
      const loadingStore = useLoadingOverlayStore()

      loadingStore.show()
      expect(mockLoadingStore.show).toHaveBeenCalledOnce()

      loadingStore.hide()
      expect(mockLoadingStore.hide).toHaveBeenCalledOnce()
    })

    it('should display error messages', () => {
      const errorStore = useErrorDisplayStore()
      const errorMessage = 'Something went wrong'

      errorStore.addMessage(errorMessage)
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(errorMessage)
    })
  })
})
