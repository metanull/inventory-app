import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  type CountryResource,
  type CountryStoreRequest,
  type CountryUpdateRequest,
} from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'
import { ErrorHandler } from '@/utils/errorHandler'

export const useCountryStore = defineStore('country', () => {
  const countries = ref<CountryResource[]>([])
  const currentCountry = ref<CountryResource | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createCountryApi()
  }

  // Computed properties
  const sortedCountries = computed(() => {
    return [...countries.value].sort((a, b) => a.internal_name.localeCompare(b.internal_name))
  })

  const countriesCount = computed(() => countries.value.length)

  // Actions
  const fetchCountries = async (): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      const api = createApiClient()
      const response = await api.countryIndex()

      if (response.data && response.data.data) {
        countries.value = response.data.data
      } else {
        countries.value = []
      }
    } catch (err) {
      error.value = 'Failed to fetch countries'
      ErrorHandler.handleError(err, 'fetchCountries')
      countries.value = []
    } finally {
      loading.value = false
    }
  }

  const fetchCountry = async (id: string): Promise<void> => {
    loading.value = true
    error.value = null
    currentCountry.value = null

    try {
      const api = createApiClient()
      const response = await api.countryShow(id)

      if (response.data && response.data.data) {
        currentCountry.value = response.data.data
      } else {
        throw new Error('Country not found')
      }
    } catch (err) {
      error.value = `Failed to fetch country with ID: ${id}`
      ErrorHandler.handleError(err, 'fetchCountry')
      currentCountry.value = null
    } finally {
      loading.value = false
    }
  }

  const createCountry = async (
    countryData: CountryStoreRequest
  ): Promise<CountryResource | null> => {
    loading.value = true
    error.value = null

    try {
      const api = createApiClient()
      const response = await api.countryStore(countryData)

      if (response.data && response.data.data) {
        const newCountry = response.data.data
        countries.value.push(newCountry)
        return newCountry
      } else {
        throw new Error('Failed to create country')
      }
    } catch (err) {
      error.value = 'Failed to create country'
      ErrorHandler.handleError(err, 'createCountry')
      return null
    } finally {
      loading.value = false
    }
  }

  const updateCountry = async (
    id: string,
    countryData: CountryUpdateRequest
  ): Promise<CountryResource | null> => {
    loading.value = true
    error.value = null

    try {
      const api = createApiClient()
      const response = await api.countryUpdate(id, countryData)

      if (response.data && response.data.data) {
        const updatedCountry = response.data.data

        // Update in countries list
        const index = countries.value.findIndex(country => country.id === id)
        if (index !== -1) {
          countries.value[index] = updatedCountry
        }

        // Update current country if it's the same
        if (currentCountry.value && currentCountry.value.id === id) {
          currentCountry.value = updatedCountry
        }

        return updatedCountry
      } else {
        throw new Error('Failed to update country')
      }
    } catch (err) {
      error.value = 'Failed to update country'
      ErrorHandler.handleError(err, 'updateCountry')
      return null
    } finally {
      loading.value = false
    }
  }

  const deleteCountry = async (id: string): Promise<boolean> => {
    loading.value = true
    error.value = null

    try {
      const api = createApiClient()
      await api.countryDestroy(id)

      // Remove from countries list
      countries.value = countries.value.filter(country => country.id !== id)

      // Clear current country if it's the same
      if (currentCountry.value && currentCountry.value.id === id) {
        currentCountry.value = null
      }

      return true
    } catch (err) {
      error.value = 'Failed to delete country'
      ErrorHandler.handleError(err, 'deleteCountry')
      return false
    } finally {
      loading.value = false
    }
  }

  const findCountryById = (id: string): CountryResource | undefined => {
    return countries.value.find(country => country.id === id)
  }

  const clearCurrentCountry = (): void => {
    currentCountry.value = null
  }

  const clearError = (): void => {
    error.value = null
  }

  return {
    // State
    countries,
    currentCountry,
    loading,
    error,

    // Computed
    sortedCountries,
    countriesCount,

    // Actions
    fetchCountries,
    fetchCountry,
    createCountry,
    updateCountry,
    deleteCountry,
    findCountryById,
    clearCurrentCountry,
    clearError,
  }
})
