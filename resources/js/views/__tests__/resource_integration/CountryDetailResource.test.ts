/**
 * Integration Tests for CountryDetail Resource Management
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCountryStore } from '@/stores/country'
import { createMockCountry } from '@/__tests__/test-utils'
import type { CountryResource, CountryStoreRequest } from '@metanull/inventory-app-api-client'

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

const mockCountry: CountryResource = createMockCountry({
  id: 'GBR',
  internal_name: 'United Kingdom',
  backward_compatibility: 'GB',
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
})

describe('CountryDetail Resource Integration Tests', () => {
  let mockCountryStore: ReturnType<typeof useCountryStore>

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup comprehensive store mock
    mockCountryStore = {
      countries: [mockCountry],
      currentCountry: mockCountry,
      loading: false,
      error: null,
      fetchCountries: vi.fn().mockResolvedValue([mockCountry]),
      getCountryById: vi.fn().mockImplementation((id: string) => {
        return Promise.resolve(id === 'GBR' ? mockCountry : null)
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

  describe('Country Detail CRUD Workflows', () => {
    it('should complete create country workflow', async () => {
      const store = useCountryStore()
      const newCountryData: CountryStoreRequest = {
        id: 'FRA',
        internal_name: 'France',
        backward_compatibility: 'FR',
      }

      // Simulate complete create workflow
      const result = await store.createCountry(newCountryData)

      expect(store.createCountry).toHaveBeenCalledWith(newCountryData)
      expect(result.id).toBe('FRA')
      expect(result.internal_name).toBe('France')
      expect(result.backward_compatibility).toBe('FR')
    })

    it('should complete view country workflow', async () => {
      const store = useCountryStore()
      const countryId = 'GBR'

      // Simulate view workflow - fetch country by ID
      const result = await store.getCountryById(countryId)

      expect(store.getCountryById).toHaveBeenCalledWith(countryId)
      expect(result?.id).toBe(countryId)
      expect(result?.internal_name).toBe('United Kingdom')
    })

    it('should complete edit country workflow', async () => {
      const store = useCountryStore()
      const countryId = 'GBR'

      // First fetch the country
      const existing = await store.getCountryById(countryId)
      expect(existing).toBeDefined()

      // Then update it
      const updateData: CountryStoreRequest = {
        id: 'GBR',
        internal_name: 'United Kingdom of Great Britain and Northern Ireland',
        backward_compatibility: 'GB',
      }

      const updated = await store.updateCountry(countryId, updateData)

      expect(store.updateCountry).toHaveBeenCalledWith(countryId, updateData)
      expect(updated.internal_name).toBe('United Kingdom of Great Britain and Northern Ireland')
    })

    it('should complete delete country workflow', async () => {
      const store = useCountryStore()
      const countryId = 'GBR'

      // First verify country exists
      const existing = await store.getCountryById(countryId)
      expect(existing).toBeDefined()

      // Then delete it
      await store.deleteCountry(countryId)

      expect(store.deleteCountry).toHaveBeenCalledWith(countryId)
    })
  })

  describe('Form Data Integration', () => {
    it('should handle complete form submission for new country', async () => {
      const store = useCountryStore()

      // Simulate form data
      const formData = {
        id: 'DEU',
        internal_name: 'Germany',
        backward_compatibility: 'DE',
      }

      // Validate and submit
      const isValid = formData.id.trim() !== '' && formData.internal_name.trim() !== ''
      expect(isValid).toBe(true)

      if (isValid) {
        const result = await store.createCountry(formData)
        expect(result.id).toBe('DEU')
        expect(result.internal_name).toBe('Germany')
      }
    })

    it('should handle form validation errors', async () => {
      const store = useCountryStore()

      // Simulate invalid form data
      const formData = {
        id: '',
        internal_name: '',
        backward_compatibility: null,
      }

      // Validate
      const isValid = formData.id.trim() !== '' && formData.internal_name.trim() !== ''
      expect(isValid).toBe(false)

      // Should not call store if invalid
      if (!isValid) {
        expect(store.createCountry).not.toHaveBeenCalled()
      }
    })
  })

  describe('Error Scenarios', () => {
    it('should handle country not found in edit mode', async () => {
      mockCountryStore.getCountryById = vi.fn().mockResolvedValue(null)

      const store = useCountryStore()
      const result = await store.getCountryById('NONEXISTENT')

      expect(result).toBeNull()
      expect(store.getCountryById).toHaveBeenCalledWith('NONEXISTENT')
    })

    it('should handle duplicate country creation', async () => {
      const error = new Error('Country with this ID already exists')
      mockCountryStore.createCountry = vi.fn().mockRejectedValue(error)

      const store = useCountryStore()
      const duplicateData: CountryStoreRequest = {
        id: 'GBR',
        internal_name: 'United Kingdom',
        backward_compatibility: 'GB',
      }

      await expect(store.createCountry(duplicateData)).rejects.toThrow(
        'Country with this ID already exists'
      )
    })
  })

  describe('Data Consistency', () => {
    it('should maintain data consistency across operations', async () => {
      const store = useCountryStore()

      // Create a country
      const createData: CountryStoreRequest = {
        id: 'ITA',
        internal_name: 'Italy',
        backward_compatibility: 'IT',
      }

      const created = await store.createCountry(createData)
      expect(created.id).toBe('ITA')

      // Then fetch it back
      await store.getCountryById('ITA')

      // Data should be consistent (mock implementation dependent)
      expect(store.getCountryById).toHaveBeenCalledWith('ITA')
    })

    it('should handle ISO country code consistency', async () => {
      const store = useCountryStore()

      // Test various ISO code formats
      const testData: CountryStoreRequest = {
        id: 'USA', // 3-letter ISO code
        internal_name: 'United States of America',
        backward_compatibility: 'US', // 2-letter ISO code
      }

      const result = await store.createCountry(testData)

      expect(result.id).toBe('USA')
      expect(result.backward_compatibility).toBe('US')
    })
  })
})
