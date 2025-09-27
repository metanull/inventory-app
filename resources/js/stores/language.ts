import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  type LanguageResource,
  type LanguageStoreRequest,
  type LanguageUpdateRequest,
} from '@metanull/inventory-app-api-client'
import { ErrorHandler, isAuthRedirect } from '@/utils/errorHandler'
import { useApiClient } from '@/composables/useApiClient'
import {
  type IndexQueryOptions,
  type PaginationMeta,
  extractPaginationMeta,
} from '@/utils/apiQueryParams'

export const useLanguageStore = defineStore('language', () => {
  const languages = ref<LanguageResource[]>([])
  const currentLanguage = ref<LanguageResource | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const page = ref(1)
  const perPage = ref(20)
  const total = ref<number | null>(null)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createLanguageApi()
  }

  const defaultLanguage = computed(() => languages.value.find(lang => lang.is_default))
  const defaultLanguages = computed(() => languages.value.filter(lang => lang.is_default))

  // Fetch all languages (supports pagination only)
  const fetchLanguages = async ({
    page: p = 1,
    perPage: pp = 20,
  }: IndexQueryOptions = {}) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.languageIndex(p, pp)
      const data = response.data?.data || []
      const meta: PaginationMeta | undefined = extractPaginationMeta(response.data)
      languages.value = data
      if (meta) {
        total.value = typeof meta.total === 'number' ? meta.total : total.value
        page.value = typeof meta.current_page === 'number' ? meta.current_page : p
        perPage.value = typeof meta.per_page === 'number' ? meta.per_page : pp
      } else {
        page.value = p
        perPage.value = pp
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to fetch languages')
      // Suppress user-facing error if we are redirecting to login due to 401
      if (!isAuthRedirect(err)) {
        error.value = 'Failed to fetch languages'
      }
      throw err
    } finally {
      loading.value = false
    }
  }

    // Fetch a single language by ID
  const fetchLanguage = async (id: string): Promise<LanguageResource> => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.languageShow(id)
      currentLanguage.value = response.data.data
      return response.data.data
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to fetch language ${id}`)
      if (!isAuthRedirect(err)) {
        error.value = 'Failed to fetch language'
      }
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
      if (isDefault) {
        // Setting as default: set target to true, all others to false
        languages.value = languages.value.map(lang => ({
          ...lang,
          is_default: lang.id === id ? true : false,
        }))
      } else {
        // Unsetting as default: only update the target language
        languages.value = languages.value.map(lang =>
          lang.id === id ? { ...lang, is_default: false } : lang
        )
      }

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
    page,
    perPage,
    total,
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
