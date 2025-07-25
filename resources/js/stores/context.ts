import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { ContextApi, Configuration, type ContextResource } from '@metanull/inventory-app-api-client'
import { useAuthStore } from './auth'
import { ErrorHandler } from '@/utils/errorHandler'

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
declare const process: {
  env: Record<string, string | undefined>
}

export const useContextStore = defineStore('context', () => {
  const contexts = ref<ContextResource[]>([])
  const currentContext = ref<ContextResource | null>(null)
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

    return new ContextApi(configuration)
  }

  const defaultContext = computed(() => contexts.value.find(context => context.is_default))
  const defaultContexts = computed(() => contexts.value.filter(context => context.is_default))

  // Fetch all contexts
  const fetchContexts = async () => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.contextIndex()
      contexts.value = response.data.data || []
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
      contexts.value = contexts.value.map(context => ({
        ...context,
        is_default: context.id === id ? isDefault : false,
      }))

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
