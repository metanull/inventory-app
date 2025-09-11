import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
  type CollectionResource,
  type CollectionStoreRequest,
} from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'

export const useCollectionStore = defineStore('collection', () => {
  // State
  const collections = ref<CollectionResource[]>([])
  const currentCollection = ref<CollectionResource | null>(null)
  const loading = ref(false)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createCollectionApi()
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
