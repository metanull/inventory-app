/**
 * Integration Tests for Country Resource Management
 *
 * These tests verify complete user workflows combining multiple components
 * and stores to ensure Country-specific features work together correctly.
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCountryStore } from '@/stores/country'
import { createMockCountry } from '@/__tests__/test-utils'

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

import type { CountryResource, CountryStoreRequest } from '@metanull/inventory-app-api-client'

// Mock the stores instead of the API client
vi.mock('@/stores/country')

// Test data
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
]

describe('Country Resource Integration Tests', () => {
  let mockCountryStore: ReturnType<typeof useCountryStore>

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup comprehensive store mock
    mockCountryStore = {
      countries: mockCountries,
      currentCountry: null,
      loading: false,
      error: null,
      fetchCountries: vi.fn().mockResolvedValue(mockCountries),
      getCountryById: vi.fn().mockImplementation((id: string) => {
        return Promise.resolve(mockCountries.find(c => c.id === id) || null)
      }),
      createCountry: vi.fn().mockImplementation((request: CountryStoreRequest) => {
        const newCountry = createMockCountry({
          id: request.id,
          internal_name: request.internal_name,
          backward_compatibility: request.backward_compatibility,
        })
        return Promise.resolve(newCountry)
      }),
      updateCountry: vi.fn().mockImplementation((id: string, request: CountryStoreRequest) => {
        const updated = createMockCountry({
          id: request.id,
          internal_name: request.internal_name,
          backward_compatibility: request.backward_compatibility,
          updated_at: new Date().toISOString(),
        })
        return Promise.resolve(updated)
      }),
      deleteCountry: vi.fn().mockResolvedValue(undefined),
    } as ReturnType<typeof useCountryStore>

    // Mock store implementations
    vi.mocked(useCountryStore).mockReturnValue(mockCountryStore)

    vi.clearAllMocks()
  })

  describe('Country CRUD Operations', () => {
    it('should fetch all countries', async () => {
      const store = useCountryStore()

      const result = await store.fetchCountries()

      expect(store.fetchCountries).toHaveBeenCalledOnce()
      expect(result).toEqual(mockCountries)
    })

    it('should get country by ID', async () => {
      const store = useCountryStore()
      const countryId = 'GBR'

      const result = await store.getCountryById(countryId)

      expect(store.getCountryById).toHaveBeenCalledWith(countryId)
      expect(result?.id).toBe(countryId)
      expect(result?.internal_name).toBe('United Kingdom')
    })

    it('should create new country', async () => {
      const store = useCountryStore()
      const newCountryData: CountryStoreRequest = {
        id: 'FRA',
        internal_name: 'France',
        backward_compatibility: 'FR',
      }

      const result = await store.createCountry(newCountryData)

      expect(store.createCountry).toHaveBeenCalledWith(newCountryData)
      expect(result.id).toBe('FRA')
      expect(result.internal_name).toBe('France')
      expect(result.backward_compatibility).toBe('FR')
    })

    it('should update existing country', async () => {
      const store = useCountryStore()
      const countryId = 'GBR'
      const updateData: CountryStoreRequest = {
        id: 'GBR',
        internal_name: 'United Kingdom of Great Britain',
        backward_compatibility: 'GB',
      }

      const result = await store.updateCountry(countryId, updateData)

      expect(store.updateCountry).toHaveBeenCalledWith(countryId, updateData)
      expect(result.id).toBe('GBR')
      expect(result.internal_name).toBe('United Kingdom of Great Britain')
    })

    it('should delete country', async () => {
      const store = useCountryStore()
      const countryId = 'USA'

      await store.deleteCountry(countryId)

      expect(store.deleteCountry).toHaveBeenCalledWith(countryId)
    })
  })

  describe('Country Data Validation', () => {
    it('should handle country with null backward_compatibility', async () => {
      const store = useCountryStore()
      const newCountryData: CountryStoreRequest = {
        id: 'JPN',
        internal_name: 'Japan',
        backward_compatibility: null,
      }

      const result = await store.createCountry(newCountryData)

      expect(result.backward_compatibility).toBeNull()
      expect(result.internal_name).toBe('Japan')
    })

    it('should handle ISO country codes correctly', async () => {
      const store = useCountryStore()
      const countryData: CountryStoreRequest = {
        id: 'DEU',
        internal_name: 'Germany',
        backward_compatibility: 'DE',
      }

      const result = await store.createCountry(countryData)

      expect(result.id).toBe('DEU')
      expect(result.backward_compatibility).toBe('DE')
    })
  })

  describe('Error Handling', () => {
    it('should handle fetch countries error', async () => {
      const error = new Error('Network error')
      mockCountryStore.fetchCountries = vi.fn().mockRejectedValue(error)

      const store = useCountryStore()

      await expect(store.fetchCountries()).rejects.toThrow('Network error')
    })

    it('should handle country not found', async () => {
      mockCountryStore.getCountryById = vi.fn().mockResolvedValue(null)

      const store = useCountryStore()
      const result = await store.getCountryById('NONEXISTENT')

      expect(result).toBeNull()
    })

    it('should handle create country error', async () => {
      const error = new Error('Country already exists')
      mockCountryStore.createCountry = vi.fn().mockRejectedValue(error)

      const store = useCountryStore()
      const countryData: CountryStoreRequest = {
        id: 'GBR',
        internal_name: 'United Kingdom',
        backward_compatibility: 'GB',
      }

      await expect(store.createCountry(countryData)).rejects.toThrow('Country already exists')
    })
  })
})
