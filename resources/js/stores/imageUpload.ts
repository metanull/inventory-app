import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { type ImageUploadResource } from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'
import {
  type IndexQueryOptions,
  buildPagination,
  mergeParams,
  type PaginationMeta,
  extractPaginationMeta,
} from '@/utils/apiQueryParams'

// Upload status interface
export interface UploadStatus {
  id: string
  file: File
  progress: number
  status: 'pending' | 'uploading' | 'processing' | 'completed' | 'error'
  error?: string
  uploadedResource?: ImageUploadResource
}

// Upload status response interface
export interface UploadStatusResponse {
  status: string
  available_image?: unknown
  error?: string
}

export const useImageUploadStore = defineStore('imageUpload', () => {
  // State
  const uploads = ref<ImageUploadResource[]>([])
  const currentUpload = ref<ImageUploadResource | null>(null)
  const loading = ref(false)
  const page = ref(1)
  const perPage = ref(20)
  const total = ref<number | null>(null)

  // Upload tracking state
  const activeUploads = ref<Map<string, UploadStatus>>(new Map())
  const uploadQueue = ref<File[]>([])
  const isUploading = ref(false)

  // Monitor state
  let processingMonitorInterval: ReturnType<typeof setInterval> | null = null

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createImageUploadApi()
  }

  // Computed getters
  const activeUploadsList = computed(() => Array.from(activeUploads.value.values()))
  const processingUploads = computed(() =>
    activeUploadsList.value.filter(upload => upload.status === 'processing')
  )
  const uploadingCount = computed(
    () => activeUploadsList.value.filter(upload => upload.status === 'uploading').length
  )
  const totalPendingCount = computed(
    () =>
      activeUploadsList.value.filter(
        upload =>
          upload.status === 'pending' ||
          upload.status === 'uploading' ||
          upload.status === 'processing'
      ).length
  )

  // Clear current upload
  const clearCurrentUpload = () => {
    currentUpload.value = null
  }

  // Fetch all uploads
  const fetchUploads = async ({
    page: p = 1,
    perPage: pp = 20,
  }: IndexQueryOptions = {}): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const params = mergeParams(buildPagination(p, pp))
      const response = await apiClient.imageUploadIndex({ params })
      const data = response.data?.data ?? []
      const meta: PaginationMeta | undefined = extractPaginationMeta(response.data)
      uploads.value = data

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

  // Fetch single upload by ID
  const fetchUpload = async (uploadId: string): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.imageUploadShow(uploadId)
      currentUpload.value = response.data.data || null
    } finally {
      loading.value = false
    }
  }

  // Check upload status (for tracking processing)
  const checkUploadStatus = async (uploadId: string): Promise<UploadStatusResponse | null> => {
    try {
      const apiClient = createApiClient()
      const response = await apiClient.imageUploadStatus(uploadId)
      console.debug(`Upload status for ${uploadId}:`, response.data)
      return response.data
    } catch (error) {
      console.error(`Error checking upload status for ${uploadId}:`, error)
      // Return a status indicating the check failed, not null
      return {
        status: 'check_failed',
        error: error instanceof Error ? error.message : 'Status check failed',
      }
    }
  }

  // Upload a single file
  const uploadFile = async (
    file: File,
    onProgress?: (progress: number) => void
  ): Promise<ImageUploadResource | null> => {
    const uploadId = Math.random().toString(36).substr(2, 9)

    // Track upload status
    const uploadStatus: UploadStatus = {
      id: uploadId,
      file,
      progress: 0,
      status: 'uploading',
    }
    activeUploads.value.set(uploadId, uploadStatus)

    try {
      const apiClient = createApiClient()

      // Create progress handler
      const axiosConfig = {
        onUploadProgress: (progressEvent: { loaded: number; total?: number }) => {
          if (progressEvent.total) {
            const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total)
            uploadStatus.progress = progress
            activeUploads.value.set(uploadId, { ...uploadStatus })
            onProgress?.(progress)
          }
        },
      }

      const response = await apiClient.imageUploadStore(file, axiosConfig)
      const uploadedResource = response.data.data as ImageUploadResource

      // Update status to processing
      uploadStatus.status = 'processing'
      uploadStatus.uploadedResource = uploadedResource
      activeUploads.value.set(uploadId, { ...uploadStatus })

      // Add to uploads list
      uploads.value.unshift(uploadedResource)

      // Ensure processing monitor is running
      ensureProcessingMonitor()

      return uploadedResource
    } catch (error) {
      // Update status to error
      uploadStatus.status = 'error'
      uploadStatus.error = error instanceof Error ? error.message : 'Upload failed'
      activeUploads.value.set(uploadId, { ...uploadStatus })
      console.error('Upload failed:', error)
      return null
    }
  }

  // Upload multiple files
  const uploadFiles = async (
    files: File[],
    onProgress?: (overall: number) => void
  ): Promise<void> => {
    isUploading.value = true
    const fileArray = Array.from(files)
    let completed = 0

    try {
      // Upload files sequentially to avoid overwhelming the server
      for (const file of fileArray) {
        await uploadFile(file, progress => {
          // Calculate overall progress
          const overallProgress = Math.round(
            ((completed + progress / 100) / fileArray.length) * 100
          )
          onProgress?.(overallProgress)
        })
        completed++
      }
    } finally {
      isUploading.value = false
    }
  }

  // Add files to upload queue
  const queueFiles = (files: File[]) => {
    uploadQueue.value.push(...files)
  }

  // Process upload queue
  const processUploadQueue = async () => {
    if (uploadQueue.value.length === 0 || isUploading.value) return

    const filesToUpload = [...uploadQueue.value]
    uploadQueue.value = []
    await uploadFiles(filesToUpload)
  }

  // Remove completed or errored uploads from tracking
  const clearCompletedUploads = () => {
    for (const [id, upload] of activeUploads.value.entries()) {
      if (upload.status === 'completed' || upload.status === 'error') {
        activeUploads.value.delete(id)
      }
    }
  }

  // Delete an upload
  const deleteUpload = async (uploadId: string): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      await apiClient.imageUploadDestroy(uploadId)

      // Remove from local state
      uploads.value = uploads.value.filter((u: ImageUploadResource) => u.id !== uploadId)

      if (currentUpload.value?.id === uploadId) {
        currentUpload.value = null
      }

      // Remove from active uploads tracking
      for (const [id, upload] of activeUploads.value.entries()) {
        if (upload.uploadedResource?.id === uploadId) {
          activeUploads.value.delete(id)
          break
        }
      }
    } finally {
      loading.value = false
    }
  }

  // Ensure processing monitor is running when needed
  const ensureProcessingMonitor = () => {
    const hasProcessingUploads = processingUploads.value.length > 0

    if (hasProcessingUploads && !processingMonitorInterval) {
      console.debug('Starting processing monitor')
      processingMonitorInterval = setInterval(async () => {
        const processingIds = processingUploads.value
          .map(upload => upload.uploadedResource?.id)
          .filter(Boolean)

        console.debug('Processing monitor check:', {
          processingCount: processingIds.length,
          ids: processingIds,
        })

        if (processingIds.length === 0) {
          console.debug('No processing uploads found, stopping monitor')
          if (processingMonitorInterval) {
            clearInterval(processingMonitorInterval)
            processingMonitorInterval = null
          }
          return
        }

        for (const id of processingIds) {
          if (id) {
            const status = await checkUploadStatus(id)
            if (status) {
              // Find the corresponding upload in our tracking
              for (const [uploadId, upload] of activeUploads.value.entries()) {
                if (upload.uploadedResource?.id === id) {
                  console.debug(`Updating status for upload ${uploadId}:`, {
                    previousStatus: upload.status,
                    newStatusResponse: status,
                  })

                  if (status.status === 'processing') {
                    // Still processing, no change needed
                    console.debug(`Upload ${uploadId} still processing`)
                  } else if (status.status === 'processed' && status.available_image) {
                    // Processing completed successfully
                    console.debug(`Upload ${uploadId} completed successfully`)
                    upload.status = 'completed'
                    activeUploads.value.set(uploadId, { ...upload })
                  } else if (status.status === 'check_failed') {
                    // API call failed, but don't mark as error - might be temporary
                    console.warn(`Status check failed for upload ${uploadId}, will retry`)
                  } else if (status.status === 'not_found') {
                    // Neither ImageUpload nor AvailableImage exists - this might be an error or timing issue
                    console.warn(`Upload ${uploadId} not found in API, marking as error`)
                    upload.status = 'error'
                    upload.error = 'Upload not found in system'
                    activeUploads.value.set(uploadId, { ...upload })
                  } else {
                    // Unknown status or processing failed
                    console.warn(`Upload ${uploadId} has unknown status:`, status)
                    upload.status = 'error'
                    upload.error = status.error || 'Processing failed'
                    activeUploads.value.set(uploadId, { ...upload })
                  }
                  break
                }
              }
            } else {
              // This should not happen with improved error handling, but just in case
              console.error(`No status response for upload ${id}`)
            }
          }
        }
      }, 2000) // Check every 2 seconds
    } else if (!hasProcessingUploads && processingMonitorInterval) {
      console.debug('No processing uploads, stopping monitor')
      clearInterval(processingMonitorInterval)
      processingMonitorInterval = null
    }
  }

  // Periodically check processing status (legacy function for backward compatibility)
  const startProcessingMonitor = () => {
    ensureProcessingMonitor()

    // Return cleanup function
    return () => {
      console.debug('Stopping processing monitor via cleanup')
      if (processingMonitorInterval) {
        clearInterval(processingMonitorInterval)
        processingMonitorInterval = null
      }
    }
  }

  return {
    // State
    uploads,
    currentUpload,
    loading,
    page,
    perPage,
    total,
    activeUploads: activeUploadsList,
    uploadQueue,
    isUploading,

    // Computed
    processingUploads,
    uploadingCount,
    totalPendingCount,

    // Actions
    clearCurrentUpload,
    fetchUploads,
    fetchUpload,
    checkUploadStatus,
    uploadFile,
    uploadFiles,
    queueFiles,
    processUploadQueue,
    clearCompletedUploads,
    deleteUpload,
    startProcessingMonitor,
    ensureProcessingMonitor,
  }
})
