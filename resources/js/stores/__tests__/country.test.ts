import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCountryStore } from '@/stores/country'
import type {
  CountryResource,
  CountryStoreRequest,
  CountryUpdateRequest,
} from '@metanull/inventory-app-api-client'

// Mock the API client
const mockCountryApi = {
  countryIndex: vi.fn(),
  countryShow: vi.fn(),
  countryStore: vi.fn(),
  countryUpdate: vi.fn(),
  countryDestroy: vi.fn(),
}

vi.mock('@metanull/inventory-app-api-client', () => ({
  CountryApi: vi.fn().mockImplementation(() => mockCountryApi),
  Configuration: vi.fn(),
}))

// Mock the auth store
vi.mock('@/stores/auth', () => ({
  useAuthStore: vi.fn(() => ({
    token: 'mock-token',
    isAuthenticated: true,
  })),
}))

// Mock the error handler
vi.mock('@/utils/errorHandler', () => ({
  ErrorHandler: {
    handleError: vi.fn(),
  },
}))

const mockCountryData: CountryResource = {
  id: 'USA',
  internal_name: 'United States',
  backward_compatibility: 'US',
  created_at: '2024-01-01T00:00:00Z',
  updated_at: '2024-01-01T00:00:00Z',
}

const mockCountriesResponse = {
  data: {
    data: [
      mockCountryData,
      {
        id: 'CAN',
        internal_name: 'Canada',
        backward_compatibility: 'CA',
        created_at: '2024-01-02T00:00:00Z',
        updated_at: '2024-01-02T00:00:00Z',
      },
    ],
  },
}

describe('Country Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())

    // Reset all mocks
    vi.clearAllMocks()
  })

  describe('Initial State', () => {
    it('should have correct initial state', () => {
      const countryStore = useCountryStore()

      expect(countryStore.countries).toEqual([])
      expect(countryStore.currentCountry).toBeNull()
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBeNull()
      expect(countryStore.countriesCount).toBe(0)
    })
  })

  describe('fetchCountries', () => {
    it('should fetch countries successfully', async () => {
      const countryStore = useCountryStore()
      mockCountryApi.countryIndex.mockResolvedValue(mockCountriesResponse)

      await countryStore.fetchCountries()

      expect(mockCountryApi.countryIndex).toHaveBeenCalledOnce()
      expect(countryStore.countries).toEqual(mockCountriesResponse.data.data)
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBeNull()
    })

    it('should handle fetch countries error', async () => {
      const countryStore = useCountryStore()
      const error = new Error('Network error')
      mockCountryApi.countryIndex.mockRejectedValue(error)

      await countryStore.fetchCountries()

      expect(countryStore.countries).toEqual([])
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBe('Failed to fetch countries')
    })

    it('should handle empty response', async () => {
      const countryStore = useCountryStore()
      mockCountryApi.countryIndex.mockResolvedValue({ data: {} })

      await countryStore.fetchCountries()

      expect(countryStore.countries).toEqual([])
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBeNull()
    })
  })

  describe('fetchCountry', () => {
    it('should fetch a single country successfully', async () => {
      const countryStore = useCountryStore()
      const countryResponse = { data: { data: mockCountryData } }
      mockCountryApi.countryShow.mockResolvedValue(countryResponse)

      await countryStore.fetchCountry('USA')

      expect(mockCountryApi.countryShow).toHaveBeenCalledWith('USA')
      expect(countryStore.currentCountry).toEqual(mockCountryData)
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBeNull()
    })

    it('should handle fetch country error', async () => {
      const countryStore = useCountryStore()
      const error = new Error('Country not found')
      mockCountryApi.countryShow.mockRejectedValue(error)

      await countryStore.fetchCountry('INVALID')

      expect(countryStore.currentCountry).toBeNull()
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBe('Failed to fetch country with ID: INVALID')
    })
  })

  describe('createCountry', () => {
    it('should create a country successfully', async () => {
      const countryStore = useCountryStore()
      const newCountryData: CountryStoreRequest = {
        id: 'GBR',
        internal_name: 'United Kingdom',
        backward_compatibility: 'GB',
      }
      const createdCountry: CountryResource = {
        ...newCountryData,
        backward_compatibility: 'GB',
        created_at: '2024-01-03T00:00:00Z',
        updated_at: '2024-01-03T00:00:00Z',
      }
      const response = { data: { data: createdCountry } }
      mockCountryApi.countryStore.mockResolvedValue(response)

      const result = await countryStore.createCountry(newCountryData)

      expect(mockCountryApi.countryStore).toHaveBeenCalledWith(newCountryData)
      expect(result).toEqual(createdCountry)
      expect(countryStore.countries).toContainEqual(createdCountry)
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBeNull()
    })

    it('should handle create country error', async () => {
      const countryStore = useCountryStore()
      const newCountryData: CountryStoreRequest = {
        id: 'GBR',
        internal_name: 'United Kingdom',
        backward_compatibility: 'GB',
      }
      const error = new Error('Validation error')
      mockCountryApi.countryStore.mockRejectedValue(error)

      const result = await countryStore.createCountry(newCountryData)

      expect(result).toBeNull()
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBe('Failed to create country')
    })
  })

  describe('updateCountry', () => {
    it('should update a country successfully', async () => {
      const countryStore = useCountryStore()
      // Pre-populate the store with a country
      countryStore.countries.push(mockCountryData)
      countryStore.currentCountry = mockCountryData

      const updateData: CountryUpdateRequest = {
        internal_name: 'United States of America',
        backward_compatibility: 'US',
      }
      const updatedCountry: CountryResource = {
        ...mockCountryData,
        internal_name: 'United States of America',
      }
      const response = { data: { data: updatedCountry } }
      mockCountryApi.countryUpdate.mockResolvedValue(response)

      const result = await countryStore.updateCountry('USA', updateData)

      expect(mockCountryApi.countryUpdate).toHaveBeenCalledWith('USA', updateData)
      expect(result).toEqual(updatedCountry)
      expect(countryStore.currentCountry).toEqual(updatedCountry)
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBeNull()
    })

    it('should handle update country error', async () => {
      const countryStore = useCountryStore()
      const updateData: CountryUpdateRequest = {
        internal_name: 'Updated Name',
        backward_compatibility: null,
      }
      const error = new Error('Update failed')
      mockCountryApi.countryUpdate.mockRejectedValue(error)

      const result = await countryStore.updateCountry('USA', updateData)

      expect(result).toBeNull()
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBe('Failed to update country')
    })
  })

  describe('deleteCountry', () => {
    it('should delete a country successfully', async () => {
      const countryStore = useCountryStore()
      // Pre-populate the store
      countryStore.countries.push(mockCountryData)
      countryStore.currentCountry = mockCountryData

      mockCountryApi.countryDestroy.mockResolvedValue({})

      const result = await countryStore.deleteCountry('USA')

      expect(mockCountryApi.countryDestroy).toHaveBeenCalledWith('USA')
      expect(result).toBe(true)
      expect(countryStore.countries).not.toContainEqual(mockCountryData)
      expect(countryStore.currentCountry).toBeNull()
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBeNull()
    })

    it('should handle delete country error', async () => {
      const countryStore = useCountryStore()
      const error = new Error('Delete failed')
      mockCountryApi.countryDestroy.mockRejectedValue(error)

      const result = await countryStore.deleteCountry('USA')

      expect(result).toBe(false)
      expect(countryStore.loading).toBe(false)
      expect(countryStore.error).toBe('Failed to delete country')
    })
  })

  describe('Computed Properties', () => {
    it('should sort countries alphabetically', () => {
      const countryStore = useCountryStore()
      const countries = [
        {
          id: 'USA',
          internal_name: 'United States',
          backward_compatibility: 'US',
          created_at: null,
          updated_at: null,
        },
        {
          id: 'CAN',
          internal_name: 'Canada',
          backward_compatibility: 'CA',
          created_at: null,
          updated_at: null,
        },
        {
          id: 'GBR',
          internal_name: 'United Kingdom',
          backward_compatibility: 'GB',
          created_at: null,
          updated_at: null,
        },
      ]

      countryStore.countries.push(...countries)

      expect(countryStore.sortedCountries[0].internal_name).toBe('Canada')
      expect(countryStore.sortedCountries[1].internal_name).toBe('United Kingdom')
      expect(countryStore.sortedCountries[2].internal_name).toBe('United States')
    })

    it('should return correct countries count', () => {
      const countryStore = useCountryStore()

      expect(countryStore.countriesCount).toBe(0)

      countryStore.countries.push(mockCountryData)
      expect(countryStore.countriesCount).toBe(1)
    })
  })

  describe('Utility Methods', () => {
    it('should find country by id', () => {
      const countryStore = useCountryStore()
      countryStore.countries.push(mockCountryData)

      const found = countryStore.findCountryById('USA')
      expect(found).toEqual(mockCountryData)

      const notFound = countryStore.findCountryById('INVALID')
      expect(notFound).toBeUndefined()
    })

    it('should clear current country', () => {
      const countryStore = useCountryStore()
      countryStore.currentCountry = mockCountryData

      countryStore.clearCurrentCountry()
      expect(countryStore.currentCountry).toBeNull()
    })

    it('should clear error', () => {
      const countryStore = useCountryStore()
      countryStore.error = 'Some error'

      countryStore.clearError()
      expect(countryStore.error).toBeNull()
    })
  })
})
