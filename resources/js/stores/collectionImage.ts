import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  type CollectionImageResource,
  type StoreCollectionImageRequest,
  type UpdateCollectionImageRequest,
  type AttachFromAvailableCollectionImageRequest,
} from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'
import { ErrorHandler } from '@/utils/errorHandler'

export const useCollectionImageStore = defineStore('collectionImage', () => {
  const collectionImages = ref<CollectionImageResource[]>([])
  const currentCollectionImage = ref<CollectionImageResource | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Blob URL cache for images (to avoid re-fetching)
  const imageBlobUrls = ref<Map<string, string>>(new Map())

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createCollectionImageApi()
  }

  // Fetch all images for a specific collection
  const fetchCollectionImages = async (collectionId: string, includes?: string[]) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const includeParam = includes?.join(',')
      const response = await apiClient.collectionImagesIndex(collectionId, includeParam)

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

      collectionImages.value = data
    } catch (err: unknown) {
      console.error('[collectionImageStore] Error fetching images:', err)
      ErrorHandler.handleError(err, 'Failed to fetch collection images')
      error.value = 'Failed to fetch collection images'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Fetch a single collection image by ID
  const fetchCollectionImage = async (id: string, includes?: string[]) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const includeParam = includes?.join(',')
      const response = await apiClient.collectionImageShow(id, includeParam)
      currentCollectionImage.value = response.data.data
      return response.data.data
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to fetch collection image ${id}`)
      error.value = 'Failed to fetch collection image'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Create a new collection image (direct upload - rarely used in favor of attach)
  const createCollectionImage = async (collectionId: string, data: StoreCollectionImageRequest) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.collectionImagesStore(collectionId, data)

      // Refetch the list to get the new image
      await fetchCollectionImages(collectionId)

      return true
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to create collection image')
      error.value = 'Failed to create collection image'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Attach an available image to a collection (primary way to add images)
  const attachImageToCollection = async (collectionId: string, availableImageId: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const request: AttachFromAvailableCollectionImageRequest = {
        available_image_id: availableImageId,
      }
      await apiClient.collectionAttachImage(collectionId, request)

      // Refetch the list to get the new image
      await fetchCollectionImages(collectionId)

      return true
    } catch (err: unknown) {
      console.error('[collectionImageStore] Error attaching image:', err)
      ErrorHandler.handleError(err, 'Failed to attach image to collection')
      error.value = 'Failed to attach image to collection'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update an existing collection image (mainly for alt_text)
  const updateCollectionImage = async (id: string, data: UpdateCollectionImageRequest) => {
    loading.value = false // Don't show global loading for inline edits
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.collectionImageUpdate(id, data)

      // Update in local array directly (optimistic update for better UX)
      const index = collectionImages.value.findIndex(img => img.id === id)
      if (index !== -1) {
        // Update only the changed fields, keeping the rest of the object
        const existingImage = collectionImages.value[index]
        collectionImages.value[index] = {
          ...existingImage,
          ...data,
        } as CollectionImageResource
      }

      return collectionImages.value[index]
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to update collection image')
      error.value = 'Failed to update collection image'
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
      const response = await apiClient.collectionImageMoveUp(id)
      const updatedImage = response.data.data

      // Refetch the list to get correct ordering
      if (updatedImage.collection_id) {
        await fetchCollectionImages(updatedImage.collection_id)
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
      const response = await apiClient.collectionImageMoveDown(id)
      const updatedImage = response.data.data

      // Refetch the list to get correct ordering
      if (updatedImage.collection_id) {
        await fetchCollectionImages(updatedImage.collection_id)
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

  // Tighten ordering for all images of a collection
  const tightenOrdering = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.collectionImageTightenOrdering(id)

      // Refetch to get updated ordering
      const collectionImage = collectionImages.value.find(img => img.id === id)
      if (collectionImage?.collection_id) {
        await fetchCollectionImages(collectionImage.collection_id)
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to tighten image ordering')
      error.value = 'Failed to tighten image ordering'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Detach collection image and move back to available images
  const detachImageFromCollection = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.collectionImageDetach(id)

      // Remove from local array
      collectionImages.value = collectionImages.value.filter(img => img.id !== id)

      // Clear current if it's the one being detached
      if (currentCollectionImage.value?.id === id) {
        currentCollectionImage.value = null
      }

      return response.data
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to detach image from collection')
      error.value = 'Failed to detach image from collection'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete a collection image permanently
  const deleteCollectionImage = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.collectionImageDestroy(id)

      // Remove from local array
      collectionImages.value = collectionImages.value.filter(img => img.id !== id)

      // Clear current if it's the one being deleted
      if (currentCollectionImage.value?.id === id) {
        currentCollectionImage.value = null
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to delete collection image')
      error.value = 'Failed to delete collection image'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Reset store state
  const reset = () => {
    collectionImages.value = []
    currentCollectionImage.value = null
    loading.value = false
    error.value = null
    // Clean up blob URLs to avoid memory leaks
    imageBlobUrls.value.forEach(url => URL.revokeObjectURL(url))
    imageBlobUrls.value.clear()
  }

  // Get image URL for display (fetches as blob to support authentication)
  const getImageUrl = async (collectionImage: CollectionImageResource): Promise<string> => {
    // Check if we already have a blob URL cached
    const cached = imageBlobUrls.value.get(collectionImage.id)
    if (cached) {
      return cached
    }

    try {
      const apiClient = createApiClient()
      // Fetch the image data as a blob using the view endpoint
      // This sends authentication headers which <img> tags cannot do
      const response = await apiClient.collectionImageView(collectionImage.id, {
        responseType: 'blob',
      })

      // Create a blob URL
      const blob = new Blob([response.data as Blob], {
        type: collectionImage.mime_type || 'image/jpeg',
      })
      const blobUrl = URL.createObjectURL(blob)

      // Cache the blob URL
      imageBlobUrls.value.set(collectionImage.id, blobUrl)

      return blobUrl
    } catch (error) {
      console.error('Failed to load collection image:', error)
      // Return a fallback/placeholder image URL
      return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTMgMTZWOEMzIDYuMzQzMTUgNC4zNDMxNSA1IDYgNUgxOEMxOS42NTY5IDUgMjEgNi4zNDMxNSAyMSA4VjE2QzIxIDE3LjY1NjkgMTkuNjU2OSAxOSAxOCAxOUg2QzQuMzQzMTUgMTkgMyAxNy42NTY5IDMgMTZaIiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiIvPgo8cGF0aCBkPSJNOSAxMEMxMC4xMDQ2IDEwIDExIDkuMTA0NTcgMTEgOEMxMSA2Ljg5NTQzIDEwLjEwNDYgNiA5IDZDNy44OTU0MyA2IDcgNi44OTU0MyA3IDhDNyA5LjEwNDU3IDcuODk1NDMgMTAgOSAxMFoiIGZpbGw9IiM5Q0EzQUYiLz4KPHBhdGggZD0ibTIxIDE1LTMuNS0zLjUtMi41IDIuNS0zLTMtNCA0LjUiIHN0cm9rZT0iIzlDQTNBRiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+Cg=='
    }
  }

  return {
    // State
    collectionImages: computed(() => collectionImages.value),
    currentCollectionImage: computed(() => currentCollectionImage.value),
    loading: computed(() => loading.value),
    error: computed(() => error.value),

    // Actions
    fetchCollectionImages,
    fetchCollectionImage,
    createCollectionImage,
    attachImageToCollection,
    updateCollectionImage,
    moveImageUp,
    moveImageDown,
    tightenOrdering,
    detachImageFromCollection,
    deleteCollectionImage,
    getImageUrl,
    reset,
  }
})
