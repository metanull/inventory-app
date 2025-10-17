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

  // Blob URL cache for images (to avoid re-fetching)
  const imageBlobUrls = ref<Map<string, string>>(new Map())

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
      await apiClient.itemAttachImage(itemId, request)

      // Refetch the list to get the new image
      await fetchItemImages(itemId)

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
    loading.value = false // Don't show global loading for inline edits
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.itemImageUpdate(id, data)

      // Update in local array directly (optimistic update for better UX)
      const index = itemImages.value.findIndex(img => img.id === id)
      if (index !== -1) {
        // Update only the changed fields, keeping the rest of the object
        const existingImage = itemImages.value[index]
        itemImages.value[index] = {
          ...existingImage,
          ...data,
        } as ItemImageResource
      }

      return itemImages.value[index]
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
    // Clean up blob URLs to avoid memory leaks
    imageBlobUrls.value.forEach(url => URL.revokeObjectURL(url))
    imageBlobUrls.value.clear()
  }

  // Get image URL for display (fetches as blob to support authentication)
  const getImageUrl = async (itemImage: ItemImageResource): Promise<string> => {
    // Check if we already have a blob URL cached
    const cached = imageBlobUrls.value.get(itemImage.id)
    if (cached) {
      return cached
    }

    try {
      const apiClient = createApiClient()
      // Fetch the image data as a blob using the view endpoint
      // This sends authentication headers which <img> tags cannot do
      const response = await apiClient.itemImageView(itemImage.id, {
        responseType: 'blob',
      })

      // Create a blob URL
      const blob = new Blob([response.data as Blob], { type: itemImage.mime_type || 'image/jpeg' })
      const blobUrl = URL.createObjectURL(blob)

      // Cache the blob URL
      imageBlobUrls.value.set(itemImage.id, blobUrl)

      return blobUrl
    } catch (error) {
      console.error('Failed to load item image:', error)
      // Return a fallback/placeholder image URL
      return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTMgMTZWOEMzIDYuMzQzMTUgNC4zNDMxNSA1IDYgNUgxOEMxOS42NTY5IDUgMjEgNi4zNDMxNSAyMSA4VjE2QzIxIDE3LjY1NjkgMTkuNjU2OSAxOSAxOCAxOUg2QzQuMzQzMTUgMTkgMyAxNy42NTY5IDMgMTZaIiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiIvPgo8cGF0aCBkPSJNOSAxMEMxMC4xMDQ2IDEwIDExIDkuMTA0NTcgMTEgOEMxMSA2Ljg5NTQzIDEwLjEMDQ2IDYgOSA2QzcuODk1NDMgNiA3IDYuODk1NDMgNyA4QzcgOS4xMDQ1NyA3Ljg5NTQzIDEwIDkgMTBaIiBmaWxsPSIjOUNBM0FGIi8+CjxwYXRoIGQ9Im0yMSAxNS0zLjUtMy41LTIuNSAyLjUtMy0zLTQgNC41IiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo='
    }
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
    getImageUrl,
    reset,
  }
})
