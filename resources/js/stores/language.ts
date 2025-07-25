import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  LanguageApi,
  Configuration,
  type LanguageResource,
  type LanguageStoreRequest,
  type LanguageUpdateRequest,
} from '@metanull/inventory-app-api-client'
import { useAuthStore } from './auth'
import { ErrorHandler } from '@/utils/errorHandler'

// Declare process for Node.js environments
declare const process: {
  env: Record<string, string | undefined>
}

export const useLanguageStore = defineStore('language', () => {
  const languages = ref<LanguageResource[]>([])
  const currentLanguage = ref<LanguageResource | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const authStore = useAuthStore()

  // Create API client instance with configuration
  const createApiClient = () => {
    // Support both Vite (import.meta.env) and Node (process.env) for baseURL
    let baseURL: string
    if (
      typeof import.meta !== 'undefined' &&
      import.meta.env &&
      import.meta.env.VITE_API_BASE_URL
    ) {
      baseURL = import.meta.env.VITE_API_BASE_URL
    } else if (typeof process !== 'undefined' && process.env && process.env.VITE_API_BASE_URL) {
      baseURL = process.env.VITE_API_BASE_URL
    } else {
      baseURL = 'http://127.0.0.1:8000/api'
    }

    const configParams: { basePath: string; accessToken?: string } = {
      basePath: baseURL,
    }

    if (authStore.token) {
      configParams.accessToken = authStore.token
    }

    // Create configuration for the API client
    const configuration = new Configuration(configParams)

    return new LanguageApi(configuration)
  }

  const defaultLanguage = computed(() => languages.value.find(lang => lang.is_default))
  const defaultLanguages = computed(() => languages.value.filter(lang => lang.is_default))

  // Fetch all languages
  const fetchLanguages = async () => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.languageIndex()
      languages.value = response.data.data || []
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to fetch languages')
      error.value = 'Failed to fetch languages'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Fetch a single language by ID
  const fetchLanguage = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.languageShow(id)
      currentLanguage.value = response.data.data
      return response.data.data
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to fetch language ${id}`)
      error.value = 'Failed to fetch language'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Create a new language
  const createLanguage = async (languageData: LanguageStoreRequest) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.languageStore(languageData)
      const newLanguage = response.data.data

      // Add to local languages array
      languages.value.push(newLanguage)

      return newLanguage
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to create language')
      error.value = 'Failed to create language'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update an existing language
  const updateLanguage = async (id: string, languageData: LanguageUpdateRequest) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.languageUpdate(id, languageData)
      const updatedLanguage = response.data.data

      // Update in local languages array
      const index = languages.value.findIndex(lang => lang.id === id)
      if (index !== -1) {
        languages.value[index] = updatedLanguage
      }

      // Update current language if it matches
      if (currentLanguage.value?.id === id) {
        currentLanguage.value = updatedLanguage
      }

      return updatedLanguage
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to update language ${id}`)
      error.value = 'Failed to update language'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete a language
  const deleteLanguage = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.languageDestroy(id)

      // Remove from local languages array
      languages.value = languages.value.filter(lang => lang.id !== id)

      // Clear current language if it matches
      if (currentLanguage.value?.id === id) {
        currentLanguage.value = null
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to delete language ${id}`)
      error.value = 'Failed to delete language'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Set a language as default
  const setDefaultLanguage = async (id: string, isDefault: boolean) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.languageSetDefault(id, { is_default: isDefault })
      const updatedLanguage = response.data.data

      // Update the default status for all languages
      languages.value = languages.value.map(lang => ({
        ...lang,
        is_default: lang.id === id ? isDefault : false,
      }))

      // Update current language if it matches
      if (currentLanguage.value?.id === id) {
        currentLanguage.value = updatedLanguage
      }

      return updatedLanguage
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to set default language ${id}`)
      error.value = 'Failed to set default language'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get the default language
  const getDefaultLanguage = async () => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.languageGetDefault()
      const defaultLang = response.data.data

      // Update the default language in the languages array if it exists
      const index = languages.value.findIndex(lang => lang.id === defaultLang.id)
      if (index !== -1) {
        languages.value[index] = defaultLang
      } else {
        // If the default language isn't in our languages array, add it
        languages.value.push(defaultLang)
      }

      return defaultLang
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to get default language')
      error.value = 'Failed to get default language'
      throw err
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  const clearCurrentLanguage = () => {
    currentLanguage.value = null
  }

  return {
    languages,
    currentLanguage,
    loading,
    error,
    defaultLanguage,
    defaultLanguages,
    fetchLanguages,
    fetchLanguage,
    createLanguage,
    updateLanguage,
    deleteLanguage,
    setDefaultLanguage,
    getDefaultLanguage,
    clearError,
    clearCurrentLanguage,
  }
})
