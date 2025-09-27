import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { type ContextResource } from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'
import { ErrorHandler } from '@/utils/errorHandler'
import {
  type IndexQueryOptions,
  type PaginationMeta,
  extractPaginationMeta,
} from '@/utils/apiQueryParams'

// Type definitions for Context API requests based on OpenAPI spec
interface ContextStoreRequest {
  internal_name: string
  backward_compatibility?: string | null
  is_default?: boolean
}

interface ContextUpdateRequest {
  internal_name: string
  backward_compatibility?: string | null
  is_default?: boolean
}

export const useContextStore = defineStore('context', () => {
  const contexts = ref<ContextResource[]>([])
  const currentContext = ref<ContextResource | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const page = ref(1)
  const perPage = ref(20)
  const total = ref<number | null>(null)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createContextApi()
  }

  const defaultContext = computed(() => contexts.value.find(context => context.is_default))
  const defaultContexts = computed(() => contexts.value.filter(context => context.is_default))

  // Fetch all contexts (supports includes + pagination)
  const fetchContexts = async ({
    page: p = 1,
    perPage: pp = 20,
  }: IndexQueryOptions = {}) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.contextIndex()
      const data = response.data?.data || []
      const meta: PaginationMeta | undefined = extractPaginationMeta(response.data)
      contexts.value = data
      if (meta) {
        total.value = typeof meta.total === 'number' ? meta.total : total.value
        page.value = typeof meta.current_page === 'number' ? meta.current_page : p
        perPage.value = typeof meta.per_page === 'number' ? meta.per_page : pp
      } else {
        page.value = p
        perPage.value = pp
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to fetch contexts')
      error.value = 'Failed to fetch contexts'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Fetch a single context by ID
  const fetchContext = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.contextShow(id)
      currentContext.value = response.data.data
      return response.data.data
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to fetch context ${id}`)
      error.value = 'Failed to fetch context'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Create a new context
  const createContext = async (contextData: ContextStoreRequest) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.contextStore(contextData)
      const newContext = response.data.data

      // Add to local contexts array
      contexts.value.push(newContext)

      return newContext
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to create context')
      error.value = 'Failed to create context'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update an existing context
  const updateContext = async (id: string, contextData: ContextUpdateRequest) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.contextUpdate(id, contextData)
      const updatedContext = response.data.data

      // Update in local contexts array
      const index = contexts.value.findIndex(context => context.id === id)
      if (index !== -1) {
        contexts.value[index] = updatedContext
      }

      // Update current context if it matches
      if (currentContext.value?.id === id) {
        currentContext.value = updatedContext
      }

      return updatedContext
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to update context ${id}`)
      error.value = 'Failed to update context'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete a context
  const deleteContext = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.contextDestroy(id)

      // Remove from local contexts array
      contexts.value = contexts.value.filter(context => context.id !== id)

      // Clear current context if it matches
      if (currentContext.value?.id === id) {
        currentContext.value = null
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to delete context ${id}`)
      error.value = 'Failed to delete context'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Set a context as default
  const setDefaultContext = async (id: string, isDefault: boolean) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.contextSetDefault(id, { is_default: isDefault })
      const updatedContext = response.data.data

      // Update the default status for all contexts
      if (isDefault) {
        // Setting as default: set target to true, all others to false
        contexts.value = contexts.value.map(context => ({
          ...context,
          is_default: context.id === id ? true : false,
        }))
      } else {
        // Unsetting as default: only update the target context
        contexts.value = contexts.value.map(context =>
          context.id === id ? { ...context, is_default: false } : context
        )
      }

      // Update current context if it matches
      if (currentContext.value?.id === id) {
        currentContext.value = updatedContext
      }

      return updatedContext
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to set default context ${id}`)
      error.value = 'Failed to set default context'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get the default context
  const getDefaultContext = async () => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.contextGetDefault()
      const defaultCtx = response.data.data

      // Update the default context in the contexts array if it exists
      const index = contexts.value.findIndex(context => context.id === defaultCtx.id)
      if (index !== -1) {
        contexts.value[index] = defaultCtx
      } else {
        // If the default context isn't in our contexts array, add it
        contexts.value.push(defaultCtx)
      }

      return defaultCtx
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to get default context')
      error.value = 'Failed to get default context'
      throw err
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  const clearCurrentContext = () => {
    currentContext.value = null
  }

  return {
    contexts,
    currentContext,
    loading,
    error,
    page,
    perPage,
    total,
    defaultContext,
    defaultContexts,
    fetchContexts,
    fetchContext,
    createContext,
    updateContext,
    deleteContext,
    setDefaultContext,
    getDefaultContext,
    clearError,
    clearCurrentContext,
  }
})
