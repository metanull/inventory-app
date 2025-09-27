import { defineStore } from 'pinia'
import { ref } from 'vue'
import { type ItemResource, type ItemStoreRequest } from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'
import {
  type IndexQueryOptions,
  type ShowQueryOptions,
  type PaginationMeta,
  extractPaginationMeta,
} from '@/utils/apiQueryParams'

export const useItemStore = defineStore('item', () => {
  // State
  const items = ref<ItemResource[]>([])
  const currentItem = ref<ItemResource | null>(null)
  const loading = ref(false)
  const page = ref(1)
  const perPage = ref(20)
  const total = ref<number | null>(null)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createItemApi()
  }

  // Clear current item
  const clearCurrentItem = () => {
    currentItem.value = null
  }

  // Fetch all items
  const fetchItems = async ({
    include = [],
    page: p = 1,
    perPage: pp = 20,
  }: IndexQueryOptions = {}): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const includeStr = include.length > 0 ? include.join(',') : undefined
      const response = await apiClient.itemIndex(p, pp, includeStr)
      const data = response.data?.data ?? []
      const meta: PaginationMeta | undefined = extractPaginationMeta(response.data)
      items.value = data
      // Update pagination state if meta is present
      if (meta) {
        total.value = typeof meta.total === 'number' ? meta.total : total.value
        page.value = typeof meta.current_page === 'number' ? meta.current_page : p
        perPage.value = typeof meta.per_page === 'number' ? meta.per_page : pp
      } else {
        // Fallback to requested values
        page.value = p
        perPage.value = pp
      }
    } finally {
      loading.value = false
    }
  }

  // Fetch single item by ID
  const fetchItem = async (
    itemId: string,
    { include = [] }: ShowQueryOptions = {}
  ): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const includeStr = include.length > 0 ? include.join(',') : undefined
      const response = await apiClient.itemShow(itemId, includeStr)
      currentItem.value = response.data.data || null
    } finally {
      loading.value = false
    }
  }

  // Create new item
  const createItem = async (
    itemData: ItemStoreRequest,
    options: ShowQueryOptions = {}
  ): Promise<ItemResource> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.itemStore(itemData)
      const newItem = response.data.data

      if (newItem) {
        // Add to items list if not already present
        const existingIndex = items.value.findIndex((item: ItemResource) => item.id === newItem.id)
        if (existingIndex === -1) {
          items.value.unshift(newItem)
        }
        // Reload current item with requested includes (if any)
        await fetchItem(newItem.id, options)
      }

      return newItem
    } finally {
      loading.value = false
    }
  }

  // Update existing item
  const updateItem = async (
    itemId: string,
    itemData: ItemStoreRequest,
    options: ShowQueryOptions = {}
  ): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.itemUpdate(itemId, itemData)
      const updatedItem = response.data.data

      if (updatedItem) {
        // Update in items list
        const index = items.value.findIndex((item: ItemResource) => item.id === itemId)
        if (index !== -1) {
          items.value[index] = updatedItem
        }
        // Reload current item with requested includes (if currently selected)
        if (currentItem.value?.id === itemId) await fetchItem(itemId, options)
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
      const index = items.value.findIndex((item: ItemResource) => item.id === itemId)
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
    page,
    perPage,
    total,

    // Actions
    clearCurrentItem,
    fetchItems,
    fetchItem,
    createItem,
    updateItem,
    deleteItem,
  }
})
