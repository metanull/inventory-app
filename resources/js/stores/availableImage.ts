import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
  type AvailableImageResource,
  type UpdateAvailableImageRequest,
} from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'
import {
  type IndexQueryOptions,
  type ShowQueryOptions,
  type PaginationMeta,
  extractPaginationMeta,
} from '@/utils/apiQueryParams'

export const useAvailableImageStore = defineStore('availableImage', () => {
  // State
  const availableImages = ref<AvailableImageResource[]>([])
  const currentAvailableImage = ref<AvailableImageResource | null>(null)
  const loading = ref(false)
  const page = ref(1)
  const perPage = ref(20)
  const total = ref<number | null>(null)

  // Image blob URL cache
  const imageBlobUrls = ref<Map<string, string>>(new Map())

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createAvailableImageApi()
  }

  // Clear current available image
  const clearCurrentAvailableImage = () => {
    currentAvailableImage.value = null
  }

  // Fetch all available images
  const fetchAvailableImages = async ({
    page: p = 1,
    perPage: pp = 20,
  }: IndexQueryOptions = {}): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.availableImageIndex(p, pp)
      const data = response.data?.data ?? []
      const meta: PaginationMeta | undefined = extractPaginationMeta(response.data)
      availableImages.value = data

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
    } catch (error) {
      console.error('AvailableImage Store: Error in fetchAvailableImages:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  // Fetch single available image by ID
  const fetchAvailableImage = async (
    availableImageId: string,
    _options: ShowQueryOptions = {}
  ): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.availableImageShow(availableImageId)
      currentAvailableImage.value = response.data.data || null
    } finally {
      loading.value = false
    }
  }

  // Update an existing available image
  const updateAvailableImage = async (
    availableImageId: string,
    availableImageData: UpdateAvailableImageRequest,
    options: ShowQueryOptions = {}
  ): Promise<AvailableImageResource> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.availableImageUpdate(availableImageId, availableImageData)
      const updatedAvailableImage = response.data.data as AvailableImageResource

      // Update local state
      const index = availableImages.value.findIndex(
        (img: AvailableImageResource) => img.id === availableImageId
      )
      if (index !== -1) {
        availableImages.value[index] = updatedAvailableImage
      }

      if (currentAvailableImage.value?.id === availableImageId) {
        await fetchAvailableImage(availableImageId, options)
      }

      return updatedAvailableImage
    } finally {
      loading.value = false
    }
  }

  // Delete an available image
  const deleteAvailableImage = async (availableImageId: string): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      await apiClient.availableImageDestroy(availableImageId)

      // Remove from local state
      availableImages.value = availableImages.value.filter(
        (img: AvailableImageResource) => img.id !== availableImageId
      )

      if (currentAvailableImage.value?.id === availableImageId) {
        currentAvailableImage.value = null
      }
    } finally {
      loading.value = false
    }
  }

  // Get image URL for display
  const getImageUrl = async (availableImage: AvailableImageResource): Promise<string> => {
    // Check if we already have a blob URL cached
    const cached = imageBlobUrls.value.get(availableImage.id)
    if (cached) {
      return cached
    }

    try {
      const apiClient = createApiClient()
      // Fetch the image data as a blob
      const response = await apiClient.availableImageView(availableImage.id, {
        responseType: 'blob',
      })

      // Create a blob URL
      const blob = new Blob([response.data as Blob], { type: 'image/jpeg' }) // Adjust content type as needed
      const blobUrl = URL.createObjectURL(blob)

      // Cache the blob URL
      imageBlobUrls.value.set(availableImage.id, blobUrl)

      return blobUrl
    } catch (error) {
      console.error('Failed to load image:', error)
      // Return a fallback/placeholder image URL
      return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTMgMTZWOEMzIDYuMzQzMTUgNC4zNDMxNSA1IDYgNUgxOEMxOS42NTY5IDUgMjEgNi4zNDMxNSAyMSA4VjE2QzIxIDE3LjY1NjkgMTkuNjU2OSAxOSAxOCAxOUg2QzQuMzQzMTUgMTkgMyAxNy42NTY5IDMgMTZaIiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiIvPgo8cGF0aCBkPSJNOSAxMEMxMC4xMDQ2IDEwIDExIDkuMTA0NTcgMTEgOEMxMSA2Ljg5NTQzIDEwLjEwNDYgNiA5IDZDNy44OTU0MyA2IDcgNi44OTU0MyA3IDhDNyA5LjEwNDU3IDcuODk1NDMgMTAgOSAxMFoiIGZpbGw9IiM5Q0EzQUYiLz4KPHBhdGggZD0ibTIxIDE1LTMuNS0zLjUtMi41IDIuNS0zLTMtNCA0LjUiIHN0cm9rZT0iIzlDQTNBRiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+Cg=='
    }
  }

  // Get image download URL
  const getImageDownloadUrl = (availableImage: AvailableImageResource): string => {
    const baseUrl = window.location.origin
    return `${baseUrl}/api/available-image/${availableImage.id}/download`
  }

  return {
    // State
    availableImages,
    currentAvailableImage,
    loading,
    page,
    perPage,
    total,

    // Actions
    clearCurrentAvailableImage,
    fetchAvailableImages,
    fetchAvailableImage,
    updateAvailableImage,
    deleteAvailableImage,
    getImageUrl,
    getImageDownloadUrl,
  }
})
