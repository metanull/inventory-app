import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  type ItemTranslationResource,
  type StoreItemTranslationRequest,
  type UpdateItemTranslationRequest,
} from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'
import { ErrorHandler } from '@/utils/errorHandler'
import {
  type IndexQueryOptions,
  type PaginationMeta,
  extractPaginationMeta,
} from '@/utils/apiQueryParams'

interface ItemTranslationFilters {
  item_id?: string
  language_id?: string
  context_id?: string
  default_context?: boolean
}

export const useItemTranslationStore = defineStore('itemTranslation', () => {
  const itemTranslations = ref<ItemTranslationResource[]>([])
  const currentItemTranslation = ref<ItemTranslationResource | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const page = ref(1)
  const perPage = ref(20)
  const total = ref<number | null>(null)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createItemTranslationApi()
  }

  // Fetch all item translations (supports filters + pagination)
  const fetchItemTranslations = async ({
    page: p = 1,
    perPage: pp = 20,
    filters,
  }: IndexQueryOptions & { filters?: ItemTranslationFilters } = {}) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.itemTranslationIndex(
        p,
        pp,
        filters?.item_id,
        filters?.language_id,
        filters?.context_id,
        filters?.default_context
      )
      // Response.data is the array directly (not wrapped in { data: [...] })
      const data = Array.isArray(response.data) ? response.data : []
      // Extract metadata if present (pagination info may be in headers or response envelope)
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const meta: PaginationMeta | undefined = extractPaginationMeta(response as any)
      itemTranslations.value = data
      if (meta) {
        total.value = typeof meta.total === 'number' ? meta.total : total.value
        page.value = typeof meta.current_page === 'number' ? meta.current_page : p
        perPage.value = typeof meta.per_page === 'number' ? meta.per_page : pp
      } else {
        page.value = p
        perPage.value = pp
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to fetch item translations')
      error.value = 'Failed to fetch item translations'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Fetch a single item translation by ID
  const fetchItemTranslation = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.itemTranslationShow(id)
      currentItemTranslation.value = response.data.data
      return response.data.data
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to fetch item translation ${id}`)
      error.value = 'Failed to fetch item translation'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Create a new item translation
  const createItemTranslation = async (data: StoreItemTranslationRequest) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.itemTranslationStore(data)

      // Extract the created resource from response
      const createdTranslation = response.data.data as ItemTranslationResource

      // Update current translation
      currentItemTranslation.value = createdTranslation

      // Add to list if we have one
      if (itemTranslations.value) {
        itemTranslations.value.unshift(createdTranslation)
      }

      return createdTranslation
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to create item translation')
      error.value = 'Failed to create item translation'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update an existing item translation
  const updateItemTranslation = async (id: string, data: UpdateItemTranslationRequest) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.itemTranslationUpdate(id, data)

      // Extract the updated resource from response
      const updatedTranslation = response.data.data as ItemTranslationResource

      // Update current translation
      currentItemTranslation.value = updatedTranslation

      // Update in local array if it exists
      const index = itemTranslations.value.findIndex(t => t.id === id)
      if (index !== -1) {
        itemTranslations.value[index] = updatedTranslation
      }

      return updatedTranslation
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to update item translation')
      error.value = 'Failed to update item translation'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete an item translation
  const deleteItemTranslation = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.itemTranslationDestroy(id)

      // Remove from local array
      itemTranslations.value = itemTranslations.value.filter(t => t.id !== id)

      // Clear current if it's the one being deleted
      if (currentItemTranslation.value?.id === id) {
        currentItemTranslation.value = null
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to delete item translation')
      error.value = 'Failed to delete item translation'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Reset store state
  const reset = () => {
    itemTranslations.value = []
    currentItemTranslation.value = null
    loading.value = false
    error.value = null
    page.value = 1
    perPage.value = 20
    total.value = null
  }

  return {
    // State
    itemTranslations: computed(() => itemTranslations.value),
    currentItemTranslation: computed(() => currentItemTranslation.value),
    loading: computed(() => loading.value),
    error: computed(() => error.value),
    page,
    perPage,
    total: computed(() => total.value),

    // Actions
    fetchItemTranslations,
    fetchItemTranslation,
    createItemTranslation,
    updateItemTranslation,
    deleteItemTranslation,
    reset,
  }
})
