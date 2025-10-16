import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  type ItemImageResource,
  type StoreItemImageRequest,
  type UpdateItemImageRequest,
  type AttachFromAvailableItemImageRequest,
} from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'
import { ErrorHandler } from '@/utils/errorHandler'

export const useItemImageStore = defineStore('itemImage', () => {
  const itemImages = ref<ItemImageResource[]>([])
  const currentItemImage = ref<ItemImageResource | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createItemImageApi()
  }

  // Fetch all images for a specific item
  const fetchItemImages = async (itemId: string, includes?: string[]) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const includeParam = includes?.join(',')
      const response = await apiClient.itemImagesIndex(itemId, includeParam)
      console.log('[itemImageStore] Raw response:', response.data)

      // ResourceCollection returns { data: [...] }
      const responseData = response.data as { data?: unknown } | unknown[]
      const data = Array.isArray(responseData)
        ? responseData
        : responseData &&
            typeof responseData === 'object' &&
            'data' in responseData &&
            Array.isArray(responseData.data)
          ? responseData.data
          : []

      console.log('[itemImageStore] Parsed images:', data.length, data)
      itemImages.value = data
    } catch (err: unknown) {
      console.error('[itemImageStore] Error fetching images:', err)
      ErrorHandler.handleError(err, 'Failed to fetch item images')
      error.value = 'Failed to fetch item images'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Fetch a single item image by ID
  const fetchItemImage = async (id: string, includes?: string[]) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const includeParam = includes?.join(',')
      const response = await apiClient.itemImageShow(id, includeParam)
      currentItemImage.value = response.data.data
      return response.data.data
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to fetch item image ${id}`)
      error.value = 'Failed to fetch item image'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Create a new item image (direct upload - rarely used in favor of attach)
  const createItemImage = async (itemId: string, data: StoreItemImageRequest) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.itemImagesStore(itemId, data)

      // Refetch the list to get the new image
      await fetchItemImages(itemId)

      return true
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to create item image')
      error.value = 'Failed to create item image'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Attach an available image to an item (primary way to add images)
  const attachImageToItem = async (itemId: string, availableImageId: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const request: AttachFromAvailableItemImageRequest = {
        available_image_id: availableImageId,
      }
      console.log('[itemImageStore] Attaching image:', { itemId, availableImageId })
      const response = await apiClient.itemAttachImage(itemId, request)
      console.log('[itemImageStore] Attach response:', response.data)

      // Refetch the list to get the new image
      await fetchItemImages(itemId)
      console.log('[itemImageStore] Images after attach:', itemImages.value.length)

      return true
    } catch (err: unknown) {
      console.error('[itemImageStore] Error attaching image:', err)
      ErrorHandler.handleError(err, 'Failed to attach image to item')
      error.value = 'Failed to attach image to item'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update an existing item image (mainly for alt_text)
  const updateItemImage = async (id: string, data: UpdateItemImageRequest) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.itemImageUpdate(id, data)

      // Refetch to get updated data
      await fetchItemImage(id)

      // Update in local array if it exists
      const index = itemImages.value.findIndex(img => img.id === id)
      if (index !== -1 && currentItemImage.value) {
        itemImages.value[index] = currentItemImage.value
      }

      return currentItemImage.value
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to update item image')
      error.value = 'Failed to update item image'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Move image up in display order
  const moveImageUp = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.itemImageMoveUp(id)
      const updatedImage = response.data.data

      // Refetch the list to get correct ordering
      if (updatedImage.item_id) {
        await fetchItemImages(updatedImage.item_id)
      }

      return updatedImage
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to move image up')
      error.value = 'Failed to move image up'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Move image down in display order
  const moveImageDown = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.itemImageMoveDown(id)
      const updatedImage = response.data.data

      // Refetch the list to get correct ordering
      if (updatedImage.item_id) {
        await fetchItemImages(updatedImage.item_id)
      }

      return updatedImage
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to move image down')
      error.value = 'Failed to move image down'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Tighten ordering for all images of an item
  const tightenOrdering = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.itemImageTightenOrdering(id)

      // Refetch to get updated ordering
      const itemImage = itemImages.value.find(img => img.id === id)
      if (itemImage?.item_id) {
        await fetchItemImages(itemImage.item_id)
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to tighten image ordering')
      error.value = 'Failed to tighten image ordering'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Detach item image and move back to available images
  const detachImageFromItem = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.itemImageDetach(id)

      // Remove from local array
      itemImages.value = itemImages.value.filter(img => img.id !== id)

      // Clear current if it's the one being detached
      if (currentItemImage.value?.id === id) {
        currentItemImage.value = null
      }

      return response.data
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to detach image from item')
      error.value = 'Failed to detach image from item'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete an item image permanently
  const deleteItemImage = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.itemImageDestroy(id)

      // Remove from local array
      itemImages.value = itemImages.value.filter(img => img.id !== id)

      // Clear current if it's the one being deleted
      if (currentItemImage.value?.id === id) {
        currentItemImage.value = null
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to delete item image')
      error.value = 'Failed to delete item image'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Reset store state
  const reset = () => {
    itemImages.value = []
    currentItemImage.value = null
    loading.value = false
    error.value = null
  }

  return {
    // State
    itemImages: computed(() => itemImages.value),
    currentItemImage: computed(() => currentItemImage.value),
    loading: computed(() => loading.value),
    error: computed(() => error.value),

    // Actions
    fetchItemImages,
    fetchItemImage,
    createItemImage,
    attachImageToItem,
    updateItemImage,
    moveImageUp,
    moveImageDown,
    tightenOrdering,
    detachImageFromItem,
    deleteItemImage,
    reset,
  }
})
