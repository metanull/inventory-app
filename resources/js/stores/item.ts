import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
  ItemApi,
  Configuration,
  type ItemResource,
  type ItemStoreRequest,
} from '@metanull/inventory-app-api-client'
import { useAuthStore } from './auth'

declare const process: {
  env: Record<string, string | undefined>
}

export const useItemStore = defineStore('item', () => {
  // State
  const items = ref<ItemResource[]>([])
  const currentItem = ref<ItemResource | null>(null)
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

    return new ItemApi(configuration)
  }

  // Clear current item
  const clearCurrentItem = () => {
    currentItem.value = null
  }

  // Fetch all items
  const fetchItems = async (): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.itemIndex()
      items.value = response.data.data || []
    } finally {
      loading.value = false
    }
  }

  // Fetch single item by ID
  const fetchItem = async (itemId: string): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.itemShow(itemId)
      currentItem.value = response.data.data || null
    } finally {
      loading.value = false
    }
  }

  // Create new item
  const createItem = async (itemData: ItemStoreRequest): Promise<ItemResource> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.itemStore(itemData)
      const newItem = response.data.data

      if (newItem) {
        // Add to items list if not already present
        const existingIndex = items.value.findIndex(item => item.id === newItem.id)
        if (existingIndex === -1) {
          items.value.unshift(newItem)
        }
        // Set as current item
        currentItem.value = newItem
      }

      return newItem
    } finally {
      loading.value = false
    }
  }

  // Update existing item
  const updateItem = async (itemId: string, itemData: ItemStoreRequest): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.itemUpdate(itemId, itemData)
      const updatedItem = response.data.data

      if (updatedItem) {
        // Update in items list
        const index = items.value.findIndex(item => item.id === itemId)
        if (index !== -1) {
          items.value[index] = updatedItem
        }
        // Update current item if it's the one being edited
        if (currentItem.value?.id === itemId) {
          currentItem.value = updatedItem
        }
      }
    } finally {
      loading.value = false
    }
  }

  // Delete item
  const deleteItem = async (itemId: string): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      await apiClient.itemDestroy(itemId)

      // Remove from items list
      const index = items.value.findIndex(item => item.id === itemId)
      if (index !== -1) {
        items.value.splice(index, 1)
      }
      // Clear current item if it's the one being deleted
      if (currentItem.value?.id === itemId) {
        currentItem.value = null
      }
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    items,
    currentItem,
    loading,

    // Actions
    clearCurrentItem,
    fetchItems,
    fetchItem,
    createItem,
    updateItem,
    deleteItem,
  }
})
