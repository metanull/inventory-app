import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
  CollectionApi,
  Configuration,
  type CollectionResource,
  type CollectionStoreRequest,
} from '@metanull/inventory-app-api-client'
import { useAuthStore } from './auth'

declare const process: {
  env: Record<string, string | undefined>
}

export const useCollectionStore = defineStore('collection', () => {
  // State
  const collections = ref<CollectionResource[]>([])
  const currentCollection = ref<CollectionResource | null>(null)
  const loading = ref(false)

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

    return new CollectionApi(configuration)
  }

  // Clear current collection
  const clearCurrentCollection = () => {
    currentCollection.value = null
  }

  // Fetch all collections
  const fetchCollections = async (): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.collectionIndex()
      collections.value = response.data.data || []
    } finally {
      loading.value = false
    }
  }

  // Fetch single collection by ID
  const fetchCollection = async (collectionId: string): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.collectionShow(collectionId)
      currentCollection.value = response.data.data || null
    } finally {
      loading.value = false
    }
  }

  // Create new collection
  const createCollection = async (
    collectionData: CollectionStoreRequest
  ): Promise<CollectionResource> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.collectionStore(collectionData)
      const newCollection = response.data.data

      if (newCollection) {
        // Add to collections list if not already present
        const existingIndex = collections.value.findIndex(
          collection => collection.id === newCollection.id
        )
        if (existingIndex === -1) {
          collections.value.unshift(newCollection)
        }
        // Set as current collection
        currentCollection.value = newCollection
      }

      return newCollection
    } finally {
      loading.value = false
    }
  }

  // Update existing collection
  const updateCollection = async (
    collectionId: string,
    collectionData: CollectionStoreRequest
  ): Promise<CollectionResource> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.collectionUpdate(collectionId, collectionData)
      const updatedCollection = response.data.data as CollectionResource

      // Update local state
      const index = collections.value.findIndex(c => c.id === collectionId)
      if (index !== -1) {
        collections.value[index] = updatedCollection
      }

      if (currentCollection.value?.id === collectionId) {
        currentCollection.value = updatedCollection
      }

      return updatedCollection
    } finally {
      loading.value = false
    }
  }

  // Delete a collection
  const deleteCollection = async (collectionId: string): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      await apiClient.collectionDestroy(collectionId)

      // Remove from local state
      collections.value = collections.value.filter(c => c.id !== collectionId)

      if (currentCollection.value?.id === collectionId) {
        currentCollection.value = null
      }
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    collections,
    currentCollection,
    loading,

    // Actions
    clearCurrentCollection,
    fetchCollections,
    fetchCollection,
    createCollection,
    updateCollection,
    deleteCollection,
  }
})
