import { defineStore } from 'pinia'
import { ref } from 'vue'
import { type ItemResource, type ItemStoreRequest } from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'

export const useItemStore = defineStore('item', () => {
  // State
  const items = ref<ItemResource[]>([])
  const currentItem = ref<ItemResource | null>(null)
  const loading = ref(false)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createItemApi()
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
